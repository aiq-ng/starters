

USE inventory_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Manager', 'Staff') DEFAULT 'Staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 location VARCHAR(100) NOT NULL,
 vendor VARCHAR(100) NOT NULL,
 code VARCHAR(20) NOT NULL UNIQUE,
 sku VARCHAR(50) UNIQUE,
 barcode VARCHAR(50),
 media_path VARCHAR(255),
 price DECIMAL(10, 2) NOT NULL,
 quantity INT NOT NULL,
 unit VARCHAR(50) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE warehouses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    warehouse_id INT NOT NULL,
    user_id INT,  -- User responsible for the inventory
    status ENUM('Available', 'Depleting', 'Unavailable', 'KIV') DEFAULT 'Available',
    inventory_date DATE NOT NULL,  -- Date of the inventory check
    on_hand INT NOT NULL,  -- Quantity in stock at the time of the check
    to_be_ordered INT NOT NULL, --
    to_be_delivered INT NOT NULL, --
    counted INT NOT NULL,  -- Quantity counted during the inventory check
  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);

CREATE TABLE ProductUpdates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT,
    previous_quantity INT NOT NULL,
    new_quantity INT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    notes TEXT,
    discrepancies INT NOT NULL,  -- Difference between old and new quantity
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
);
  