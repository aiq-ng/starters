-- Seed data for roles
INSERT INTO roles (id, name) VALUES
('550e8400-e29b-41d4-a716-446655440000', 'Admin'),
('550e8400-e29b-41d4-a716-446655440001', 'Head of department'),
('550e8400-e29b-41d4-a716-446655440002', 'Assistant to head of department'),
('550e8400-e29b-41d4-a716-446655440003', 'Supervisor'),
('550e8400-e29b-41d4-a716-446655440004', 'Team Lead');

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
    (SELECT id FROM departments WHERE name = 'Snacks'));
