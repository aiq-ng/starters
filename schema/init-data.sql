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
('Due on receipt', 'Payment due on receipt'),
('Due on delivery', 'Payment due on delivery'),
('Due in 7 days', 'Payment due in 7 days'),
('Due in 14 days', 'Payment due in 14 days'),
('Due in 30 days', 'Payment due in 30 days');

-- Seed departments
INSERT INTO departments (name, description) VALUES
('Snacks', 'Department for local and imported snacks'),
('Beverages', 'Department for drinks, tea, coffee, and juices'),
('Fresh Produce', 'Department for fresh fruits and vegetables');

-- Seed branches
INSERT INTO branches (name, description) VALUES
('Lagos', 'Branch located in Lagos, Nigeria'),
('Abuja', 'Branch located in Abuja, Nigeria'),
('Port Harcourt', 'Branch located in Port Harcourt, Nigeria');

-- Seed item categories
INSERT INTO item_categories (name, description) VALUES
('perishables', 'Food items with a short shelf life'),
('non-perishables', 'Food items with a long shelf life');

-- Seed vendor categories
INSERT INTO vendor_categories (name, description) VALUES
('Fresh Produce', 'Vendors supplying fresh fruits and vegetables'),
('Kitchen Supply', 'Vendors supplying kitchenware and utensils'),
('Meat', 'Vendors supplying fresh and frozen meats'),
('Seafood', 'Vendors supplying fresh and frozen seafood'),
('Snacks', 'Vendors supplying local snacks like chin chin and plantain chips'),
('Furniture', 'Vendors supplying household and office furniture');

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


INSERT INTO items (name, description, price, department_id, manufacturer_id, category_id, unit_id, quantity, threshold_value, expiry_date, media)
VALUES
('Beef', 'Fresh beef cuts', 10.00, 1, 1, 1, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/IwdmYjG.jpeg"]'),
('Chicken', 'Fresh chicken cuts', 20.00, 2, 2, 2, 1, 50, 5, '2025-12-31', '["https://i.imgur.com/gnRz12P.png"]'),
('Catfish', 'Fresh catfish fillets', 15.00, 3, 3, 1, 1, 10, 20, '2025-12-31', '["https://i.imgur.com/MxiMX9v.png"]'),
('Pork', 'Fresh pork cuts', 12.00, 1, 1, 2, 1, 30, 3, '2025-12-31', '["https://i.imgur.com/dGGizfQ.png"]'),
('Lamb', 'Fresh lamb cuts', 25.00, 2, 2, 1, 1, 10, 2, '2025-12-31', '["https://i.imgur.com/8TIGZM2.png"]'),
('Salmon', 'Fresh salmon fillets', 30.00, 3, 3, 2, 1, 5, 10, '2025-12-31', '["https://i.imgur.com/ISOOCLs.png"]'),
('Eggs', 'Farm fresh eggs', 5.00, 1, 1, 2, 1, 500, 50, '2025-12-31', '["https://i.imgur.com/G0mVY78.png"]'),
('Cheese', 'Fresh cheese', 3.50, 2, 2, 1, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/IVCT63j.png"]'),
('Milk', 'Fresh cow milk', 2.00, 3, 3, 2, 1, 400, 40, '2025-12-31', '["https://i.imgur.com/5JXHh4d.png"]'),
('Yogurt', 'Fresh yogurt', 1.80, 1, 1, 1, 1, 300, 30, '2025-12-31', '["https://i.imgur.com/NQTBB4c.jpeg"]'),
('Bread', 'Freshly baked bread', 1.50, 1, 1, 1, 1, 5, 10, '2025-12-31', '["https://i.imgur.com/jA1O0Qb.png"]'),
('Rice', 'Premium rice', 2.50, 1, 2, 2, 1, 250, 25, '2025-12-31', '["https://i.imgur.com/pwXSxkn.png"]'),
('Pasta', 'Premium pasta', 1.00, 1, 3, 2, 1, 500, 50, '2025-12-31', '["https://i.imgur.com/ZLncFYM.png"]'),
('Honey', 'Organic honey', 5.00, 3, 2, 2, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/PheCs9s.png"]'),
('Olive Oil', 'Extra virgin olive oil', 6.00, 1, 1, 1, 1, 80, 8, '2025-12-31', '["https://i.imgur.com/GEkayag.png"]'),
('Vegetable Oil', 'Pure vegetable oil', 3.50, 2, 2, 1, 1, 200, 20, '2025-12-31', '["https://i.imgur.com/W5F6Gzv.png"]'),
('Mustard', 'Organic mustard', 1.50, 3, 3, 2, 1, 150, 15, '2025-12-31', '["https://i.imgur.com/GwxyZSF.png"]'),
('Ketchup', 'Organic ketchup', 1.50, 1, 1, 1, 1, 29, 30, '2025-12-31', '["https://i.imgur.com/JfO21Bm.png"]'),
('Mayonnaise', 'Organic mayonnaise', 2.00, 3, 3, 2, 1, 100, 10, '2025-12-31', '["https://i.imgur.com/PSuuQmI.png"]'),
('Soy Sauce', 'Premium soy sauce', 1.00, 2, 2, 2, 1, 150, 15, '2025-12-31', '["https://i.imgur.com/zMLrQYD.png"]');

-- Seed vendors
INSERT INTO vendors (
    salutation, first_name, last_name, company_name, display_name, 
    email, work_phone, mobile_phone, address, social_media, 
    payment_term_id, currency_id, category_id, balance
) VALUES
('Mr', 'John', 'Doe', 'Beef Supplies Ltd.', 'John Doe (Beef Supplies)', 
 'johndoe@beefsupplies.ng', '0123456789', '08012345678', 
 '123 Meat Street, Lagos, Nigeria', '{"facebook": "https://facebook.com/beefsupplies"}', 
 1, 1, 1, 0.00),
('Mrs', 'Jane', 'Smith', 'Fresh Chickens Ltd.', 'Jane Smith (Fresh Chickens)', 
 'janesmith@freshchickens.ng', '0123456790', '08012345679', 
 '456 Poultry Avenue, Ibadan, Nigeria', '{"twitter": "https://twitter.com/freshchickens"}', 
 2, 1, 2, 50000.00),
('Miss', 'Mary', 'Johnson', 'Catfish Traders Co.', 'Mary Johnson (Catfish Traders)', 
 'maryjohnson@catfishco.ng', '0123456791', '08012345680', 
 '789 Fish Market, Port Harcourt, Nigeria', '{"instagram": "https://instagram.com/catfishco"}', 
 3, 1, 1, 20000.00),
('Dr', 'Peter', 'Oluwole', 'Pork Processing Plc.', 'Dr. Peter Oluwole (Pork Processing)', 
 'peteroluwole@porkplc.ng', '0123456792', '08012345681', 
 '101 Meat Lane, Abuja, Nigeria', '{"linkedin": "https://linkedin.com/company/porkprocessing"}', 
 4, 1, 2, 0.00),
('Prof', 'Amaka', 'Okafor', 'Lamb Lovers Inc.', 'Prof. Amaka Okafor (Lamb Lovers)', 
 'amakaokafor@lamblovers.ng', '0123456793', '08012345682', 
 '202 Sheep Street, Enugu, Nigeria', '{"youtube": "https://youtube.com/lamblovers"}', 
 1, 1, 2, 0.00);

-- Seed customers
INSERT INTO customers (customer_type, salutation, first_name, last_name, display_name, company_name, email, work_phone, mobile_phone, address, social_media, balance)
VALUES
('individual', 'Mr', 'Aliyu', 'Abdullahi', 'Aliyu Abdullahi', 
 'Agro Tech LTD', 'aliyuabdullahi@gmail.com', '0123456794', '08012345683', 
 'No. 15 Market Road, Kano, Nigeria', '{"facebook": "https://facebook.com/aliyuabdullahi"}', 0.00),
('business', 'Mrs', 'Titi', 'Adedayo', 'Adedayo Enterprises', 
 'Adedayo Enterprises', 'titiadedayo@adedayoenterprises.ng', '0123456795', 
 '08012345684', 'Plot 7 Industrial Layout, Lagos, Nigeria', 
 '{"twitter": "https://twitter.com/adedayoenterprises"}', 1200.00),
('individual', 'Miss', 'Bola', 'Ogunyemi', 'Bola Ogunyemi', 
 'Nat Agro Ltd', 'bolaogunyemi@yahoo.com', '0123456796', '08012345685', 
 'Flat 3, Block B, Ibadan, Nigeria', '{"instagram": "https://instagram.com/bolaogunyemi"}', 50000.00),
('business', 'Dr', 'Chinedu', 'Eze', 'Eze Agro Ltd.', 
 'Eze Agro Ltd.', 'chinedueze@ezeagro.ng', '0123456797', 
 '08012345686', '123 Farmland Avenue, Umuahia, Nigeria', 
 '{"linkedin": "https://linkedin.com/company/ezeagro"}', 0.00),
('individual', 'Prof', 'Amina', 'Yusuf', 'Prof. Amina Yusuf', 
 'Gerald Agro Ltd', 'aminayusuf@gmail.com', '0123456798', '08012345687', 
 'No. 10 Crescent, Abuja, Nigeria', '{"youtube": "https://youtube.com/aminayusuf"}', 0.00);

-- Seed customer transactions
INSERT INTO customer_transactions 
(customer_id, transaction_type, amount, notes)
VALUES
(1, 'credit', 500000.00, 'Initial deposit by Aliyu Abdullahi'),
(2, 'debit', 300000.00, 'Payment for supply of fresh chicken'),
(3, 'credit', 100000.00, 'Payment for supply of fresh catfish'),
(4, 'debit', 45000.00, 'Purchase of fresh pork cuts'),
(5, 'credit', 100000.00, 'Payment for supply of fresh lamb cuts');

-- Seed vendor transactions
INSERT INTO vendor_transactions
(vendor_id, transaction_type, amount, notes)
VALUES
(1, 'credit', 500000.00, 'Initial deposit by Beef Supplies Ltd.'),
(2, 'debit', 300000.00, 'Payment for supply of fresh beef'),
(3, 'debit', 200000.00, 'Purchase of catfish processing equipment'),
(4, 'debit', 450000.00, 'Purchase of meat processing equipment'),
(5, 'credit', 100000.00, 'Payment for meat supplies');

-- Insert into purchase_orders
INSERT INTO purchase_orders (
    vendor_id, branch_id, delivery_date, 
    payment_term_id, subject, notes, terms_and_conditions, 
    discount, shipping_charge, total
)
VALUES
(1, 1, '2024-11-20', 3, 
    'Bulk Purchase of Food Items', 'Ensure quality items', 
    'Goods must be delivered in good condition', 1000, 5000, 150000),
(2, 2, '2024-11-15', 4, 
    'Monthly Grocery Restock', 'Deliver to Abuja branch warehouse', 
    'Invoice must include all taxes', 2000, 2500, 200000),
(3, 3, '2024-11-10', 3, 
    'Catering Supplies', 'Urgent delivery required', 
    'Late delivery will incur penalties', 1500, 1000, 120000);

-- Insert into purchase_order_items
INSERT INTO purchase_order_items (
    purchase_order_id, item_id, quantity, price, tax_id
)
VALUES
(1, 1, 10, 30000, 1),
(1, 2, 20, 10000, 1),
(2, 3, 15, 25000, 2),
(3, 1, 5, 30000, 1),
(3, 3, 10, 25000, 2);

-- Insert into sales_orders
INSERT INTO sales_orders (
    order_type, order_title, customer_id, payment_term_id, 
    payment_method_id, delivery_option, assigned_driver_id, 
    delivery_date, additional_note, customer_note, discount, 
    delivery_charge, total, status
)
VALUES
('order', 'Chicken Jumbo Pack', 1, 1, 1, 'delivery', 1, 
    '2024-12-01', 'Deliver before noon', 'Please call on arrival', 
    1000, 2000, 52000, 'upcoming'),
('order', 'Maxi Puff Puff', 2, 2, 2, 'pickup', NULL, 
    '2024-12-15', 'Ready for Christmas', 'Add plenty sugar', 
    500, 0, 34500, 'pending'),
 ('order', 'Salmon Special', 3, 3, 3, 'delivery', 2, 
    '2024-12-20', 'Handle with care', 'Call before delivery', 
    1500, 2500, 45000, 'completed'),
 ('order', 'Beef Box', 4, 4, 1, 'pickup', NULL, 
    '2024-12-25', 'Festive season order', 'Add extra spice', 
    2000, 0, 30000, 'sent');

-- Insert into sales_order_items
INSERT INTO sales_order_items (sales_order_id, item_id, quantity, price)
VALUES
(1, 1, 1, 30000),
(1, 2, 2, 10000),
(2, 3, 1, 25000),
(2, 2, 1, 10000),
(3, 1, 1, 30000),
(3, 3, 2, 25000),
(4, 1, 1, 30000),
(4, 2, 1, 10000);
