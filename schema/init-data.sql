-- Insert default roles
-- Seed data for roles
INSERT INTO roles (name) VALUES
('Admin'),
('Manager'),
('Staff'),
('Rider');

-- Seed data for users
INSERT INTO users (name, email, password, role_id, avatar_url) VALUES
('Starters', 'starters@admin.com', 'password123', 1, 'https://example.com/avatars/admin.jpg'),
('Alice Johnson', 'alice@example.com', 'password123', 1, 'https://example.com/avatars/alice.jpg'),
('Bob Smith', 'bob@example.com', 'password123', 2, 'https://example.com/avatars/bob.jpg'),
('Charlie Brown', 'charlie@example.com', 'password123', 3, 'https://example.com/avatars/charlie.jpg');

-- Seed currencies
INSERT INTO currencies (name, symbol) VALUES
('Naira', '₦'),
('Cedis', 'GH₵'),
('Dollar', '$'),
('Rand', 'R');

-- Seed payment methods
INSERT INTO payment_methods (name, description) VALUES
('Bank Transfer', 'Payment via bank transfer'),
('USSD Payment', 'Payment via USSD banking codes'),
('Cash', 'Payment in cash');

-- Seed payment terms
INSERT INTO payment_terms (name, description) VALUES
('Net 7', 'Payment due in 7 days'),
('Net 14', 'Payment due in 14 days'),
('Net 30', 'Payment due in 30 days'),
('COD', 'Cash on delivery');

-- Seed departments
INSERT INTO departments (name, description) VALUES
('Electronics', 'Department for electronic gadgets and devices'),
('Groceries', 'Department for food and beverages'),
('Furniture', 'Department for household furniture');

-- Seed branches
INSERT INTO branches (name, description) VALUES
('Lagos', 'Branch located in Lagos, Nigeria'),
('Abuja', 'Branch located in Abuja, Nigeria');

-- Seed item categories
INSERT INTO item_categories (name, description) VALUES
('perishaables', 'Food items with a short shelf life'),
('non-perishables', 'Food items with a long shelf life');

-- Seed vendor categories
INSERT INTO vendor_categories (name, description) VALUES
('Electronics Vendors', 'Vendors supplying electronic items'),
('Food Vendors', 'Vendors supplying food and beverages'),
('Furniture Vendors', 'Vendors supplying household furniture');

-- Seed item manufacturers
INSERT INTO item_manufacturers (name, website) VALUES
('Yaale Electronics', 'https://www.yaaleelectronics.com'),
('FarmFresh Nigeria', 'https://www.farmfresh.com.ng'),
('Vono Furniture', 'https://www.vonofurniture.com.ng');

-- Seed taxes
INSERT INTO taxes (name, rate, description) VALUES
('VAT', 7.50, 'Value Added Tax in Nigeria'),
('Sales Tax', 5.00, 'General Sales Tax in West Africa');

-- Seed data for units
INSERT INTO units (name, abbreviation) VALUES
('item', 'pcs'),
('kilogram', 'kg'),
('liter', 'L'),
('box', 'box'),
('meter', 'm'),
('dozen', 'doz');


INSERT INTO items (name, description, sku, price, manufacturer_id, category_id, unit_id, stock_quantity, threshold, expiry_date, media, status)
VALUES
('Beef', 'Fresh beef cuts', 'SKU001', 10.00, 1, 1, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/IwdmYjG.jpeg"]', 'in stock'),
('Chicken', 'Fresh chicken cuts', 'SKU002', 20.00, 2, 2, 1, 50, 5, '2025-12-31', '["https://i.imgur.com/gnRz12P.png"]', 'in stock'),
('Catfish', 'Fresh catfish fillets', 'SKU003', 15.00, 3, 3, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/MxiMX9v.png"]', 'in stock'),
('Pork', 'Fresh pork cuts', 'SKU004', 12.00, 4, 4, 1, 30, 3, '2025-12-31', '["https://i.imgur.com/dGGizfQ.png"]', 'out of stock'),
('Lamb', 'Fresh lamb cuts', 'SKU005', 25.00, 5, 5, 1, 10, 2, '2025-12-31', '["https://i.imgur.com/8TIGZM2.png"]', 'low stock'),
('Salmon', 'Fresh salmon fillets', 'SKU006', 30.00, 6, 6, 1, 150, 10, '2025-12-31', '["https://i.imgur.com/ISOOCLs.png"]', 'in stock'),
('Eggs', 'Farm fresh eggs', 'SKU011', 5.00, 7, 7, 1, 500, 50, '2025-12-31', '["https://i.imgur.com/G0mVY78.png"]', 'in stock'),
('Cheese', 'Fresh cheese', 'SKU012', 3.50, 8, 8, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/IVCT63j.png"]', 'in stock'),
('Milk', 'Fresh cow milk', 'SKU013', 2.00, 9, 9, 1, 400, 40, '2025-12-31', '["https://i.imgur.com/5JXHh4d.png"]', 'low stock'),
('Yogurt', 'Fresh yogurt', 'SKU014', 1.80, 10, 10, 1, 300, 30, '2025-12-31', '["https://i.imgur.com/NQTBB4c.jpeg"]', 'in stock'),
('Bread', 'Freshly baked bread', 'SKU016', 1.50, 11, 11, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/jA1O0Qb.png"]', 'in stock'),
('Rice', 'Premium rice', 'SKU017', 2.50, 12, 12, 1, 250, 25, '2025-12-31', '["https://i.imgur.com/pwXSxkn.png"]', 'in stock'),
('Pasta', 'Premium pasta', 'SKU018', 1.00, 13, 13, 1, 500, 50, '2025-12-31', '["https://i.imgur.com/ZLncFYM.png"]', 'out of stock'),
('Honey', 'Organic honey', 'SKU023', 5.00, 14, 14, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/PheCs9s.png"]', 'in stock'),
('Olive Oil', 'Extra virgin olive oil', 'SKU024', 6.00, 15, 15, 1, 80, 8, '2025-12-31', '["https://i.imgur.com/GEkayag.png"]', 'in stock'),
('Vegetable Oil', 'Pure vegetable oil', 'SKU025', 3.50, 16, 16, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/W5F6Gzv.png"]', 'low stock'),
('Mustard', 'Organic mustard', 'SKU029', 1.50, 17, 17, 1, 150, 15, '2025-12-31', '["https://i.imgur.com/GwxyZSF.png"]', 'in stock'),
('Ketchup', 'Organic ketchup', 'SKU030', 1.50, 18, 18, 1, 300, 30, '2025-12-31', '["https://i.imgur.com/JfO21Bm.png"]', 'in stock'),
('Mayonnaise', 'Organic mayonnaise', 'SKU031', 2.00, 19, 19, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/PSuuQmI.png"]', 'out of stock'),
('Soy Sauce', 'Premium soy sauce', 'SKU032', 1.00, 20, 20, 1, 150, 15, '2025-12-31', '["https://i.imgur.com/zMLrQYD.png"]', 'in stock');

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
INSERT INTO inventory (product_id, warehouse_id, storage_id, quantity, on_hand, to_be_delivered, to_be_ordered, counted)
VALUES
(1, 1, 1, 50, 45, 10, 5, 50),   -- Beef
(2, 2, 2, 75, 70, 15, 10, 70),  -- Chicken
(3, 1, 1, 30, 25, 5, 3, 25),    -- Catfish
(4, 2, 2, 60, 55, 12, 7, 60),   -- Pork
(5, 1, 1, 40, 35, 8, 6, 30),    -- Lamb
(6, 2, 2, 20, 15, 4, 2, 15),    -- Salmon
(7, 1, 1, 100, 90, 20, 10, 85), -- Eggs
(8, 2, 2, 80, 75, 18, 9, 70),   -- Cheese
(9, 1, 1, 120, 110, 25, 15, 100),-- Milk
(10, 2, 2, 50, 45, 7, 5, 45),   -- Yogurt
(11, 1, 1, 90, 85, 12, 8, 85),  -- Bread
(12, 2, 2, 40, 35, 6, 4, 35),   -- Rice
(13, 1, 1, 55, 50, 10, 5, 55),  -- Pasta
(14, 2, 2, 25, 20, 4, 3, 20),   -- Honey
(15, 1, 1, 35, 30, 5, 2, 30),   -- Olive Oil
(16, 2, 2, 60, 55, 10, 7, 50),  -- Vegetable Oil
(17, 1, 1, 20, 18, 2, 1, 18),   -- Mustard
(18, 2, 2, 80, 75, 16, 12, 75), -- Ketchup
(19, 1, 1, 30, 25, 4, 3, 30),   -- Mayonnaise
(20, 2, 2, 50, 45, 8, 6, 45);   -- Soy Sauce

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

-- Seed data for items_category table
INSERT INTO items_category (name, description)
VALUES
    ('Electronics', 'Devices such as phones, laptops, and accessories'),
    ('Furniture', 'Chairs, tables, and other household furniture'),
    ('Groceries', 'Daily essential food items and beverages'),
    ('Clothing', 'Apparel including shirts, trousers, and dresses'),
    ('Sports', 'Equipment and accessories for sports and fitness'),
    ('Books', 'Fiction, non-fiction, and academic books'),
    ('Toys', 'Toys and games for children and adults'),
    ('Beauty', 'Cosmetics and personal care products');

-- Seed data for departments table
INSERT INTO departments (name, description)
VALUES
    ('Sales', 'Responsible for managing customer relationships and driving revenue'),
    ('Marketing', 'Handles advertising, brand management, and promotions'),
    ('Finance', 'Oversees financial planning, reporting, and accounting'),
    ('Operations', 'Responsible for day-to-day management and efficiency of business processes'),
    ('Dispatch Riders', 'Responsible for delivering goods to customers within designated areas'),
    ('Chefs', 'Oversees food preparation and cooking in the kitchen'),
    ('Kitchen', 'Handles food storage, preparation, and overall kitchen management');

-- Seed data for item_manufacturers table
INSERT INTO item_manufacturers (name, website)
VALUES
    ('Jumia Electronics', 'https://www.jumia.com.ng'),
    ('Vono Furniture', 'https://www.vonofurniture.com.ng'),
    ('FarmFresh Nigeria', 'https://www.farmfresh.com.ng'),
    ('House of Sisi', 'https://www.houseofsisi.com.ng'),
    ('Sporting Goods Nigeria', 'https://www.sportinggoods.com.ng'),
    ('Breeze Publishers', 'https://www.breezepublishers.com.ng'),
    ('Naija Toys', 'https://www.naijatoys.com.ng'),
    ('Beauty Haven', 'https://www.beautyhaven.com.ng');

