\c starters;

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
BEGIN
    -- Update the availability status based on stock quantity
    UPDATE items
    SET availability = 
        CASE 
            WHEN (SELECT COALESCE(SUM(quantity), 0) FROM item_stocks WHERE item_id = NEW.item_id) = 0 
                THEN 'out of stock'
            WHEN (SELECT COALESCE(SUM(quantity), 0) FROM item_stocks WHERE item_id = NEW.item_id) < threshold_value 
                THEN 'low stock'
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
    -- Fetch tax rate if tax_id is not NULL, otherwise set tax_rate to 0
    IF NEW.tax_id IS NOT NULL THEN
        SELECT COALESCE(rate, 0) INTO tax_rate FROM taxes 
        WHERE id = NEW.tax_id;
    ELSE
        tax_rate := 0;
    END IF;

    NEW.total := NEW.quantity * NEW.price * (1 + tax_rate / 100);
    NEW.updated_at := CURRENT_TIMESTAMP;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION calculate_sales_order_items_price()
RETURNS TRIGGER AS $$
DECLARE
    tax_rate DECIMAL(5,2);
BEGIN
    IF NEW.tax_id IS NOT NULL THEN
        SELECT COALESCE(rate, 0) INTO tax_rate FROM taxes 
        WHERE id = NEW.tax_id;
    ELSE
        tax_rate := 0;
    END IF;

    NEW.total := NEW.quantity * NEW.price * (1 + tax_rate / 100);
    NEW.updated_at := CURRENT_TIMESTAMP;
    
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
    discount_value DECIMAL(20, 2);
    discount_type VARCHAR(20);
    total_amount DECIMAL(20, 2);
    discount_applied DECIMAL(20, 2);
BEGIN
    -- Calculate total sales order items amount
    SELECT COALESCE(SUM(total), 0) 
    INTO total_amount
    FROM sales_order_items 
    WHERE sales_order_id = NEW.sales_order_id;

    -- Apply total_boxes multiplier
    total_amount := total_amount * COALESCE((SELECT total_boxes FROM sales_orders WHERE id = NEW.sales_order_id), 1);

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

    -- Update sales order total
    UPDATE sales_orders
    SET total = total_amount - COALESCE(discount_applied, 0) 
                + COALESCE(delivery_charge, 0)
    WHERE id = NEW.sales_order_id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION update_vendor_balance()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE vendors
    SET balance = COALESCE((
        SELECT SUM(total) FROM purchase_orders
        WHERE purchase_orders.vendor_id = COALESCE(NEW.vendor_id, OLD.vendor_id)
        AND purchase_orders.status = 'paid'
    ), 0)
    WHERE id = COALESCE(NEW.vendor_id, OLD.vendor_id);

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION update_customer_balance()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE customers
    SET balance = COALESCE((
        SELECT SUM(total) FROM sales_orders
        WHERE sales_orders.customer_id = COALESCE(NEW.customer_id, OLD.customer_id)
        AND sales_orders.payment_status = 'paid'
    ), 0)
    WHERE id = COALESCE(NEW.customer_id, OLD.customer_id);

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION set_delivery_charge()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE sales_orders
    SET delivery_charge = COALESCE((
        SELECT amount FROM delivery_charges 
        WHERE id = NEW.delivery_charge_id
    ), 0)
    WHERE id = NEW.id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Triggers
CREATE TRIGGER before_insert_update_items
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

CREATE TRIGGER purchase_order_balance_update
AFTER INSERT OR UPDATE OR DELETE ON purchase_orders
FOR EACH ROW
EXECUTE FUNCTION update_vendor_balance();

CREATE TRIGGER sales_order_balance_update
AFTER INSERT OR UPDATE OR DELETE ON sales_orders
FOR EACH ROW
EXECUTE FUNCTION update_customer_balance();

CREATE TRIGGER trigger_set_delivery_charge
AFTER INSERT OR UPDATE OF delivery_charge_id
ON sales_orders
FOR EACH ROW
EXECUTE FUNCTION set_delivery_charge();

