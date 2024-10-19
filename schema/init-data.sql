use startersDB;

-- Insert default roles
INSERT INTO roles (name) VALUES
('Admin'),
('Manager'),
('Staff');

-- Seed data for users
INSERT INTO users (name, email, password, role, avatar_url) VALUES
('Alice Johnson', 'alice@example.com', 'password123', 1, 'https://example.com/avatars/alice.jpg'),
('Bob Smith', 'bob@example.com', 'password123', 2, 'https://example.com/avatars/bob.jpg'),
('Charlie Brown', 'charlie@example.com', 'password123', 3, 'https://example.com/avatars/charlie.jpg');

-- Seed data for products
INSERT INTO products (code, name, sku, price, profit, margin, barcode, quantity, unit, low_stock_alert, medias)
VALUES
('P001', 'Product 1', 'SKU001', 10.00, 2.00, 20.00, '1234567890123', 100, 'item', FALSE, '{"images": ["url1", "url2"]}'),
('P002', 'Product 2', 'SKU002', 20.00, 5.00, 25.00, '1234567890124', 200, 'item', TRUE, '{"images": ["url3"]}');

-- Seed data for warehouses
INSERT INTO warehouses (name, location)
VALUES
('Warehouse A', 'Location A'),
('Warehouse B', 'Location B');

-- Seed data for inventory
INSERT INTO inventory (product_id, warehouse_id, quantity, on_hand)
VALUES
(1, 1, 50, 45),
(2, 2, 75, 70);

-- Seed data for inventory plans
INSERT INTO inventory_plans (name, inventory_date, warehouse_id, status, progress)
VALUES
('Inventory Plan 1', '2024-10-01', 1, 'pending', 0.00),
('Inventory Plan 2', '2024-10-02', 2, 'processing', 50.00);

-- Seed data for inventory_plan_items
INSERT INTO inventory_plan_items (inventory_plan_id, product_id, quantity, on_hand, counted)
VALUES
(1, 1, 50, 45, 48),
(2, 2, 75, 70, 72);

-- Seed data for vendors
INSERT INTO vendors (name, email, phone, address)
VALUES
('Vendor A', 'vendorA@example.com', '1234567890', 'Vendor Address A'),
('Vendor B', 'vendorB@example.com', '0987654321', 'Vendor Address B');

-- Seed data for product_vendors
INSERT INTO product_vendors (product_id, vendor_id)
VALUES
(1, 1),
(2, 2);

-- Seed data for inventory activities
INSERT INTO inventory_activities (inventory_plan_id, employee_id, action)
VALUES
(1, 1, 'create'),
(2, 2, 'update');

