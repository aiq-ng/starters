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
('Snacks', 'Department for local and imported snacks'),
('Beverages', 'Department for drinks, tea, coffee, and juices'),
('Fresh Produce', 'Department for fresh fruits and vegetables');

-- Seed branches
INSERT INTO branches (name, description) VALUES
('Lagos', 'Branch located in Lagos, Nigeria'),
('Abuja', 'Branch located in Abuja, Nigeria');

-- Seed item categories
INSERT INTO item_categories (name, description) VALUES
('perishables', 'Food items with a short shelf life'),
('non-perishables', 'Food items with a long shelf life');

-- Seed vendor categories
INSERT INTO vendor_categories (name, description) VALUES
('Fresh Produce Vendors', 'Vendors supplying fresh fruits and vegetables'),
('Meat Vendors', 'Vendors supplying fresh and frozen meats'),
('Seafood Vendors', 'Vendors supplying fresh and frozen seafood'),
('Snacks Vendors', 'Vendors supplying local snacks like chin chin and plantain chips'),
('Furniture Vendors', 'Vendors supplying household and office furniture');

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

-- Seed item manufacturers
INSERT INTO item_manufacturers (name, website) VALUES
('Dangote Industries', 'https://www.dangote.com'),
('Nestle Nigeria', 'https://www.nestle-cwa.com'),
('PZ Cussons', 'https://www.pzcussons.com'),
('Chi Limited', 'https://www.houseofchi.com'),
('Honeywell Flour Mills', 'https://www.honeywellflour.com');


INSERT INTO items (name, description, sku, price, department_id, manufacturer_id, category_id, unit_id, quantity, threshold_value, expiry_date, media, availability)
VALUES
('Beef', 'Fresh beef cuts', 'SKU001', 10.00, 1, 1, 1, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/IwdmYjG.jpeg"]', 'in stock'),
('Chicken', 'Fresh chicken cuts', 'SKU002', 20.00, 2, 2, 2, 1, 50, 5, '2025-12-31', '["https://i.imgur.com/gnRz12P.png"]', 'in stock'),
('Catfish', 'Fresh catfish fillets', 'SKU003', 15.00, 3, 3, 1, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/MxiMX9v.png"]', 'in stock'),
('Pork', 'Fresh pork cuts', 'SKU004', 12.00, 1, 1, 2, 1, 30, 3, '2025-12-31', '["https://i.imgur.com/dGGizfQ.png"]', 'out of stock'),
('Lamb', 'Fresh lamb cuts', 'SKU005', 25.00, 2, 2, 1, 1, 10, 2, '2025-12-31', '["https://i.imgur.com/8TIGZM2.png"]', 'low stock'),
('Salmon', 'Fresh salmon fillets', 'SKU006', 30.00, 3, 3, 2, 1, 150, 10, '2025-12-31', '["https://i.imgur.com/ISOOCLs.png"]', 'in stock'),
('Eggs', 'Farm fresh eggs', 'SKU011', 5.00, 1, 1, 2, 1, 500, 50, '2025-12-31', '["https://i.imgur.com/G0mVY78.png"]', 'in stock'),
('Cheese', 'Fresh cheese', 'SKU012', 3.50, 2, 2, 1, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/IVCT63j.png"]', 'in stock'),
('Milk', 'Fresh cow milk', 'SKU013', 2.00, 3, 3, 2, 1, 400, 40, '2025-12-31', '["https://i.imgur.com/5JXHh4d.png"]', 'low stock'),
('Yogurt', 'Fresh yogurt', 'SKU014', 1.80, 1, 1, 1, 1, 300, 30, '2025-12-31', '["https://i.imgur.com/NQTBB4c.jpeg"]', 'in stock'),
('Bread', 'Freshly baked bread', 'SKU016', 1.50, 1, 1, 1, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/jA1O0Qb.png"]', 'in stock'),
('Rice', 'Premium rice', 'SKU017', 2.50, 1, 2, 2, 1, 250, 25, '2025-12-31', '["https://i.imgur.com/pwXSxkn.png"]', 'in stock'),
('Pasta', 'Premium pasta', 'SKU018', 1.00, 1, 3, 2, 1, 500, 50, '2025-12-31', '["https://i.imgur.com/ZLncFYM.png"]', 'out of stock'),
('Honey', 'Organic honey', 'SKU023', 5.00, 3, 2, 2, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/PheCs9s.png"]', 'in stock'),
('Olive Oil', 'Extra virgin olive oil', 'SKU024', 6.00, 1, 1, 1, 1, 80, 8, '2025-12-31', '["https://i.imgur.com/GEkayag.png"]', 'in stock'),
('Vegetable Oil', 'Pure vegetable oil', 'SKU025', 3.50, 2, 2, 1, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/W5F6Gzv.png"]', 'low stock'),
('Mustard', 'Organic mustard', 'SKU029', 1.50, 3, 3, 2, 1, 150, 15, '2025-12-31', '["https://i.imgur.com/GwxyZSF.png"]', 'in stock'),
('Ketchup', 'Organic ketchup', 'SKU030', 1.50, 1, 1, 1, 1, 300, 30, '2025-12-31', '["https://i.imgur.com/JfO21Bm.png"]', 'in stock'),
('Mayonnaise', 'Organic mayonnaise', 'SKU031', 2.00, 3, 3, 2, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/PSuuQmI.png"]', 'out of stock'),
('Soy Sauce', 'Premium soy sauce', 'SKU032', 1.00, 2, 2, 2, 1, 150, 15, '2025-12-31', '["https://i.imgur.com/zMLrQYD.png"]', 'in stock');

-- Seed vendors
INSERT INTO vendors (
    salutation, first_name, last_name, company_name, display_name, 
    email, work_phone, mobile_phone, address, social_media, 
    payment_term_id, currency_id, category_id
) VALUES
('Mr', 'John', 'Doe', 'Beef Supplies Ltd.', 'John Doe (Beef Supplies)', 
 'johndoe@beefsupplies.ng', '0123456789', '08012345678', 
 '123 Meat Street, Lagos, Nigeria', '{"facebook": "https://facebook.com/beefsupplies"}', 
 1, 1, 1),
('Mrs', 'Jane', 'Smith', 'Fresh Chickens Ltd.', 'Jane Smith (Fresh Chickens)', 
 'janesmith@freshchickens.ng', '0123456790', '08012345679', 
 '456 Poultry Avenue, Ibadan, Nigeria', '{"twitter": "https://twitter.com/freshchickens"}', 
 2, 1, 2),
('Miss', 'Mary', 'Johnson', 'Catfish Traders Co.', 'Mary Johnson (Catfish Traders)', 
 'maryjohnson@catfishco.ng', '0123456791', '08012345680', 
 '789 Fish Market, Port Harcourt, Nigeria', '{"instagram": "https://instagram.com/catfishco"}', 
 3, 1, 1),
('Dr', 'Peter', 'Oluwole', 'Pork Processing Plc.', 'Dr. Peter Oluwole (Pork Processing)', 
 'peteroluwole@porkplc.ng', '0123456792', '08012345681', 
 '101 Meat Lane, Abuja, Nigeria', '{"linkedin": "https://linkedin.com/company/porkprocessing"}', 
 4, 1, 2),
('Prof', 'Amaka', 'Okafor', 'Lamb Lovers Inc.', 'Prof. Amaka Okafor (Lamb Lovers)', 
 'amakaokafor@lamblovers.ng', '0123456793', '08012345682', 
 '202 Sheep Street, Enugu, Nigeria', '{"youtube": "https://youtube.com/lamblovers"}', 
 1, 1, 2);

-- Seed customers
INSERT INTO customers (customer_type, salutation, first_name, last_name, display_name, company_name, email, work_phone, mobile_phone, address, social_media)
VALUES
('individual', 'Mr', 'Aliyu', 'Abdullahi', 'Aliyu Abdullahi', 
 NULL, 'aliyuabdullahi@gmail.com', '0123456794', '08012345683', 
 'No. 15 Market Road, Kano, Nigeria', '{"facebook": "https://facebook.com/aliyuabdullahi"}'),
('business', 'Mrs', 'Titi', 'Adedayo', 'Adedayo Enterprises', 
 'Adedayo Enterprises', 'titiadedayo@adedayoenterprises.ng', '0123456795', 
 '08012345684', 'Plot 7 Industrial Layout, Lagos, Nigeria', 
 '{"twitter": "https://twitter.com/adedayoenterprises"}'),
('individual', 'Miss', 'Bola', 'Ogunyemi', 'Bola Ogunyemi', 
 NULL, 'bolaogunyemi@yahoo.com', '0123456796', '08012345685', 
 'Flat 3, Block B, Ibadan, Nigeria', '{"instagram": "https://instagram.com/bolaogunyemi"}'),
('business', 'Dr', 'Chinedu', 'Eze', 'Eze Agro Ltd.', 
 'Eze Agro Ltd.', 'chinedueze@ezeagro.ng', '0123456797', 
 '08012345686', '123 Farmland Avenue, Umuahia, Nigeria', 
 '{"linkedin": "https://linkedin.com/company/ezeagro"}'),
('individual', 'Prof', 'Amina', 'Yusuf', 'Prof. Amina Yusuf', 
 NULL, 'aminayusuf@gmail.com', '0123456798', '08012345687', 
 'No. 10 Crescent, Abuja, Nigeria', '{"youtube": "https://youtube.com/aminayusuf"}');

