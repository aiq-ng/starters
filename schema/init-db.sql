CREATE TABLE refresh_tokens (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Roles
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

-- Permissions
CREATE TABLE permissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- Currencies
CREATE TABLE currencies (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    symbol VARCHAR(10) UNIQUE,
    code VARCHAR(10) UNIQUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Base Types
CREATE TABLE base_pay_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

-- Create work_leave_qualifications table
CREATE TABLE work_leave_qualifications (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

-- Branches
CREATE TABLE branches (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Item Categories
CREATE TABLE item_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Units of Measurement
CREATE TABLE units (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    abbreviation VARCHAR(10) UNIQUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Work Leave Qualifications
CREATE TABLE no_of_working_days (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Vendor Categories
CREATE TABLE vendor_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Payment Methods
CREATE TABLE payment_methods (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loan_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE loans (
    id SERIAL PRIMARY KEY,
    lender_id INT NOT NULL,
    lender_type VARCHAR(50) NOT NULL CHECK (lender_type IN ('user', 'vendor')),
    amount DECIMAL(20, 2),
    interest_rate DECIMAL(5, 2),
    start_date DATE,
    end_date DATE,
    loan_type_id INT REFERENCES loan_types(id) ON DELETE SET NULL,
    status VARCHAR(50) DEFAULT 'pending' CHECK (status IN 
        ('pending', 'approved', 'disbursed', 'repaid', 'defaulted')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Payment Terms
CREATE TABLE payment_terms (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Taxes
CREATE TABLE taxes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    rate DECIMAL(5, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Item Manufacturers
CREATE TABLE item_manufacturers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Cash Accounts
CREATE TABLE cash_accounts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    balance DECIMAL(20, 2) DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Departments
CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    salary_type VARCHAR(50) CHECK (salary_type IN ('fixed', 'base')),
    base_type_id INT REFERENCES base_pay_types(id) ON DELETE SET NULL,
    base_rate DECIMAL(20, 2), -- rate per hour or delivery
    base_salary DECIMAL(20, 2), -- for fixed salary
    work_leave_qualification INT REFERENCES work_leave_qualifications(id) ON DELETE SET NULL,
    work_leave_period VARCHAR(50),
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
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
    department_id INT REFERENCES departments(id) ON DELETE SET NULL,
    role_id INT REFERENCES roles(id) ON DELETE SET NULL,
    no_of_working_days_id INT REFERENCES no_of_working_days(id) ON DELETE SET NULL,
    salary DECIMAL(20, 2),
    bank_details JSONB, -- {bank_name, account_number}
    leave DATE, 
    nin VARCHAR(255),
    passport VARCHAR(255),
    status VARCHAR(50) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP    
);

CREATE TABLE user_permissions (
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    permission_id INT REFERENCES permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, permission_id)
);

CREATE TABLE user_leaves (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
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

-- Price Lists
CREATE TABLE price_lists (
    id SERIAL PRIMARY KEY,
    item_category_id INT REFERENCES item_categories(id) ON DELETE SET NULL,
    unit_id INT REFERENCES units(id) ON DELETE SET NULL,
    item_details VARCHAR(100) NOT NULL UNIQUE,
    unit_price DECIMAL(20, 2),
    minimum_order INT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Vendors
CREATE TABLE vendors (
    id SERIAL PRIMARY KEY,
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
    payment_term_id INT REFERENCES payment_terms(id) ON DELETE SET NULL,
    currency_id INT REFERENCES currencies(id) ON DELETE SET NULL,
    category_id INT REFERENCES vendor_categories(id) ON DELETE SET NULL,
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
    id SERIAL PRIMARY KEY,
    vendor_id INT REFERENCES vendors(id) ON DELETE SET NULL,
    transaction_type VARCHAR(50) 
        CHECK (transaction_type IN ('credit', 'debit')),
    payment_method_id INT REFERENCES payment_methods(id) ON DELETE SET NULL,
    cash_account_id INT REFERENCES cash_accounts(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(id::TEXT, 10, '0')
    ) STORED,
    notes TEXT,
    invoice_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Customers
CREATE TABLE customers (
    id SERIAL PRIMARY KEY,
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
    payment_term_id INT REFERENCES payment_terms(id) ON DELETE SET NULL,
    currency_id INT REFERENCES currencies(id) ON DELETE SET NULL,
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
    id SERIAL PRIMARY KEY,
    customer_id INT REFERENCES customers(id) ON DELETE SET NULL,
    transaction_type VARCHAR(50) 
        CHECK (transaction_type IN ('credit', 'debit')),
    payment_method_id INT REFERENCES payment_methods(id) ON DELETE SET NULL,
    cash_account_id INT REFERENCES cash_accounts(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(id::TEXT, 10, '0')
    ) STORED,
    notes TEXT,
    invoice_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Items
CREATE TABLE items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    sku VARCHAR(100) GENERATED ALWAYS AS (
        UPPER(SUBSTRING(name FROM 1 FOR 3)) || '-' || LPAD(id::TEXT, 4, '0')
    ) STORED,
    unit_id INT REFERENCES units(id) ON DELETE SET NULL,
    category_id INT REFERENCES item_categories(id) ON DELETE SET NULL,
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
    id SERIAL PRIMARY KEY,
    item_id INT REFERENCES items(id) ON DELETE CASCADE,
    stock_code VARCHAR(100) GENERATED ALWAYS AS (
        'STK-' || LPAD(id::TEXT, 5, '0')
    ) STORED,
    quantity INT DEFAULT 0 NOT NULL,
    date_received DATE DEFAULT CURRENT_DATE,
    expiry_date DATE,
    branch_id INT REFERENCES branches(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Item Stock Vendors
CREATE TABLE item_stock_vendors (
    stock_id INT REFERENCES item_stocks(id) ON DELETE CASCADE,
    vendor_id INT REFERENCES vendors(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, vendor_id)
);

-- Item Stock Departments
CREATE TABLE item_stock_departments (
    stock_id INT REFERENCES item_stocks(id) ON DELETE CASCADE,
    department_id INT REFERENCES departments(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, department_id)
);

-- Item Stock Manufacturers
CREATE TABLE item_stock_manufacturers (
    stock_id INT REFERENCES item_stocks(id) ON DELETE CASCADE,
    manufacturer_id INT REFERENCES item_manufacturers(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, manufacturer_id)
);

-- Item Stock Branches
CREATE TABLE item_stock_branches (
    stock_id INT REFERENCES item_stocks(id) ON DELETE CASCADE,
    branch_id INT REFERENCES branches(id) ON DELETE CASCADE,
    PRIMARY KEY (stock_id, branch_id)
);

-- Item Stock Adjustments
CREATE TABLE item_stock_adjustments (
    id SERIAL PRIMARY KEY,
    stock_id INT REFERENCES item_stocks(id) ON DELETE CASCADE,
    manager_id INT REFERENCES users(id) ON DELETE SET NULL,
    source_type VARCHAR(10) NOT NULL 
        CHECK (source_type IN ('user', 'vendor')),
    source_id INT NOT NULL,
    source_department_id INT REFERENCES departments(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    adjustment_type VARCHAR(50) 
        CHECK (adjustment_type IN ('addition', 'subtraction')),
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Purchase Orders
CREATE TABLE purchase_orders (
    id SERIAL PRIMARY KEY,
    vendor_id INT REFERENCES vendors(id) ON DELETE SET NULL,
    branch_id INT REFERENCES branches(id) ON DELETE SET NULL,
    purchase_order_number VARCHAR(50) GENERATED ALWAYS AS (
        'PO-' || LPAD(id::TEXT, 5, '0')
    ) STORED,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(id::TEXT, 5, '0')
    ) STORED,
    invoice_number VARCHAR(50) GENERATED ALWAYS AS (
        'INV-' || LPAD(id::TEXT, 5, '0')
    ) STORED,
    delivery_date DATE,
    payment_term_id INT REFERENCES payment_terms(id) ON DELETE SET NULL,
    subject TEXT,
    notes TEXT,
    terms_and_conditions TEXT,
    discount DECIMAL(20, 2) DEFAULT 0,
    shipping_charge DECIMAL(20, 2) DEFAULT 0,
    total DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'issued' 
        CHECK (status IN ('draft', 'sent', 'received', 'paid', 'overdue', 'cancelled', 'issued')),
    processed_by INT REFERENCES users(id) ON DELETE SET NULL,
    date_received DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Purchase Order Items
CREATE TABLE purchase_order_items (
    id SERIAL PRIMARY KEY,
    purchase_order_id INT REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_id INT REFERENCES items(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    price DECIMAL(20, 2),
    tax_id INT REFERENCES taxes(id) ON DELETE SET NULL,
    total DECIMAL(20, 2) GENERATED ALWAYS AS (quantity * price) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Sales Orders
CREATE TABLE sales_orders (
    id SERIAL PRIMARY KEY,
    order_type VARCHAR(50) 
        CHECK (order_type IN ('order', 'service')),
    order_title VARCHAR(255),
    order_id VARCHAR(255) GENERATED ALWAYS AS (
        CASE
            WHEN order_type = 'order' THEN 'SLO-' || LPAD(id::TEXT, 3, '0')
            ELSE 'SLS-' || LPAD(id::TEXT, 3, '0')
        END
    ) STORED,
    invoice_number VARCHAR(50) GENERATED ALWAYS AS (
        'INV-' || LPAD(id::TEXT, 5, '0')
    ) STORED,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(id::TEXT, 5, '0')
    ) STORED,
    customer_id INT REFERENCES customers(id) ON DELETE SET NULL,
    payment_term_id INT REFERENCES payment_terms(id) ON DELETE SET NULL,
    payment_method_id INT REFERENCES payment_methods(id) ON DELETE SET NULL,
    delivery_option VARCHAR(50) 
        CHECK (delivery_option IN ('pickup', 'delivery')),
    assigned_driver_id INT REFERENCES users(id) ON DELETE SET NULL,
    delivery_date DATE,
    additional_note TEXT,
    customer_note TEXT,
    discount DECIMAL(20, 2) DEFAULT 0,
    delivery_charge DECIMAL(20, 2) DEFAULT 0,
    total DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending' 
        CHECK (status IN ('upcoming', 'pending', 'sent', 'paid', 'cancelled')),
    processed_by INT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Sales Order Items
CREATE TABLE sales_order_items (
    id SERIAL PRIMARY KEY,
    sales_order_id INT REFERENCES sales_orders(id) ON DELETE CASCADE,
    item_id INT REFERENCES price_lists(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    price DECIMAL(20, 2) NOT NULL,
    total DECIMAL(20, 2) GENERATED ALWAYS AS (quantity * price) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE expenses (
    id SERIAL PRIMARY KEY,
    expense_title VARCHAR(255),
    expense_category INT REFERENCES expenses_categories(id) ON DELETE SET NULL, 
    expense_id VARCHAR(255) GENERATED ALWAYS AS (
        'EXP-' || LPAD(id::TEXT, 5, '0')
    ) STORED,
    payment_method_id INT REFERENCES payment_methods(id) ON DELETE SET NULL,
    payment_term_id INT REFERENCES payment_terms(id) ON DELETE SET NULL,
    department_id INT REFERENCES departments(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    bank_charges DECIMAL(20, 2) DEFAULT 0,
    date_of_expense DATE DEFAULT CURRENT_DATE,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'pending' 
        CHECK (status IN ('paid', 'cancelled')),
    processed_by INT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Comments
CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    parent_id INT REFERENCES comments(id) ON DELETE CASCADE,
    entity_id INT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Notifications
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    entity_id INT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
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

