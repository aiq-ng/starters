-- Table to store user roles
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

-- Table to store user details
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

-- Table to store information about units of measurement
CREATE TABLE units (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    abbreviation VARCHAR(10) UNIQUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Table to store information about products
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE,
    price DECIMAL(10, 2) NOT NULL,
    profit DECIMAL(10, 2),
    margin DECIMAL(5, 2),
    barcode VARCHAR(100),
    unit_id INT REFERENCES units(id) ON DELETE SET NULL,
    low_stock_alert BOOLEAN DEFAULT FALSE,
    media JSONB,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Table to store information about warehouses
CREATE TABLE warehouses (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL
);

-- Table to store storage locations within warehouses
CREATE TABLE warehouse_storages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    warehouse_id INT REFERENCES warehouses(id) ON DELETE CASCADE
);

-- Table to manage stock levels of products within warehouses
CREATE TABLE inventory (
    id SERIAL PRIMARY KEY,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    warehouse_id INT REFERENCES warehouses(id) ON DELETE CASCADE,
    storage_id INT REFERENCES warehouse_storages(id) ON DELETE CASCADE,
    quantity INT NOT NULL,
    on_hand INT NOT NULL,
    to_be_delivered INT DEFAULT 0,
    to_be_ordered INT DEFAULT 0,
    UNIQUE (product_id, warehouse_id, storage_id)
);

-- Table to manage inventory plans
CREATE TABLE inventory_plans (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    inventory_date DATE NOT NULL,
    warehouse_id INT REFERENCES warehouses(id) ON DELETE CASCADE,
    status VARCHAR(50) CHECK (status IN ('pending', 'processing', 'complete')) DEFAULT 'pending',
    progress DECIMAL(5, 2) DEFAULT 0
);

-- Linking inventory plans with specific products
CREATE TABLE inventory_plan_items (
    id SERIAL PRIMARY KEY,
    inventory_plan_id INT REFERENCES inventory_plans(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    quantity INT NOT NULL,
    on_hand INT NOT NULL,
    counted INT NOT NULL,
    difference INT GENERATED ALWAYS AS (counted - on_hand) STORED
);

-- Table for vendors information
CREATE TABLE vendors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT
);

-- Linking products with vendors
CREATE TABLE product_vendors (
    id SERIAL PRIMARY KEY,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    vendor_id INT REFERENCES vendors(id) ON DELETE CASCADE,
    UNIQUE (product_id, vendor_id)
);

-- Table to track inventory activities by users
CREATE TABLE inventory_activities (
    id SERIAL PRIMARY KEY,
    inventory_plan_id INT REFERENCES inventory_plans(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    action VARCHAR(50) CHECK (action IN ('create', 'update', 'complete', 'sale', 'purchase', 'audit')) NOT NULL,
    timestamp TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Table to store sales information
CREATE TABLE sales (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),
    product_id INT REFERENCES products(id),
    vendor_id INT REFERENCES vendors(id),
    quantity INT NOT NULL,
    sale_price NUMERIC(10, 2) NOT NULL,
    total_price NUMERIC(10, 2) GENERATED ALWAYS AS (quantity * sale_price) STORED,
    sale_date DATE NOT NULL
);

-- Table to store inventory audit trail
CREATE TABLE inventory_audits (
    id SERIAL PRIMARY KEY,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    old_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    discrepancy INT NOT NULL,
    reason TEXT CHECK (reason IN ('damaged', 'stolen', 'returned', 'adjustment')),
    notes TEXT,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Table to store supplier information
CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Table to store purchase orders
CREATE TABLE purchases (
    id SERIAL PRIMARY KEY,
    purchase_date DATE NOT NULL,
    supplier_id INT REFERENCES suppliers(id) ON DELETE CASCADE,
    total_cost DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Table to store items within a purchase
CREATE TABLE purchase_items (
    id SERIAL PRIMARY KEY,
    purchase_id INT REFERENCES purchases(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) GENERATED ALWAYS AS (quantity * price_per_unit) STORED,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (purchase_id, product_id)
);

