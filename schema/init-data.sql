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
