-- Seed data for roles
INSERT INTO roles (name) VALUES
('Admin'),
('Manager'),
('Staff'),
('Rider'),
('Human Resources'),
('Inventory Manager'),
('Sales'),
('Accountant'),
('Marketing'),
('Finance'),
('Chef');

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

INSERT INTO no_of_working_days (name, description)
VALUES 
('Standard Week', 'A typical working week with 5 days, Monday to Friday'),
('Extended Week', 'A working week that includes Saturday, making it 6 days'),
('Shift Work', 'A rotating shift schedule, covering various days including weekends'),
('Flexible Week', 'A flexible working arrangement with varied working days'),
('Compressed Week', 'A compressed working schedule with fewer working days but longer hours');


INSERT INTO loan_types (name, description) VALUES
('personal', 'Personal loans for individual use'),
('staff', 'Loans provided to staff members'),
('business', 'Business loans for companies'),
('education', 'Loans for educational purposes'),
('mortgage', 'Loans for purchasing property');

-- Seed departments
INSERT INTO departments (name, salary_type, base_type_id, base_rate, base_salary, description, work_leave_qualification)
VALUES
('Accounting', 'fixed', NULL, NULL, 2500.00, 'Department for finance and accounting', 
    (SELECT id FROM work_leave_qualifications WHERE name = '3 months')),
('Human Resources', 'fixed', NULL, NULL, 2000.00, 'Department for HR and recruitment', 
    (SELECT id FROM work_leave_qualifications WHERE name = '6 months')),
('Dispatch Riders', 'base', (SELECT id FROM base_pay_types WHERE name = 'hourly'), 15.00, NULL, 'Department for dispatch riders', 
    (SELECT id FROM work_leave_qualifications WHERE name = 'annually')),
('Kitchen', 'fixed', NULL, NULL, 2200.00, 'Department for kitchenware and utensils', 
    (SELECT id FROM work_leave_qualifications WHERE name = '3 months')),
('Chef', 'fixed', NULL, NULL, 3000.00, 'Department for chefs and kitchen staff', 
    (SELECT id FROM work_leave_qualifications WHERE name = '6 months')),
('Sales Representatives', 'base', 
    (SELECT id FROM base_pay_types WHERE name = 'hourly'), 20.00, NULL, 
    'Department for sales staff, paid hourly', 
    (SELECT id FROM work_leave_qualifications WHERE name = 'annually'));

-- Seed data for users
INSERT INTO users (
    firstname, lastname, email, password, role_id, avatar_url, 
    date_of_birth, address, next_of_kin, date_of_employment, salary, 
    bank_details, leave, nin, passport, department_id
) VALUES
('Starters', 'Admin', 'starters@admin.com', 'password123', 
    (SELECT id FROM roles WHERE name = 'Admin'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    NULL, NULL, NULL, NULL, NULL, 
    NULL, NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', 
    (SELECT id FROM departments WHERE name = 'Snacks')),
('Opororo', 'Nathaniel', 'nat@aiq.com', 'password', 
    (SELECT id FROM roles WHERE name = 'Rider'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1990-05-15', '123 Main St, Cityville', 'Tom Johnson', '2020-01-01', 3500.00, 
    '{"bank_name": "Bank ABC", "account_number": "1234567890"}', NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', 
    (SELECT id FROM departments WHERE name = 'Dispatch Riders')),
('Emmanuel', 'Afcon', 'sog@aiq.com', 'password', 
    (SELECT id FROM roles WHERE name = 'Sales'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1985-10-20', '456 Oak St, Townsville', 'Sarah Smith', '2018-09-15', 4000.00, 
    '{"bank_name": "Bank XYZ", "account_number": "9876543210"}', '2023-06-01', 'https://i.imgur.com/CD2345678D.jpeg', 'https://i.imgur.com/B2345678.jpeg', 
    (SELECT id FROM departments WHERE name = 'Sales Representatives')),
('Babanla', 'Odunlami', 'odun@aiq.com', 'password', 
    (SELECT id FROM roles WHERE name = 'Accountant'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1992-08-10', '789 Pine St, Villageville', 'Emily Brown', '2019-02-18', 2800.00, 
    '{"bank_name": "Bank LMN", "account_number": "1112233445"}', '2023-05-15', 'https://i.imgur.com/EF3456789E.jpeg', 'https://i.imgur.com/C3456789.jpeg', 
    (SELECT id FROM departments WHERE name = 'Kitchen')),
('Kingsley', 'Jobojobo', 'kingsley@aiq.com', 'password', 
    (SELECT id FROM roles WHERE name = 'Chef'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1988-12-05', '321 Elm St, Hamletville', 'Anna Doe', '2021-07-21', 4200.00, 
    '{"bank_name": "Bank DEF", "account_number": "9988776655"}', NULL, 'https://i.imgur.com/GH4567890F.jpeg', 'https://i.imgur.com/D4567890.jpeg', 
    (SELECT id FROM departments WHERE name = 'HR')),
('Jane', 'Smith', 'jane@example.com', 'hashedpassword1', 
    (SELECT id FROM roles WHERE name = 'Marketing'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1994-03-25', '654 Maple St, Citytown', 'Linda Smith', '2022-06-11', 3100.00, 
    '{"bank_name": "Bank GHI", "account_number": "6677889900"}', '2023-08-01', 'https://i.imgur.com/IJ5678901G.jpeg', 'https://i.imgur.com/E5678901.jpeg', 
    (SELECT id FROM departments WHERE name = 'Marketing')),
('Mary', 'Jones', 'mary@example.com', 'hashedpassword1', 
    (SELECT id FROM roles WHERE name = 'Sales'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1991-11-30', '987 Cedar St, Smallville', 'John Jones', '2017-04-09', 3600.00, 
    '{"bank_name": "Bank JKL", "account_number": "1122334455"}', NULL, 'https://i.imgur.com/KL6789012H.jpeg', 'https://i.imgur.com/F6789012.jpeg', 
    (SELECT id FROM departments WHERE name = 'Sales Representatives')),
('Peter', 'Brown', 'peter@example.com', 'hashedpassword1', 
    (SELECT id FROM roles WHERE name = 'Manager'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1989-07-15', '123 Birch St, Greenfield', 'Samantha Brown', '2016-03-20', 3900.00, 
    '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 
    (SELECT id FROM departments WHERE name = 'Beverages'));


INSERT INTO loans (
    lender_id, lender_type, amount, interest_rate, start_date, 
    end_date, loan_type_id, status, created_at, updated_at
) 
VALUES
((SELECT id FROM users WHERE email = 'starters@admin.com'), 'user', 10000.00, 5.00, '2024-01-01', '2025-01-01', 
 (SELECT id FROM loan_types WHERE name = 'personal'), 'approved', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),

((SELECT id FROM users WHERE email = 'nat@aiq.com'), 'user', 5000.00, 4.00, '2024-02-01', '2024-08-01', 
 (SELECT id FROM loan_types WHERE name = 'staff'), 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),

((SELECT id FROM users WHERE email = 'sog@aiq.com'), 'user', 20000.00, 6.50, '2024-03-01', '2026-03-01', 
 (SELECT id FROM loan_types WHERE name = 'business'), 'disbursed', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),

((SELECT id FROM users WHERE email = 'odun@aiq.com'), 'user', 15000.00, 3.50, '2024-04-01', '2025-04-01', 
 (SELECT id FROM loan_types WHERE name = 'education'), 'repaid', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);



INSERT INTO user_permissions (user_id, permission_id)
VALUES
((SELECT id FROM users WHERE email = 'starters@admin.com'), 
    (SELECT id FROM permissions WHERE name = 'admin')),
((SELECT id FROM users WHERE email = 'nat@aiq.com'), 
    (SELECT id FROM permissions WHERE name = 'sales')),
((SELECT id FROM users WHERE email = 'sog@aiq.com'), 
    (SELECT id FROM permissions WHERE name = 'procurement')),
((SELECT id FROM users WHERE email = 'odun@aiq.com'), 
    (SELECT id FROM permissions WHERE name = 'hr')),
((SELECT id FROM users WHERE email = 'kingsley@aiq.com'), 
    (SELECT id FROM permissions WHERE name = 'accounting')),
((SELECT id FROM users WHERE email = 'jane@example.com'), 
    (SELECT id FROM permissions WHERE name = 'inventory')),
((SELECT id FROM users WHERE email = 'mary@example.com'), 
    (SELECT id FROM permissions WHERE name = 'inventory'));


-- Seed data for user_leaves
INSERT INTO user_leaves (user_id, leave_type, start_date, end_date, status, notes) VALUES
((SELECT id FROM users WHERE email = 'starters@admin.com'), 'annual', '2024-01-15', '2024-01-20', 'leave taken', 'Annual leave for vacation'),
((SELECT id FROM users WHERE email = 'nat@aiq.com'), 'sick', '2024-02-05', '2024-02-07', 'booked', 'Recovering from flu'),
((SELECT id FROM users WHERE email = 'sog@aiq.com'), 'maternity', '2024-03-01', '2024-05-30', 'on leave', 'Maternity leave for childbirth'),
((SELECT id FROM users WHERE email = 'odun@aiq.com'), 'paternity', '2024-03-10', '2024-03-14', 'leave taken', 'Paternity leave for newborn support'),
((SELECT id FROM users WHERE email = 'john@example.com'), 'study', '2024-04-01', '2024-04-15', 'cancelled', 'Cancelled due to change in schedule'),
((SELECT id FROM users WHERE email = 'jane@example.com'), 'compassionate', '2024-05-10', '2024-05-12', 'booked', 'Family emergency'),
((SELECT id FROM users WHERE email = 'mary@example.com'), 'unpaid', '2024-06-01', '2024-06-07', 'booked', 'Personal matters');

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
((SELECT id FROM item_categories WHERE name = 'pastry'), 'Croissant', 1.50, 10, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),
((SELECT id FROM item_categories WHERE name = 'pastry'), 'Chocolate Cake', 15.00, 1, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),
((SELECT id FROM item_categories WHERE name = 'pastry'), 'Baguette', 2.00, 5, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),

-- Seafood (unit: kg)
((SELECT id FROM item_categories WHERE name = 'seafood'), 'Salmon Fillet', 12.99, 2, 
 (SELECT id FROM units WHERE abbreviation = 'kg')),
((SELECT id FROM item_categories WHERE name = 'seafood'), 'Shrimp', 9.99, 5, 
 (SELECT id FROM units WHERE abbreviation = 'kg')),
((SELECT id FROM item_categories WHERE name = 'seafood'), 'Crab Legs', 25.00, 1, 
 (SELECT id FROM units WHERE abbreviation = 'kg')),

-- Grill (unit: pcs)
((SELECT id FROM item_categories WHERE name = 'grill'), 'Grilled Chicken Breast', 8.50, 2, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),
((SELECT id FROM item_categories WHERE name = 'grill'), 'BBQ Ribs', 15.00, 1, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),
((SELECT id FROM item_categories WHERE name = 'grill'), 'Grilled Fish', 10.00, 2, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),

-- Meat (unit: kg)
((SELECT id FROM item_categories WHERE name = 'meat'), 'Beef Steak', 18.00, 1, 
 (SELECT id FROM units WHERE abbreviation = 'kg')),
((SELECT id FROM item_categories WHERE name = 'meat'), 'Pork Chops', 12.00, 2, 
 (SELECT id FROM units WHERE abbreviation = 'kg')),
((SELECT id FROM item_categories WHERE name = 'meat'), 'Ground Beef', 5.50, 5, 
 (SELECT id FROM units WHERE abbreviation = 'kg')),

-- Dairy (unit: L and pcs)
((SELECT id FROM item_categories WHERE name = 'dairy'), 'Whole Milk', 2.50, 10, 
 (SELECT id FROM units WHERE abbreviation = 'L')),
((SELECT id FROM item_categories WHERE name = 'dairy'), 'Cheddar Cheese', 4.00, 5, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),
((SELECT id FROM item_categories WHERE name = 'dairy'), 'Greek Yogurt', 3.00, 8, 
 (SELECT id FROM units WHERE abbreviation = 'pcs')),

-- Beverages (unit: btl)
((SELECT id FROM item_categories WHERE name = 'beverages'), 'Orange Juice', 3.00, 6, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),
((SELECT id FROM item_categories WHERE name = 'beverages'), 'Mineral Water', 1.00, 12, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),
((SELECT id FROM item_categories WHERE name = 'beverages'), 'Cola Drink', 1.50, 12, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),
((SELECT id FROM item_categories WHERE name = 'beverages'), 'Energy Drink', 2.50, 6, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),

-- Condiments (unit: btl)
((SELECT id FROM item_categories WHERE name = 'condiments'), 'Tomato Ketchup', 2.00, 10, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),
((SELECT id FROM item_categories WHERE name = 'condiments'), 'Mayonnaise', 2.50, 10, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),
((SELECT id FROM item_categories WHERE name = 'condiments'), 'BBQ Sauce', 3.00, 5, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),
((SELECT id FROM item_categories WHERE name = 'condiments'), 'Mustard', 1.50, 10, 
 (SELECT id FROM units WHERE abbreviation = 'btl')),

-- Canned Goods (unit: ctn)
((SELECT id FROM item_categories WHERE name = 'canned'), 'Canned Beans', 1.20, 20, 
 (SELECT id FROM units WHERE abbreviation = 'ctn')),
((SELECT id FROM item_categories WHERE name = 'canned'), 'Canned Tomatoes', 1.50, 15, 
 (SELECT id FROM units WHERE abbreviation = 'ctn')),
((SELECT id FROM item_categories WHERE name = 'canned'), 'Canned Corn', 1.30, 20, 
 (SELECT id FROM units WHERE abbreviation = 'ctn')),
((SELECT id FROM item_categories WHERE name = 'canned'), 'Canned Tuna', 2.00, 10, 
 (SELECT id FROM units WHERE abbreviation = 'ctn')),

-- Frozen Goods (unit: pk)
((SELECT id FROM item_categories WHERE name = 'frozen'), 'Frozen Peas', 2.00, 10, 
 (SELECT id FROM units WHERE abbreviation = 'pk')),
((SELECT id FROM item_categories WHERE name = 'frozen'), 'Frozen Strawberries', 4.00, 8, 
 (SELECT id FROM units WHERE abbreviation = 'pk')),
((SELECT id FROM item_categories WHERE name = 'frozen'), 'Frozen Spinach', 2.50, 10, 
 (SELECT id FROM units WHERE abbreviation = 'pk')),
((SELECT id FROM item_categories WHERE name = 'frozen'), 'Frozen Mixed Vegetables', 3.00, 8, 
 (SELECT id FROM units WHERE abbreviation = 'pk'));


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
 (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
 (SELECT id FROM currencies WHERE code = 'NGN'), 
 (SELECT id FROM vendor_categories WHERE name = 'Meat'), 
 0.00),
('Mrs', 'Jane', 'Smith', 'Fresh Chickens Ltd.', 'Jane Smith (Fresh Chickens)', 
 'janesmith@freshchickens.ng', '0123456790', '08012345679', 
 '456 Poultry Avenue, Ibadan, Nigeria', '{"twitter": "https://twitter.com/freshchickens"}', 
 (SELECT id FROM payment_terms WHERE name = 'Due on delivery'), 
 (SELECT id FROM currencies WHERE code = 'NGN'), 
 (SELECT id FROM vendor_categories WHERE name = 'Fresh Produce'), 
 50000.00),
('Miss', 'Mary', 'Johnson', 'Catfish Traders Co.', 'Mary Johnson (Catfish Traders)', 
 'maryjohnson@catfishco.ng', '0123456791', '08012345680', 
 '789 Fish Market, Port Harcourt, Nigeria', '{"instagram": "https://instagram.com/catfishco"}', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 (SELECT id FROM currencies WHERE code = 'NGN'), 
 (SELECT id FROM vendor_categories WHERE name = 'Seafood'), 
 20000.00),
('Dr', 'Peter', 'Oluwole', 'Pork Processing Plc.', 'Dr. Peter Oluwole (Pork Processing)', 
 'peteroluwole@porkplc.ng', '0123456792', '08012345681', 
 '101 Meat Lane, Abuja, Nigeria', '{"linkedin": "https://linkedin.com/company/porkprocessing"}', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 (SELECT id FROM currencies WHERE code = 'NGN'), 
 (SELECT id FROM vendor_categories WHERE name = 'Meat'), 
 0.00),
('Prof', 'Amaka', 'Okafor', 'Lamb Lovers Inc.', 'Prof. Amaka Okafor (Lamb Lovers)', 
 'amakaokafor@lamblovers.ng', '0123456793', '08012345682', 
 '202 Sheep Street, Enugu, Nigeria', '{"youtube": "https://youtube.com/lamblovers"}', 
 (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
 (SELECT id FROM currencies WHERE code = 'NGN'), 
 (SELECT id FROM vendor_categories WHERE name = 'Meat'), 
 0.00);


-- Seed vendor transactions
INSERT INTO vendor_transactions (vendor_id, transaction_type, amount, notes)
VALUES
((SELECT id FROM vendors WHERE display_name = 'John Doe (Beef Supplies)'), 'credit', 500000.00, 'Initial deposit by Beef Supplies Ltd.'),
((SELECT id FROM vendors WHERE display_name = 'Jane Smith (Fresh Chickens)'), 'debit', 300000.00, 'Payment for supply of fresh beef'),
((SELECT id FROM vendors WHERE display_name = 'Mary Johnson (Catfish Traders)'), 'debit', 200000.00, 'Purchase of catfish processing equipment'),
((SELECT id FROM vendors WHERE display_name = 'Dr. Peter Oluwole (Pork Processing)'), 'debit', 450000.00, 'Purchase of meat processing equipment'),
((SELECT id FROM vendors WHERE display_name = 'Prof. Amaka Okafor (Lamb Lovers)'), 'credit', 100000.00, 'Payment for meat supplies');


-- Seed data for items
INSERT INTO items (name, description, price, unit_id, opening_stock, threshold_value, media, category_id)
VALUES
('Beef', 'Fresh beef cuts', 10.00, (SELECT id FROM units WHERE name = 'kilogram'), 100, 10, '["https://i.imgur.com/IwdmYjG.jpeg"]', (SELECT id FROM item_categories WHERE name = 'meat')),
('Chicken', 'Fresh chicken cuts', 20.00, (SELECT id FROM units WHERE name = 'kilogram'), 500, 50, '["https://i.imgur.com/gnRz12P.png"]', (SELECT id FROM item_categories WHERE name = 'meat')),
('Catfish', 'Fresh catfish fillets', 15.00, (SELECT id FROM units WHERE name = 'kilogram'), 100, 50, '["https://i.imgur.com/MxiMX9v.png"]', (SELECT id FROM item_categories WHERE name = 'seafood')),
('Pork', 'Fresh pork cuts', 12.00, (SELECT id FROM units WHERE name = 'kilogram'), 300, 3, '["https://i.imgur.com/dGGizfQ.png"]', (SELECT id FROM item_categories WHERE name = 'meat')),
('Lamb', 'Fresh lamb cuts', 25.00, (SELECT id FROM units WHERE name = 'kilogram'), 100, 2, '["https://i.imgur.com/8TIGZM2.png"]', (SELECT id FROM item_categories WHERE name = 'meat')),
('Salmon', 'Fresh salmon fillets', 30.00, (SELECT id FROM units WHERE name = 'kilogram'), 100, 10, '["https://i.imgur.com/ISOOCLs.png"]', (SELECT id FROM item_categories WHERE name = 'seafood')),
('Eggs', 'Farm fresh eggs', 5.00, (SELECT id FROM units WHERE name = 'dozen'), 500, 50, '["https://i.imgur.com/G0mVY78.png"]', (SELECT id FROM item_categories WHERE name = 'dairy')),
('Cheese', 'Fresh cheese', 3.50, (SELECT id FROM units WHERE name = 'kilogram'), 200, 20, '["https://i.imgur.com/IVCT63j.png"]', (SELECT id FROM item_categories WHERE name = 'dairy')),
('Milk', 'Fresh cow milk', 2.00, (SELECT id FROM units WHERE name = 'liter'), 400, 40, '["https://i.imgur.com/5JXHh4d.png"]', (SELECT id FROM item_categories WHERE name = 'dairy')),
('Yogurt', 'Fresh yogurt', 1.80, (SELECT id FROM units WHERE name = 'liter'), 300, 30, '["https://i.imgur.com/NQTBB4c.jpeg"]', (SELECT id FROM item_categories WHERE name = 'dairy')),
('Bread', 'Freshly baked bread', 1.50, (SELECT id FROM units WHERE name = 'item'), 100, 10, '["https://i.imgur.com/jA1O0Qb.png"]', (SELECT id FROM item_categories WHERE name = 'pastry')),
('Rice', 'Premium rice', 2.50, (SELECT id FROM units WHERE name = 'kilogram'), 250, 25, '["https://i.imgur.com/pwXSxkn.png"]', (SELECT id FROM item_categories WHERE name = 'grill')),
('Pasta', 'Premium pasta', 1.00, (SELECT id FROM units WHERE name = 'kilogram'), 500, 50, '["https://i.imgur.com/ZLncFYM.png"]', (SELECT id FROM item_categories WHERE name = 'grill')),
('Honey', 'Organic honey', 5.00, (SELECT id FROM units WHERE name = 'bottle'), 100, 10, '["https://i.imgur.com/PheCs9s.png"]', (SELECT id FROM item_categories WHERE name = 'condiments')),
('Olive Oil', 'Extra virgin olive oil', 6.00, (SELECT id FROM units WHERE name = 'bottle'), 80, 8, '["https://i.imgur.com/GEkayag.png"]', (SELECT id FROM item_categories WHERE name = 'condiments')),
('Vegetable Oil', 'Pure vegetable oil', 3.50, (SELECT id FROM units WHERE name = 'bottle'), 200, 20, '["https://i.imgur.com/W5F6Gzv.png"]', (SELECT id FROM item_categories WHERE name = 'condiments')),
('Mustard', 'Organic mustard', 1.50, (SELECT id FROM units WHERE name = 'bottle'), 150, 15, '["https://i.imgur.com/GwxyZSF.png"]', (SELECT id FROM item_categories WHERE name = 'condiments')),
('Ketchup', 'Organic ketchup', 1.50, (SELECT id FROM units WHERE name = 'bottle'), 200, 30, '["https://i.imgur.com/JfO21Bm.png"]', (SELECT id FROM item_categories WHERE name = 'condiments')),
('Mayonnaise', 'Organic mayonnaise', 2.00, (SELECT id FROM units WHERE name = 'bottle'), 100, 10, '["https://i.imgur.com/PSuuQmI.png"]', (SELECT id FROM item_categories WHERE name = 'condiments')),
('Soy Sauce', 'Premium soy sauce', 1.00, (SELECT id FROM units WHERE name = 'bottle'), 150, 15, '["https://i.imgur.com/zMLrQYD.png"]', (SELECT id FROM item_categories WHERE name = 'condiments'));


-- Seed data for item_stocks
INSERT INTO item_stocks (item_id, quantity, date_received, expiry_date)
VALUES
((SELECT id FROM items WHERE name = 'Beef'), 100, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Chicken'), 5, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Catfish'), 0, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Pork'), 30, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Lamb'), 0, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Salmon'), 5, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Eggs'), 50, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Cheese'), 0, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Milk'), 30, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Yogurt'), 3, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Bread'), 0, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Rice'), 22, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Pasta'), 500, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Honey'), 100, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Olive Oil'), 0, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Vegetable Oil'), 0, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Mustard'), 14, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Ketchup'), 20, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Mayonnaise'), 9, '2023-12-01', '2025-12-31'),
((SELECT id FROM items WHERE name = 'Soy Sauce'), 10, '2023-12-01', '2025-12-31');


-- Seed data for item_stock_vendors
INSERT INTO item_stock_vendors (stock_id, vendor_id)
VALUES
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
 (SELECT id FROM vendors WHERE display_name = 'John Doe (Beef Supplies)')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Chicken')), 
 (SELECT id FROM vendors WHERE display_name = 'John Doe (Beef Supplies)')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Catfish')), 
 (SELECT id FROM vendors WHERE display_name = 'John Doe (Beef Supplies)')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Pork')), 
 (SELECT id FROM vendors WHERE display_name = 'John Doe (Beef Supplies)')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Lamb')), 
 (SELECT id FROM vendors WHERE display_name = 'John Doe (Beef Supplies)'));

-- Seed data for item_stock_departments
INSERT INTO item_stock_departments (stock_id, department_id)
VALUES
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
 (SELECT id FROM departments WHERE name = 'Accounting')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Chicken')), 
 (SELECT id FROM departments WHERE name = 'Human Resources')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Catfish')), 
 (SELECT id FROM departments WHERE name = 'Dispatch Riders')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Pork')), 
 (SELECT id FROM departments WHERE name = 'Kitchen')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Lamb')), 
 (SELECT id FROM departments WHERE name = 'Chef'));

-- Seed data for item_stock_manufacturers
INSERT INTO item_stock_manufacturers (stock_id, manufacturer_id)
VALUES
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
 (SELECT id FROM item_manufacturers WHERE name = 'Dangote Industries')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Chicken')), 
 (SELECT id FROM item_manufacturers WHERE name = 'Nestle Nigeria')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Catfish')), 
 (SELECT id FROM item_manufacturers WHERE name = 'PZ Cussons')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Pork')), 
 (SELECT id FROM item_manufacturers WHERE name = 'Chi Limited')),
((SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Lamb')), 
 (SELECT id FROM item_manufacturers WHERE name = 'Honeywell Flour Mills'));

-- Seed customers
INSERT INTO customers (customer_type, salutation, first_name, last_name, display_name, company_name, email, work_phone, mobile_phone, address, social_media, balance, payment_term_id, currency_id)
VALUES
('individual', 'Mr', 'Aliyu', 'Abdullahi', 'Aliyu Abdullahi', 
 'Agro Tech LTD', 'aliyuabdullahi@gmail.com', '0123456794', '08012345683', 
 'No. 15 Market Road, Kano, Nigeria', '{"facebook": "https://facebook.com/aliyuabdullahi"}', 0.00, 
 (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
 (SELECT id FROM currencies WHERE name = 'Naira')),
('business', 'Mrs', 'Titi', 'Adedayo', 'Adedayo Enterprises', 
 'Adedayo Enterprises', 'titiadedayo@adedayoenterprises.ng', '0123456795', 
 '08012345684', 'Plot 7 Industrial Layout, Lagos, Nigeria', 
 '{"twitter": "https://twitter.com/adedayoenterprises"}', 1200.00, 
 (SELECT id FROM payment_terms WHERE name = 'Due on delivery'), 
 (SELECT id FROM currencies WHERE name = 'Naira')),
('individual', 'Miss', 'Bola', 'Ogunyemi', 'Bola Ogunyemi', 
 'Nat Agro Ltd', 'bolaogunyemi@yahoo.com', '0123456796', '08012345685', 
 'Flat 3, Block B, Ibadan, Nigeria', '{"instagram": "https://instagram.com/bolaogunyemi"}', 50000.00, 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 (SELECT id FROM currencies WHERE name = 'Naira')),
('business', 'Dr', 'Chinedu', 'Eze', 'Eze Agro Ltd.', 
 'Eze Agro Ltd.', 'chinedueze@ezeagro.ng', '0123456797', 
 '08012345686', '123 Farmland Avenue, Umuahia, Nigeria', 
 '{"linkedin": "https://linkedin.com/company/ezeagro"}', 0.00, 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 (SELECT id FROM currencies WHERE name = 'Naira')),
('individual', 'Prof', 'Amina', 'Yusuf', 'Prof. Amina Yusuf', 
 'Gerald Agro Ltd', 'aminayusuf@gmail.com', '0123456798', '08012345687', 
 'No. 10 Crescent, Abuja, Nigeria', '{"youtube": "https://youtube.com/aminayusuf"}', 0.00, 
 (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
 (SELECT id FROM currencies WHERE name = 'Naira')),
('individual', 'Mr', 'Tunde', 'Ojo', 'Tunde Ojo', 
 'Ojo Farms', 'test1@gmail.com', '0123456799', '08012345688',
 'No. 20 Farm Road, Ibadan, Nigeria', '{"facebook": "https://facebook.com/tundeojo"}', 0.00, 
 (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
 (SELECT id FROM currencies WHERE name = 'Naira')),
('business', 'Mrs', 'Bimpe', 'Adeyemi', 'AdeyemiFarms',
 'Adeyemi Farms', 'adms@gmail.com', '0123456700', '08012345689',
 'No. 30 Farm Road, Lagos, Nigeria', '{"twitter": "https://twitter.com/adeyemifarms"}', 0.00, 
 (SELECT id FROM payment_terms WHERE name = 'Due on delivery'), 
 (SELECT id FROM currencies WHERE name = 'Naira'));


-- Seed customer transactions
INSERT INTO customer_transactions 
(customer_id, transaction_type, amount, notes)
VALUES
((SELECT id FROM customers WHERE display_name = 'Aliyu Abdullahi'), 'credit', 500000.00, 'Initial deposit by Aliyu Abdullahi'),
((SELECT id FROM customers WHERE display_name = 'Titi Adedayo'), 'debit', 300000.00, 'Payment for supply of fresh chicken'),
((SELECT id FROM customers WHERE display_name = 'Bola Ogunyemi'), 'credit', 100000.00, 'Payment for supply of fresh catfish'),
((SELECT id FROM customers WHERE display_name = 'Chinedu Eze'), 'debit', 45000.00, 'Purchase of fresh pork cuts'),
((SELECT id FROM customers WHERE display_name = 'Amina Yusuf'), 'credit', 100000.00, 'Payment for supply of fresh lamb cuts');


-- Insert into purchase_orders
INSERT INTO purchase_orders (
    vendor_id, branch_id, delivery_date, 
    payment_term_id, subject, notes, terms_and_conditions, 
    discount, shipping_charge, total, status, processed_by, 
    date_received, created_at
)
VALUES
-- January 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-01-10', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Fresh Produce Supplies', 'Ensure items are fresh', 
 'Delivery to main warehouse only', 150, 4000, 90000, 'paid', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-01-15', '2024-01-05'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Abuja'), '2024-01-25', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'Monthly Stock', 'Restock Abuja branch', 
 'Include detailed itemized invoice', 100, 3000, 85000, 'issued', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-01-30', '2024-01-18'),

-- February 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-02-14', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Valentine Special Orders', 'Expedite delivery', 
 'Late delivery will result in cancellation', 200, 5000, 120000, 'sent', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-02-18', '2024-02-05'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Port Harcourt'), '2024-02-20', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Catering Supplies', 'Urgent bulk order', 
 'Deliver to Lagos branch', 300, 6000, 150000, 'overdue', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-02-22', '2024-02-14'),

-- March 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Abuja'), '2024-03-10', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Spring Restock', 'Quality inspection required', 
 'Goods must match sample', 250, 4000, 100000, 'received', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-03-14', '2024-03-01'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-03-30', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'Meat Supplies', 'Deliver in refrigerated truck', 
 'All safety measures must be followed', 200, 5000, 180000, 'paid', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-03-31', '2024-03-20'),

-- April 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Port Harcourt'), '2024-04-15', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Fruits and Vegetables', 'Ensure freshness', 
 'Late delivery not acceptable', 150, 2500, 95000, 'issued', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-04-18', '2024-04-10'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-04-28', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'General Supplies', 'Ensure all items are complete', 
 'Delivery location: Lagos', 100, 3000, 110000, 'sent', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-04-30', '2024-04-22'),

-- May 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Port Harcourt'), '2024-05-15', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Warehouse Stock Replenishment', 'Priority delivery', 
 'Strict quality check required', 200, 4000, 130000, 'paid', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-05-20', '2024-05-10'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-05-25', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Monthly Restock', 'Contact manager upon delivery', 
 'Invoice to include all discounts', 100, 2500, 75000, 'received', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-05-28', '2024-05-18'),

-- June 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Abuja'), '2024-06-05', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Meat Supply', 'Freshly butchered meat only', 
 'Deliver within the first week of the month', 300, 4000, 140000, 'paid', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-06-08', '2024-06-01'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Port Harcourt'), '2024-06-15', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'Bakery Items', 'Ensure all items are gluten-free', 
 'Delivery time strictly between 8AM-10AM', 100, 2000, 70000, 'sent', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-06-17', '2024-06-10'),

-- July 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-07-12', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'Grocery Supplies', 'Ensure no damages', 
 'Delivery time strictly between 2PM-4PM', 250, 4500, 120000, 'paid', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-07-15', '2024-07-05'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Abuja'), '2024-07-25', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Monthly Restock', 'Double-check item quality', 
 'Delivery to main warehouse', 100, 3000, 90000, 'received', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-07-28', '2024-07-20'),

-- August 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Port Harcourt'), '2024-08-10', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Catering Supplies', 'Expedite delivery', 
 'Late delivery not acceptable', 300, 5000, 150000, 'overdue', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-08-14', '2024-08-01'),
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-08-30', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'Fruit and Veg Supply', 'Ensure organic produce', 
 'All invoices to be sent within 24 hours', 200, 4500, 95000, 'sent', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-09-01', '2024-08-20'),

-- September 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Abuja'), '2024-09-05', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Bulk Meat Supply', 'Ensure packaging is secure', 
 'Delivery must be within 24 hours of confirmation', 150, 4500, 130000, 'sent', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-09-08', '2024-09-01'),

-- October 2024
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-10-12', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'Chicken Restock', 'Ensure fresh packaging', 
 'Delivery must happen in the morning', 200, 3500, 125000, 'overdue', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-10-15', '2024-10-05'),

-- November 2024
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Lagos'), '2024-11-10', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'Winter Stock', 'Double-check stock levels', 
 'Shipment must arrive on the first week of the month', 250, 4500, 135000, 'issued', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-11-15', '2024-11-05'),

-- December 2024
((SELECT id FROM vendors WHERE company_name = 'Fresh Chickens Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Abuja'), '2024-12-20', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
 'Holiday Restock', 'Ensure all items are properly labeled', 
 'Delivery must happen before December 25th', 300, 5000, 145000, 'paid', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2024-12-22', '2024-12-10'),

-- January 2025
((SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
 (SELECT id FROM branches WHERE name = 'Port Harcourt'), '2025-01-05', 
 (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
 'New Year Supplies', 'Ensure all items are delivered in time', 
 'Items should be inspected before delivery', 150, 3000, 120000, 'received', 
 (SELECT id FROM users WHERE email = 'starters@admin.com'), 
 '2025-01-07', '2025-01-01');

-- Insert into purchase_order_items
INSERT INTO purchase_order_items (
    purchase_order_id, item_id, quantity, price, tax_id
)
VALUES
((SELECT id FROM purchase_orders WHERE notes = 'Ensure items are fresh'), (SELECT id FROM items WHERE name = 'Beef'), 2, 5000.00, (SELECT id FROM taxes WHERE name = 'VAT (7.50)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Chicken'), 1, 10000.00, (SELECT id FROM taxes WHERE name = 'Sales Tax (5.00)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Catfish'), 5, 4000.00, (SELECT id FROM taxes WHERE name = 'VAT (7.50)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Pork'), 10, 2000.00, (SELECT id FROM taxes WHERE name = 'Sales Tax (5.00)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Lamb'), 3, 3000.00, (SELECT id FROM taxes WHERE name = 'VAT (7.50)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Salmon'), 7, 7000.00, (SELECT id FROM taxes WHERE name = 'Sales Tax (5.00)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Eggs'), 4, 8000.00, (SELECT id FROM taxes WHERE name = 'VAT (7.50)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Cheese'), 2, 6000.00, (SELECT id FROM taxes WHERE name = 'Sales Tax (5.00)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Milk'), 6, 9000.00, (SELECT id FROM taxes WHERE name = 'VAT (7.50)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Yogurt'), 8, 12000.00, (SELECT id FROM taxes WHERE name = 'Sales Tax (5.00)')),
((SELECT id FROM purchase_orders WHERE notes = 'Ensure all items are delivered in time'), (SELECT id FROM items WHERE name = 'Bread'), 2, 4000.00, (SELECT id FROM taxes WHERE name = 'VAT (7.50)'));


-- Insert into sales_orders
INSERT INTO sales_orders (
    order_type, order_title, customer_id, payment_term_id, 
    payment_method_id, delivery_option, assigned_driver_id, 
    delivery_date, additional_note, customer_note, discount, 
    delivery_charge, total, status, processed_by, created_at
)
VALUES
-- January 2024
('order', 'January Breakfast Pack', 
    (SELECT id FROM customers WHERE display_name = 'Aliyu Abdullahi'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-01-10', 'Deliver hot', 'Knock loudly', 
    2000, 1000, 60000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2025-01-21'),
('order', 'Corporate Brunch', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on delivery'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'pickup', NULL, 
    '2024-01-25', 'Prepare sandwiches', 'Contact HR', 
    3000, 0, 150000, 'new order', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), NOW()),
('order', 'Lunch Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'sent', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), NOW()),

-- February 2024
('order', 'Valentine Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'pickup', NULL, 
    '2024-02-14', 'Pack with care', 'Include roses', 
    5000, 0, 90000, 'sent', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-02-01'),
('service', 'February Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-02-20', 'Coordinate with team', 'Serve on time', 
    8000, 12000, 350000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-02-05'),
('order', 'Lunch Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    CURRENT_DATE, 'Deliver by 1 PM', 'Call before delivery', 
    1500, 500, 70000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), NOW()),

-- March 2024
('order', 'Spring Special Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'pickup', NULL, 
    '2024-03-12', 'Include extra snacks', 'Confirm delivery time', 
    3500, 0, 120000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-03-05'),
('service', 'Corporate Lunch', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-03-20', 'Follow-up with team', 'Serve by noon', 
    4500, 500, 200000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-03-10'),
('order', 'March Celebration', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    CURRENT_DATE, 'Deliver before 5 PM', 'Include birthday cake', 
    1500, 1000, 80000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), NOW()),

-- April 2024
('order', 'Easter Special', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'pickup', NULL, 
    '2024-04-10', 'Include chocolate eggs', 'Contact before delivery', 
    2500, 0, 110000, 'new order', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-04-01'),
('service', 'Easter Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-04-14', 'Ensure everything is fresh', 'Prepare table settings', 
    6000, 500, 250000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-04-05'),
('order', 'April Lunch', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    CURRENT_DATE, 'Deliver by 12 PM', 'Call before arrival', 
    1800, 300, 95000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), NOW()),

-- May 2024
('order', 'May Day Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-05-01', 'Include extra juice', 'Deliver by noon', 
    2500, 0, 125000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-05-01'),
('service', 'Corporate Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'pickup', NULL, 
    '2024-05-20', 'Coordinate with client', 'Ensure proper packaging', 
    7000, 12000, 400000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-05-15'),
('order', 'May Celebration', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    CURRENT_DATE, 'Deliver by 3 PM', 'Call before delivery', 
    2500, 1000, 130000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), NOW()),

-- June 2024
('order', 'Summer Breeze Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-06-01', 'Ensure fresh items', 'Confirm before shipping', 
    3000, 0, 150000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-06-01'),

-- July 2024
('order', 'July 4th Celebration Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-07-04', 'Include fireworks', 'Call before delivery', 
    4000, 0, 150000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-07-01'),
('service', 'Corporate BBQ', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'pickup', NULL, 
    '2024-07-10', 'Ensure extra ribs', 'Confirm with HR', 
    3500, 0, 175000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-07-05'),

-- August 2024
('order', 'Back to School Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-08-10', 'Include extra notebooks', 'Call before delivery', 
    2500, 0, 100000, 'new order', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-08-01'),
('service', 'August Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'pickup', NULL, 
    '2024-08-15', 'Ensure vegetarian options', 'Coordinate with team', 
    5000, 500, 250000, 'paid', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-08-05'),

-- September 2024
('order', 'Labor Day Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-09-01', 'Include extra drinks', 'Call before delivery', 
    2000, 500, 95000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-09-01'),
('service', 'September Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-09-05', 'Include dessert', 'Ensure vegetarian options', 
    4500, 0, 220000, 'new order', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-09-01'),

-- October 2024
('order', 'Halloween Special', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'pickup', NULL, 
    '2024-10-31', 'Include spooky snacks', 'Ensure packaging is festive', 
    5000, 0, 200000, 'new order', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-10-01'),
('service', 'Corporate Halloween Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-10-25', 'Include extra candy', 'Confirm number of attendees', 
    4500, 1000, 225000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-10-05'),

-- November 2024
('order', 'Thanksgiving Feast', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-11-26', 'Include extra pies', 'Deliver before 5 PM', 
    6000, 0, 250000, 'pending', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-11-01'),
('service', 'Corporate Thanksgiving Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'pickup', NULL, 
    '2024-11-25', 'Ensure gluten-free options', 'Confirm with client', 
    5000, 5000, 300000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-11-10'),

-- December 2024
('order', 'Christmas Dinner Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2024-12-25', 'Include gifts', 'Ensure delivery by noon', 
    7000, 0, 300000, 'new order', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-12-01'),
('service', 'Christmas Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    'pickup', NULL, 
    '2024-12-24', 'Prepare extra sides', 'Coordinate with team', 
    5500, 1000, 275000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2024-12-05'),

-- January 2025
('order', 'New Year Celebration Pack', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'delivery', 
    (SELECT id FROM users WHERE email = 'jane@example.com'), 
    '2025-01-01', 'Include extra drinks', 'Confirm delivery before 3 PM', 
    4000, 500, 160000, 'pending', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2025-01-01'),
('service', 'Corporate New Year Catering', 
    (SELECT id FROM customers WHERE display_name = 'AdeyemiFarms'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    'pickup', NULL, 
    '2025-01-02', 'Ensure vegetarian options', 'Confirm with team', 
    6000, 0, 250000, 'completed', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), '2025-01-05');


-- Insert into sales_order_items
INSERT INTO sales_order_items (
    sales_order_id, item_id, quantity, price
)
VALUES
((SELECT id FROM sales_orders WHERE customer_note = 'Confirm with team'), 
 (SELECT id FROM price_lists WHERE item_details = 'Croissant'), 2, 5000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Knock loudly'), 
 (SELECT id FROM price_lists WHERE item_details = 'Salmon Fillet'), 5, 400.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Knock loudly'), 
 (SELECT id FROM price_lists WHERE item_details = 'Pork Chops'), 10, 200.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Knock loudly'), 
 (SELECT id FROM price_lists WHERE item_details = 'BBQ Ribs'), 3, 12000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Confirm with team'), 
 (SELECT id FROM price_lists WHERE item_details = 'Whole Milk'), 2, 25000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Confirm with team'), 
 (SELECT id FROM price_lists WHERE item_details = 'Cheddar Cheese'), 1, 18000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Contact HR'), 
 (SELECT id FROM price_lists WHERE item_details = 'Greek Yogurt'), 4, 5000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Contact HR'), 
 (SELECT id FROM price_lists WHERE item_details = 'Mineral Water'), 2, 2500.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Contact HR'), 
 (SELECT id FROM price_lists WHERE item_details = 'Energy Drink'), 3, 3000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Contact HR'), 
 (SELECT id FROM price_lists WHERE item_details = 'Tomato Ketchup'), 1, 5000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Ensure delivery by noon'), 
 (SELECT id FROM price_lists WHERE item_details = 'Mayonnaise'), 2, 1500.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Ensure delivery by noon'), 
 (SELECT id FROM price_lists WHERE item_details = 'BBQ Sauce'), 4, 10000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Ensure delivery by noon'), 
 (SELECT id FROM price_lists WHERE item_details = 'Mustard'), 2, 15000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Contact before delivery'), 
 (SELECT id FROM price_lists WHERE item_details = 'Canned Beans'), 1, 20000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Ensure delivery by noon'), 
 (SELECT id FROM price_lists WHERE item_details = 'Canned Tomatoes'), 3, 8000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Contact before delivery'), 
 (SELECT id FROM price_lists WHERE item_details = 'Frozen Peas'), 5, 1000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Ensure vegetarian options'), 
 (SELECT id FROM price_lists WHERE item_details = 'Frozen Strawberries'), 4, 2000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Ensure vegetarian options'), 
 (SELECT id FROM price_lists WHERE item_details = 'Frozen Spinach'), 3, 2000.00),
((SELECT id FROM sales_orders WHERE customer_note = 'Ensure vegetarian options'), 
 (SELECT id FROM price_lists WHERE item_details = 'Frozen Mixed Vegetables'), 2, 1500.00);



-- Insert into item_stock_adjustments
INSERT INTO item_stock_adjustments (
    stock_id, manager_id, source_type, source_id, source_department_id, 
    quantity, adjustment_type, description, created_at
)
VALUES
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'vendor', 
    (SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    20, 
    'addition', 
    'Restocked beef inventory', 
    '2024-01-10'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Chicken')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'vendor', 
    (SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    10, 
    'addition', 
    'Restocked chicken inventory', 
    '2024-01-12'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Lamb')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'vendor', 
    (SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    15, 
    'addition', 
    'Restocked catfish inventory', 
    '2024-01-15'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Pork')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'user', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    5, 
    'subtraction', 
    'Sold pork cuts', 
    '2024-01-18'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'vendor', 
    (SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    10, 
    'addition', 
    'Restocked lamb inventory', 
    '2024-01-20'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Chicken')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'user', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    5, 
    'subtraction', 
    'Sold salmon fillets', 
    '2024-01-22'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'vendor', 
    (SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    50, 
    'addition', 
    'Restocked eggs', 
    '2024-01-24'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'vendor', 
    (SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    10, 
    'addition', 
    'Restocked cheese', 
    '2024-01-25'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Beef')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'user', 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    15, 
    'subtraction', 
    'Sold milk', 
    '2024-01-26'
),
(
    (SELECT id FROM item_stocks WHERE item_id = (SELECT id FROM items WHERE name = 'Lamb')), 
    (SELECT id FROM users WHERE email = 'starters@admin.com'), 
    'vendor', 
    (SELECT id FROM vendors WHERE company_name = 'Beef Supplies Ltd.'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    20, 
    'addition', 
    'Restocked yogurt', 
    '2024-01-28'
);


INSERT INTO comments (
    user_id, parent_id, entity_id, entity_type, 
    comment, created_at
)
VALUES
((SELECT id FROM users WHERE email = 'starters@admin.com'), NULL, (SELECT id FROM items WHERE name = 'Beef'), 'item_stock_adjustment', 
    'Great quality beef!', '2024-01-10'),
((SELECT id FROM users WHERE email = 'nat@aiq.com'), NULL, (SELECT id FROM items WHERE name = 'Catfish'), 'item_stock_adjustment', 
    'Chicken was fresh and tasty.', '2024-01-12'),
((SELECT id FROM users WHERE email = 'sog@aiq.com'), NULL, (SELECT id FROM items WHERE name = 'Soy Sauce'), 'item_stock_adjustment', 
    'Catfish fillets were amazing.', '2024-01-15'),
((SELECT id FROM users WHERE email = 'starters@admin.com'), NULL, (SELECT id FROM items WHERE name = 'Honey'), 'item_stock_adjustment', 
    'I agree, very fresh!', '2024-01-16'),
((SELECT id FROM users WHERE email = 'nat@aiq.com'), NULL, (SELECT id FROM items WHERE name = 'Mustard'), 'item_stock_adjustment', 
    'Pork cuts were decent.', '2024-01-18'),
((SELECT id FROM users WHERE email = 'sog@aiq.com'), NULL, (SELECT id FROM items WHERE name = 'Mayonnaise'), 'item_stock_adjustment', 
    'Lamb was tender and juicy.', '2024-01-20'),
((SELECT id FROM users WHERE email = 'starters@admin.com'), NULL, (SELECT id FROM items WHERE name = 'Eggs'), 'item_stock_adjustment', 
    'Salmon was okay.', '2024-01-22'),
((SELECT id FROM users WHERE email = 'nat@aiq.com'), NULL, (SELECT id FROM items WHERE name = 'Ketchup'), 'item_stock_adjustment', 
    'Eggs are always good.', '2024-01-24'),
((SELECT id FROM users WHERE email = 'sog@aiq.com'), NULL, (SELECT id FROM items WHERE name = 'Olive Oil'), 'item_stock_adjustment', 
    'Cheese was fresh.', '2024-01-25'),
((SELECT id FROM users WHERE email = 'starters@admin.com'), NULL, (SELECT id FROM items WHERE name = 'Pasta'), 'item_stock_adjustment', 
    'Milk was great!', '2024-01-26');


INSERT INTO expenses_categories (name, description)
VALUES
('Travel', 'Expenses related to business travel'),
('Office Supplies', 'Expenses for office supplies and stationery'),
('Utilities', 'Payments for electricity, water, and internet bills'),
('Meals', 'Expenses for meals and entertainment'),
('Maintenance', 'Expenses related to equipment maintenance');

INSERT INTO expenses 
(expense_title, expense_category, payment_method_id, payment_term_id, 
 department_id, amount, bank_charges, date_of_expense, notes, status, processed_by)
VALUES
('Flight to Client Meeting', 
    (SELECT id FROM expenses_categories WHERE name = 'Travel'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    1500.00, 15.00, '2024-01-10', 
    'Flight to meet client for project discussion', 'paid', 
    (SELECT id FROM users WHERE email = 'odun@aiq.com')),
    
('Office Stationery Purchase', 
    (SELECT id FROM expenses_categories WHERE name = 'Office Supplies'), 
    (SELECT id FROM payment_methods WHERE name = 'USSD Payment'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on delivery'), 
    (SELECT id FROM departments WHERE name = 'Beverages'), 
    250.00, 5.00, '2024-01-20', 
    'Purchase of pens, paper, and files', 'paid', 
    (SELECT id FROM users WHERE email = 'john@example.com')),

('March Internet Bill', 
    (SELECT id FROM expenses_categories WHERE name = 'Utilities'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM departments WHERE name = 'Dispatch Riders'), 
    120.00, 2.50, '2024-02-05', 
    'Payment for monthly internet service', 'paid', 
    (SELECT id FROM users WHERE email = 'nat@aiq.com')),

('Team Lunch', 
    (SELECT id FROM expenses_categories WHERE name = 'Meals'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    300.00, 10.00, '2025-01-15', 
    'Lunch with the team after project completion', 'paid', 
    (SELECT id FROM users WHERE email = 'jane@example.com')),

('Printer Maintenance', 
    (SELECT id FROM expenses_categories WHERE name = 'Maintenance'), 
    (SELECT id FROM payment_methods WHERE name = 'USSD Payment'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 30 days'), 
    (SELECT id FROM departments WHERE name = 'Beverages'), 
    500.00, 0.00, '2025-01-28', 
    'Scheduled maintenance for office printer', 'cancelled', 
    (SELECT id FROM users WHERE email = 'john@example.com')),

('Business Conference', 
    (SELECT id FROM expenses_categories WHERE name = 'Travel'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    800.00, 10.00, '2025-01-30', 
    'Conference related to industry developments', 'paid', 
    (SELECT id FROM users WHERE email = 'odun@aiq.com')),

('Annual Software License', 
    (SELECT id FROM expenses_categories WHERE name = 'Office Supplies'), 
    (SELECT id FROM payment_methods WHERE name = 'USSD Payment'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on delivery'), 
    (SELECT id FROM departments WHERE name = 'Beverages'), 
    2000.00, 50.00, '2025-01-07', 
    'Annual renewal of office software licenses', 'paid', 
    (SELECT id FROM users WHERE email = 'john@example.com')),

('Team Dinner', 
    (SELECT id FROM expenses_categories WHERE name = 'Meals'), 
    (SELECT id FROM payment_methods WHERE name = 'Cash'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 7 days'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    350.00, 15.00, '2025-01-15', 
    'Dinner with the team for quarterly review', 'paid', 
    (SELECT id FROM users WHERE email = 'jane@example.com')),

('March Electricity Bill', 
    (SELECT id FROM expenses_categories WHERE name = 'Utilities'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    (SELECT id FROM payment_terms WHERE name = 'Due in 14 days'), 
    (SELECT id FROM departments WHERE name = 'Dispatch Riders'), 
    250.00, 5.00, '2025-01-03', 
    'Payment for electricity consumption in the office', 'paid', 
    (SELECT id FROM users WHERE email = 'nat@aiq.com')),

('Travel to Conference', 
    (SELECT id FROM expenses_categories WHERE name = 'Travel'), 
    (SELECT id FROM payment_methods WHERE name = 'Bank Transfer'), 
    (SELECT id FROM payment_terms WHERE name = 'Due on receipt'), 
    (SELECT id FROM departments WHERE name = 'Snacks'), 
    1200.00, 20.00, '2025-01-10', 
    'Business trip to the industry conference', 'paid', 
    (SELECT id FROM users WHERE email = 'odun@aiq.com'));

