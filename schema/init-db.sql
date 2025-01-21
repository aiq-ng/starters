-- Roles
CREATE TABLE roles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL
);

-- Permissions
CREATE TABLE permissions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- Currencies
CREATE TABLE currencies (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    symbol VARCHAR(10) UNIQUE,
    code VARCHAR(10) UNIQUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Base Types
CREATE TABLE base_pay_types (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- Create work_leave_qualifications table
CREATE TABLE work_leave_qualifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL
);

-- Branches
CREATE TABLE branches (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Item Categories
CREATE TABLE item_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Units of Measurement
CREATE TABLE units (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    abbreviation VARCHAR(10) UNIQUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Work Leave Qualifications
CREATE TABLE no_of_working_days (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Vendor Categories
CREATE TABLE vendor_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Payment Methods
CREATE TABLE payment_methods (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loan_types (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Payment Terms
CREATE TABLE payment_terms (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Taxes
CREATE TABLE taxes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    rate DECIMAL(5, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Item Manufacturers
CREATE TABLE item_manufacturers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Cash Accounts
CREATE TABLE cash_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    balance DECIMAL(20, 2) DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Departments
CREATE TABLE departments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    salary_type VARCHAR(50) CHECK (salary_type IN ('fixed', 'base')),
    base_type_id UUID REFERENCES base_pay_types(id) ON DELETE SET NULL,
    base_rate DECIMAL(20, 2), -- rate per hour or delivery
    base_salary DECIMAL(20, 2), -- for fixed salary
    work_leave_qualification UUID REFERENCES work_leave_qualifications(id) ON DELETE SET NULL,
    work_leave_period VARCHAR(50),
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    firstname VARCHAR(100),
    lastname VARCHAR(100),
    name VARCHAR(255) GENERATED ALWAYS AS (firstname || ' ' || lastname) STORED,
    avatar_url VARCHAR(255),
    date_of_birth DATE,
    address TEXT,
    next_of_kin VARCHAR(100),
    date_of_employment DATE,
    department_id UUID REFERENCES departments(id) ON DELETE SET NULL,
    role_id UUID REFERENCES roles(id) ON DELETE SET NULL,
    no_of_working_days_id UUID REFERENCES no_of_working_days(id) ON DELETE SET NULL,
    salary DECIMAL(20, 2),
    bank_details JSONB, -- {bank_name, account_number}
    leave DATE, 
    nin VARCHAR(255),
    passport VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'afk')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP    
);

CREATE TABLE refresh_tokens (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_permissions (
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    permission_id UUID REFERENCES permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, permission_id)
);

CREATE TABLE user_leaves (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    leave_type VARCHAR(50) CHECK (leave_type IN ('annual', 'sick', 'maternity', 'paternity', 'compassionate', 'study', 'unpaid')),
    start_date DATE DEFAULT CURRENT_DATE,
    end_date DATE,
    days INT GENERATED ALWAYS AS (
        CASE
            WHEN end_date IS NOT NULL THEN (end_date - start_date)
            ELSE NULL
        END
    ) STORED,
    status VARCHAR(50) DEFAULT 'booked' CHECK (status IN ('booked', 'on leave', 'leave taken', 'cancelled')),
    notes TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    lender_id UUID REFERENCES users(id) ON DELETE SET NULL,
    lender_type VARCHAR(50) NOT NULL CHECK (lender_type IN ('user', 'vendor')),
    amount DECIMAL(20, 2),
    interest_rate DECIMAL(5, 2),
    start_date DATE,
    end_date DATE,
    loan_type_id UUID REFERENCES loan_types(id) ON DELETE SET NULL,
    status VARCHAR(50) DEFAULT 'pending' CHECK (status IN 
        ('pending', 'approved', 'disbursed', 'repaid', 'defaulted')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Price Lists
CREATE TABLE price_lists (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    item_category_id UUID REFERENCES item_categories(id) ON DELETE SET NULL,
    unit_id UUID REFERENCES units(id) ON DELETE SET NULL,
    item_details VARCHAR(100) NOT NULL UNIQUE,
    unit_price DECIMAL(20, 2),
    minimum_order INT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Vendors
CREATE TABLE vendors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    salutation VARCHAR(10) 
        CHECK (salutation IN ('Mr', 'Mrs', 'Miss', 'Dr', 'Prof')),
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    company_name VARCHAR(255),
    display_name VARCHAR(255),
    email VARCHAR(255),
    work_phone VARCHAR(20),
    mobile_phone VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    social_media JSONB,
    payment_term_id UUID REFERENCES payment_terms(id) ON DELETE SET NULL,
    currency_id UUID REFERENCES currencies(id) ON DELETE SET NULL,
    category_id UUID REFERENCES vendor_categories(id) ON DELETE SET NULL,
    balance DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) GENERATED ALWAYS AS (
        CASE
            WHEN balance = 0 THEN 'active'
            WHEN balance > 0 THEN 'owing'
            ELSE 'paid'
        END
    ) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Vendor Transactions
CREATE TABLE vendor_transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    vendor_id UUID REFERENCES vendors(id) ON DELETE SET NULL,
    transaction_type VARCHAR(50) 
        CHECK (transaction_type IN ('credit', 'debit')),
    payment_method_id UUID REFERENCES payment_methods(id) ON DELETE SET NULL,
    cash_account_id UUID REFERENCES cash_accounts(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(order_sequence::TEXT, 10, '0')
    ) STORED,
    notes TEXT,
    invoice_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Customers
CREATE TABLE customers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_type VARCHAR(50) 
        CHECK (customer_type IN ('individual', 'business')),
    salutation VARCHAR(10) 
        CHECK (salutation IN ('Mr', 'Mrs', 'Miss', 'Dr', 'Prof')),
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    display_name VARCHAR(255),
    company_name VARCHAR(255),
    email VARCHAR(255),
    work_phone VARCHAR(20),
    mobile_phone VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    social_media JSONB,
    payment_term_id UUID REFERENCES payment_terms(id) ON DELETE SET NULL,
    currency_id UUID REFERENCES currencies(id) ON DELETE SET NULL,
    balance DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) GENERATED ALWAYS AS (
        CASE
            WHEN balance = 0 THEN 'active'
            WHEN balance > 0 THEN 'owing'
            ELSE 'paid'
        END
    ) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Customer Transactions
CREATE TABLE customer_transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    customer_id UUID REFERENCES customers(id) ON DELETE SET NULL,
    transaction_type VARCHAR(50) 
        CHECK (transaction_type IN ('credit', 'debit')),
    payment_method_id UUID REFERENCES payment_methods(id) ON DELETE SET NULL,
    cash_account_id UUID REFERENCES cash_accounts(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(order_sequence::TEXT, 10, '0')
    ) STORED,
    notes TEXT,
    invoice_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Items
CREATE TABLE items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    sku VARCHAR(100) GENERATED ALWAYS AS (
        UPPER(SUBSTRING(name FROM 1 FOR 3)) || '-' || LPAD(order_sequence::TEXT, 4, '0')
    ) STORED,
    unit_id UUID REFERENCES units(id) ON DELETE SET NULL,
    category_id UUID REFERENCES item_categories(id) ON DELETE SET NULL,
    price DECIMAL(20, 2),
    opening_stock INT DEFAULT 0,
    threshold_value INT DEFAULT 0,
    availability VARCHAR(50), 
    media JSONB,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Item Stocks
CREATE TABLE item_stocks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    item_id UUID REFERENCES items(id) ON DELETE CASCADE,
    stock_code VARCHAR(100) GENERATED ALWAYS AS (
        'STK-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED,
    quantity INT DEFAULT 0 NOT NULL,
    date_received DATE DEFAULT CURRENT_DATE,
    expiry_date DATE,
    branch_id UUID REFERENCES branches(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Item Stock Vendors
CREATE TABLE item_stock_vendors (
    stock_id UUID REFERENCES item_stocks(id) ON DELETE CASCADE,
    vendor_id UUID REFERENCES vendors(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, vendor_id)
);

-- Item Stock Departments
CREATE TABLE item_stock_departments (
    stock_id UUID REFERENCES item_stocks(id) ON DELETE CASCADE,
    department_id UUID REFERENCES departments(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, department_id)
);

-- Item Stock Manufacturers
CREATE TABLE item_stock_manufacturers (
    stock_id UUID REFERENCES item_stocks(id) ON DELETE CASCADE,
    manufacturer_id UUID REFERENCES item_manufacturers(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, manufacturer_id)
);

-- Item Stock Branches
CREATE TABLE item_stock_branches (
    stock_id UUID REFERENCES item_stocks(id) ON DELETE CASCADE,
    branch_id UUID REFERENCES branches(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, branch_id)
);

-- Item Stock Adjustments
CREATE TABLE item_stock_adjustments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    stock_id UUID REFERENCES item_stocks(id) ON DELETE CASCADE,
    manager_id UUID REFERENCES users(id) ON DELETE SET NULL,
    source_type VARCHAR(10) NOT NULL 
        CHECK (source_type IN ('user', 'vendor')),
    source_id UUID NOT NULL,
    source_department_id UUID REFERENCES departments(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    adjustment_type VARCHAR(50) 
        CHECK (adjustment_type IN ('addition', 'subtraction')),
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Purchase Orders
CREATE TABLE purchase_orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    vendor_id UUID REFERENCES vendors(id) ON DELETE SET NULL,
    branch_id UUID REFERENCES branches(id) ON DELETE SET NULL,
    purchase_order_number VARCHAR(50) GENERATED ALWAYS AS (
        'PO-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED,
    invoice_number VARCHAR(50) GENERATED ALWAYS AS (
        'INV-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED,
    delivery_date DATE,
    payment_term_id UUID REFERENCES payment_terms(id) ON DELETE SET NULL,
    subject TEXT,
    notes TEXT,
    terms_and_conditions TEXT,
    discount DECIMAL(20, 2) DEFAULT 0,
    shipping_charge DECIMAL(20, 2) DEFAULT 0,
    total DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'issued' 
        CHECK (status IN ('draft', 'sent', 'received', 'paid', 'overdue', 'cancelled', 'issued')),
    processed_by UUID REFERENCES users(id) ON DELETE SET NULL,
    date_received DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Purchase Order Items
CREATE TABLE purchase_order_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    purchase_order_id UUID REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_id UUID REFERENCES items(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    price DECIMAL(20, 2),
    tax_id UUID REFERENCES taxes(id) ON DELETE SET NULL,
    total DECIMAL(20, 2) GENERATED ALWAYS AS (quantity * price) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Sales Orders
CREATE TABLE sales_orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    order_type VARCHAR(50) 
        CHECK (order_type IN ('order', 'service')),
    order_title VARCHAR(255),
    order_id VARCHAR(255) GENERATED ALWAYS AS (
        CASE
            WHEN order_type = 'order' THEN 'SLO-' || LPAD(order_sequence::TEXT, 3, '0')
            ELSE 'SLS-' || LPAD(order_sequence::TEXT, 3, '0')
        END
    ) STORED,
    invoice_number VARCHAR(50) GENERATED ALWAYS AS (
        'INV-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED,
    customer_id UUID REFERENCES customers(id) ON DELETE SET NULL,
    payment_term_id UUID REFERENCES payment_terms(id) ON DELETE SET NULL,
    payment_method_id UUID REFERENCES payment_methods(id) ON DELETE SET NULL,
    delivery_option VARCHAR(50) 
        CHECK (delivery_option IN ('pickup', 'delivery')),
    assigned_driver_id UUID REFERENCES users(id) ON DELETE SET NULL,
    delivery_date DATE,
    delivery_time TIME DEFAULT (CURRENT_TIME + INTERVAL '30 minutes'),
    delivery_address TEXT,
    additional_note TEXT,
    customer_note TEXT,
    discount DECIMAL(20, 2) DEFAULT 0,
    delivery_charge DECIMAL(20, 2) DEFAULT 0,
    total DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending' 
        -- upcoming for services
        CHECK (status IN ('pending', 'cancelled', 'completed', 'sent', 'new order', 'in progress', 'paid', 'upcoming')), 
    sent_to_kitchen BOOLEAN DEFAULT FALSE,
    processed_by UUID REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Sales Order Items
CREATE TABLE sales_order_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    sales_order_id UUID REFERENCES sales_orders(id) ON DELETE CASCADE,
    item_id UUID REFERENCES price_lists(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    price DECIMAL(20, 2) NOT NULL,
    total DECIMAL(20, 2) GENERATED ALWAYS AS (quantity * price) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    expense_title VARCHAR(255),
    expense_category UUID REFERENCES expenses_categories(id) ON DELETE SET NULL, 
    expense_id VARCHAR(255) GENERATED ALWAYS AS (
        'EXP-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED,
    payment_method_id UUID REFERENCES payment_methods(id) ON DELETE SET NULL,
    payment_term_id UUID REFERENCES payment_terms(id) ON DELETE SET NULL,
    department_id UUID REFERENCES departments(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    bank_charges DECIMAL(20, 2) DEFAULT 0,
    date_of_expense DATE DEFAULT CURRENT_DATE,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'pending' 
        CHECK (status IN ('paid', 'cancelled')),
    processed_by UUID REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Comments
CREATE TABLE comments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    parent_id UUID REFERENCES comments(id) ON DELETE CASCADE,
    entity_id UUID NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Notifications
CREATE TABLE notifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    entity_id UUID,
    entity_type VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE OR REPLACE FUNCTION update_item_availability()
RETURNS TRIGGER AS $$
DECLARE
    total_stock INT;
BEGIN
    SELECT COALESCE(SUM(quantity), 0)
    INTO total_stock
    FROM item_stocks
    WHERE item_id = NEW.item_id;

    UPDATE items
    SET availability = CASE
        WHEN total_stock = 0 THEN 'out of stock'
        WHEN total_stock < threshold_value THEN 'low stock'
        ELSE 'in stock'
    END,
    updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.item_id;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION set_status_to_overdue()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.delivery_date < CURRENT_DATE AND NEW.status NOT IN ('paid', 'cancelled', 'received') THEN
        NEW.status := 'overdue';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_item_availability
AFTER INSERT OR UPDATE OR DELETE
ON item_stocks
FOR EACH ROW
EXECUTE FUNCTION update_item_availability();

CREATE TRIGGER check_overdue_status
BEFORE UPDATE ON purchase_orders
FOR EACH ROW
EXECUTE FUNCTION set_status_to_overdue();
