-- Insert default roles
INSERT INTO roles (name) VALUES
('Admin'),
('Manager'),
('Staff');

-- Seed data for units
INSERT INTO units (name, abbreviation) VALUES
('item', 'pcs'),
('kilogram', 'kg'),
('liter', 'L'),
('box', 'box'),
('meter', 'm'),
('dozen', 'doz');

-- Seed data for reasons
INSERT INTO reasons (reason) VALUES
('adjustment'),
('damaged'),
('stolen'),
('returned'),
('other');

-- Seed data for users
INSERT INTO users (name, email, password, role_id, avatar_url) VALUES
('Starters', 'starters@admin.com', 'password123', 1, 'https://example.com/avatars/admin.jpg'),
('Alice Johnson', 'alice@example.com', 'password123', 1, 'https://example.com/avatars/alice.jpg'),
('Bob Smith', 'bob@example.com', 'password123', 2, 'https://example.com/avatars/bob.jpg'),
('Charlie Brown', 'charlie@example.com', 'password123', 3, 'https://example.com/avatars/charlie.jpg');

INSERT INTO products (code, name, sku, price, profit, margin, barcode, unit_id, low_stock_alert, media, status)
VALUES
('PROD001', 'Beef', 'SKU001', 10.00, 2.00, 20.00, '1234567890123', 1, FALSE, '["https://i.imgur.com/IwdmYjG.jpeg"]', 'available'),
('PROD002', 'Chicken', 'SKU002', 20.00, 5.00, 25.00, '1234567890124', 1, TRUE, '["https://i.imgur.com/gnRz12P.png"]', 'depleting'),
('PROD003', 'Catfish', 'SKU003', 15.00, 3.00, 15.00, '1234567890125', 1, FALSE, '["https://i.imgur.com/MxiMX9v.png"]', 'available'),
('PROD004', 'Pork', 'SKU004', 12.00, 3.00, 25.00, '1234567890126', 1, FALSE, '["https://i.imgur.com/dGGizfQ.png"]', 'unavailable'),
('PROD005', 'Lamb', 'SKU005', 25.00, 7.00, 28.00, '1234567890127', 1, TRUE, '["https://i.imgur.com/8TIGZM2.png"]', 'available'),
('PROD006', 'Salmon', 'SKU006', 30.00, 10.00, 33.33, '1234567890128', 1, FALSE, '["https://i.imgur.com/ISOOCLs.png"]', 'depleting'),
('PROD007', 'Eggs', 'SKU011', 5.00, 1.00, 20.00, '1234567890133', 1, TRUE, '["https://i.imgur.com/G0mVY78.png"]', 'available'),
('PROD008', 'Cheese', 'SKU012', 3.50, 0.50, 14.29, '1234567890134', 1, FALSE, '["https://i.imgur.com/IVCT63j.png"]', 'available'),
('PROD009', 'Milk', 'SKU013', 2.00, 0.20, 10.00, '1234567890135', 1, TRUE, '["https://i.imgur.com/5JXHh4d.png"]', 'kiv'),
('PROD010', 'Yogurt', 'SKU014', 1.80, 0.30, 16.67, '1234567890136', 1, FALSE, '["https://i.imgur.com/NQTBB4c.jpeg"]', 'available'),
('PROD011', 'Bread', 'SKU016', 1.50, 0.10, 6.67, '1234567890138', 1, FALSE, '["https://i.imgur.com/jA1O0Qb.png"]', 'depleting'),
('PROD012', 'Rice', 'SKU017', 2.50, 0.20, 8.00, '1234567890139', 1, TRUE, '["https://i.imgur.com/pwXSxkn.png"]', 'available'),
('PROD013', 'Pasta', 'SKU018', 1.00, 0.15, 15.00, '1234567890140', 1, FALSE, '["https://i.imgur.com/ZLncFYM.png"]', 'unavailable'),
('PROD014', 'Honey', 'SKU023', 5.00, 1.00, 20.00, '1234567890145', 1, TRUE, '["https://i.imgur.com/PheCs9s.png"]', 'available'),
('PROD015', 'Olive Oil', 'SKU024', 6.00, 1.50, 25.00, '1234567890146', 1, FALSE, '["https://i.imgur.com/GEkayag.png"]', 'available'),
('PROD016', 'Vegetable Oil', 'SKU025', 3.50, 0.50, 14.29, '1234567890147', 1, TRUE, '["https://i.imgur.com/W5F6Gzv.png"]', 'unavailable'),
('PROD017', 'Mustard', 'SKU029', 1.50, 0.10, 6.67, '1234567890151', 1, TRUE, '["https://i.imgur.com/GwxyZSF.png"]', 'available'),
('PROD018', 'Ketchup', 'SKU030', 1.50, 0.20, 13.33, '1234567890152', 1, FALSE, '["https://i.imgur.com/JfO21Bm.png"]', 'kiv'),
('PROD019', 'Mayonnaise', 'SKU031', 2.00, 0.30, 15.00, '1234567890153', 1, TRUE, '["https://i.imgur.com/PSuuQmI.png"]', 'unavailable'),
('PROD020', 'Soy Sauce', 'SKU032', 1.00, 0.15, 15.00, '1234567890154', 1, FALSE, '["https://i.imgur.com/zMLrQYD.png"]', 'available');

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
INSERT INTO inventory (product_id, warehouse_id, storage_id, quantity, on_hand, to_be_delivered, to_be_ordered)
VALUES
(1, 1, 1, 50, 45, 10, 5),   -- Beef
(2, 2, 2, 75, 70, 15, 10),  -- Chicken
(3, 1, 1, 30, 25, 5, 3),    -- Catfish
(4, 2, 2, 60, 55, 12, 7),   -- Pork
(5, 1, 1, 40, 35, 8, 6),    -- Lamb
(6, 2, 2, 20, 15, 4, 2),    -- Salmon
(7, 1, 1, 100, 90, 20, 10), -- Eggs
(8, 2, 2, 80, 75, 18, 9),   -- Cheese
(9, 1, 1, 120, 110, 25, 15),-- Milk
(10, 2, 2, 50, 45, 7, 5),   -- Yogurt
(11, 1, 1, 90, 85, 12, 8),  -- Bread
(12, 2, 2, 40, 35, 6, 4),   -- Rice
(13, 1, 1, 55, 50, 10, 5),  -- Pasta
(14, 2, 2, 25, 20, 4, 3),   -- Honey
(15, 1, 1, 35, 30, 5, 2),   -- Olive Oil
(16, 2, 2, 60, 55, 10, 7),  -- Vegetable Oil
(17, 1, 1, 20, 18, 2, 1),   -- Mustard
(18, 2, 2, 80, 75, 16, 12), -- Ketchup
(19, 1, 1, 30, 25, 4, 3),   -- Mayonnaise
(20, 2, 2, 50, 45, 8, 6);   -- Soy Sauce

-- Seed data for inventory plans
INSERT INTO inventory_plans (name, status, plan_date)
VALUES
('Fresh Food Stock Level', 'todo', '2024-10-01'),
('Perishable Goods Stock Level', 'processing', '2024-10-02'),
('Presidential Dinner Stock Items', 'todo', '2024-10-03'),
('Event XYZ Stock Items', 'completed', '2024-10-04');

-- Seed data for inventory_plan_products
INSERT INTO inventory_plan_products (inventory_plan_id, product_id)
VALUES
(1, 1),
(2, 2),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 20);

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

-- Seed data for sales
INSERT INTO sales (user_id, product_id, quantity, sale_price, sale_date) VALUES
(1, 1, 10, 10.00, '2024-01-15'),  -- Alice sold 10 Beef
(2, 2, 5, 20.00, '2024-01-20'),  -- Bob sold 5 Chicken
(3, 3, 8, 15.00, '2024-01-25'),  -- Charlie sold 8 Catfish
(1, 4, 12, 12.00, '2024-01-30'), -- Alice sold 12 Pork
(2, 5, 15, 25.00, '2024-02-05'), -- Bob sold 15 Lamb
(3, 6, 3, 30.00, '2024-02-10'),   -- Charlie sold 3 Salmon
(1, 7, 20, 5.00, '2024-02-15'),   -- Alice sold 20 Eggs
(2, 8, 10, 3.50, '2024-02-20');     -- Bob sold 10 Cheese

-- Seed data for inventory audits
INSERT INTO inventory_audits (product_id, user_id, old_quantity, new_quantity, discrepancy, reason_id, notes) VALUES
(1, 1, 100, 80, 20, 1, 'Damaged items found'), 
(2, 2, 50, 45, 5, 2, 'Items missing after delivery');
-- Seed data for suppliers
INSERT INTO suppliers (name, email, phone, address) VALUES
('Supplier A', 'supplierone@example.com', '123-456-7890', '123 Supplier St, City, Country'),
('Supplier B', 'suppliertwo@example.com', '987-654-3210', '456 Supplier Ave, City, Country'),
('Supplier C', 'supplierthree@example.com', '456-789-1230', '789 Supplier Blvd, City, Country');

-- Seed data for purchases
INSERT INTO purchases (purchase_date, supplier_id, total_cost) VALUES
('2024-10-01', 1, 1500.00),
('2024-10-05', 2, 2000.00),
('2024-10-10', 3, 2500.00);

-- Seed data for purchase_items
INSERT INTO purchase_items (purchase_id, product_name, quantity, price_per_unit, purchased_at) VALUES
(1, 'sugar', 10, 8.00, '2024-01-15'),
(1, 'beans', 15, 12.00, '2024-01-20'),
(2, 'milk', 5, 15.00, '2024-01-25'),
(2, 'flour', 3, 10.00, '2024-02-05'),
(3, 'oil', 4, 12.00, '2024-02-10');
