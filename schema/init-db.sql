-- Roles
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);


--Admins
--CREATE TYPE permission_type AS ENUM ('Accountant', 'HR', 'Inventory Manager', 'Sales');

--CREATE TABLE admins (
--    id SERIAL PRIMARY KEY,
--    username VARCHAR(100) UNIQUE NOT NULL,
--    password VARCHAR(255) NOT NULL,
--    role_id INT DEFAULT 1,
--    permissions permission_type NOT NULL
--    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
--    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
--    CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET DEFAULT  
--);

--Employees

--CREATE TABLE employees (
--    id SERIAL PRIMARY KEY,
--    firstname VARCHAR(100) NOT NULL,
--    lastname VARCHAR(100) NOT NULL,
--    department VARCHAR(100) NOT NULL,
--    salaries INT, 
--    bank_details JSONB,
--    date_of_birth DATE, 
--    leave DATE, 
--    date_of_employment DATE,
--    nin JSONB,
--    passport JSONB,
--    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
--    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP    
--);


-- Users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT DEFAULT 3,
    avatar_url TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET DEFAULT
);

-- Currencies
CREATE TABLE currencies (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    symbol VARCHAR(10) UNIQUE NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Departments
CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
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

-- Payment Terms
CREATE TABLE payment_terms (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
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
    balance DECIMAL(10, 2) DEFAULT 0,
    status VARCHAR(50) GENERATED ALWAYS AS (
        CASE
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
    amount DECIMAL(10, 2) NOT NULL,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(id::TEXT, 10, '0')
    ) STORED,
    notes TEXT,
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
    price DECIMAL(10, 2),
    opening_stock INT DEFAULT 0,
    on_hand INT DEFAULT 0,
    threshold_value INT DEFAULT 0,
    availability VARCHAR(50) GENERATED ALWAYS AS (
        CASE 
            WHEN on_hand = 0 THEN 'out of stock'
            WHEN on_hand < threshold_value THEN 'low stock'
            ELSE 'in stock'
        END
    ) STORED,
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


-- Item Stock Adjustments
CREATE TABLE item_stock_adjustments (
    id SERIAL PRIMARY KEY,
    stock_id INT REFERENCES item_stocks(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE SET NULL,
    user_department_id INT REFERENCES departments(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    adjustment_type VARCHAR(50) 
        CHECK (adjustment_type IN ('addition', 'subtraction')),
    description TEXT,
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
    balance DECIMAL(10, 2) DEFAULT 0,
    status VARCHAR(50) GENERATED ALWAYS AS (
        CASE
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
    amount DECIMAL(10, 2) NOT NULL,
    reference_number VARCHAR(50) GENERATED ALWAYS AS (
        'REF' || LPAD(id::TEXT, 10, '0')
    ) STORED,
    notes TEXT,
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
    delivery_date DATE NOT NULL,
    payment_term_id INT REFERENCES payment_terms(id) ON DELETE SET NULL,
    subject TEXT,
    notes TEXT,
    terms_and_conditions TEXT,
    discount DECIMAL(10, 2) DEFAULT 0,
    shipping_charge DECIMAL(10, 2) DEFAULT 0,
    total DECIMAL(10, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending' 
        CHECK (status IN ('pending', 'processing', 'completed', 'cancelled')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Purchase Order Items
CREATE TABLE purchase_order_items (
    id SERIAL PRIMARY KEY,
    purchase_order_id INT REFERENCES purchase_orders(id) ON DELETE CASCADE,
    item_id INT REFERENCES items(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    tax_id INT REFERENCES taxes(id) ON DELETE SET NULL,
    total DECIMAL(10, 2) GENERATED ALWAYS AS (quantity * price) STORED,
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
    customer_id INT REFERENCES customers(id) ON DELETE SET NULL,
    payment_term_id INT REFERENCES payment_terms(id) ON DELETE SET NULL,
    payment_method_id INT REFERENCES payment_methods(id) ON DELETE SET NULL,
    delivery_option VARCHAR(50) 
        CHECK (delivery_option IN ('pickup', 'delivery')),
    assigned_driver_id INT REFERENCES users(id) ON DELETE SET NULL,
    delivery_date DATE NOT NULL,
    additional_note TEXT,
    customer_note TEXT,
    discount DECIMAL(10, 2) DEFAULT 0,
    delivery_charge DECIMAL(10, 2) DEFAULT 0,
    total DECIMAL(10, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending' 
        CHECK (status IN ('upcoming', 'pending', 'sent', 'completed', 'cancelled')),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Sales Order Items
CREATE TABLE sales_order_items (
    id SERIAL PRIMARY KEY,
    sales_order_id INT REFERENCES sales_orders(id) ON DELETE CASCADE,
    item_id INT REFERENCES items(id) ON DELETE SET NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) GENERATED ALWAYS AS (quantity * price) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
