-- Seed data for roles
INSERT INTO roles (name) VALUES
('Admin'),
('Manager'),
('Staff'),
('Rider'),
('HR'),
('Inventory Manager'),
('Sales'),
('Accountant'),
('Marketing'),
('Finance'),
('Customer Service');

INSERT INTO permissions (name, description) VALUES
('sales', 'View and manage sales orders'),
('procurement', 'View and manage purchase orders'),
('hr', 'View and manage HR data'),
('accounting', 'View and manage accounting data'),
('admin', 'Full access to all features'),
('inventory', 'View and manage inventory data');

-- Seed data for departments
INSERT INTO base_pay_types (name, description)
VALUES 
('hourly', 'Base salary calculated based on hourly rates'),
('delivery', 'Base salary calculated based on delivery rates');

-- Seed departments
INSERT INTO departments (name, salary_type, base_type_id, base_rate, base_salary, description)
VALUES
('Snacks', 'fixed', NULL, NULL, 2500.00, 'Department for local and imported snacks'),
('Beverages', 'fixed', NULL, NULL, 2000.00, 'Department for drinks, tea, coffee, and juices'),
('Dispatch Riders', 'base', 2, 15.00, NULL, 'Department for dispatch riders'),
('Kitchen', 'fixed', NULL, NULL, 2200.00, 'Department for kitchenware and utensils'),
('Chef', 'fixed', NULL, NULL, 3000.00, 'Department for chefs and kitchen staff'),
('Sales Representatives', 'base', 1, 20.00, NULL, 'Department for sales staff, paid hourly');

-- Seed data for payment_terms
INSERT INTO no_of_working_days (name, description)
VALUES
('5-Day Workweek', 'Standard Monday to Friday workweek'),
('6-Day Workweek', 'Monday to Saturday workweek'),
('4-Day Workweek', 'Compressed workweek with 4 working days'),
('Flexible Workweek', 'Varied workdays based on company policy'),
('Weekend Work', 'Work schedule includes weekends');

-- Seed data for users
INSERT INTO users (
    firstname, lastname, email, password, role_id, avatar_url, 
    date_of_birth, address, next_of_kin, date_of_employment, salary, 
    bank_details, leave, nin, passport, department_id
) VALUES
('Starters', 'Admin', 'starters@admin.com', 'password123', 1, 'https://i.imgur.com/0GY9tnz.jpeg', 
 NULL, NULL, NULL, NULL, NULL, 
 NULL, NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', NULL),
('Opororo', 'Nathaniel', 'nat@aiq.com', 'password', 2, 'https://i.imgur.com/0GY9tnz.jpeg', 
 '1990-05-15', '123 Main St, Cityville', 'Tom Johnson', '2020-01-01', 3500.00, 
 '{"bank_name": "Bank ABC", "account_number": "1234567890"}', NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', 1),
('Emmanuel', 'Afcon', 'sog@aiq.com', 'password', 2, 'https://i.imgur.com/0GY9tnz.jpeg', 
 '1985-10-20', '456 Oak St, Townsville', 'Sarah Smith', '2018-09-15', 4000.00, 
 '{"bank_name": "Bank XYZ", "account_number": "9876543210"}', '2023-06-01', 'https://i.imgur.com/CD2345678D.jpeg', 'https://i.imgur.com/B2345678.jpeg', 2),
('Babanla', 'Odunlami', 'odun@aiq.com', 'password', 3, 'https://i.imgur.com/0GY9tnz.jpeg', 
 '1992-08-10', '789 Pine St, Villageville', 'Emily Brown', '2019-02-18', 2800.00, 
 '{"bank_name": "Bank LMN", "account_number": "1112233445"}', '2023-05-15', 'https://i.imgur.com/EF3456789E.jpeg', 'https://i.imgur.com/C3456789.jpeg', 3),
('John', 'Doe', 'john@example.com', 'hashedpassword1', 8, 'https://i.imgur.com/0GY9tnz.jpeg', 
 '1988-12-05', '321 Elm St, Hamletville', 'Anna Doe', '2021-07-21', 4200.00, 
 '{"bank_name": "Bank DEF", "account_number": "9988776655"}', NULL, 'https://i.imgur.com/GH4567890F.jpeg', 'https://i.imgur.com/D4567890.jpeg', 4),
('Jane', 'Smith', 'jane@example.com', 'hashedpassword1', 5, 'https://i.imgur.com/0GY9tnz.jpeg', 
 '1994-03-25', '654 Maple St, Citytown', 'Linda Smith', '2022-06-11', 3100.00, 
 '{"bank_name": "Bank GHI", "account_number": "6677889900"}', '2023-08-01', 'https://i.imgur.com/IJ5678901G.jpeg', 'https://i.imgur.com/E5678901.jpeg', 2),
('Mary', 'Jones', 'mary@example.com', 'hashedpassword1', 6, 'https://i.imgur.com/0GY9tnz.jpeg', 
 '1991-11-30', '987 Cedar St, Smallville', 'John Jones', '2017-04-09', 3600.00, 
 '{"bank_name": "Bank JKL", "account_number": "1122334455"}', NULL, 'https://i.imgur.com/KL6789012H.jpeg', 'https://i.imgur.com/F6789012.jpeg', 3),
('Peter', 'Brown', 'peter@example.com', 'hashedpassword1', 7, 'https://i.imgur.com/0GY9tnz.jpeg', 
 '1989-07-15', '123 Birch St, Greenfield', 'Samantha Brown', '2016-03-20', 3900.00, 
 '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 2);

INSERT INTO user_permissions (user_id, permission_id) VALUES
(1, 5),
(2, 1),
(3, 2),
(4, 3),
(5, 4),
(6, 6),
(7, 6);

-- Seed data for user_leaves
INSERT INTO user_leaves (user_id, leave_type, start_date, end_date, status, notes)
VALUES
(1, 'annual', '2024-01-15', '2024-01-20', 'leave taken', 'Annual leave for vacation'),
(2, 'sick', '2024-02-05', '2024-02-07', 'booked', 'Recovering from flu'),
(3, 'maternity', '2024-03-01', '2024-05-30', 'on leave', 'Maternity leave for childbirth'),
(4, 'paternity', '2024-03-10', '2024-03-14', 'leave taken', 'Paternity leave for newborn support'),
(5, 'study', '2024-04-01', '2024-04-15', 'cancelled', 'Cancelled due to change in schedule'),
(6, 'compassionate', '2024-05-10', '2024-05-12', 'booked', 'Family emergency'),
(7, 'unpaid', '2024-06-01', '2024-06-07', 'booked', 'Personal matters');

-- Seed currencies
INSERT INTO currencies (name, symbol, code) VALUES
('Naira', '₦', 'NGN'),
('Pound', '£', 'GBP'),
('Euro', '€', 'EUR'),
('Cedis', 'GH₵', 'GHS'),
('Dollar', '$', 'USD'),
('Rand', 'R', 'ZAR');

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

INSERT INTO branches (name, description) VALUES
('Lagos', 'Branch located in Lagos, Nigeria'),
('Abuja', 'Branch located in Abuja, Nigeria'),
('Port Harcourt', 'Branch located in Port Harcourt, Nigeria');

-- Seed data for units
INSERT INTO units (name, abbreviation) VALUES
('item', 'pcs'),
('kilogram', 'kg'),
('liter', 'L'),
('box', 'box'),
('meter', 'm'),
('carton', 'ctn'),
('pack', 'pk'),
('crate', 'crate'),
('bottle', 'btl'),
('dozen', 'doz');

-- Seed item categories
INSERT INTO item_categories (name, description) VALUES
('pastry', 'Baked goods like bread, cakes, and pastries'),
('seafood', 'Fresh and frozen seafood items'),
('grill', 'Grilled food items like chicken and fish'),
('meat', 'Fresh and frozen meat items'),
('dairy', 'Milk, cheese, yogurt, and other dairy products'),
('beverages', 'Drinks like water, juice, and soft drinks'),
('condiments', 'Sauces, spices, and seasonings'),
('canned', 'Canned food items like beans and tomatoes'),
('frozen', 'Frozen food items like vegetables and fruits');

INSERT INTO price_lists (item_category_id, item_details, unit_price, minimum_order, unit_id) VALUES
-- Pastry (unit: pcs)
(1, 'Croissant', 1.50, 10, 1),
(1, 'Chocolate Cake', 15.00, 1, 1),
(1, 'Baguette', 2.00, 5, 1),

-- Seafood (unit: kg)
(2, 'Salmon Fillet', 12.99, 2, 2),
(2, 'Shrimp', 9.99, 5, 2),
(2, 'Crab Legs', 25.00, 1, 2),

-- Grill (unit: pcs)
(3, 'Grilled Chicken Breast', 8.50, 2, 1),
(3, 'BBQ Ribs', 15.00, 1, 1),
(3, 'Grilled Fish', 10.00, 2, 1),

-- Meat (unit: kg)
(4, 'Beef Steak', 18.00, 1, 2),
(4, 'Pork Chops', 12.00, 2, 2),
(4, 'Ground Beef', 5.50, 5, 2),

-- Dairy (unit: L and pcs)
(5, 'Whole Milk', 2.50, 10, 3),
(5, 'Cheddar Cheese', 4.00, 5, 1),
(5, 'Greek Yogurt', 3.00, 8, 1),

-- Beverages (unit: btl)
(6, 'Orange Juice', 3.00, 6, 9),
(6, 'Mineral Water', 1.00, 12, 9),
(6, 'Cola Drink', 1.50, 12, 9),

-- Condiments (unit: btl)
(7, 'Tomato Ketchup', 2.00, 10, 9),
(7, 'Mayonnaise', 2.50, 10, 9),
(7, 'BBQ Sauce', 3.00, 5, 9),

-- Canned Goods (unit: ctn)
(8, 'Canned Beans', 1.20, 20, 6),
(8, 'Canned Tomatoes', 1.50, 15, 6),
(8, 'Canned Corn', 1.30, 20, 6),

-- Frozen Goods (unit: pk)
(9, 'Frozen Peas', 2.00, 10, 7),
(9, 'Frozen Strawberries', 4.00, 8, 7),
(9, 'Frozen Spinach', 2.50, 10, 7);

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
('Vono Furniture', 'https://www.vonofurniture.com.ng'),
('Naija Snacks', 'https://www.naijasnacks.com'),
('Kitchen Essentials', 'https://www.kitchenessentials.com');

-- Seed taxes
INSERT INTO taxes (name, rate, description) VALUES
('VAT', 7.50, 'Value Added Tax in Nigeria'),
('Sales Tax', 5.00, 'General Sales Tax in West Africa');

-- Seed item manufacturers
INSERT INTO item_manufacturers (name, website) VALUES
('Dangote Industries', 'https://www.dangote.com'),
('Nestle Nigeria', 'https://www.nestle-cwa.com'),
('PZ Cussons', 'https://www.pzcussons.com'),
('Chi Limited', 'https://www.houseofchi.com'),
('Honeywell Flour Mills', 'https://www.honeywellflour.com');

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

-- Seed vendor transactions
INSERT INTO vendor_transactions
(vendor_id, transaction_type, amount, notes)
VALUES
(1, 'credit', 500000.00, 'Initial deposit by Beef Supplies Ltd.'),
(2, 'debit', 300000.00, 'Payment for supply of fresh beef'),
(3, 'debit', 200000.00, 'Purchase of catfish processing equipment'),
(4, 'debit', 450000.00, 'Purchase of meat processing equipment'),
(5, 'credit', 100000.00, 'Payment for meat supplies');

-- Seed data for items
INSERT INTO items (name, description, price, unit_id, opening_stock, threshold_value, media, category_id)
VALUES
('Beef', 'Fresh beef cuts', 10.00, 1, 100, 10, '["https://i.imgur.com/IwdmYjG.jpeg"]', 1),
('Chicken', 'Fresh chicken cuts', 20.00, 1, 500, 50, '["https://i.imgur.com/gnRz12P.png"]', 1),
('Catfish', 'Fresh catfish fillets', 15.00, 1, 100, 50, '["https://i.imgur.com/MxiMX9v.png"]', 1),
('Pork', 'Fresh pork cuts', 12.00, 1, 300, 3, '["https://i.imgur.com/dGGizfQ.png"]', 1),
('Lamb', 'Fresh lamb cuts', 25.00, 1, 100, 2, '["https://i.imgur.com/8TIGZM2.png"]', 2),
('Salmon', 'Fresh salmon fillets', 30.00, 1, 100, 10, '["https://i.imgur.com/ISOOCLs.png"]',2),
('Eggs', 'Farm fresh eggs', 5.00, 1, 500, 50, '["https://i.imgur.com/G0mVY78.png"]', 2),
('Cheese', 'Fresh cheese', 3.50, 1, 200, 20, '["https://i.imgur.com/IVCT63j.png"]', 1),
('Milk', 'Fresh cow milk', 2.00, 1, 400, 40, '["https://i.imgur.com/5JXHh4d.png"]', 2),
('Yogurt', 'Fresh yogurt', 1.80, 1, 300, 30, '["https://i.imgur.com/NQTBB4c.jpeg"]', 1),
('Bread', 'Freshly baked bread', 1.50, 1, 100, 10, '["https://i.imgur.com/jA1O0Qb.png"]', 1),
('Rice', 'Premium rice', 2.50, 1, 250, 25, '["https://i.imgur.com/pwXSxkn.png"]', 2),
('Pasta', 'Premium pasta', 1.00, 1, 500, 50, '["https://i.imgur.com/ZLncFYM.png"]', 2),
('Honey', 'Organic honey', 5.00, 1, 100, 10, '["https://i.imgur.com/PheCs9s.png"]', 1),
('Olive Oil', 'Extra virgin olive oil', 6.00, 1, 80, 8, '["https://i.imgur.com/GEkayag.png"]', 2),
('Vegetable Oil', 'Pure vegetable oil', 3.50, 1, 200, 20, '["https://i.imgur.com/W5F6Gzv.png"]', 2),
('Mustard', 'Organic mustard', 1.50, 1, 150, 15, '["https://i.imgur.com/GwxyZSF.png"]', 1),
('Ketchup', 'Organic ketchup', 1.50, 1, 200, 30, '["https://i.imgur.com/JfO21Bm.png"]', 2),
('Mayonnaise', 'Organic mayonnaise', 2.00, 1, 100, 10, '["https://i.imgur.com/PSuuQmI.png"]', 1),
('Soy Sauce', 'Premium soy sauce', 1.00, 1, 150, 15, '["https://i.imgur.com/zMLrQYD.png"]', 2);

-- Seed data for item_stocks
INSERT INTO item_stocks (item_id, quantity, date_received, expiry_date)
VALUES
(1, 100, '2023-12-01', '2025-12-31'),
(2, 5, '2023-12-01', '2025-12-31'),
(3, 0, '2023-12-01', '2025-12-31'),
(4, 30, '2023-12-01', '2025-12-31'),
(5, 0, '2023-12-01', '2025-12-31'),
(6, 5, '2023-12-01', '2025-12-31'),
(7, 50, '2023-12-01', '2025-12-31'),
(8, 0, '2023-12-01', '2025-12-31'),
(9, 30, '2023-12-01', '2025-12-31'),
(10, 3, '2023-12-01', '2025-12-31'),
(11, 0, '2023-12-01', '2025-12-31'),
(12, 22, '2023-12-01', '2025-12-31'),
(13, 500, '2023-12-01', '2025-12-31'),
(14, 100, '2023-12-01', '2025-12-31'),
(15, 0, '2023-12-01', '2025-12-31'),
(16, 0, '2023-12-01', '2025-12-31'),
(17, 14, '2023-12-01', '2025-12-31'),
(18, 20, '2023-12-01', '2025-12-31'),
(19, 9, '2023-12-01', '2025-12-31'),
(20, 10, '2023-12-01', '2025-12-31');

-- Seed data for item_stock_vendors
INSERT INTO item_stock_vendors (stock_id, vendor_id)
VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

-- Seed data for item_stock_departments
INSERT INTO item_stock_departments (stock_id, department_id)
VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

-- Seed data for item_stock_manufacturers
INSERT INTO item_stock_manufacturers (stock_id, manufacturer_id)
VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

-- Seed customers
INSERT INTO customers (customer_type, salutation, first_name, last_name, display_name, company_name, email, work_phone, mobile_phone, address, social_media, balance, payment_term_id, currency_id)
VALUES
('individual', 'Mr', 'Aliyu', 'Abdullahi', 'Aliyu Abdullahi', 
 'Agro Tech LTD', 'aliyuabdullahi@gmail.com', '0123456794', '08012345683', 
 'No. 15 Market Road, Kano, Nigeria', '{"facebook": "https://facebook.com/aliyuabdullahi"}', 0.00, 1, 1),
('business', 'Mrs', 'Titi', 'Adedayo', 'Adedayo Enterprises', 
 'Adedayo Enterprises', 'titiadedayo@adedayoenterprises.ng', '0123456795', 
 '08012345684', 'Plot 7 Industrial Layout, Lagos, Nigeria', 
 '{"twitter": "https://twitter.com/adedayoenterprises"}', 1200.00, 2, 1),
('individual', 'Miss', 'Bola', 'Ogunyemi', 'Bola Ogunyemi', 
 'Nat Agro Ltd', 'bolaogunyemi@yahoo.com', '0123456796', '08012345685', 
 'Flat 3, Block B, Ibadan, Nigeria', '{"instagram": "https://instagram.com/bolaogunyemi"}', 50000.00, 3, 1),
('business', 'Dr', 'Chinedu', 'Eze', 'Eze Agro Ltd.', 
 'Eze Agro Ltd.', 'chinedueze@ezeagro.ng', '0123456797', 
 '08012345686', '123 Farmland Avenue, Umuahia, Nigeria', 
 '{"linkedin": "https://linkedin.com/company/ezeagro"}', 0.00, 4, 1),
('individual', 'Prof', 'Amina', 'Yusuf', 'Prof. Amina Yusuf', 
 'Gerald Agro Ltd', 'aminayusuf@gmail.com', '0123456798', '08012345687', 
 'No. 10 Crescent, Abuja, Nigeria', '{"youtube": "https://youtube.com/aminayusuf"}', 0.00, 1, 1);

-- Seed customer transactions
INSERT INTO customer_transactions 
(customer_id, transaction_type, amount, notes)
VALUES
(1, 'credit', 500000.00, 'Initial deposit by Aliyu Abdullahi'),
(2, 'debit', 300000.00, 'Payment for supply of fresh chicken'),
(3, 'credit', 100000.00, 'Payment for supply of fresh catfish'),
(4, 'debit', 45000.00, 'Purchase of fresh pork cuts'),
(5, 'credit', 100000.00, 'Payment for supply of fresh lamb cuts');

-- Insert into purchase_orders
INSERT INTO purchase_orders (
    vendor_id, branch_id, delivery_date, 
    payment_term_id, subject, notes, terms_and_conditions, 
    discount, shipping_charge, total, status, processed_by
)
VALUES
(1, 1, '2024-12-20', 3, 
    'Bulk Purchase of Food Items', 'Ensure quality items', 
    'Goods must be delivered in good condition', 100, 5000, 150000, 'draft', 1),
(2, 2, '2024-12-15', 4, 
    'Monthly Grocery Restock', 'Deliver to Abuja branch warehouse', 
    'Invoice must include all taxes', 200, 2500, 200000, 'sent', 1),
(3, 3, '2024-11-10', 3, 
    'Catering Supplies', 'Urgent delivery required', 
    'Late delivery will incur penalties', 150, 1000, 1120000, 'received', 1),
(3, 3, '2024-10-11', 2, 
    'Bulk Purchase of Food Items', 'Ensure quality items', 
    'Goods must be delivered in good condition', 100, 5000, 150000, 'paid', 1),
(4, 1, '2024-11-25', 3, 
    'Monthly Meat Supplies', 'Deliver to Lagos branch warehouse', 
    'Invoice must include all taxes', 200, 2500, 200000, 'overdue', 1), 
(5, 2, '2024-11-30', 3, 
    'Bulk Purchase of Food Items', 'Ensure quality items', 
    'Goods must be delivered in good condition', 100, 5000, 150000, 'issued', 1);

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
    delivery_charge, total, status, processed_by
)
VALUES
('order', 'Chicken Jumbo Pack', 1, 1, 1, 'delivery', 1, 
    '2024-11-01', 'Deliver before noon', 'Please call on arrival', 
    1000, 2000, 520000, 'upcoming', 1),
('order', 'Maxi Puff Puff', 2, 2, 2, 'pickup', NULL, 
    '2024-11-15', 'Ready for Christmas', 'Add plenty sugar', 
    500, 0, 345000, 'pending', 1),
 ('order', 'Salmon Special', 3, 3, 3, 'delivery', 2, 
    '2024-11-20', 'Handle with care', 'Call before delivery', 
    1500, 2500, 450000, 'completed', 1),
 ('order', 'Beef Box', 4, 4, 1, 'pickup', NULL, 
    '2024-11-25', 'Festive season order', 'Add extra spice', 
    2000, 0, 300000, 'sent', 1),
 ('order', 'Chicken Jumbo Pack', 1, 1, 1, 'delivery', 1, 
    '2024-10-01', 'Deliver before noon', 'Please call on arrival', 
    1000, 2000, 520000, 'completed', 1);

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

-- Insert into item_stock_adjustments
INSERT INTO item_stock_adjustments (
    stock_id, manager_id, source_type, source_id, source_department_id, 
    quantity, adjustment_type, description, created_at
)
VALUES
(1, 1, 'vendor', 1, 1, 20, 'addition', 'Restocked beef inventory', '2024-01-10'),
(2, 1, 'vendor', 2, 1, 10, 'addition', 'Restocked chicken inventory', '2024-01-12'),
(3, 1, 'vendor', 3, 2, 15, 'addition', 'Restocked catfish inventory', '2024-01-15'),
(4, 1, 'user', 1, 3, 5, 'subtraction', 'Sold pork cuts', '2024-01-18'),
(5, 1, 'vendor', 2, 1, 10, 'addition', 'Restocked lamb inventory', '2024-01-20'),
(6, 1, 'user', 3, 2, 5, 'subtraction', 'Sold salmon fillets', '2024-01-22'),
(7, 1, 'vendor', 1, 3, 50, 'addition', 'Restocked eggs', '2024-01-24'),
(8, 1, 'vendor', 2, 2, 10, 'addition', 'Restocked cheese', '2024-01-25'),
(9, 1, 'user', 3, 1, 15, 'subtraction', 'Sold milk', '2024-01-26'),
(10, 1, 'vendor', 1, 2, 20, 'addition', 'Restocked yogurt', '2024-01-28');


-- Insert into item_stock_transfers
INSERT INTO comments (user_id, parent_id, entity_id, entity_type, comment, created_at)
VALUES
(1, NULL, 1, 'item_stock_adjustment', 'Great quality beef!', '2024-01-10'),
(2, NULL, 2, 'item_stock_adjustment', 'Chicken was fresh and tasty.', '2024-01-12'),
(3, NULL, 3, 'item_stock_adjustment', 'Catfish fillets were amazing.', '2024-01-15'),
(1, 1, 1, 'item_stock_adjustment', 'I agree, very fresh!', '2024-01-16'),
(2, NULL, 4, 'item_stock_adjustment', 'Pork cuts were decent.', '2024-01-18'),
(3, NULL, 5, 'item_stock_adjustment', 'Lamb was tender and juicy.', '2024-01-20'),
(1, NULL, 6, 'item_stock_adjustment', 'Salmon was okay.', '2024-01-22'),
(2, NULL, 7, 'item_stock_adjustment', 'Eggs are always good.', '2024-01-24'),
(3, NULL, 8, 'item_stock_adjustment', 'Cheese was fresh.', '2024-01-25'),
(1, NULL, 9, 'item_stock_adjustment', 'Milk was great!', '2024-01-26');

