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

-- Seed data for salutations
INSERT INTO salutations (name) VALUES
('Mr.'),
('Mrs.'),
('Miss'),
('Dr.'),
('Prof.'),
('Engr.');

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
    (SELECT id FROM departments WHERE name = 'Beverages')),
('Tk', 'Chef', 'chef@admin.com', 'password', 
    (SELECT id FROM roles WHERE name = 'Chef'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1989-07-15', '123 Birch St, Greenfield', 'Samantha Brown', '2016-03-20', 3900.00, 
    '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 
    (SELECT id FROM departments WHERE name = 'Kitchen')),
('Peter', 'Chef', 'chef1@admin.com', 'password', 
    (SELECT id FROM roles WHERE name = 'Chef'), 
    'https://i.imgur.com/0GY9tnz.jpeg', 
    '1989-07-15', '123 Birch St, Greenfield', 'Samantha Brown', '2016-03-20', 3900.00, 
    '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 
    (SELECT id FROM departments WHERE name = 'Kitchen'));


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

INSERT INTO order_ratings (order_id, name, rating, review)
VALUES
((SELECT id FROM sales_orders WHERE customer_note = 'Confirm with team'), 
 'AdeyemiFarms', 4, 'Good service'),

((SELECT id FROM sales_orders WHERE customer_note = 'Knock loudly'), 
 'AdeyemiFarms', 3, 'Late delivery'),

((SELECT id FROM sales_orders WHERE customer_note = 'Contact HR'), 
 'AdeyemiFarms', 5, 'Excellent service'),

((SELECT id FROM sales_orders WHERE customer_note = 'Ensure delivery by noon'), 
 'AdeyemiFarms', 4, 'Good service');


INSERT INTO expenses_categories (name, description)
VALUES
('Travel', 'Expenses related to business travel'),
('Office Supplies', 'Expenses for office supplies and stationery'),
('Utilities', 'Payments for electricity, water, and internet bills'),
('Meals', 'Expenses for meals and entertainment'),
('Maintenance', 'Expenses related to equipment maintenance');

INSERT INTO delivery_charges (name, amount, description) VALUES
('Standard Delivery', 500.00, 'Delivery within 3-5 business days'),
('Express Delivery', 1500.00, 'Delivery within 24 hours'),
('Same-Day Delivery', 2500.00, 'Delivery within the same day for orders placed before 12 PM');

