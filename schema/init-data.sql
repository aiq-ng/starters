-- Seed data for roles
INSERT INTO roles (id, name) VALUES
('550e8400-e29b-41d4-a716-446655440000', 'Admin'),
('550e8400-e29b-41d4-a716-446655440001', 'Head of department'),
('550e8400-e29b-41d4-a716-446655440002', 'Assistant to head of department'),
('550e8400-e29b-41d4-a716-446655440003', 'Supervisor'),
('550e8400-e29b-41d4-a716-446655440004', 'Team Lead');

-- Seed data for salutations
INSERT INTO salutations (id, name) VALUES
('660e9500-e25b-41d4-b716-556655440000', 'Mr.'),
('660e9500-e25b-41d4-b716-556655440001', 'Mrs.'),
('660e9500-e25b-41d4-b716-556655440002', 'Miss'),
('660e9500-e25b-41d4-b716-556655440003', 'Dr.'),
('660e9500-e25b-41d4-b716-556655440004', 'Prof.'),
('660e9500-e25b-41d4-b716-556655440005', 'Engr.');

-- Seed data for permissions
INSERT INTO permissions (id, name, description) VALUES
('770a1600-f31c-42d5-c827-667755440000', 'sales', 
 'View and manage sales orders'),
('770a1600-f31c-42d5-c827-667755440001', 'procurement', 
 'View and manage purchase orders'),
('770a1600-f31c-42d5-c827-667755440002', 'hr', 
 'View and manage HR data'),
('770a1600-f31c-42d5-c827-667755440003', 'accounting', 
 'View and manage accounting data'),
('770a1600-f31c-42d5-c827-667755440004', 'admin', 
 'Full access to all features'),
('770a1600-f31c-42d5-c827-667755440005', 'inventory', 
 'View and manage inventory data');

-- Seed data for base pay types
INSERT INTO base_pay_types (id, name, description) VALUES
('880b2700-f42d-53e6-d938-778866550000', 'hourly', 
 'Base salary calculated based on hourly rates'),
('880b2700-f42d-53e6-d938-778866550001', 'delivery', 
 'Base salary calculated based on delivery rates');

-- Insert default qualification periods
-- Insert default qualification periods
INSERT INTO work_leave_qualifications (id, name) VALUES
('990c3800-b53e-44f7-e049-889977660000', '3 months'),
('990c3800-b53e-44f7-e049-889977660001', '6 months'),
('990c3800-b53e-44f7-e049-889977660002', 'annually');

-- Seed data for number of working days
INSERT INTO no_of_working_days (id, name, description) VALUES
('aa0d4900-c64f-55f8-f150-99aa88770000', 'Standard Week', 
 'A typical working week with 5 days, Monday to Friday'),
('aa0d4900-c64f-55f8-f150-99aa88770001', 'Extended Week', 
 'A working week that includes Saturday, making it 6 days'),
('aa0d4900-c64f-55f8-f150-99aa88770002', 'Shift Work', 
 'A rotating shift schedule, covering various days including weekends'),
('aa0d4900-c64f-55f8-f150-99aa88770003', 'Flexible Week', 
 'A flexible working arrangement with varied working days'),
('aa0d4900-c64f-55f8-f150-99aa88770004', 'Compressed Week', 
 'A compressed working schedule with fewer working days but longer hours');

-- Seed data for loan types
INSERT INTO loan_types (id, name, description) VALUES
('bb1e5a00-d75f-46f9-b261-aabb99880000', 'personal', 
 'Personal loans for individual use'),
('bb1e5a00-d75f-46f9-b261-aabb99880001', 'staff', 
 'Loans provided to staff members'),
('bb1e5a00-d75f-46f9-b261-aabb99880002', 'business', 
 'Business loans for companies'),
('bb1e5a00-d75f-46f9-b261-aabb99880003', 'education', 
 'Loans for educational purposes'),
('bb1e5a00-d75f-46f9-b261-aabb99880004', 'mortgage', 
 'Loans for purchasing property');

-- Seed data for departments
INSERT INTO departments (
    id, name, salary_type, base_type_id, base_rate, base_salary, 
    description, work_leave_qualification
) VALUES
('cc2f6b00-e86f-47a0-b372-bbccaa990000', 'Accounting', 'fixed', 
 NULL, NULL, 2500.00, 'Department for finance and accounting', 
 (SELECT id FROM work_leave_qualifications WHERE name = '3 months' LIMIT 1)),
('cc2f6b00-e86f-47a0-b372-bbccaa990001', 'Human Resources', 'fixed', 
 NULL, NULL, 2000.00, 'Department for HR and recruitment', 
 (SELECT id FROM work_leave_qualifications WHERE name = '6 months' LIMIT 1)),
('cc2f6b00-e86f-47a0-b372-bbccaa990002', 'Dispatch Riders', 'base', 
 (SELECT id FROM base_pay_types WHERE name = 'hourly' LIMIT 1), 15.00, NULL, 
 'Department for dispatch riders', 
 (SELECT id FROM work_leave_qualifications WHERE name = 'annually' LIMIT 1)),
('cc2f6b00-e86f-47a0-b372-bbccaa990003', 'Kitchen', 'fixed', 
 NULL, NULL, 2200.00, 'Department for kitchen staff', 
 (SELECT id FROM work_leave_qualifications WHERE name = '3 months' LIMIT 1)),
('cc2f6b00-e86f-47a0-b372-bbccaa990004', 'Sales Representatives', 'base', 
 (SELECT id FROM base_pay_types WHERE name = 'hourly' LIMIT 1), 20.00, NULL, 
 'Department for sales staff, paid hourly', 
 (SELECT id FROM work_leave_qualifications WHERE name = 'annually' LIMIT 1));


-- Seed data for users
INSERT INTO users (
    id, firstname, lastname, email, password, role_id, avatar_url, 
    date_of_birth, address, next_of_kin, date_of_employment, salary, 
    bank_details, leave, nin, passport, department_id
) VALUES
('25d36e74-fbb4-45ec-8ec2-4f8abc7d79ca', 'Starters', 'Admin', 
    'starters@admin.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Admin'), 
    'https://i.imgur.com/0GY9tnz.jpeg', NULL, NULL, NULL, NULL, 
    NULL, NULL, NULL, 'https://i.imgur.com/AB1234567C.jpeg', 
    'https://i.imgur.com/A1234567.jpeg', 
    (SELECT id FROM departments WHERE name = 'Snacks')),

('276405ab-b5ea-4325-9ad8-976a66f264a9', 'Opororo', 'Nathaniel', 
    'nat@aiq.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Rider'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1990-05-15', 
    '123 Main St, Cityville', 'Tom Johnson', '2020-01-01', 3500.00, 
    '{"bank_name": "Bank ABC", "account_number": "1234567890"}', NULL, 
    'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', 
    (SELECT id FROM departments WHERE name = 'Dispatch Riders')),

('2fe8e866-5300-46f9-bc0d-90b53a8645c6', 'Emmanuel', 'Afcon', 
    'sog@aiq.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Sales'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1985-10-20', 
    '456 Oak St, Townsville', 'Sarah Smith', '2018-09-15', 4000.00, 
    '{"bank_name": "Bank XYZ", "account_number": "9876543210"}', '2023-06-01', 
    'https://i.imgur.com/CD2345678D.jpeg', 'https://i.imgur.com/B2345678.jpeg', 
    (SELECT id FROM departments WHERE name = 'Sales Representatives')),

('2df706f3-76ee-4ff8-8bbb-7772595eac69', 'Babanla', 'Odunlami', 
    'odun@aiq.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Accountant'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1992-08-10', 
    '789 Pine St, Villageville', 'Emily Brown', '2019-02-18', 2800.00, 
    '{"bank_name": "Bank LMN", "account_number": "1112233445"}', '2023-05-15', 
    'https://i.imgur.com/EF3456789E.jpeg', 'https://i.imgur.com/C3456789.jpeg', 
    (SELECT id FROM departments WHERE name = 'Kitchen')),

('2a5abd5e-ca8b-42e0-917a-a5d00eda896f', 'Kingsley', 'Jobojobo', 
    'kingsley@aiq.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Chef'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1988-12-05', 
    '321 Elm St, Hamletville', 'Anna Doe', '2021-07-21', 4200.00, 
    '{"bank_name": "Bank DEF", "account_number": "9988776655"}', NULL, 
    'https://i.imgur.com/GH4567890F.jpeg', 'https://i.imgur.com/D4567890.jpeg', 
    (SELECT id FROM departments WHERE name = 'HR')),

('20c27317-e747-4f86-9d78-b77e2be931fe', 'Jane', 'Smith', 
    'jane@example.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Marketing'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1994-03-25', 
    '654 Maple St, Citytown', 'Linda Smith', '2022-06-11', 3100.00, 
    '{"bank_name": "Bank GHI", "account_number": "6677889900"}', '2023-08-01', 
    'https://i.imgur.com/IJ5678901G.jpeg', 'https://i.imgur.com/E5678901.jpeg', 
    (SELECT id FROM departments WHERE name = 'Marketing')),

('2f9980b9-c1d4-4ca3-9731-45fcdf3c23cd', 'Mary', 'Jones', 
    'mary@example.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Sales'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1991-11-30', 
    '987 Cedar St, Smallville', 'John Jones', '2017-04-09', 3600.00, 
    '{"bank_name": "Bank JKL", "account_number": "1122334455"}', NULL, 
    'https://i.imgur.com/KL6789012H.jpeg', 'https://i.imgur.com/F6789012.jpeg', 
    (SELECT id FROM departments WHERE name = 'Sales Representatives')),

('260fe3ef-da1b-46d9-a333-1c2f40b30378', 'Peter', 'Brown', 
    'peter@example.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Manager'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1989-07-15', 
    '123 Birch St, Greenfield', 'Samantha Brown', '2016-03-20', 3900.00, 
    '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 
    'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 
    (SELECT id FROM departments WHERE name = 'Beverages')),

('25a7e877-51e4-47fd-8bd2-52c01892d05b', 'Tk', 'Chef', 
    'chef@admin.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Chef'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1989-07-15', 
    '123 Birch St, Greenfield', 'Samantha Brown', '2016-03-20', 3900.00, 
    '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 
    'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 
    (SELECT id FROM departments WHERE name = 'Kitchen')),

('294b2962-3e12-4675-a8d4-0fa2c92506c4', 'Peter', 'Chef', 
    'chef1@admin.com', crypt('password', gen_salt('bf')), 
    (SELECT id FROM roles WHERE name = 'Chef'), 
    'https://i.imgur.com/0GY9tnz.jpeg', '1989-07-15', 
    '123 Birch St, Greenfield', 'Samantha Brown', '2016-03-20', 3900.00, 
    '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 
    'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 
    (SELECT id FROM departments WHERE name = 'Kitchen'));


-- Seed currencies
INSERT INTO currencies (id, name, symbol, code) VALUES 
('150e8400-e29b-41d4-a716-446655440100', 'Naira', '₦', 'NGN');

-- Seed payment methods
INSERT INTO payment_methods (id, name, description) VALUES 
('150e8400-e29b-41d4-a716-446655440101', 'Bank Transfer', 
    'Payment via bank transfer'), 
('150e8400-e29b-41d4-a716-446655440102', 'USSD Payment', 
    'Payment via USSD banking codes'), 
('150e8400-e29b-41d4-a716-446655440103', 'Cash', 'Payment in cash');

-- Seed payment terms
INSERT INTO payment_terms (id, name, description) VALUES 
('150e8400-e29b-41d4-a716-446655440104', 'Due on receipt', 
    'Payment due on receipt'), 
('150e8400-e29b-41d4-a716-446655440105', 'Due on delivery', 
    'Payment due on delivery'), 
('150e8400-e29b-41d4-a716-446655440106', 'Due in 7 days', 
    'Payment due in 7 days'), 
('150e8400-e29b-41d4-a716-446655440107', 'Due in 14 days', 
    'Payment due in 14 days'), 
('150e8400-e29b-41d4-a716-446655440108', 'Due in 30 days', 
    'Payment due in 30 days');

-- Seed branches
INSERT INTO branches (id, name, description) VALUES 
('150e8400-e29b-41d4-a716-446655440109', 'Lagos', 
    'Branch located in Lagos, Nigeria'), 
('150e8400-e29b-41d4-a716-44665544010a', 'Abuja', 
    'Branch located in Abuja, Nigeria'), 
('150e8400-e29b-41d4-a716-44665544010b', 'Port Harcourt', 
    'Branch located in Port Harcourt, Nigeria');

-- Seed units
INSERT INTO units (id, name, abbreviation) VALUES 
('150e8400-e29b-41d4-a716-44665544010c', 'item', 'pcs'), 
('150e8400-e29b-41d4-a716-44665544010d', 'kilogram', 'kg'), 
('150e8400-e29b-41d4-a716-44665544010e', 'liter', 'L'), 
('150e8400-e29b-41d4-a716-44665544010f', 'box', 'box'), 
('150e8400-e29b-41d4-a716-446655440110', 'meter', 'm'), 
('150e8400-e29b-41d4-a716-446655440111', 'carton', 'ctn'), 
('150e8400-e29b-41d4-a716-446655440112', 'pack', 'pk'), 
('150e8400-e29b-41d4-a716-446655440113', 'crate', 'crate'), 
('150e8400-e29b-41d4-a716-446655440114', 'bottle', 'btl'), 
('150e8400-e29b-41d4-a716-446655440115', 'dozen', 'doz');

-- Seed item categories
INSERT INTO item_categories (id, name, description) VALUES 
('150e8400-e29b-41d4-a716-446655440116', 'pastry', 
    'Baked goods like bread, cakes, and pastries'), 
('150e8400-e29b-41d4-a716-446655440117', 'seafood', 
    'Fresh and frozen seafood items'), 
('150e8400-e29b-41d4-a716-446655440118', 'grill', 
    'Grilled food items like chicken and fish'), 
('150e8400-e29b-41d4-a716-446655440119', 'meat', 
    'Fresh and frozen meat items'), 
('150e8400-e29b-41d4-a716-44665544011a', 'dairy', 
    'Milk, cheese, yogurt, and other dairy products'), 
('150e8400-e29b-41d4-a716-44665544011b', 'beverages', 
    'Drinks like water, juice, and soft drinks'), 
('150e8400-e29b-41d4-a716-44665544011c', 'condiments', 
    'Sauces, spices, and seasonings'), 
('150e8400-e29b-41d4-a716-44665544011d', 'canned', 
    'Canned food items like beans and tomatoes'), 
('150e8400-e29b-41d4-a716-44665544011e', 'frozen', 
    'Frozen food items like vegetables and fruits');

-- Seed vendor categories
INSERT INTO vendor_categories (id, name, description) VALUES 
('150e8400-e29b-41d4-a716-44665544011f', 'Fresh Produce', 
    'Vendors supplying fresh fruits and vegetables'), 
('150e8400-e29b-41d4-a716-446655440120', 'Kitchen Supply', 
    'Vendors supplying kitchenware and utensils'), 
('150e8400-e29b-41d4-a716-446655440121', 'Meat', 
    'Vendors supplying fresh and frozen meats'), 
('150e8400-e29b-41d4-a716-446655440122', 'Seafood', 
    'Vendors supplying fresh and frozen seafood'), 
('150e8400-e29b-41d4-a716-446655440123', 'Snacks', 
    'Vendors supplying local snacks like chin chin and plantain chips'), 
('150e8400-e29b-41d4-a716-446655440124', 'Furniture', 
    'Vendors supplying household and office furniture');

-- Seed item manufacturers
INSERT INTO item_manufacturers (id, name, website) VALUES 
('150e8400-e29b-41d4-a716-446655440125', 'Yaale Electronics', 
    'https://www.yaaleelectronics.com'), 
('150e8400-e29b-41d4-a716-446655440126', 'FarmFresh Nigeria', 
    'https://www.farmfresh.com.ng'), 
('150e8400-e29b-41d4-a716-446655440127', 'Vono Furniture', 
    'https://www.vonofurniture.com.ng'), 
('150e8400-e29b-41d4-a716-446655440128', 'Naija Snacks', 
    'https://www.naijasnacks.com'), 
('150e8400-e29b-41d4-a716-446655440129', 'Kitchen Essentials', 
    'https://www.kitchenessentials.com');


-- Seed taxes
INSERT INTO taxes (id, name, rate, description) VALUES 
('150e8400-e29b-41d4-a716-44665544012a', 'VAT', 7.50, 
    'Value Added Tax in Nigeria'), 
('150e8400-e29b-41d4-a716-44665544012b', 'Sales Tax', 5.00, 
    'General Sales Tax in West Africa');

-- Seed item manufacturers
INSERT INTO item_manufacturers (id, name, website) VALUES 
('150e8400-e29b-41d4-a716-44665544012c', 'Dangote Industries', 
    'https://www.dangote.com'), 
('150e8400-e29b-41d4-a716-44665544012d', 'Nestle Nigeria', 
    'https://www.nestle-cwa.com'), 
('150e8400-e29b-41d4-a716-44665544012e', 'PZ Cussons', 
    'https://www.pzcussons.com'), 
('150e8400-e29b-41d4-a716-44665544012f', 'Chi Limited', 
    'https://www.houseofchi.com'), 
('150e8400-e29b-41d4-a716-446655440130', 'Honeywell Flour Mills', 
    'https://www.honeywellflour.com');

-- Seed order ratings
INSERT INTO order_ratings (id, order_id, name, rating, review) VALUES 
('150e8400-e29b-41d4-a716-446655440131', 
    (SELECT id FROM sales_orders WHERE customer_note = 'Confirm with team'), 
    'AdeyemiFarms', 4, 'Good service'), 
('150e8400-e29b-41d4-a716-446655440132', 
    (SELECT id FROM sales_orders WHERE customer_note = 'Knock loudly'), 
    'AdeyemiFarms', 3, 'Late delivery'), 
('150e8400-e29b-41d4-a716-446655440133', 
    (SELECT id FROM sales_orders WHERE customer_note = 'Contact HR'), 
    'AdeyemiFarms', 5, 'Excellent service'), 
('150e8400-e29b-41d4-a716-446655440134', 
    (SELECT id FROM sales_orders WHERE customer_note = 'Ensure delivery by noon'), 
    'AdeyemiFarms', 4, 'Good service');

-- Seed expenses categories
INSERT INTO expenses_categories (id, name, description) VALUES 
('150e8400-e29b-41d4-a716-446655440135', 'Travel', 
    'Expenses related to business travel'), 
('150e8400-e29b-41d4-a716-446655440136', 'Office Supplies', 
    'Expenses for office supplies and stationery'), 
('150e8400-e29b-41d4-a716-446655440137', 'Utilities', 
    'Payments for electricity, water, and internet bills'), 
('150e8400-e29b-41d4-a716-446655440138', 'Meals', 
    'Expenses for meals and entertainment'), 
('150e8400-e29b-41d4-a716-446655440139', 'Maintenance', 
    'Expenses related to equipment maintenance');

-- Seed delivery charges
INSERT INTO delivery_charges (id, name, amount, description) VALUES 
('150e8400-e29b-41d4-a716-44665544013a', 'Standard Delivery', 500.00, 
    'Delivery within 3-5 business days'), 
('150e8400-e29b-41d4-a716-44665544013b', 'Express Delivery', 1500.00, 
    'Delivery within 24 hours'), 
('150e8400-e29b-41d4-a716-44665544013c', 'Same-Day Delivery', 2500.00, 
    'Delivery within the same day for orders placed before 12 PM');

