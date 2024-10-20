-- Insert default roles
INSERT INTO roles (name) VALUES
('Admin'),
('Manager'),
('Staff');

-- Seed data for units
INSERT INTO units (name, abbreviation) VALUES
('Item', 'pcs'),
('Kilogram', 'kg'),
('Liter', 'L'),
('Box', 'box'),
('Meter', 'm'),
('Dozen', 'doz');

-- Seed data for users
INSERT INTO users (name, email, password, role_id, avatar_url) VALUES
('Alice Johnson', 'alice@example.com', 'password123', 1, 'https://example.com/avatars/alice.jpg'),
('Bob Smith', 'bob@example.com', 'password123', 2, 'https://example.com/avatars/bob.jpg'),
('Charlie Brown', 'charlie@example.com', 'password123', 3, 'https://example.com/avatars/charlie.jpg');

-- Seed data for products
INSERT INTO products (code, name, sku, price, profit, margin, barcode, unit_id, low_stock_alert, media)
VALUES
('PROD001', 'Product 1', 'SKU001', 10.00, 2.00, 20.00, '1234567890123', 1, FALSE, '{"images": ["url1", "url2"]}'),
('PROD002', 'Product 2', 'SKU002', 20.00, 5.00, 25.00, '1234567890124', 1, TRUE, '{"images": ["url3"]}');

-- Seed data for warehouses
INSERT INTO warehouses (name, address)
VALUES
('Warehouse A', 'No 2 B Close off 11 crescent, Kado'),
('Warehouse B', 'No 5 C Close off 22 crescent, Gwarinpa');

-- Seed data for warehouse_storages
INSERT INTO warehouse_storages (name, warehouse_id) VALUES
('Cold Room', 1),
('Kitchen', 2);

-- Seed data for inventory
INSERT INTO inventory (product_id, warehouse_id, storage_id, quantity, on_hand)
VALUES
(1, 1, 1, 50, 45),
(2, 2, 2, 75, 70);

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
('Mrs Abubakar Global Enterprises', 'info@acmesupplies.com', '08123456789', '12 Builders Lane, Abuja, Nigeria'),
('Jumbo Foods Nigeria Ltd.', 'contact@jumbofoods.com.ng', '08012345678', '45 Olufemi Street, Lagos, Nigeria'),
('Easy Fresh Farms', 'info@easyfreshfarms.com', '09012345678', '25 Greenfield Avenue, Ibadan, Oyo State, Nigeria'),
('Sunshine Beverages', 'info@sunshinebeverages.com.ng', '08134567890', '55 Refreshment Avenue, Kaduna, Kaduna State, Nigeria');

-- Seed data for product_vendors
INSERT INTO product_vendors (product_id, vendor_id)
VALUES
(1, 1),
(2, 2);

-- Seed data for inventory activities
INSERT INTO inventory_activities (inventory_plan_id, user_id, action)
VALUES
(1, 1, 'create'),
(2, 2, 'update');

