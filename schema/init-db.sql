CREATE DATABASE IF NOT EXISTS startersDB;

USE startersDB;

-- Table to store user roles
CREATE TABLE IF NOT EXISTS roles (
	id SERIAL PRIMARY KEY,
	name VARCHAR(50) UNIQUE NOT NULL
);

-- Table to store user details
CREATE TABLE IF NOT EXISTS users (
	id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	email VARCHAR(255) UNIQUE NOT NULL,
	password VARCHAR(255) NOT NULL,
	role_id INT DEFAULT 3,
	avatar_url TEXT,
	created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET DEFAULT
);

-- Table to store information about products
CREATE TABLE IF NOT EXISTS products (
	id SERIAL PRIMARY KEY,
	code VARCHAR(50) UNIQUE NOT NULL,
	name VARCHAR(255) NOT NULL,
	sku VARCHAR(100) UNIQUE,
	price DECIMAL(10, 2) NOT NULL,
	profit DECIMAL(10, 2),
	margin DECIMAL(5, 2),
	barcode VARCHAR(100),
	quantity INT NOT NULL,
	unit VARCHAR(50) DEFAULT 'item',
	low_stock_alert BOOLEAN DEFAULT FALSE,
	medias JSONB,
	created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Table to store information about warehouses
CREATE TABLE IF NOT EXISTS warehouses (
	id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	location TEXT NOT NULL
);

-- Table to manage stock levels of products within warehouses
CREATE TABLE IF NOT EXISTS inventory (
	id SERIAL PRIMARY KEY,
	product_id INT REFERENCES products(id) ON DELETE CASCADE,
	warehouse_id INT REFERENCES warehouses(id) ON DELETE CASCADE,
	quantity INT NOT NULL,
	on_hand INT NOT NULL,
	to_be_delivered INT DEFAULT 0,
	to_be_ordered INT DEFAULT 0,
	UNIQUE (product_id, warehouse_id)
);

-- Table to manage inventory plans
CREATE TABLE IF NOT EXISTS inventory_plans (
	id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	inventory_date DATE NOT NULL,
	warehouse_id INT REFERENCES warehouses(id) ON DELETE CASCADE,
	status VARCHAR(50) CHECK (status IN ('pending', 'processing', 'complete')) DEFAULT 'pending',
	progress DECIMAL(5, 2) DEFAULT 0
);

-- Linking inventory plans with specific products
CREATE TABLE IF NOT EXISTS inventory_plan_items (
	id SERIAL PRIMARY KEY,
	inventory_plan_id INT REFERENCES inventory_plans(id) ON DELETE CASCADE,
	product_id INT REFERENCES products(id) ON DELETE CASCADE,
	quantity INT NOT NULL,
	on_hand INT NOT NULL,
	counted INT NOT NULL,
	difference INT GENERATED ALWAYS AS (counted - on_hand) STORED
);

-- Table for vendors information
CREATE TABLE IF NOT EXISTS vendors (
	id SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	email VARCHAR(255),
	phone VARCHAR(20),
	address TEXT
);

-- Linking products with vendors
CREATE TABLE IF NOT EXISTS product_vendors (
	id SERIAL PRIMARY KEY,
	product_id INT REFERENCES products(id) ON DELETE CASCADE,
	vendor_id INT REFERENCES vendors(id) ON DELETE CASCADE,
	UNIQUE (product_id, vendor_id)
);

-- Table to track inventory activities by employees
CREATE TABLE IF NOT EXISTS inventory_activities (
	id SERIAL PRIMARY KEY,
	inventory_plan_id INT REFERENCES inventory_plans(id) ON DELETE CASCADE,
	employee_id INT REFERENCES employees(id) ON DELETE CASCADE,
	action VARCHAR(50) CHECK (action IN ('create', 'update', 'complete')),
	timestamp TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

