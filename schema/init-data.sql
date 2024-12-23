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
 NULL, NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', NULL);

INSERT INTO user_permissions (user_id, permission_id) VALUES
(1, 5);

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

INSERT INTO expenses_categories (name, description)
VALUES
('Travel', 'Expenses related to business travel'),
('Office Supplies', 'Expenses for office supplies and stationery'),
('Utilities', 'Payments for electricity, water, and internet bills'),
('Meals', 'Expenses for meals and entertainment'),
('Maintenance', 'Expenses related to equipment maintenance');

