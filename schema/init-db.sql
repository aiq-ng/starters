CREATE EXTENSION IF NOT EXISTS pgcrypto;

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

-- Salutation
CREATE TABLE salutations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL
);

--delivery charge
CREATE TABLE delivery_charges (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE,
    amount DECIMAL(20, 2) NOT NULL,
    description TEXT
);

CREATE TABLE discounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE,
    discount_type VARCHAR(20) CHECK (discount_type IN ('amount', 'percentage')),
    value DECIMAL(20, 2) NOT NULL,
    description TEXT
);

-- Currencies
CREATE TABLE currencies (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    symbol VARCHAR(10) UNIQUE,
    code VARCHAR(10) UNIQUE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
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
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Item Categories
CREATE TABLE item_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Units of Measurement
CREATE TABLE units (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    abbreviation VARCHAR(10) UNIQUE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Work Leave Qualifications
CREATE TABLE no_of_working_days (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Vendor Categories
CREATE TABLE vendor_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Payment Methods
CREATE TABLE payment_methods (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
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
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Taxes
CREATE TABLE taxes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(50) UNIQUE NOT NULL,
    rate DECIMAL(5, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Item Manufacturers
CREATE TABLE item_manufacturers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Cash Accounts
CREATE TABLE cash_accounts (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    balance DECIMAL(20, 2) DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Departments
CREATE TABLE departments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE,
    salary_type VARCHAR(50) CHECK (salary_type IN ('fixed', 'base')),
    base_type_id UUID REFERENCES base_pay_types(id) ON DELETE SET NULL,
    base_rate DECIMAL(20, 2), -- rate per hour or delivery
    base_salary DECIMAL(20, 2), -- for fixed salary
    work_leave_qualification UUID REFERENCES work_leave_qualifications(id) ON DELETE SET NULL,
    work_leave_period VARCHAR(50),
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    employee_id VARCHAR(8) GENERATED ALWAYS AS (LEFT(id::TEXT, 8)) STORED UNIQUE,
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
    gender VARCHAR(10) CHECK (gender IN ('male', 'female', 'other')),
    marital_status VARCHAR(20) CHECK (marital_status IN ('single', 'married', 'divorced', 'widowed')),
    employment_type VARCHAR(50) CHECK (employment_type IN ('full-time', 'part-time', 'contract', 'internship')),
    emergency_contact TEXT,
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
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()    
);

CREATE TABLE refresh_tokens (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT clock_timestamp(),
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
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
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
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Price Lists
CREATE TABLE price_lists (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    item_category_id UUID REFERENCES item_categories(id) ON DELETE SET NULL,
    unit_id UUID REFERENCES units(id) ON DELETE SET NULL,
    item_details VARCHAR(100) NOT NULL UNIQUE,

    tax_id UUID REFERENCES taxes(id),
    tax DECIMAL(5, 2) DEFAULT 0,

    unit_price DECIMAL(20, 2),
    minimum_order INT,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Vendors
CREATE TABLE vendors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    salutation VARCHAR(50),
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    company_name VARCHAR(255),
    display_name VARCHAR(255) GENERATED ALWAYS AS (
        CASE
            WHEN company_name IS NOT NULL THEN company_name
            ELSE first_name || ' ' || last_name
        END
    ) STORED,
    email VARCHAR(255),
    work_phone VARCHAR(20),
    mobile_phone VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    social_media JSONB,

    payment_term_id UUID REFERENCES payment_terms(id),
    payment_term VARCHAR(50),

    currency_id UUID REFERENCES currencies(id) ON DELETE SET NULL,
    category_id UUID REFERENCES vendor_categories(id) ON DELETE SET NULL,
    balance DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) GENERATED ALWAYS AS (
        CASE 
            WHEN balance > 0 THEN 'owing'
            ELSE 'active'
        END
    ) STORED,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Vendor Transactions
CREATE TABLE vendor_transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    vendor_id UUID REFERENCES vendors(id) ON DELETE CASCADE,
    transaction_type VARCHAR(50) 
        CHECK (transaction_type IN ('credit', 'debit')),

    payment_method_id UUID REFERENCES payment_methods(id),
    payment_method VARCHAR(50),

    payment_term_id UUID REFERENCES payment_terms(id),
    payment_term VARCHAR(50),

    cash_account_id UUID REFERENCES cash_accounts(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    reference_number VARCHAR(50) UNIQUE,
    notes TEXT,
    invoice_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Customers
CREATE TABLE customers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_type VARCHAR(50) 
        CHECK (customer_type IN ('individual', 'business')),
    salutation VARCHAR(10), 
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    display_name VARCHAR(255) GENERATED ALWAYS AS (
        CASE
            WHEN customer_type = 'individual' THEN first_name || ' ' || last_name
            ELSE first_name
        END
    ) STORED,
    company_name VARCHAR(255),
    email VARCHAR(255),
    work_phone VARCHAR(20),
    mobile_phone VARCHAR(20),
    address TEXT,
    website VARCHAR(255),
    social_media JSONB,

    payment_term_id UUID REFERENCES payment_terms(id),
    payment_term VARCHAR(50),

    currency_id UUID REFERENCES currencies(id) ON DELETE SET NULL,
    balance DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) GENERATED ALWAYS AS (
        CASE
            WHEN balance > 0 THEN 'owing'
            ELSE 'active'
        END
    ) STORED,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Customer Transactions
CREATE TABLE customer_transactions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    customer_id UUID REFERENCES customers(id) ON DELETE CASCADE,
    transaction_type VARCHAR(50) 
        CHECK (transaction_type IN ('credit', 'debit')),

    payment_method_id UUID REFERENCES payment_methods(id),
    payment_method VARCHAR(50),

    payment_term_id UUID REFERENCES payment_terms(id),
    payment_term VARCHAR(50),

    cash_account_id UUID REFERENCES cash_accounts(id) ON DELETE SET NULL,
    amount DECIMAL(20, 2),
    reference_number VARCHAR(50) UNIQUE,
    notes TEXT,
    invoice_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Items
CREATE TABLE items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    sku VARCHAR(100),
    barcode VARCHAR(255),
    unit_id UUID REFERENCES units(id) ON DELETE SET NULL,
    category_id UUID REFERENCES item_categories(id) ON DELETE SET NULL,
    price DECIMAL(20, 2),
    opening_stock INT DEFAULT 0,
    threshold_value INT DEFAULT 0,
    availability VARCHAR(50), 
    media JSONB,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
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

    branch_id UUID REFERENCES branches(id),
    branch VARCHAR(255),

    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    CONSTRAINT non_negative_quantity CHECK (quantity >= 0)
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

    manager_id UUID REFERENCES users(id),
    manager VARCHAR(255),

    source_type VARCHAR(10) NOT NULL 
        CHECK (source_type IN ('user', 'vendor')),
    source_id UUID,

    source_department_id UUID REFERENCES departments(id),
    source_department VARCHAR(100),

    quantity INT NOT NULL,
    adjustment_type VARCHAR(50) 
        CHECK (adjustment_type IN ('addition', 'subtraction')),
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Purchase Orders
CREATE TABLE purchase_orders (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_sequence BIGSERIAL UNIQUE,

    vendor_id UUID REFERENCES vendors(id),
    vendor VARCHAR(255),

    branch_id UUID REFERENCES branches(id),
    branch VARCHAR(255),

    purchase_order_number VARCHAR(50) GENERATED ALWAYS AS (
        'PO-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED UNIQUE,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED UNIQUE,
    invoice_number VARCHAR(50) GENERATED ALWAYS AS (
        'INV-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED UNIQUE,
    delivery_date DATE,

    payment_term_id UUID REFERENCES payment_terms(id),
    payment_term VARCHAR(50),

    payment_method_id UUID REFERENCES payment_methods(id),
    payment_method VARCHAR(50),

    payment_due_date DATE,
    subject TEXT,
    notes TEXT,
    terms_and_conditions TEXT,
    discount DECIMAL(20, 2) DEFAULT 0,
    shipping_charge DECIMAL(20, 2) DEFAULT 0,
    total DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'issued' 
        CHECK (status IN ('draft', 'sent', 'received', 'paid', 'overdue', 'cancelled', 'issued')),

    processed_by UUID REFERENCES users(id),
    manager VARCHAR(255),

    date_received DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Purchase Order Items
CREATE TABLE purchase_order_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    purchase_order_id UUID REFERENCES purchase_orders(id) ON DELETE CASCADE,

    item_id UUID REFERENCES items(id),
    item VARCHAR(255),

    quantity INT NOT NULL,
    price DECIMAL(20, 2),

    tax_id UUID REFERENCES taxes(id),
    tax DECIMAL(5, 2) DEFAULT 0,

    total DECIMAL(20, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
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
    ) STORED UNIQUE,
    invoice_number VARCHAR(50) GENERATED ALWAYS AS (
        'INV-' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED UNIQUE,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(order_sequence::TEXT, 5, '0')
    ) STORED UNIQUE,

    customer_id UUID REFERENCES customers(id),
    customer VARCHAR(255),

    payment_term_id UUID REFERENCES payment_terms(id),
    payment_term VARCHAR(50),

    payment_method_id UUID REFERENCES payment_methods(id),
    payment_method VARCHAR(50),

    delivery_option VARCHAR(50) 
        CHECK (delivery_option IN ('pickup', 'delivery')),
    delivery_date DATE,
    delivery_time TIME DEFAULT (CURRENT_TIME + INTERVAL '30 minutes'),
    delivery_address TEXT,
    additional_note TEXT,
    customer_note TEXT,

    discount DECIMAL(20, 2) DEFAULT 0,
    discount_id UUID REFERENCES discounts(id) ON DELETE SET NULL,

    delivery_charge_id UUID REFERENCES delivery_charges(id) ON DELETE SET NULL,
    delivery_charge DECIMAL(20, 2) DEFAULT 0,

    total_boxes INT DEFAULT 1,
    total DECIMAL(20, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending' 
        -- upcoming for services
        CHECK (status IN ('pending', 'void', 'new order', 'in progress', 'in delivery', 'delivered', 'draft')),
    payment_status VARCHAR(50) DEFAULT 'unpaid' 
        CHECK (payment_status IN ('paid', 'unpaid')),
    sent_to_kitchen BOOLEAN DEFAULT FALSE,

    processed_by UUID REFERENCES users(id) ON DELETE SET NULL,
    manager VARCHAR(255),

    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Sales Order Items
CREATE TABLE sales_order_items (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    sales_order_id UUID REFERENCES sales_orders(id) ON DELETE CASCADE,

    item_id UUID REFERENCES price_lists(id),
    item_name VARCHAR(255),

    platter_items TEXT,
    quantity INT NOT NULL,
    price DECIMAL(20, 2) NOT NULL,

    tax_id UUID REFERENCES taxes(id),
    tax DECIMAL(5, 2) NOT NULL DEFAULT 0,

    total DECIMAL(20, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);


CREATE TABLE audit_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    user_id UUID REFERENCES users(id),
    manager VARCHAR(255),

    entity_id UUID,
    entity_type VARCHAR(50),
    action VARCHAR(50),
    entity_data JSONB,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

CREATE TABLE order_ratings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    order_id UUID REFERENCES sales_orders(id) ON DELETE CASCADE,
    name VARCHAR(255),
    rating INT NOT NULL,
    review TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Chef Assignments
CREATE TABLE chef_assignments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    chef_id UUID REFERENCES users(id) ON DELETE CASCADE,
    order_id UUID REFERENCES sales_orders(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    CONSTRAINT unique_chef_order UNIQUE (chef_id, order_id)
);

-- Driver Assignments
CREATE TABLE driver_assignments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    driver_id UUID REFERENCES users(id) ON DELETE CASCADE,
    order_id UUID REFERENCES sales_orders(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    CONSTRAINT unique_driver_order UNIQUE (driver_id, order_id)
);

CREATE TABLE expenses_categories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
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
    payment_method VARCHAR(50),

    payment_term_id UUID REFERENCES payment_terms(id) ON DELETE SET NULL,
    payment_term VARCHAR(50),

    department_id UUID REFERENCES departments(id) ON DELETE SET NULL,
    department VARCHAR(255),

    amount DECIMAL(20, 2),
    bank_charges DECIMAL(20, 2) DEFAULT 0,
    date_of_expense DATE DEFAULT CURRENT_DATE,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'pending' 
        CHECK (status IN ('paid', 'cancelled')),

    processed_by UUID REFERENCES users(id) ON DELETE SET NULL,
    manager VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT clock_timestamp(),
    updated_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Comments
CREATE TABLE comments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    handler VARCHAR(255),

    parent_id UUID REFERENCES comments(id) ON DELETE CASCADE,
    entity_id UUID NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

-- Notifications
CREATE TABLE notifications (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    handler VARCHAR(255),

    entity_id UUID,
    entity_type VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT clock_timestamp()
);

CREATE TABLE IF NOT EXISTS settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    content JSONB DEFAULT '{}'::JSONB,
    scope VARCHAR(20) DEFAULT 'global' CHECK (
        scope IN ('global', 'account', 'sales', 'purchase', 'inventory', 'hr')
    ),
    target_id UUID,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (name, scope, target_id)
);

