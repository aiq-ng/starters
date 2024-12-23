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

-- Insert default qualification periods
INSERT INTO work_leave_qualifications (name) VALUES
('3 months'),
('6 months'),
('annually');

INSERT INTO loan_types (name, description) VALUES
('personal', 'Personal loans for individual use'),
('staff', 'Loans provided to staff members'),
('business', 'Business loans for companies'),
('education', 'Loans for educational purposes'),
('mortgage', 'Loans for purchasing property');

INSERT INTO loans (lender_id, lender_type, amount, interest_rate, start_date, end_date, loan_type_id, status, created_at, updated_at) 
VALUES 
(1, 'user', 10000.00, 5.00, '2024-01-01', '2025-01-01', 1, 'approved', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'user', 5000.00, 4.00, '2024-02-01', '2024-08-01', 2, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 'vendor', 20000.00, 6.50, '2024-03-01', '2026-03-01', 3, 'disbursed', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 'vendor', 15000.00, 3.50, '2024-04-01', '2025-04-01', 4, 'repaid', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

-- Seed departments
INSERT INTO departments (name, salary_type, base_type_id, base_rate, base_salary, description,
work_leave_qualification)
VALUES
('Snacks', 'fixed', NULL, NULL, 2500.00, 'Department for local and imported snacks', 1),
('Beverages', 'fixed', NULL, NULL, 2000.00, 'Department for drinks, tea, coffee, and juices', 2),
('Dispatch Riders', 'base', 2, 15.00, NULL, 'Department for dispatch riders', 3),
('Kitchen', 'fixed', NULL, NULL, 2200.00, 'Department for kitchenware and utensils', 1),
('Chef', 'fixed', NULL, NULL, 3000.00, 'Department for chefs and kitchen staff', 2),
('Sales Representatives', 'base', 1, 20.00, NULL, 'Department for sales staff, paid hourly', 3);

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
('Naira', '₦', 'NGN');

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
(6, 'Energy Drink', 2.50, 6, 9),

-- Condiments (unit: btl)
(7, 'Tomato Ketchup', 2.00, 10, 9),
(7, 'Mayonnaise', 2.50, 10, 9),
(7, 'BBQ Sauce', 3.00, 5, 9),
(7, 'Mustard', 1.50, 10, 9),

-- Canned Goods (unit: ctn)
(8, 'Canned Beans', 1.20, 20, 6),
(8, 'Canned Tomatoes', 1.50, 15, 6),
(8, 'Canned Corn', 1.30, 20, 6),
(8, 'Canned Tuna', 2.00, 10, 6),

-- Frozen Goods (unit: pk)
(9, 'Frozen Peas', 2.00, 10, 7),
(9, 'Frozen Strawberries', 4.00, 8, 7),
(9, 'Frozen Spinach', 2.50, 10, 7),
(9, 'Frozen Mixed Vegetables', 3.00, 8, 7);

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
('VAT (7.50)', 7.50, 'Value Added Tax in Nigeria'),
('Sales Tax (5.00)', 5.00, 'General Sales Tax in West Africa');

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
 'No. 10 Crescent, Abuja, Nigeria', '{"youtube": "https://youtube.com/aminayusuf"}', 0.00, 1, 1),
('individual', 'Mr', 'Tunde', 'Ojo', 'Tunde Ojo', 
 'Ojo Farms', 'test1@gmail.com', '0123456799', '08012345688',
 'No. 20 Farm Road, Ibadan, Nigeria', '{"facebook": "https://facebook.com/tundeojo"}', 0.00, 1, 1),
('business', 'Mrs', 'Bimpe', 'Adeyemi', 'Adeyemi Farms',
 'Adeyemi Farms', 'adms@gmail.com', '0123456700', '08012345689',
 'No. 30 Farm Road, Lagos, Nigeria', '{"twitter": "https://twitter.com/adeyemifarms"}', 0.00, 2, 1);

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
    discount, shipping_charge, total, status, processed_by, 
    date_received, created_at
)
VALUES
-- January 2024
(1, 1, '2024-01-10', 3, 
    'Fresh Produce Supplies', 'Ensure items are fresh', 
    'Delivery to main warehouse only', 150, 4000, 90000, 'paid', 2, 
    '2024-01-15', '2024-01-05'),
(2, 2, '2024-01-25', 4, 
    'Monthly Stock', 'Restock Abuja branch', 
    'Include detailed itemized invoice', 100, 3000, 85000, 'issued', 2, 
    '2024-01-30', '2024-01-18'),

-- February 2024
(3, 1, '2024-02-14', 2, 
    'Valentine Special Orders', 'Expedite delivery', 
    'Late delivery will result in cancellation', 200, 5000, 120000, 'sent', 1, 
    '2024-02-18', '2024-02-05'),
(4, 3, '2024-02-20', 3, 
    'Catering Supplies', 'Urgent bulk order', 
    'Deliver to Lagos branch', 300, 6000, 150000, 'overdue', 3, 
    '2024-02-22', '2024-02-14'),

-- March 2024
(5, 2, '2024-03-10', 3, 
    'Spring Restock', 'Quality inspection required', 
    'Goods must match sample', 250, 4000, 100000, 'received', 2, 
    '2024-03-14', '2024-03-01'),
(1, 1, '2024-03-30', 4, 
    'Meat Supplies', 'Deliver in refrigerated truck', 
    'All safety measures must be followed', 200, 5000, 180000, 'paid', 1, 
    '2024-03-31', '2024-03-20'),

-- April 2024
(2, 3, '2024-04-15', 2, 
    'Fruits and Vegetables', 'Ensure freshness', 
    'Late delivery not acceptable', 150, 2500, 95000, 'issued', 1, 
    '2024-04-18', '2024-04-10'),
(3, 1, '2024-04-28', 3, 
    'General Supplies', 'Ensure all items are complete', 
    'Delivery location: Lagos', 100, 3000, 110000, 'sent', 2, 
    '2024-04-30', '2024-04-22'),

-- May 2024
(4, 2, '2024-05-15', 4, 
    'Warehouse Stock Replenishment', 'Priority delivery', 
    'Strict quality check required', 200, 4000, 130000, 'paid', 1, 
    '2024-05-20', '2024-05-10'),
(5, 3, '2024-05-25', 3, 
    'Monthly Restock', 'Contact manager upon delivery', 
    'Invoice to include all discounts', 100, 2500, 75000, 'received', 3, 
    '2024-05-28', '2024-05-18'),

-- June 2024
(1, 2, '2024-06-05', 3, 
    'Meat Supply', 'Freshly butchered meat only', 
    'Deliver within the first week of the month', 300, 4000, 140000, 'paid', 2, 
    '2024-06-08', '2024-06-01'),
(3, 3, '2024-06-15', 2, 
    'Bakery Items', 'Ensure all items are gluten-free', 
    'Delivery time strictly between 8AM-10AM', 100, 2000, 70000, 'sent', 1, 
    '2024-06-17', '2024-06-10'),

-- July 2024
(2, 1, '2024-07-12', 4, 
    'Grocery Supplies', 'Ensure no damages', 
    'Delivery time strictly between 2PM-4PM', 250, 4500, 120000, 'paid', 2, 
    '2024-07-15', '2024-07-05'),
(4, 2, '2024-07-25', 3, 
    'Monthly Restock', 'Double-check item quality', 
    'Delivery to main warehouse', 100, 3000, 90000, 'received', 1, 
    '2024-07-28', '2024-07-20'),

-- August 2024
(5, 3, '2024-08-10', 3, 
    'Catering Supplies', 'Expedite delivery', 
    'Late delivery not acceptable', 300, 5000, 150000, 'overdue', 1, 
    '2024-08-14', '2024-08-01'),
(1, 1, '2024-08-30', 2, 
    'Fruit and Veg Supply', 'Ensure organic produce', 
    'All invoices to be sent within 24 hours', 200, 4500, 95000, 'sent', 3, 
    '2024-08-31', '2024-08-20'),

-- September 2024
(2, 2, '2024-09-05', 3, 
    'Fresh Meat Supply', 'Refrigeration required', 
    'Delivery must be made in refrigerated trucks', 100, 2500, 80000, 'paid', 1, 
    '2024-09-07', '2024-09-01'),
(3, 1, '2024-09-20', 4, 
    'Bulk Order - Dry Goods', 'Deliver to Abuja branch', 
    'Late delivery charges will apply', 250, 3500, 125000, 'received', 3, 
    '2024-09-25', '2024-09-15'),

-- October 2024
(4, 2, '2024-10-10', 3, 
    'Beverage Stock', 'Special instructions for storage', 
    'Ensure all items match sample', 300, 4500, 160000, 'paid', 2, 
    '2024-10-13', '2024-10-05'),
(5, 3, '2024-10-25', 2, 
    'Monthly Restock', 'Check invoice for tax inclusion', 
    'Delivery location: Lagos', 150, 5000, 98000, 'sent', 1, 
    '2024-10-28', '2024-10-18'),

-- November 2024
(1, 1, '2024-11-12', 4, 
    'Seasonal Orders', 'Ensure proper packaging', 
    'Deliver within specified hours', 200, 4000, 90000, 'issued', 3, 
    '2024-11-15', '2024-11-05'),
(2, 2, '2024-11-28', 3, 
    'Catering Supplies', 'Deliver with invoice', 
    'Refrigeration mandatory', 150, 2500, 70000, 'paid', 2, 
    '2024-11-30', '2024-11-20'),

-- December 2024
(3, 1, '2024-12-05', 2, 
    'Holiday Orders', 'Expedite delivery', 
    'Late orders will not be accepted', 300, 6000, 150000, 'paid', 1, 
    '2024-12-07', '2024-12-01'),
(4, 3, '2024-12-20', 4, 
    'Year-End Stock', 'Ensure items are fresh', 
    'Deliver to Lagos branch', 200, 5000, 125000, 'paid', 2, 
    '2024-12-25', '2024-12-15'),

-- January 2025
(5, 2, '2025-01-10', 3, 
    'New Year Supplies', 'Double-check quality', 
    'Include all taxes in the invoice', 150, 3000, 85000, 'paid', 1, 
    '2025-01-14', '2025-01-01'),
(1, 1, '2025-01-25', 4, 
    'Monthly Restock', 'Deliver to warehouse', 
    'Invoice must match itemized list', 200, 4000, 110000, 'received', 2, 
    '2025-01-28', '2025-01-18');
;

-- Insert into purchase_order_items
INSERT INTO purchase_order_items (
    purchase_order_id, item_id, quantity, price, tax_id
)
VALUES
(1, 1, 2, 5000.00, 1), (1, 2, 1, 10000.00, 2), (2, 3, 5, 4000.00, 1), (2, 4, 10, 2000.00, 2), 
(3, 5, 3, 3000.00, 1), (3, 6, 7, 7000.00, 2), (4, 7, 4, 8000.00, 1), (4, 8, 2, 6000.00, 2), 
(5, 9, 6, 9000.00, 1), (5, 10, 8, 12000.00, 2), (6, 11, 2, 4000.00, 1), (6, 12, 3, 4500.00, 2), 
(7, 13, 4, 5000.00, 1), (7, 14, 6, 5500.00, 2), (8, 15, 1, 7000.00, 1), (8, 16, 5, 7500.00, 2), 
(9, 17, 3, 8000.00, 1), (9, 18, 2, 8500.00, 2), (10, 1, 4, 9000.00, 1), (10, 2, 6, 9500.00, 2), 
(11, 3, 1, 10000.00, 1), (11, 4, 7, 10500.00, 2), (12, 5, 5, 11000.00, 1), (12, 6, 3, 11500.00, 2), 
(13, 7, 2, 12000.00, 1), (13, 8, 4, 12500.00, 2), (14, 9, 5, 13000.00, 1), (14, 10, 6, 13500.00, 2), 
(15, 11, 7, 14000.00, 1), (15, 12, 8, 14500.00, 2), (16, 13, 9, 15000.00, 1), (16, 14, 10, 15500.00, 2), 
(17, 15, 11, 16000.00, 1), (17, 16, 12, 16500.00, 2), (18, 17, 13, 17000.00, 1), (18, 18, 14, 17500.00, 2), 
(19, 1, 15, 18000.00, 1), (19, 2, 16, 18500.00, 2), (20, 3, 17, 19000.00, 1), (20, 4, 18, 19500.00, 2), 
(21, 5, 19, 20000.00, 1), (21, 6, 20, 20500.00, 2), (22, 7, 21, 21000.00, 1), (22, 8, 22, 21500.00, 2), 
(23, 9, 23, 22000.00, 1), (23, 10, 24, 22500.00, 2), (24, 11, 25, 23000.00, 1), (24, 12, 26, 23500.00, 2), 
(25, 13, 27, 24000.00, 1), (25, 14, 28, 24500.00, 2), (26, 15, 29, 25000.00, 1), (26, 16, 30, 25500.00, 2);


-- Insert into sales_orders
INSERT INTO sales_orders (
    order_type, order_title, customer_id, payment_term_id, 
    payment_method_id, delivery_option, assigned_driver_id, 
    delivery_date, additional_note, customer_note, discount, 
    delivery_charge, total, status, processed_by, created_at
)
VALUES
-- January 2024
('order', 'January Breakfast Pack', 1, 1, 1, 'delivery', 2, 
    '2024-01-10', 'Deliver hot', 'Knock loudly', 
    2000, 1000, 60000, 'pending', 1, '2024-01-01'),
('service', 'Corporate Brunch', 2, 2, 2, 'pickup', NULL, 
    '2024-01-25', 'Prepare sandwiches', 'Contact HR', 
    3000, 0, 150000, 'sent', 2, '2024-01-15'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- February 2024
('order', 'Valentine Pack', 3, 1, 3, 'pickup', NULL, 
    '2024-02-14', 'Pack with care', 'Include roses', 
    5000, 0, 90000, 'paid', 3, '2024-02-01'),
('service', 'February Catering', 4, 2, 1, 'delivery', 1, 
    '2024-02-20', 'Coordinate with manager', 'Serve on time', 
    8000, 12000, 350000, 'pending', 2, '2024-02-05'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- March 2024
('order', 'March Mega Pack', 5, 3, 1, 'delivery', 3, 
    '2024-03-05', 'Deliver fresh', 'Check address twice', 
    2000, 5000, 120000, 'sent', 1, '2024-03-01'),
('service', 'Event Catering', 1, 1, 2, 'pickup', NULL, 
    '2024-03-20', 'Add extra servings', 'Include desserts', 
    5000, 0, 250000, 'paid', 3, '2024-03-10'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- April 2024
('order', 'Easter Pack', 2, 1, 1, 'delivery', 2, 
    '2024-04-10', 'Deliver before 9 AM', 'Handle with care', 
    4000, 2000, 85000, 'upcoming', 1, '2024-04-01'),
('service', 'April Lunch Service', 3, 2, 3, 'pickup', NULL, 
    '2024-04-25', 'Serve warm', 'Contact event planner', 
    7000, 0, 320000, 'sent', 2, '2024-04-15'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- May 2024
('order', 'Mothers Day Delight', 4, 1, 1, 'delivery', 1, 
    '2024-05-12', 'Deliver with flowers', 'Add a card', 
    3000, 2000, 180000, 'pending', 3, '2024-05-01'),
('service', 'Wedding Reception', 5, 3, 2, 'delivery', 3, 
    '2024-05-25', 'Coordinate with planner', 'Setup quickly', 
    15000, 30000, 600000, 'paid', 2, '2024-05-10'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- June 2024
('order', 'Fathers Day Special', 6, 2, 1, 'pickup', NULL, 
    '2024-06-16', 'Pack carefully', 'Double-check the spices', 
    2500, 0, 145000, 'upcoming', 2, '2024-06-01'),
('service', 'Corporate Dinner', 1, 1, 2, 'delivery', 4, 
    '2024-06-30', 'Serve by 7 PM', 'Coordinate with HR', 
    8000, 15000, 400000, 'sent', 4, '2024-06-20'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- July 2024
('order', 'Summer BBQ Pack', 3, 3, 1, 'pickup', NULL, 
    '2024-07-10', 'Pack extra sauce', 'Include plates', 
    5000, 0, 200000, 'pending', 1, '2024-07-01'),
('service', 'July Banquet Service', 5, 2, 3, 'delivery', 2, 
    '2024-07-20', 'Coordinate with staff', 'Serve dessert on time', 
    12000, 20000, 550000, 'paid', 3, '2024-07-10'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- August 2024
('order', 'August Seafood Pack', 4, 1, 2, 'delivery', 3, 
    '2024-08-15', 'Deliver with ice packs', 'Ensure freshness', 
    2000, 1000, 250000, 'upcoming', 4, '2024-08-01'),
('service', 'Birthday Catering', 6, 3, 1, 'pickup', NULL, 
    '2024-08-25', 'Prepare cake and food', 'Include candles', 
    7000, 0, 220000, 'sent', 2, '2024-08-10'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- September 2024
('order', 'September Festive Pack', 2, 2, 1, 'delivery', 2, 
    '2024-09-10', 'Deliver on time', 'Call before delivery', 
    4000, 2000, 180000, 'pending', 1, '2024-09-01'),
('service', 'Corporate Buffet', 3, 1, 2, 'pickup', NULL, 
    '2024-09-25', 'Ensure enough servings', 'Include utensils', 
    5000, 0, 300000, 'paid', 3, '2024-09-15'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- October 2024
('order', 'October Mega Deal', 4, 1, 1, 'delivery', 1, 
    '2024-10-10', 'Pack everything neatly', 'Add extra sauce', 
    2500, 2000, 140000, 'upcoming', 2, '2024-10-01'),
('service', 'October Event Service', 5, 3, 3, 'delivery', 2, 
    '2024-10-25', 'Coordinate with manager', 'Setup before guests arrive', 
    15000, 20000, 500000, 'sent', 4, '2024-10-10'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- November 2024
('order', 'November Festive Feast', 6, 1, 2, 'pickup', NULL, 
    '2024-11-15', 'Pack carefully', 'Include spoons', 
    3500, 0, 190000, 'paid', 1, '2024-11-01'),
('service', 'Thanksgiving Dinner', 1, 2, 1, 'delivery', 3, 
    '2024-11-25', 'Serve hot', 'Coordinate with family', 
    8000, 20000, 450000, 'pending', 2, '2024-11-10'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- December 2024
('order', 'Christmas Special', 2, 1, 1, 'delivery', 1, 
    '2024-12-20', 'Deliver by 8 AM', 'Call before delivery', 
    7000, 15000, 350000, 'paid', 4, '2024-12-01'),
('service', 'Christmas Eve Dinner', 4, 3, 3, 'pickup', NULL, 
    '2024-12-24', 'Prepare food and drinks', 'Coordinate with team', 
    10000, 0, 300000, 'paid', 3, '2024-12-10'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW()),

-- January 2025
('order', 'New Year Delight', 3, 2, 1, 'delivery', 4, 
    '2025-01-01', 'Deliver before noon', 'Pack everything tightly', 
    3000, 5000, 250000, 'upcoming', 1, '2024-12-20'),
('service', 'January Gala Service', 5, 1, 2, 'pickup', NULL, 
    '2025-01-15', 'Coordinate with manager', 'Include decorations', 
    8000, 0, 400000, 'paid', 2, '2025-01-01'),
('order', 'Lunch Pack', 3, 3, 1, 'delivery', 3, 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'paid', 3, NOW());


-- Insert into sales_order_items
INSERT INTO sales_order_items (
    sales_order_id, item_id, quantity, price
) VALUES
(1, 1, 2, 5000.00), (1, 2, 1, 10000.00), (1, 3, 5, 400.00), (1, 4, 10, 200.00),
(2, 5, 3, 12000.00), (2, 6, 2, 25000.00), (2, 7, 1, 18000.00), (2, 8, 4, 5000.00),
(3, 9, 2, 2500.00), (3, 10, 3, 3000.00), (3, 11, 1, 5000.00), (3, 12, 2, 1500.00),
(4, 13, 4, 10000.00), (4, 14, 2, 15000.00), (4, 15, 1, 20000.00), (4, 16, 3, 8000.00),
(5, 17, 1, 2500.00), (5, 18, 2, 12000.00), (5, 19, 3, 2000.00), (5, 20, 5, 1000.00),
(6, 21, 2, 5000.00), (6, 22, 1, 8000.00), (6, 23, 4, 2000.00), (6, 24, 3, 1500.00),
(7, 25, 1, 3000.00), (7, 26, 3, 2000.00), (7, 1, 2, 5000.00), (7, 2, 4, 1500.00),
(8, 3, 4, 4000.00), (8, 4, 1, 6000.00), (8, 5, 2, 8000.00), (8, 6, 3, 5000.00),
(9, 7, 2, 2000.00), (9, 8, 1, 4000.00), (9, 9, 5, 800.00), (9, 10, 3, 1500.00),
(10, 11, 3, 10000.00), (10, 12, 2, 25000.00), (10, 13, 1, 30000.00), (10, 14, 4, 15000.00),
(11, 15, 2, 2000.00), (11, 16, 3, 1500.00), (11, 17, 1, 3000.00), (11, 18, 4, 1000.00),
(12, 19, 1, 5000.00), (12, 20, 3, 7000.00), (12, 21, 2, 4000.00), (12, 22, 4, 1500.00),
(13, 23, 2, 5000.00), (13, 24, 3, 2000.00), (13, 25, 4, 1200.00), (13, 26, 5, 1000.00),
(14, 1, 1, 10000.00), (14, 2, 2, 15000.00), (14, 3, 4, 25000.00), (14, 4, 3, 20000.00),
(15, 5, 3, 4000.00), (15, 6, 1, 5000.00), (15, 7, 2, 1500.00), (15, 8, 4, 2000.00),
(16, 9, 2, 6000.00), (16, 10, 1, 7000.00), (16, 11, 3, 5000.00), (16, 12, 4, 4000.00),
(17, 13, 4, 2500.00), (17, 14, 1, 3000.00), (17, 15, 3, 10000.00), (17, 16, 2, 5000.00),
(18, 17, 2, 15000.00), (18, 18, 4, 20000.00), (18, 19, 1, 8000.00), (18, 20, 3, 5000.00),
(19, 21, 1, 10000.00), (19, 22, 2, 3000.00), (19, 23, 3, 5000.00), (19, 24, 4, 2000.00),
(20, 25, 2, 15000.00), (20, 26, 1, 25000.00), (20, 1, 3, 10000.00), (20, 2, 4, 20000.00),
(21, 3, 2, 5000.00), (21, 4, 1, 8000.00), (21, 5, 4, 2000.00), (21, 6, 3, 1500.00),
(22, 7, 1, 3000.00), (22, 8, 3, 2000.00), (22, 9, 4, 1200.00), (22, 10, 5, 1000.00),
(23, 11, 2, 2000.00), (23, 12, 1, 4000.00), (23, 13, 3, 8000.00), (23, 14, 4, 1500.00),
(24, 15, 2, 5000.00), (24, 16, 3, 2000.00), (24, 17, 4, 2500.00), (24, 18, 5, 3000.00),
(25, 19, 1, 10000.00), (25, 20, 2, 15000.00), (25, 21, 3, 20000.00), (25, 22, 4, 25000.00),
(26, 23, 2, 5000.00), (26, 24, 1, 8000.00), (26, 25, 4, 2000.00), (26, 26, 3, 1500.00);


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

INSERT INTO expenses_categories (name, description)
VALUES
('Travel', 'Expenses related to business travel'),
('Office Supplies', 'Expenses for office supplies and stationery'),
('Utilities', 'Payments for electricity, water, and internet bills'),
('Meals', 'Expenses for meals and entertainment'),
('Maintenance', 'Expenses related to equipment maintenance');

INSERT INTO expenses (expense_title, expense_category, payment_method_id, 
 payment_term_id, department_id, amount, bank_charges, date_of_expense, 
 notes, status, processed_by)
VALUES
('Flight to Client Meeting', 1, 1, 1, 2, 1500.00, 15.00, '2024-01-10', 
 'Flight to meet client for project discussion', 'paid', 3),
('Office Stationery Purchase', 2, 2, 1, 1, 250.00, 5.00, '2024-01-20', 
 'Purchase of pens, paper, and files', 'paid', 4),

('March Internet Bill', 3, 3, 2, 3, 120.00, 2.50, '2024-02-05', 
 'Payment for monthly internet service', 'paid', 2),
('Team Lunch', 4, 1, 1, 2, 300.00, 10.00, '2024-02-15', 
 'Lunch with the team after project completion', 'paid', 5),

('Printer Maintenance', 5, 2, 2, 1, 500.00, 0.00, '2024-03-28', 
 'Scheduled maintenance for office printer', 'cancelled', 4),
('Business Conference', 1, 1, 1, 3, 800.00, 10.00, '2024-03-30', 
 'Conference related to industry developments', 'paid', 3),

('Annual Software License', 2, 2, 1, 1, 2000.00, 50.00, '2024-04-07', 
 'Annual renewal of office software licenses', 'paid', 4),
('Team Dinner', 4, 1, 1, 2, 350.00, 15.00, '2024-04-15', 
 'Dinner with the team for quarterly review', 'paid', 5),

('March Electricity Bill', 3, 3, 2, 3, 250.00, 5.00, '2024-05-03', 
 'Payment for electricity consumption in the office', 'paid', 2),
('Travel to Conference', 1, 1, 1, 2, 1200.00, 20.00, '2024-05-10', 
 'Flight to attend the industry conference', 'paid', 3),

('Office Furniture Purchase', 2, 2, 1, 1, 1500.00, 30.00, '2024-06-05', 
 'New office furniture for the expanding team', 'paid', 4),
('Client Meeting Expenses', 4, 1, 1, 2, 500.00, 10.00, '2024-06-18', 
 'Meeting expenses for a new client project', 'paid', 5),

('Monthly Internet Bill', 3, 3, 2, 3, 130.00, 3.00, '2024-07-03', 
 'Monthly internet bill for office', 'paid', 2),
('Team Workshop', 4, 1, 1, 2, 600.00, 20.00, '2024-07-12', 
 'Workshop organized for team development', 'paid', 5),

('Office Supplies', 2, 2, 1, 1, 350.00, 5.00, '2024-08-09', 
 'Replenishing office supplies for the team', 'paid', 4),
('Client Dinner', 4, 1, 1, 2, 400.00, 15.00, '2024-08-15', 
 'Dinner with clients to discuss future projects', 'paid', 5),

('Monthly Internet Bill', 3, 3, 2, 3, 130.00, 2.50, '2024-09-01', 
 'Monthly internet bill payment', 'paid', 2),
('Team Lunch', 4, 1, 1, 2, 250.00, 10.00, '2024-09-10', 
 'Lunch for the team after project completion', 'paid', 5),

('Business Conference', 1, 1, 1, 3, 800.00, 25.00, '2024-10-07', 
 'Conference related to industry developments', 'paid', 3),
('Printer Maintenance', 5, 2, 2, 1, 400.00, 0.00, '2024-10-15', 
 'Scheduled maintenance for office printer', 'paid', 4),

('Team Workshop', 4, 1, 1, 2, 500.00, 15.00, '2024-11-02', 
 'Team building workshop', 'paid', 5),
('Office Renovation', 2, 2, 1, 1, 5000.00, 100.00, '2024-11-22', 
 'Renovation of office space to accommodate more staff', 'paid', 4),

('Electricity Bill', 3, 3, 2, 3, 250.00, 5.00, '2024-12-05', 
 'Electricity payment for office', 'paid', 2),
('Client Meeting Expenses', 4, 1, 1, 2, 600.00, 20.00, '2024-12-15', 
 'Expenses for meeting with a potential client', 'paid', 5),

('Business Travel', 1, 1, 1, 2, 1300.00, 15.00, '2025-01-02', 
 'Business trip to meet potential investors', 'paid', 3),
('Team Lunch', 4, 1, 1, 2, 350.00, 10.00, '2025-01-10', 
 'Lunch for team celebration', 'paid', 5);
