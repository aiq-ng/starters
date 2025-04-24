-- Functions
CREATE OR REPLACE FUNCTION generate_sku()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.sku IS NULL THEN
        NEW.sku := UPPER(SUBSTRING((SELECT name FROM item_categories WHERE id = NEW.category_id) FROM 1 FOR 3)) || '-' || 
                   UPPER(SUBSTRING(NEW.name FROM 1 FOR 3)) || '-' || 
                   LPAD(NEW.order_sequence::TEXT, 4, '0');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_item_availability()
RETURNS TRIGGER AS $$
DECLARE
    total_quantity DECIMAL(20, 2);
BEGIN
    SELECT COALESCE(SUM(quantity), 0)
    INTO total_quantity
    FROM item_stocks
    WHERE item_id = NEW.item_id;

    UPDATE items
    SET availability = 
        CASE 
            WHEN total_quantity = 0 THEN 'out of stock'
            WHEN total_quantity < threshold_value THEN 'low stock'
            ELSE 'in stock'
        END
    WHERE id = NEW.item_id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION set_payment_due_date()
RETURNS TRIGGER AS $$
DECLARE
    term_name TEXT;
    num_value INTEGER;
BEGIN
    -- Only update payment_due_date if it's NULL
    IF NEW.payment_due_date IS NOT NULL THEN
        RETURN NEW;
    END IF;

    SELECT name INTO term_name 
    FROM payment_terms 
    WHERE id = NEW.payment_term_id;

    term_name := COALESCE(term_name, NEW.payment_term);

    IF term_name ILIKE '%delivery%' THEN
        NEW.payment_due_date := NEW.delivery_date;
    ELSIF term_name ~ '([0-9]+)' THEN
        num_value := regexp_replace(term_name, '[^0-9]', '', 'g')::INTEGER;

        IF term_name ILIKE '%day%' THEN
            NEW.payment_due_date := CURRENT_DATE + num_value;
        ELSIF term_name ILIKE '%week%' THEN
            NEW.payment_due_date := CURRENT_DATE + (num_value * 7);
        ELSIF term_name ILIKE '%month%' THEN
            NEW.payment_due_date := CURRENT_DATE + (num_value * INTERVAL '1 month');
        ELSIF term_name ILIKE '%year%' THEN
            NEW.payment_due_date := CURRENT_DATE + (num_value * INTERVAL '1 year');
        ELSE
            NEW.payment_due_date := NEW.delivery_date;
        END IF;
    ELSE
        NEW.payment_due_date := NEW.delivery_date;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION calculate_purchase_order_items_price()
RETURNS TRIGGER AS $$
DECLARE
    tax_rate DECIMAL(5,2);
BEGIN
    IF NEW.tax_id IS NOT NULL THEN
        SELECT COALESCE(rate, 0) INTO tax_rate FROM taxes 
        WHERE id = NEW.tax_id;
    ELSIF NEW.tax IS NOT NULL THEN
        tax_rate := NEW.tax;
    ELSE
        tax_rate := 0;
    END IF;

    NEW.total := NEW.quantity * NEW.price * (1 + tax_rate / 100);
    NEW.updated_at := clock_timestamp();
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION calculate_sales_order_items_price()
RETURNS TRIGGER AS $$
DECLARE
    tax_rate DECIMAL(5,2);
    item_price DECIMAL(20,2);
BEGIN
    IF NEW.price IS NULL AND NEW.item_id IS NOT NULL THEN
        SELECT unit_price INTO item_price 
        FROM price_lists WHERE id = NEW.item_id;
        
        IF item_price IS NOT NULL THEN
            NEW.price := item_price;
        END IF;
    END IF;

    IF NEW.tax_id IS NOT NULL THEN
        SELECT COALESCE(rate, 0) INTO tax_rate 
        FROM taxes WHERE id = NEW.tax_id;
    ELSIF NEW.tax IS NOT NULL THEN
        tax_rate := NEW.tax;
    ELSE
        tax_rate := 0;
    END IF;

    NEW.total := NEW.quantity * NEW.price * (1 + tax_rate / 100);
    NEW.updated_at := clock_timestamp();
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION update_purchase_order_total()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE purchase_orders
    SET total = COALESCE((
        SELECT SUM(total) 
        FROM purchase_order_items 
        WHERE purchase_order_id = NEW.purchase_order_id
    ), 0) - COALESCE(discount, 0) + COALESCE(shipping_charge, 0)
    WHERE id = NEW.purchase_order_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION update_sales_order_total()
RETURNS TRIGGER AS $$
DECLARE
    total_amount DECIMAL(20,2);
    discount_value DECIMAL(20,2);
    discount_type TEXT;
    discount_applied DECIMAL(20,2);
    v_delivery_charge DECIMAL(20,2);
BEGIN
    -- Calculate total from items
    SELECT COALESCE(SUM(total), 0) 
        INTO total_amount
        FROM sales_order_items 
    WHERE sales_order_id = NEW.sales_order_id;
    RAISE NOTICE 'Total Amount: %', total_amount;

    -- Fetch delivery charge
    SELECT d.amount
        INTO v_delivery_charge    
        FROM delivery_charges d
        JOIN sales_orders so ON so.delivery_charge_id = d.id
    WHERE so.id = NEW.sales_order_id;
    RAISE NOTICE 'Delivery Charge: %', COALESCE(v_delivery_charge, 0);

    -- Fetch discount details
    SELECT d.value, d.discount_type 
        INTO discount_value, discount_type
        FROM discounts d
        JOIN sales_orders so ON so.discount_id = d.id
    WHERE so.id = NEW.sales_order_id;

    -- Calculate discount based on type
    IF discount_type = 'amount' THEN
        discount_applied := discount_value;
    ELSIF discount_type = 'percentage' THEN
        discount_applied := (total_amount * discount_value / 100);
    ELSE
        discount_applied := 0;
    END IF;
    RAISE NOTICE 'Discount Applied: %', discount_applied;

    UPDATE sales_orders
    SET total = total_amount - COALESCE(discount_applied, 0) 
                + COALESCE(v_delivery_charge, 0),
        discount = COALESCE(discount_applied, 0),
        delivery_charge = COALESCE(v_delivery_charge, 0)
    WHERE id = NEW.sales_order_id;
    RAISE NOTICE 'Total: %', total_amount - COALESCE(discount_applied, 0) + COALESCE(v_delivery_charge, 0);

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION sync_vendor_transaction_and_update_balance()
RETURNS TRIGGER AS $$
BEGIN
    -- Ensure transaction is recorded only if purchase_order status is 'paid'
    IF NEW.status = 'paid' THEN
        -- Sync vendor_transactions
        INSERT INTO vendor_transactions (
            vendor_id, transaction_type, payment_method_id, 
            payment_term_id, cash_account_id, amount, 
            reference_number, notes, invoice_sent, created_at
        ) VALUES (
            NEW.vendor_id, 'debit', NEW.payment_method_id, 
            NEW.payment_term_id, NULL, NEW.total, 
            NEW.reference_number, NEW.notes, 
            FALSE, clock_timestamp()
        )
        ON CONFLICT (reference_number) 
        DO UPDATE SET 
            amount = EXCLUDED.amount,
            reference_number = EXCLUDED.reference_number,
            notes = EXCLUDED.notes,
            invoice_sent = EXCLUDED.invoice_sent;
    END IF;

    -- Update vendor balance
    UPDATE vendors
    SET balance = COALESCE((
        SELECT SUM(total) 
        FROM purchase_orders 
        WHERE vendor_id = NEW.vendor_id AND status = 'received'
    ), 0)
    WHERE id = NEW.vendor_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION sync_customer_transaction_and_update_balance()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.payment_status = 'paid' THEN
        INSERT INTO customer_transactions (
            customer_id, transaction_type, payment_method_id, 
            payment_term_id, cash_account_id, amount, 
            reference_number, notes, invoice_sent, created_at
        ) VALUES (
            NEW.customer_id, 'credit', NEW.payment_method_id, 
            NEW.payment_term_id, NULL, NEW.total, 
            NEW.reference_number, NEW.additional_note, 
            FALSE, clock_timestamp()
        )
        ON CONFLICT (reference_number) 
        DO UPDATE SET 
            amount = EXCLUDED.amount,
            notes = EXCLUDED.notes,
            invoice_sent = EXCLUDED.invoice_sent;
    END IF;

    UPDATE customers
    SET balance = COALESCE((
        SELECT SUM(total) 
        FROM sales_orders 
        WHERE customer_id = NEW.customer_id 
          AND payment_status = 'unpaid' AND status = 'delivered'
    ), 0)
    WHERE id = NEW.customer_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION preserve_tax_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE price_lists
    SET tax = OLD.rate,
        tax_id = NULL
    WHERE tax_id = OLD.id;

    UPDATE purchase_order_items
    SET tax = OLD.rate,
        tax_id = NULL
    WHERE tax_id = OLD.id;

    UPDATE sales_order_items
    SET tax = OLD.rate,
        tax_id = NULL
    WHERE tax_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_payment_term_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE vendors
    SET payment_term = OLD.name,
        payment_term_id = NULL
    WHERE payment_term_id = OLD.id;

    UPDATE customers
    SET payment_term = OLD.name,
        payment_term_id = NULL
    WHERE payment_term_id = OLD.id;

    UPDATE vendor_transactions
    SET payment_term = OLD.name,
        payment_term_id = NULL
    WHERE payment_term_id = OLD.id;
    
    UPDATE customer_transactions
    SET payment_term = OLD.name,
        payment_term_id = NULL
    WHERE payment_term_id = OLD.id;

    UPDATE purchase_orders
    SET payment_term = OLD.name,
        payment_term_id = NULL
    WHERE payment_term_id = OLD.id;

    UPDATE sales_orders
    SET payment_term = OLD.name,
        payment_term_id = NULL
    WHERE payment_term_id = OLD.id;

    UPDATE expenses
    SET payment_term = OLD.name,
        payment_term_id = NULL
    WHERE payment_term_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_payment_method_on_delete()
RETURNS TRIGGER AS $$
BEGIN

    UPDATE vendor_transactions
    SET payment_method = OLD.name,
        payment_method_id = NULL
    WHERE payment_method_id = OLD.id;
    
    UPDATE customer_transactions
    SET payment_method = OLD.name,
        payment_method_id = NULL
    WHERE payment_method_id = OLD.id;

    UPDATE purchase_orders
    SET payment_method = OLD.name,
        payment_method_id = NULL
    WHERE payment_method_id = OLD.id;

    UPDATE sales_orders
    SET payment_method = OLD.name,
        payment_method_id = NULL
    WHERE payment_method_id = OLD.id;

    UPDATE expenses
    SET payment_method = OLD.name,
        payment_method_id = NULL
    WHERE payment_method_id = OLD.id;


    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_vendor_on_delete()
RETURNS TRIGGER AS $$
BEGIN

    UPDATE purchase_orders
    SET vendor = OLD.display_name,
        vendor_id = NULL
    WHERE vendor_id = OLD.id;


    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_customer_name_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE sales_orders
    SET customer = OLD.display_name,
        customer_id = NULL
    WHERE customer_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_item_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE purchase_order_items
    SET item = OLD.name,
        item_id = NULL
    WHERE item_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_item_name_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE sales_order_items
    SET item_name = OLD.item_details,
        item_id = NULL
    WHERE item_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_user_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE item_stock_adjustments
    SET manager = OLD.name,
        manager_id = NULL
    WHERE manager_id = OLD.id;

    UPDATE purchase_orders
    SET manager = OLD.name,
        manager_id = NULL
    WHERE processed_by = OLD.id;

    UPDATE sales_orders
    SET manager = OLD.name,
        manager_id = NULL
    WHERE processed_by = OLD.id;

    UPDATE audit_logs
    SET manager = OLD.name,
        user_id = NULL
    WHERE user_id = OLD.id;

    UPDATE expenses
    SET manager = OLD.name,
        processed_by = NULL
    WHERE processed_by = OLD.id;
    
    UPDATE comments
    SET handler = OLD.name,
        user_id = NULL
    WHERE user_id = OLD.id;

    UPDATE notifications
    SET handler = OLD.name,
        user_id = NULL
    WHERE user_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_department_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE item_stock_adjustments
    SET source_department = OLD.name,
        source_department_id = NULL
    WHERE source_department_id = OLD.id;

    UPDATE expenses
    SET department = OLD.name,
        department_id = NULL
    WHERE department_id = OLD.id;

    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION preserve_branch_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE purchase_orders
    SET branch = OLD.name,
        branch_id = NULL
    WHERE branch_id = OLD.id;

    UPDATE item_stocks
    SET branch = OLD.name,
        branch_id = NULL
    WHERE branch_id = OLD.id;


    RETURN OLD;
END;
$$ LANGUAGE plpgsql;


-- Triggers
CREATE TRIGGER trigger_before_insert_update_items
BEFORE INSERT OR UPDATE ON items
FOR EACH ROW
EXECUTE FUNCTION generate_sku();

CREATE TRIGGER trigger_update_item_availability
AFTER INSERT OR UPDATE OR DELETE ON item_stocks
FOR EACH ROW
EXECUTE FUNCTION update_item_availability();

CREATE TRIGGER trigger_set_payment_due_date
BEFORE INSERT OR UPDATE ON purchase_orders
FOR EACH ROW
EXECUTE FUNCTION set_payment_due_date();

CREATE TRIGGER trigger_calculate_purchase_order_items_price
BEFORE INSERT OR UPDATE ON purchase_order_items
FOR EACH ROW EXECUTE FUNCTION calculate_purchase_order_items_price();

CREATE TRIGGER trigger_calculate_sales_order_items_price
BEFORE INSERT OR UPDATE ON sales_order_items
FOR EACH ROW EXECUTE FUNCTION calculate_sales_order_items_price();

CREATE TRIGGER trigger_update_purchase_order_total
AFTER INSERT OR UPDATE OR DELETE ON purchase_order_items
FOR EACH ROW EXECUTE FUNCTION update_purchase_order_total();

CREATE TRIGGER trigger_update_sales_order_total
AFTER INSERT OR UPDATE OR DELETE ON sales_order_items
FOR EACH ROW EXECUTE FUNCTION update_sales_order_total();

CREATE TRIGGER trigger_sync_vendor_transaction_and_update_balance
AFTER INSERT OR UPDATE OR DELETE ON purchase_orders
FOR EACH ROW
EXECUTE FUNCTION sync_vendor_transaction_and_update_balance();

CREATE TRIGGER trigger_sync_customer_transaction_and_update_balance
AFTER INSERT OR UPDATE OR DELETE ON sales_orders
FOR EACH ROW
EXECUTE FUNCTION sync_customer_transaction_and_update_balance();

CREATE TRIGGER trigger_preserve_tax
BEFORE DELETE ON taxes
FOR EACH ROW
EXECUTE FUNCTION preserve_tax_on_delete();

CREATE TRIGGER trigger_preserve_payment_term
BEFORE DELETE ON payment_terms
FOR EACH ROW
EXECUTE FUNCTION preserve_payment_term_on_delete();

CREATE TRIGGER trigger_preserve_payment_method
BEFORE DELETE ON payment_methods
FOR EACH ROW
EXECUTE FUNCTION preserve_payment_method_on_delete();

CREATE TRIGGER trigger_preserve_vendor
BEFORE DELETE ON vendors
FOR EACH ROW
EXECUTE FUNCTION preserve_vendor_on_delete();

CREATE TRIGGER trigger_preserve_customer_name
BEFORE DELETE ON customers
FOR EACH ROW
EXECUTE FUNCTION preserve_customer_name_on_delete();

CREATE TRIGGER trigger_preserve_item
BEFORE DELETE ON items
FOR EACH ROW
EXECUTE FUNCTION preserve_item_on_delete();

CREATE TRIGGER trigger_preserve_item_name
BEFORE DELETE ON price_lists
FOR EACH ROW
EXECUTE FUNCTION preserve_item_name_on_delete();

CREATE TRIGGER trigger_preserve_user
BEFORE DELETE ON users
FOR EACH ROW
EXECUTE FUNCTION preserve_user_on_delete();

CREATE TRIGGER trigger_preserve_department
BEFORE DELETE ON departments
FOR EACH ROW
EXECUTE FUNCTION preserve_department_on_delete();

CREATE TRIGGER trigger_preserve_branch
BEFORE DELETE ON branches
FOR EACH ROW
EXECUTE FUNCTION preserve_branch_on_delete();

