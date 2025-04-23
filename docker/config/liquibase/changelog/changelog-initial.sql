-- liquibase formatted sql

-- changeset liquibase:1745384592554-1
CREATE TABLE "public"."roles" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, CONSTRAINT "roles_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-2
INSERT INTO "public"."roles" ("id", "name") VALUES ('550e8400-e29b-41d4-a716-446655440000', 'Admin');
INSERT INTO "public"."roles" ("id", "name") VALUES ('550e8400-e29b-41d4-a716-446655440001', 'Head of department');
INSERT INTO "public"."roles" ("id", "name") VALUES ('550e8400-e29b-41d4-a716-446655440002', 'Assistant to head of department');
INSERT INTO "public"."roles" ("id", "name") VALUES ('550e8400-e29b-41d4-a716-446655440003', 'Supervisor');
INSERT INTO "public"."roles" ("id", "name") VALUES ('550e8400-e29b-41d4-a716-446655440004', 'Team Lead');

-- changeset liquibase:1745384592554-3
CREATE TABLE "public"."permissions" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, CONSTRAINT "permissions_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-4
INSERT INTO "public"."permissions" ("id", "name", "description") VALUES ('770a1600-f31c-42d5-c827-667755440000', 'sales', 'View and manage sales orders');
INSERT INTO "public"."permissions" ("id", "name", "description") VALUES ('770a1600-f31c-42d5-c827-667755440001', 'procurement', 'View and manage purchase orders');
INSERT INTO "public"."permissions" ("id", "name", "description") VALUES ('770a1600-f31c-42d5-c827-667755440002', 'hr', 'View and manage HR data');
INSERT INTO "public"."permissions" ("id", "name", "description") VALUES ('770a1600-f31c-42d5-c827-667755440003', 'accounting', 'View and manage accounting data');
INSERT INTO "public"."permissions" ("id", "name", "description") VALUES ('770a1600-f31c-42d5-c827-667755440004', 'admin', 'Full access to all features');
INSERT INTO "public"."permissions" ("id", "name", "description") VALUES ('770a1600-f31c-42d5-c827-667755440005', 'inventory', 'View and manage inventory data');

-- changeset liquibase:1745384592554-5
CREATE TABLE "public"."salutations" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, CONSTRAINT "salutations_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-6
INSERT INTO "public"."salutations" ("id", "name") VALUES ('660e9500-e25b-41d4-b716-556655440000', 'Mr.');
INSERT INTO "public"."salutations" ("id", "name") VALUES ('660e9500-e25b-41d4-b716-556655440001', 'Mrs.');
INSERT INTO "public"."salutations" ("id", "name") VALUES ('660e9500-e25b-41d4-b716-556655440002', 'Miss');
INSERT INTO "public"."salutations" ("id", "name") VALUES ('660e9500-e25b-41d4-b716-556655440003', 'Dr.');
INSERT INTO "public"."salutations" ("id", "name") VALUES ('660e9500-e25b-41d4-b716-556655440004', 'Prof.');
INSERT INTO "public"."salutations" ("id", "name") VALUES ('660e9500-e25b-41d4-b716-556655440005', 'Engr.');

-- changeset liquibase:1745384592554-7
CREATE TABLE "public"."delivery_charges" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50), "amount" numeric(20, 2) NOT NULL, "description" TEXT, CONSTRAINT "delivery_charges_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-8
INSERT INTO "public"."delivery_charges" ("id", "name", "amount", "description") VALUES ('150e8400-e29b-41d4-a716-44665544013a', 'Standard Delivery', 500.00, 'Delivery within 3-5 business days');
INSERT INTO "public"."delivery_charges" ("id", "name", "amount", "description") VALUES ('150e8400-e29b-41d4-a716-44665544013b', 'Express Delivery', 1500.00, 'Delivery within 24 hours');
INSERT INTO "public"."delivery_charges" ("id", "name", "amount", "description") VALUES ('150e8400-e29b-41d4-a716-44665544013c', 'Same-Day Delivery', 2500.00, 'Delivery within the same day for orders placed before 12 PM');

-- changeset liquibase:1745384592554-9
CREATE TABLE "public"."discounts" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50), "discount_type" VARCHAR(20), "value" numeric(20, 2) NOT NULL, "description" TEXT, CONSTRAINT "discounts_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-10
INSERT INTO "public"."discounts" ("id", "name", "discount_type", "value", "description") VALUES ('360e8400-e29b-41d4-a716-446655440002', 'Loyalty Discount', 'percentage', 5.00, '5% discount for returning customers');
INSERT INTO "public"."discounts" ("id", "name", "discount_type", "value", "description") VALUES ('360e8400-e29b-41d4-a716-446655440003', 'Black Friday Deal', 'percentage', 20.00, '20% discount for Black Friday sales');
INSERT INTO "public"."discounts" ("id", "name", "discount_type", "value", "description") VALUES ('360e8400-e29b-41d4-a716-446655440004', 'Flat ₦500 Off', 'amount', 500.00, '₦500 off on orders above ₦5000');
INSERT INTO "public"."discounts" ("id", "name", "discount_type", "value", "description") VALUES ('360e8400-e29b-41d4-a716-446655440005', 'VIP Discount', 'amount', 1000.00, '₦1000 discount for VIP customers');
INSERT INTO "public"."discounts" ("id", "name", "discount_type", "value", "description") VALUES ('360e8400-e29b-41d4-a716-446655440007', 'Festive Offer', 'percentage', 15.00, '15% discount for holiday season purchases');

-- changeset liquibase:1745384592554-11
CREATE TABLE "public"."currencies" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "symbol" VARCHAR(10), "code" VARCHAR(10), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "currencies_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-12
INSERT INTO "public"."currencies" ("id", "name", "symbol", "code", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440100', 'Naira', '₦', 'NGN', '2025-04-23 05:02:07.108194', '2025-04-23 05:02:07.108194');

-- changeset liquibase:1745384592554-13
CREATE TABLE "public"."base_pay_types" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, CONSTRAINT "base_pay_types_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-14
INSERT INTO "public"."base_pay_types" ("id", "name", "description") VALUES ('880b2700-f42d-53e6-d938-778866550000', 'hourly', 'Base salary calculated based on hourly rates');
INSERT INTO "public"."base_pay_types" ("id", "name", "description") VALUES ('880b2700-f42d-53e6-d938-778866550001', 'delivery', 'Base salary calculated based on delivery rates');

-- changeset liquibase:1745384592554-15
CREATE TABLE "public"."work_leave_qualifications" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, CONSTRAINT "work_leave_qualifications_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-16
INSERT INTO "public"."work_leave_qualifications" ("id", "name") VALUES ('990c3800-b53e-44f7-e049-889977660000', '3 months');
INSERT INTO "public"."work_leave_qualifications" ("id", "name") VALUES ('990c3800-b53e-44f7-e049-889977660001', '6 months');
INSERT INTO "public"."work_leave_qualifications" ("id", "name") VALUES ('990c3800-b53e-44f7-e049-889977660002', 'annually');

-- changeset liquibase:1745384592554-17
CREATE TABLE "public"."branches" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "branches_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-18
INSERT INTO "public"."branches" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440109', 'Lagos', 'Branch located in Lagos, Nigeria', '2025-04-23 05:02:07.112437', '2025-04-23 05:02:07.112437');
INSERT INTO "public"."branches" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544010a', 'Abuja', 'Branch located in Abuja, Nigeria', '2025-04-23 05:02:07.112686', '2025-04-23 05:02:07.112686');
INSERT INTO "public"."branches" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544010b', 'Port Harcourt', 'Branch located in Port Harcourt, Nigeria', '2025-04-23 05:02:07.112706', '2025-04-23 05:02:07.112706');

-- changeset liquibase:1745384592554-19
CREATE TABLE "public"."item_categories" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_categories_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-20
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440116', 'pastry', 'Baked goods like bread, cakes, and pastries', '2025-04-23 05:02:07.11579', '2025-04-23 05:02:07.11579');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440117', 'seafood', 'Fresh and frozen seafood items', '2025-04-23 05:02:07.116062', '2025-04-23 05:02:07.116062');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440118', 'grill', 'Grilled food items like chicken and fish', '2025-04-23 05:02:07.116072', '2025-04-23 05:02:07.116072');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440119', 'meat', 'Fresh and frozen meat items', '2025-04-23 05:02:07.116077', '2025-04-23 05:02:07.116077');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544011a', 'dairy', 'Milk, cheese, yogurt, and other dairy products', '2025-04-23 05:02:07.116081', '2025-04-23 05:02:07.116081');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544011b', 'beverages', 'Drinks like water, juice, and soft drinks', '2025-04-23 05:02:07.116084', '2025-04-23 05:02:07.116085');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544011c', 'condiments', 'Sauces, spices, and seasonings', '2025-04-23 05:02:07.116088', '2025-04-23 05:02:07.116088');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544011d', 'canned', 'Canned food items like beans and tomatoes', '2025-04-23 05:02:07.116091', '2025-04-23 05:02:07.116091');
INSERT INTO "public"."item_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544011e', 'frozen', 'Frozen food items like vegetables and fruits', '2025-04-23 05:02:07.116093', '2025-04-23 05:02:07.116093');

-- changeset liquibase:1745384592554-21
CREATE TABLE "public"."units" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "abbreviation" VARCHAR(10), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "units_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-22
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544010c', 'item', 'pcs', '2025-04-23 05:02:07.114145', '2025-04-23 05:02:07.114145');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544010d', 'kilogram', 'kg', '2025-04-23 05:02:07.114381', '2025-04-23 05:02:07.114382');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544010e', 'liter', 'L', '2025-04-23 05:02:07.11439', '2025-04-23 05:02:07.11439');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544010f', 'box', 'box', '2025-04-23 05:02:07.114394', '2025-04-23 05:02:07.114394');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440110', 'meter', 'm', '2025-04-23 05:02:07.114397', '2025-04-23 05:02:07.114397');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440111', 'carton', 'ctn', '2025-04-23 05:02:07.1144', '2025-04-23 05:02:07.1144');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440112', 'pack', 'pk', '2025-04-23 05:02:07.114403', '2025-04-23 05:02:07.114403');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440113', 'crate', 'crate', '2025-04-23 05:02:07.114406', '2025-04-23 05:02:07.114406');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440114', 'bottle', 'btl', '2025-04-23 05:02:07.114409', '2025-04-23 05:02:07.114409');
INSERT INTO "public"."units" ("id", "name", "abbreviation", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440115', 'dozen', 'doz', '2025-04-23 05:02:07.114412', '2025-04-23 05:02:07.114412');

-- changeset liquibase:1745384592554-23
CREATE TABLE "public"."no_of_working_days" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "no_of_working_days_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-24
INSERT INTO "public"."no_of_working_days" ("id", "name", "description", "created_at", "updated_at") VALUES ('aa0d4900-c64f-55f8-f150-99aa88770000', 'Standard Week', 'A typical working week with 5 days, Monday to Friday', '2025-04-23 05:02:07.069331', '2025-04-23 05:02:07.069331');
INSERT INTO "public"."no_of_working_days" ("id", "name", "description", "created_at", "updated_at") VALUES ('aa0d4900-c64f-55f8-f150-99aa88770001', 'Extended Week', 'A working week that includes Saturday, making it 6 days', '2025-04-23 05:02:07.069516', '2025-04-23 05:02:07.069516');
INSERT INTO "public"."no_of_working_days" ("id", "name", "description", "created_at", "updated_at") VALUES ('aa0d4900-c64f-55f8-f150-99aa88770002', 'Shift Work', 'A rotating shift schedule, covering various days including weekends', '2025-04-23 05:02:07.069524', '2025-04-23 05:02:07.069524');
INSERT INTO "public"."no_of_working_days" ("id", "name", "description", "created_at", "updated_at") VALUES ('aa0d4900-c64f-55f8-f150-99aa88770003', 'Flexible Week', 'A flexible working arrangement with varied working days', '2025-04-23 05:02:07.069527', '2025-04-23 05:02:07.069527');
INSERT INTO "public"."no_of_working_days" ("id", "name", "description", "created_at", "updated_at") VALUES ('aa0d4900-c64f-55f8-f150-99aa88770004', 'Compressed Week', 'A compressed working schedule with fewer working days but longer hours', '2025-04-23 05:02:07.069529', '2025-04-23 05:02:07.069529');

-- changeset liquibase:1745384592554-25
CREATE TABLE "public"."vendor_categories" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "vendor_categories_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-26
INSERT INTO "public"."vendor_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544011f', 'Fresh Produce', 'Vendors supplying fresh fruits and vegetables', '2025-04-23 05:02:07.117462', '2025-04-23 05:02:07.117462');
INSERT INTO "public"."vendor_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440120', 'Kitchen Supply', 'Vendors supplying kitchenware and utensils', '2025-04-23 05:02:07.11766', '2025-04-23 05:02:07.11766');
INSERT INTO "public"."vendor_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440121', 'Meat', 'Vendors supplying fresh and frozen meats', '2025-04-23 05:02:07.117668', '2025-04-23 05:02:07.117668');
INSERT INTO "public"."vendor_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440122', 'Seafood', 'Vendors supplying fresh and frozen seafood', '2025-04-23 05:02:07.117671', '2025-04-23 05:02:07.117671');
INSERT INTO "public"."vendor_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440123', 'Snacks', 'Vendors supplying local snacks like chin chin and plantain chips', '2025-04-23 05:02:07.117673', '2025-04-23 05:02:07.117673');
INSERT INTO "public"."vendor_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440124', 'Furniture', 'Vendors supplying household and office furniture', '2025-04-23 05:02:07.117675', '2025-04-23 05:02:07.117675');

-- changeset liquibase:1745384592554-27
CREATE TABLE "public"."payment_methods" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "payment_methods_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-28
INSERT INTO "public"."payment_methods" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440101', 'Bank Transfer', 'Payment via bank transfer', '2025-04-23 05:02:07.109663', '2025-04-23 05:02:07.109663');
INSERT INTO "public"."payment_methods" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440102', 'USSD Payment', 'Payment via USSD banking codes', '2025-04-23 05:02:07.109836', '2025-04-23 05:02:07.109836');
INSERT INTO "public"."payment_methods" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440103', 'Cash', 'Payment in cash', '2025-04-23 05:02:07.109845', '2025-04-23 05:02:07.109845');

-- changeset liquibase:1745384592554-29
CREATE TABLE "public"."loan_types" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, CONSTRAINT "loan_types_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-30
INSERT INTO "public"."loan_types" ("id", "name", "description") VALUES ('bb1e5a00-d75f-46f9-b261-aabb99880000', 'personal', 'Personal loans for individual use');
INSERT INTO "public"."loan_types" ("id", "name", "description") VALUES ('bb1e5a00-d75f-46f9-b261-aabb99880001', 'staff', 'Loans provided to staff members');
INSERT INTO "public"."loan_types" ("id", "name", "description") VALUES ('bb1e5a00-d75f-46f9-b261-aabb99880002', 'business', 'Business loans for companies');
INSERT INTO "public"."loan_types" ("id", "name", "description") VALUES ('bb1e5a00-d75f-46f9-b261-aabb99880003', 'education', 'Loans for educational purposes');
INSERT INTO "public"."loan_types" ("id", "name", "description") VALUES ('bb1e5a00-d75f-46f9-b261-aabb99880004', 'mortgage', 'Loans for purchasing property');

-- changeset liquibase:1745384592554-31
CREATE TABLE "public"."payment_terms" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "payment_terms_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-32
INSERT INTO "public"."payment_terms" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440104', 'Due on receipt', 'Payment due on receipt', '2025-04-23 05:02:07.110947', '2025-04-23 05:02:07.110947');
INSERT INTO "public"."payment_terms" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440105', 'Due on delivery', 'Payment due on delivery', '2025-04-23 05:02:07.111111', '2025-04-23 05:02:07.111111');
INSERT INTO "public"."payment_terms" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440106', 'Due in 7 days', 'Payment due in 7 days', '2025-04-23 05:02:07.111118', '2025-04-23 05:02:07.111118');
INSERT INTO "public"."payment_terms" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440107', 'Due in 14 days', 'Payment due in 14 days', '2025-04-23 05:02:07.111121', '2025-04-23 05:02:07.111121');
INSERT INTO "public"."payment_terms" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440108', 'Due in 30 days', 'Payment due in 30 days', '2025-04-23 05:02:07.111124', '2025-04-23 05:02:07.111124');

-- changeset liquibase:1745384592554-33
CREATE TABLE "public"."taxes" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "rate" numeric(5, 2) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "taxes_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-34
INSERT INTO "public"."taxes" ("id", "name", "rate", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544012a', 'VAT', 7.50, 'Value Added Tax in Nigeria', '2025-04-23 05:02:07.120249', '2025-04-23 05:02:07.120249');
INSERT INTO "public"."taxes" ("id", "name", "rate", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544012b', 'Sales Tax', 5.00, 'General Sales Tax in West Africa', '2025-04-23 05:02:07.120459', '2025-04-23 05:02:07.120459');

-- changeset liquibase:1745384592554-35
CREATE TABLE "public"."item_manufacturers" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(255) NOT NULL, "website" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_manufacturers_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-36
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440125', 'Yaale Electronics', 'https://www.yaaleelectronics.com', '2025-04-23 05:02:07.118966', '2025-04-23 05:02:07.118966');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440126', 'FarmFresh Nigeria', 'https://www.farmfresh.com.ng', '2025-04-23 05:02:07.119101', '2025-04-23 05:02:07.119101');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440127', 'Vono Furniture', 'https://www.vonofurniture.com.ng', '2025-04-23 05:02:07.119106', '2025-04-23 05:02:07.119106');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440128', 'Naija Snacks', 'https://www.naijasnacks.com', '2025-04-23 05:02:07.119108', '2025-04-23 05:02:07.119108');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440129', 'Kitchen Essentials', 'https://www.kitchenessentials.com', '2025-04-23 05:02:07.11911', '2025-04-23 05:02:07.11911');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544012c', 'Dangote Industries', 'https://www.dangote.com', '2025-04-23 05:02:07.121505', '2025-04-23 05:02:07.121505');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544012d', 'Nestle Nigeria', 'https://www.nestle-cwa.com', '2025-04-23 05:02:07.121526', '2025-04-23 05:02:07.121526');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544012e', 'PZ Cussons', 'https://www.pzcussons.com', '2025-04-23 05:02:07.121528', '2025-04-23 05:02:07.121528');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-44665544012f', 'Chi Limited', 'https://www.houseofchi.com', '2025-04-23 05:02:07.121529', '2025-04-23 05:02:07.121529');
INSERT INTO "public"."item_manufacturers" ("id", "name", "website", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440130', 'Honeywell Flour Mills', 'https://www.honeywellflour.com', '2025-04-23 05:02:07.121531', '2025-04-23 05:02:07.121531');

-- changeset liquibase:1745384592554-37
CREATE TABLE "public"."cash_accounts" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "balance" numeric(20, 2) DEFAULT 0, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "cash_accounts_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-38
CREATE TABLE "public"."departments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100), "salary_type" VARCHAR(50), "base_type_id" UUID, "base_rate" numeric(20, 2), "base_salary" numeric(20, 2), "work_leave_qualification" UUID, "work_leave_period" VARCHAR(50), "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "departments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-39
INSERT INTO "public"."departments" ("id", "name", "salary_type", "base_type_id", "base_rate", "base_salary", "work_leave_qualification", "work_leave_period", "description", "created_at", "updated_at") VALUES ('cc2f6b00-e86f-47a0-b372-bbccaa990000', 'Accounting', 'fixed', NULL, NULL, 2500.00, '990c3800-b53e-44f7-e049-889977660000', NULL, 'Department for finance and accounting', '2025-04-23 05:02:07.072785', '2025-04-23 05:02:07.072785');
INSERT INTO "public"."departments" ("id", "name", "salary_type", "base_type_id", "base_rate", "base_salary", "work_leave_qualification", "work_leave_period", "description", "created_at", "updated_at") VALUES ('cc2f6b00-e86f-47a0-b372-bbccaa990001', 'Human Resources', 'fixed', NULL, NULL, 2000.00, '990c3800-b53e-44f7-e049-889977660001', NULL, 'Department for HR and recruitment', '2025-04-23 05:02:07.072972', '2025-04-23 05:02:07.072972');
INSERT INTO "public"."departments" ("id", "name", "salary_type", "base_type_id", "base_rate", "base_salary", "work_leave_qualification", "work_leave_period", "description", "created_at", "updated_at") VALUES ('cc2f6b00-e86f-47a0-b372-bbccaa990002', 'Dispatch Riders', 'base', '880b2700-f42d-53e6-d938-778866550000', 15.00, NULL, '990c3800-b53e-44f7-e049-889977660002', NULL, 'Department for dispatch riders', '2025-04-23 05:02:07.072993', '2025-04-23 05:02:07.072993');
INSERT INTO "public"."departments" ("id", "name", "salary_type", "base_type_id", "base_rate", "base_salary", "work_leave_qualification", "work_leave_period", "description", "created_at", "updated_at") VALUES ('cc2f6b00-e86f-47a0-b372-bbccaa990003', 'Kitchen', 'fixed', NULL, NULL, 2200.00, '990c3800-b53e-44f7-e049-889977660000', NULL, 'Department for kitchen staff', '2025-04-23 05:02:07.073002', '2025-04-23 05:02:07.073002');
INSERT INTO "public"."departments" ("id", "name", "salary_type", "base_type_id", "base_rate", "base_salary", "work_leave_qualification", "work_leave_period", "description", "created_at", "updated_at") VALUES ('cc2f6b00-e86f-47a0-b372-bbccaa990004', 'Sales Representatives', 'base', '880b2700-f42d-53e6-d938-778866550000', 20.00, NULL, '990c3800-b53e-44f7-e049-889977660002', NULL, 'Department for sales staff, paid hourly', '2025-04-23 05:02:07.073014', '2025-04-23 05:02:07.073014');

-- changeset liquibase:1745384592554-40
CREATE TABLE "public"."users" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "email" VARCHAR(255), "username" VARCHAR(100), "password" VARCHAR(255), "firstname" VARCHAR(100), "lastname" VARCHAR(100), "name" VARCHAR(255) GENERATED ALWAYS AS (((firstname)::text || ' '::text) || (lastname)::text) STORED, "avatar_url" VARCHAR(255), "date_of_birth" date, "address" TEXT, "next_of_kin" VARCHAR(100), "emergency_contact" TEXT, "date_of_employment" date, "department_id" UUID, "role_id" UUID, "no_of_working_days_id" UUID, "salary" numeric(20, 2), "bank_details" JSONB, "leave" date, "nin" VARCHAR(255), "passport" VARCHAR(255), "status" VARCHAR(50) DEFAULT 'active', "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "users_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-41
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('25d36e74-fbb4-45ec-8ec2-4f8abc7d79ca', 'starters@admin.com', NULL, '$2a$06$Ll1.2BlxhC9j8oo5Qth0kOO3KqV.Mv1F1bWVI/erNH8NKOiBLdjv.', 'Starters', 'Admin', 'Starters Admin', 'https://i.imgur.com/0GY9tnz.jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', 'active', '2025-04-23 05:02:07.018012', '2025-04-23 05:02:07.018012');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('276405ab-b5ea-4325-9ad8-976a66f264a9', 'nat@aiq.com', NULL, '$2a$06$GQpJYSlMhRqy2sVbUqzDZOwqaL54I9JX2.a4jgcEQcZi8TAH.Ob3K', 'Opororo', 'Nathaniel', 'Opororo Nathaniel', 'https://i.imgur.com/0GY9tnz.jpeg', '1990-05-15', '123 Main St, Cityville', 'Tom Johnson', NULL, '2020-01-01', 'cc2f6b00-e86f-47a0-b372-bbccaa990002', NULL, NULL, 3500.00, '{"bank_name": "Bank ABC", "account_number": "1234567890"}', NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', 'active', '2025-04-23 05:02:07.079415', '2025-04-23 05:02:07.079415');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('2fe8e866-5300-46f9-bc0d-90b53a8645c6', 'sog@aiq.com', NULL, '$2a$06$vyy2/mzdXCeFNnZaMbS6Oe3vgA6F5Z726jvRLbJdUl8XnZEZBbwTi', 'Emmanuel', 'Afcon', 'Emmanuel Afcon', 'https://i.imgur.com/0GY9tnz.jpeg', '1985-10-20', '456 Oak St, Townsville', 'Sarah Smith', NULL, '2018-09-15', 'cc2f6b00-e86f-47a0-b372-bbccaa990004', NULL, NULL, 4000.00, '{"bank_name": "Bank XYZ", "account_number": "9876543210"}', '2023-06-01', 'https://i.imgur.com/CD2345678D.jpeg', 'https://i.imgur.com/B2345678.jpeg', 'active', '2025-04-23 05:02:07.082948', '2025-04-23 05:02:07.082948');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('2df706f3-76ee-4ff8-8bbb-7772595eac69', 'odun@aiq.com', NULL, '$2a$06$C1FOlyO/5jw3.4y1Ni5BpuYrb8rQg99ugQPjXJECDKtuIqQrtHKGa', 'Babanla', 'Odunlami', 'Babanla Odunlami', 'https://i.imgur.com/0GY9tnz.jpeg', '1992-08-10', '789 Pine St, Villageville', 'Emily Brown', NULL, '2019-02-18', 'cc2f6b00-e86f-47a0-b372-bbccaa990003', NULL, NULL, 2800.00, '{"bank_name": "Bank LMN", "account_number": "1112233445"}', '2023-05-15', 'https://i.imgur.com/EF3456789E.jpeg', 'https://i.imgur.com/C3456789.jpeg', 'active', '2025-04-23 05:02:07.086274', '2025-04-23 05:02:07.086274');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('2a5abd5e-ca8b-42e0-917a-a5d00eda896f', 'kingsley@aiq.com', NULL, '$2a$06$oiimV8YR.wlZfE08l2qcWu4ahxQVmt62gKr.sxcFxClM8uH1d.0yO', 'Kingsley', 'Jobojobo', 'Kingsley Jobojobo', 'https://i.imgur.com/0GY9tnz.jpeg', '1988-12-05', '321 Elm St, Hamletville', 'Anna Doe', NULL, '2021-07-21', NULL, NULL, NULL, 4200.00, '{"bank_name": "Bank DEF", "account_number": "9988776655"}', NULL, 'https://i.imgur.com/GH4567890F.jpeg', 'https://i.imgur.com/D4567890.jpeg', 'active', '2025-04-23 05:02:07.089521', '2025-04-23 05:02:07.089521');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('20c27317-e747-4f86-9d78-b77e2be931fe', 'jane@example.com', NULL, '$2a$06$EhuxYKgfUNGjKb/tb/TEa.KKOfrz5ms/Y7fajY7BVWFH67rTKIHJi', 'Jane', 'Smith', 'Jane Smith', 'https://i.imgur.com/0GY9tnz.jpeg', '1994-03-25', '654 Maple St, Citytown', 'Linda Smith', NULL, '2022-06-11', NULL, NULL, NULL, 3100.00, '{"bank_name": "Bank GHI", "account_number": "6677889900"}', '2023-08-01', 'https://i.imgur.com/IJ5678901G.jpeg', 'https://i.imgur.com/E5678901.jpeg', 'active', '2025-04-23 05:02:07.092913', '2025-04-23 05:02:07.092913');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('2f9980b9-c1d4-4ca3-9731-45fcdf3c23cd', 'mary@example.com', NULL, '$2a$06$DMlQxKhZ4dSlhZFHfm/gJ.SLLEsCdyxZODaKvBAEE8wQpSW6BylzS', 'Mary', 'Jones', 'Mary Jones', 'https://i.imgur.com/0GY9tnz.jpeg', '1991-11-30', '987 Cedar St, Smallville', 'John Jones', NULL, '2017-04-09', 'cc2f6b00-e86f-47a0-b372-bbccaa990004', NULL, NULL, 3600.00, '{"bank_name": "Bank JKL", "account_number": "1122334455"}', NULL, 'https://i.imgur.com/KL6789012H.jpeg', 'https://i.imgur.com/F6789012.jpeg', 'active', '2025-04-23 05:02:07.096326', '2025-04-23 05:02:07.096327');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('260fe3ef-da1b-46d9-a333-1c2f40b30378', 'peter@example.com', NULL, '$2a$06$UX6RB0hn40x3paArcknjaekQuR56uWhzm891hcNBiV0Tvc.ivBnYq', 'Peter', 'Brown', 'Peter Brown', 'https://i.imgur.com/0GY9tnz.jpeg', '1989-07-15', '123 Birch St, Greenfield', 'Samantha Brown', NULL, '2016-03-20', NULL, NULL, NULL, 3900.00, '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 'active', '2025-04-23 05:02:07.099868', '2025-04-23 05:02:07.099868');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('25a7e877-51e4-47fd-8bd2-52c01892d05b', 'chef@admin.com', NULL, '$2a$06$hRxrDq/XIXsoBMvc6BkezuQyRDJE03eWD/MWM3Ll4QvbpRaeA8q3.', 'Tk', 'Chef', 'Tk Chef', 'https://i.imgur.com/0GY9tnz.jpeg', '1989-07-15', '123 Birch St, Greenfield', 'Samantha Brown', NULL, '2016-03-20', 'cc2f6b00-e86f-47a0-b372-bbccaa990003', NULL, NULL, 3900.00, '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 'active', '2025-04-23 05:02:07.103218', '2025-04-23 05:02:07.103218');
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('294b2962-3e12-4675-a8d4-0fa2c92506c4', 'chef1@admin.com', NULL, '$2a$06$PE95rBk8mSmv.Qyis15BMe0Ip8IK6jlZQUcZU2Ml8K5SLALi5mJj2', 'Peter', 'Chef', 'Peter Chef', 'https://i.imgur.com/0GY9tnz.jpeg', '1989-07-15', '123 Birch St, Greenfield', 'Samantha Brown', NULL, '2016-03-20', 'cc2f6b00-e86f-47a0-b372-bbccaa990003', NULL, NULL, 3900.00, '{"bank_name": "Bank MNO", "account_number": "2233445566"}', '2023-02-28', 'https://i.imgur.com/MN7890123I.jpeg', 'https://i.imgur.com/G7890123.jpeg', 'active', '2025-04-23 05:02:07.106491', '2025-04-23 05:02:07.106491');

-- changeset liquibase:1745384592554-42
CREATE TABLE "public"."refresh_tokens" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID NOT NULL, "token" TEXT NOT NULL, "created_at" TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "refresh_tokens_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-43
CREATE TABLE "public"."user_leaves" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "leave_type" VARCHAR(50), "start_date" date DEFAULT CURRENT_DATE, "end_date" date, "days" INTEGER GENERATED ALWAYS AS CASE
    WHEN (end_date IS NOT NULL) THEN (end_date - start_date)
    ELSE NULL::integer
END STORED, "status" VARCHAR(50) DEFAULT 'booked', "notes" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "user_leaves_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-44
CREATE TABLE "public"."loans" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "lender_id" UUID, "lender_type" VARCHAR(50) NOT NULL, "amount" numeric(20, 2), "interest_rate" numeric(5, 2), "start_date" date, "end_date" date, "loan_type_id" UUID, "status" VARCHAR(50) DEFAULT 'pending', "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "loans_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-45
CREATE TABLE "public"."price_lists" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "item_category_id" UUID, "unit_id" UUID, "item_details" VARCHAR(100) NOT NULL, "tax_id" UUID, "tax" numeric(5, 2) DEFAULT 0, "unit_price" numeric(20, 2), "minimum_order" INTEGER, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "price_lists_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-46
CREATE TABLE "public"."vendors" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "salutation" VARCHAR(50), "first_name" VARCHAR(255), "last_name" VARCHAR(255), "company_name" VARCHAR(255), "display_name" VARCHAR(255) GENERATED ALWAYS AS CASE
    WHEN (company_name IS NOT NULL) THEN (company_name)::text
    ELSE (((first_name)::text || ' '::text) || (last_name)::text)
END STORED, "email" VARCHAR(255), "work_phone" VARCHAR(20), "mobile_phone" VARCHAR(20), "address" TEXT, "website" VARCHAR(255), "social_media" JSONB, "payment_term_id" UUID, "payment_term" VARCHAR(50), "currency_id" UUID, "category_id" UUID, "balance" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) GENERATED ALWAYS AS CASE
    WHEN (balance > (0)::numeric) THEN 'owing'::text
    ELSE 'active'::text
END STORED, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "vendors_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-47
CREATE TABLE "public"."vendor_transactions" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "vendor_id" UUID, "transaction_type" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_term_id" UUID, "payment_term" VARCHAR(50), "cash_account_id" UUID, "amount" numeric(20, 2), "reference_number" VARCHAR(50), "notes" TEXT, "invoice_sent" BOOLEAN DEFAULT FALSE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "vendor_transactions_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-48
CREATE TABLE "public"."customers" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "customer_type" VARCHAR(50), "salutation" VARCHAR(10), "first_name" VARCHAR(255), "last_name" VARCHAR(255), "display_name" VARCHAR(255) GENERATED ALWAYS AS CASE
    WHEN ((customer_type)::text = 'individual'::text) THEN ((((first_name)::text || ' '::text) || (last_name)::text))::character varying
    ELSE first_name
END STORED, "company_name" VARCHAR(255), "email" VARCHAR(255), "work_phone" VARCHAR(20), "mobile_phone" VARCHAR(20), "address" TEXT, "website" VARCHAR(255), "social_media" JSONB, "payment_term_id" UUID, "payment_term" VARCHAR(50), "currency_id" UUID, "balance" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) GENERATED ALWAYS AS CASE
    WHEN (balance > (0)::numeric) THEN 'owing'::text
    ELSE 'active'::text
END STORED, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "customers_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-49
CREATE TABLE "public"."customer_transactions" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "customer_id" UUID, "transaction_type" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_term_id" UUID, "payment_term" VARCHAR(50), "cash_account_id" UUID, "amount" numeric(20, 2), "reference_number" VARCHAR(50), "notes" TEXT, "invoice_sent" BOOLEAN DEFAULT FALSE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "customer_transactions_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-50
CREATE TABLE "public"."items" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "name" VARCHAR(255) NOT NULL, "description" TEXT, "sku" VARCHAR(100), "barcode" VARCHAR(255), "unit_id" UUID, "category_id" UUID, "price" numeric(20, 2), "opening_stock" INTEGER DEFAULT 0, "threshold_value" INTEGER DEFAULT 0, "availability" VARCHAR(50), "media" JSONB, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "items_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-51
CREATE TABLE "public"."item_stocks" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "item_id" UUID, "stock_code" VARCHAR(100) GENERATED ALWAYS AS ('STK-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "quantity" INTEGER DEFAULT 0 NOT NULL, "date_received" date DEFAULT CURRENT_DATE, "expiry_date" date, "branch_id" UUID, "branch" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_stocks_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-52
CREATE TABLE "public"."item_stock_adjustments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "stock_id" UUID, "manager_id" UUID, "manager" VARCHAR(255), "source_type" VARCHAR(10) NOT NULL, "source_id" UUID, "source_department_id" UUID, "source_department" VARCHAR(100), "quantity" INTEGER NOT NULL, "adjustment_type" VARCHAR(50), "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_stock_adjustments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-53
CREATE TABLE "public"."purchase_orders" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "vendor_id" UUID, "vendor" VARCHAR(255), "branch_id" UUID, "branch" VARCHAR(255), "purchase_order_number" VARCHAR(50) GENERATED ALWAYS AS ('PO-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "reference_number" VARCHAR(50) GENERATED ALWAYS AS ('REF'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "invoice_number" VARCHAR(50) GENERATED ALWAYS AS ('INV-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "delivery_date" date, "payment_term_id" UUID, "payment_term" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_due_date" date, "subject" TEXT, "notes" TEXT, "terms_and_conditions" TEXT, "discount" numeric(20, 2) DEFAULT 0, "shipping_charge" numeric(20, 2) DEFAULT 0, "total" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) DEFAULT 'issued', "processed_by" UUID, "manager" VARCHAR(255), "date_received" date DEFAULT CURRENT_DATE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "purchase_orders_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-54
CREATE TABLE "public"."purchase_order_items" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "purchase_order_id" UUID, "item_id" UUID, "item" VARCHAR(255), "quantity" INTEGER NOT NULL, "price" numeric(20, 2), "tax_id" UUID, "tax" numeric(5, 2) DEFAULT 0, "total" numeric(20, 2) DEFAULT 0 NOT NULL, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "purchase_order_items_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-55
CREATE TABLE "public"."sales_orders" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "order_type" VARCHAR(50), "order_title" VARCHAR(255), "order_id" VARCHAR(255) GENERATED ALWAYS AS CASE
    WHEN ((order_type)::text = 'order'::text) THEN ('SLO-'::text || lpad((order_sequence)::text, 3, '0'::text))
    ELSE ('SLS-'::text || lpad((order_sequence)::text, 3, '0'::text))
END STORED, "invoice_number" VARCHAR(50) GENERATED ALWAYS AS ('INV-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "reference_number" VARCHAR(50) GENERATED ALWAYS AS ('REF'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "customer_id" UUID, "customer" VARCHAR(255), "payment_term_id" UUID, "payment_term" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "delivery_option" VARCHAR(50), "delivery_date" date, "delivery_time" time(6) WITHOUT TIME ZONE DEFAULT CURRENT_TIME + '00:30:00'::interval, "delivery_address" TEXT, "additional_note" TEXT, "customer_note" TEXT, "discount" numeric(20, 2) DEFAULT 0, "discount_id" UUID, "delivery_charge_id" UUID, "delivery_charge" numeric(20, 2) DEFAULT 0, "total_boxes" INTEGER DEFAULT 1, "total" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) DEFAULT 'pending', "payment_status" VARCHAR(50) DEFAULT 'unpaid', "sent_to_kitchen" BOOLEAN DEFAULT FALSE, "processed_by" UUID, "manager" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "sales_orders_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-56
CREATE TABLE "public"."sales_order_items" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "sales_order_id" UUID, "item_id" UUID, "item_name" VARCHAR(255), "platter_items" TEXT, "quantity" INTEGER NOT NULL, "price" numeric(20, 2) NOT NULL, "tax_id" UUID, "tax" numeric(5, 2) DEFAULT 0 NOT NULL, "total" numeric(20, 2) DEFAULT 0 NOT NULL, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "sales_order_items_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-57
CREATE TABLE "public"."audit_logs" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "manager" VARCHAR(255), "entity_id" UUID, "entity_type" VARCHAR(50), "action" VARCHAR(50), "entity_data" JSONB, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "audit_logs_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-58
CREATE TABLE "public"."order_ratings" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_id" UUID, "name" VARCHAR(255), "rating" INTEGER NOT NULL, "review" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "order_ratings_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-59
INSERT INTO "public"."order_ratings" ("id", "order_id", "name", "rating", "review", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440131', NULL, 'AdeyemiFarms', 4, 'Good service', '2025-04-23 05:02:07.12309', '2025-04-23 05:02:07.12309');
INSERT INTO "public"."order_ratings" ("id", "order_id", "name", "rating", "review", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440132', NULL, 'AdeyemiFarms', 3, 'Late delivery', '2025-04-23 05:02:07.123199', '2025-04-23 05:02:07.123199');
INSERT INTO "public"."order_ratings" ("id", "order_id", "name", "rating", "review", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440133', NULL, 'AdeyemiFarms', 5, 'Excellent service', '2025-04-23 05:02:07.123204', '2025-04-23 05:02:07.123204');
INSERT INTO "public"."order_ratings" ("id", "order_id", "name", "rating", "review", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440134', NULL, 'AdeyemiFarms', 4, 'Good service', '2025-04-23 05:02:07.123209', '2025-04-23 05:02:07.123209');

-- changeset liquibase:1745384592554-60
CREATE TABLE "public"."chef_assignments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "chef_id" UUID, "order_id" UUID, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "chef_assignments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-61
CREATE TABLE "public"."driver_assignments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "driver_id" UUID, "order_id" UUID, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "driver_assignments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-62
CREATE TABLE "public"."expenses_categories" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "expenses_categories_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-63
INSERT INTO "public"."expenses_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440135', 'Travel', 'Expenses related to business travel', '2025-04-23 05:02:07.124372', '2025-04-23 05:02:07.124372');
INSERT INTO "public"."expenses_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440136', 'Office Supplies', 'Expenses for office supplies and stationery', '2025-04-23 05:02:07.124531', '2025-04-23 05:02:07.124531');
INSERT INTO "public"."expenses_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440137', 'Utilities', 'Payments for electricity, water, and internet bills', '2025-04-23 05:02:07.124538', '2025-04-23 05:02:07.124538');
INSERT INTO "public"."expenses_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440138', 'Meals', 'Expenses for meals and entertainment', '2025-04-23 05:02:07.124541', '2025-04-23 05:02:07.124541');
INSERT INTO "public"."expenses_categories" ("id", "name", "description", "created_at", "updated_at") VALUES ('150e8400-e29b-41d4-a716-446655440139', 'Maintenance', 'Expenses related to equipment maintenance', '2025-04-23 05:02:07.124543', '2025-04-23 05:02:07.124543');

-- changeset liquibase:1745384592554-64
CREATE TABLE "public"."expenses" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "expense_title" VARCHAR(255), "expense_category" UUID, "expense_id" VARCHAR(255) GENERATED ALWAYS AS ('EXP-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_term_id" UUID, "payment_term" VARCHAR(50), "department_id" UUID, "department" VARCHAR(255), "amount" numeric(20, 2), "bank_charges" numeric(20, 2) DEFAULT 0, "date_of_expense" date DEFAULT CURRENT_DATE, "notes" TEXT, "status" VARCHAR(50) DEFAULT 'pending', "processed_by" UUID, "manager" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "expenses_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-65
CREATE TABLE "public"."comments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "handler" VARCHAR(255), "parent_id" UUID, "entity_id" UUID NOT NULL, "entity_type" VARCHAR(50) NOT NULL, "comment" TEXT NOT NULL, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "comments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-66
CREATE TABLE "public"."notifications" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "handler" VARCHAR(255), "entity_id" UUID, "entity_type" VARCHAR(50), "title" VARCHAR(255) NOT NULL, "body" TEXT NOT NULL, "is_read" BOOLEAN DEFAULT FALSE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "notifications_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745384592554-67
ALTER TABLE "public"."roles" ADD CONSTRAINT "roles_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-68
ALTER TABLE "public"."permissions" ADD CONSTRAINT "permissions_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-69
ALTER TABLE "public"."salutations" ADD CONSTRAINT "salutations_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-70
ALTER TABLE "public"."delivery_charges" ADD CONSTRAINT "delivery_charges_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-71
ALTER TABLE "public"."discounts" ADD CONSTRAINT "discounts_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-72
ALTER TABLE "public"."currencies" ADD CONSTRAINT "currencies_code_key" UNIQUE ("code");

-- changeset liquibase:1745384592554-73
ALTER TABLE "public"."currencies" ADD CONSTRAINT "currencies_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-74
ALTER TABLE "public"."currencies" ADD CONSTRAINT "currencies_symbol_key" UNIQUE ("symbol");

-- changeset liquibase:1745384592554-75
ALTER TABLE "public"."base_pay_types" ADD CONSTRAINT "base_pay_types_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-76
ALTER TABLE "public"."work_leave_qualifications" ADD CONSTRAINT "work_leave_qualifications_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-77
ALTER TABLE "public"."branches" ADD CONSTRAINT "branches_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-78
ALTER TABLE "public"."item_categories" ADD CONSTRAINT "item_categories_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-79
ALTER TABLE "public"."units" ADD CONSTRAINT "units_abbreviation_key" UNIQUE ("abbreviation");

-- changeset liquibase:1745384592554-80
ALTER TABLE "public"."units" ADD CONSTRAINT "units_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-81
ALTER TABLE "public"."no_of_working_days" ADD CONSTRAINT "no_of_working_days_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-82
ALTER TABLE "public"."vendor_categories" ADD CONSTRAINT "vendor_categories_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-83
ALTER TABLE "public"."payment_methods" ADD CONSTRAINT "payment_methods_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-84
ALTER TABLE "public"."loan_types" ADD CONSTRAINT "loan_types_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-85
ALTER TABLE "public"."payment_terms" ADD CONSTRAINT "payment_terms_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-86
ALTER TABLE "public"."taxes" ADD CONSTRAINT "taxes_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-87
ALTER TABLE "public"."cash_accounts" ADD CONSTRAINT "cash_accounts_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-88
ALTER TABLE "public"."departments" ADD CONSTRAINT "departments_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-89
ALTER TABLE "public"."users" ADD CONSTRAINT "users_email_key" UNIQUE ("email");

-- changeset liquibase:1745384592554-90
ALTER TABLE "public"."users" ADD CONSTRAINT "users_username_key" UNIQUE ("username");

-- changeset liquibase:1745384592554-91
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_item_details_key" UNIQUE ("item_details");

-- changeset liquibase:1745384592554-92
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-93
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-94
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745384592554-95
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-96
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745384592554-97
ALTER TABLE "public"."items" ADD CONSTRAINT "items_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-98
ALTER TABLE "public"."items" ADD CONSTRAINT "items_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-99
ALTER TABLE "public"."item_stocks" ADD CONSTRAINT "item_stocks_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-100
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_invoice_number_key" UNIQUE ("invoice_number");

-- changeset liquibase:1745384592554-101
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-102
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_purchase_order_number_key" UNIQUE ("purchase_order_number");

-- changeset liquibase:1745384592554-103
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745384592554-104
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_invoice_number_key" UNIQUE ("invoice_number");

-- changeset liquibase:1745384592554-105
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_order_id_key" UNIQUE ("order_id");

-- changeset liquibase:1745384592554-106
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-107
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745384592554-108
ALTER TABLE "public"."chef_assignments" ADD CONSTRAINT "unique_chef_order" UNIQUE ("chef_id", "order_id");

-- changeset liquibase:1745384592554-109
ALTER TABLE "public"."driver_assignments" ADD CONSTRAINT "unique_driver_order" UNIQUE ("driver_id", "order_id");

-- changeset liquibase:1745384592554-110
ALTER TABLE "public"."expenses_categories" ADD CONSTRAINT "expenses_categories_name_key" UNIQUE ("name");

-- changeset liquibase:1745384592554-111
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745384592554-112
CREATE TABLE "public"."item_stock_branches" ("stock_id" UUID NOT NULL, "branch_id" UUID NOT NULL, CONSTRAINT "item_stock_branches_pkey" PRIMARY KEY ("stock_id", "branch_id"));

-- changeset liquibase:1745384592554-113
CREATE TABLE "public"."item_stock_departments" ("stock_id" UUID NOT NULL, "department_id" UUID NOT NULL, CONSTRAINT "item_stock_departments_pkey" PRIMARY KEY ("stock_id", "department_id"));

-- changeset liquibase:1745384592554-114
CREATE TABLE "public"."item_stock_manufacturers" ("stock_id" UUID NOT NULL, "manufacturer_id" UUID NOT NULL, CONSTRAINT "item_stock_manufacturers_pkey" PRIMARY KEY ("stock_id", "manufacturer_id"));

-- changeset liquibase:1745384592554-115
CREATE TABLE "public"."item_stock_vendors" ("stock_id" UUID NOT NULL, "vendor_id" UUID NOT NULL, CONSTRAINT "item_stock_vendors_pkey" PRIMARY KEY ("stock_id", "vendor_id"));

-- changeset liquibase:1745384592554-116
CREATE TABLE "public"."user_permissions" ("user_id" UUID NOT NULL, "permission_id" UUID NOT NULL, CONSTRAINT "user_permissions_pkey" PRIMARY KEY ("user_id", "permission_id"));

-- changeset liquibase:1745384592554-117
ALTER TABLE "public"."audit_logs" ADD CONSTRAINT "audit_logs_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-118
ALTER TABLE "public"."chef_assignments" ADD CONSTRAINT "chef_assignments_chef_id_fkey" FOREIGN KEY ("chef_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-119
ALTER TABLE "public"."chef_assignments" ADD CONSTRAINT "chef_assignments_order_id_fkey" FOREIGN KEY ("order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-120
ALTER TABLE "public"."comments" ADD CONSTRAINT "comments_parent_id_fkey" FOREIGN KEY ("parent_id") REFERENCES "public"."comments" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-121
ALTER TABLE "public"."comments" ADD CONSTRAINT "comments_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-122
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_cash_account_id_fkey" FOREIGN KEY ("cash_account_id") REFERENCES "public"."cash_accounts" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-123
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "public"."customers" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-124
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-125
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-126
ALTER TABLE "public"."customers" ADD CONSTRAINT "customers_currency_id_fkey" FOREIGN KEY ("currency_id") REFERENCES "public"."currencies" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-127
ALTER TABLE "public"."customers" ADD CONSTRAINT "customers_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-128
ALTER TABLE "public"."departments" ADD CONSTRAINT "departments_base_type_id_fkey" FOREIGN KEY ("base_type_id") REFERENCES "public"."base_pay_types" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-129
ALTER TABLE "public"."departments" ADD CONSTRAINT "departments_work_leave_qualification_fkey" FOREIGN KEY ("work_leave_qualification") REFERENCES "public"."work_leave_qualifications" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-130
ALTER TABLE "public"."driver_assignments" ADD CONSTRAINT "driver_assignments_driver_id_fkey" FOREIGN KEY ("driver_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-131
ALTER TABLE "public"."driver_assignments" ADD CONSTRAINT "driver_assignments_order_id_fkey" FOREIGN KEY ("order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-132
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_department_id_fkey" FOREIGN KEY ("department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-133
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_expense_category_fkey" FOREIGN KEY ("expense_category") REFERENCES "public"."expenses_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-134
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-135
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-136
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_processed_by_fkey" FOREIGN KEY ("processed_by") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-137
ALTER TABLE "public"."item_stock_adjustments" ADD CONSTRAINT "item_stock_adjustments_manager_id_fkey" FOREIGN KEY ("manager_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-138
ALTER TABLE "public"."item_stock_adjustments" ADD CONSTRAINT "item_stock_adjustments_source_department_id_fkey" FOREIGN KEY ("source_department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-139
ALTER TABLE "public"."item_stock_adjustments" ADD CONSTRAINT "item_stock_adjustments_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-140
ALTER TABLE "public"."item_stock_branches" ADD CONSTRAINT "item_stock_branches_branch_id_fkey" FOREIGN KEY ("branch_id") REFERENCES "public"."branches" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-141
ALTER TABLE "public"."item_stock_branches" ADD CONSTRAINT "item_stock_branches_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-142
ALTER TABLE "public"."item_stock_departments" ADD CONSTRAINT "item_stock_departments_department_id_fkey" FOREIGN KEY ("department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-143
ALTER TABLE "public"."item_stock_departments" ADD CONSTRAINT "item_stock_departments_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-144
ALTER TABLE "public"."item_stock_manufacturers" ADD CONSTRAINT "item_stock_manufacturers_manufacturer_id_fkey" FOREIGN KEY ("manufacturer_id") REFERENCES "public"."item_manufacturers" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-145
ALTER TABLE "public"."item_stock_manufacturers" ADD CONSTRAINT "item_stock_manufacturers_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-146
ALTER TABLE "public"."item_stock_vendors" ADD CONSTRAINT "item_stock_vendors_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-147
ALTER TABLE "public"."item_stock_vendors" ADD CONSTRAINT "item_stock_vendors_vendor_id_fkey" FOREIGN KEY ("vendor_id") REFERENCES "public"."vendors" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-148
ALTER TABLE "public"."item_stocks" ADD CONSTRAINT "item_stocks_branch_id_fkey" FOREIGN KEY ("branch_id") REFERENCES "public"."branches" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-149
ALTER TABLE "public"."item_stocks" ADD CONSTRAINT "item_stocks_item_id_fkey" FOREIGN KEY ("item_id") REFERENCES "public"."items" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-150
ALTER TABLE "public"."items" ADD CONSTRAINT "items_category_id_fkey" FOREIGN KEY ("category_id") REFERENCES "public"."item_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-151
ALTER TABLE "public"."items" ADD CONSTRAINT "items_unit_id_fkey" FOREIGN KEY ("unit_id") REFERENCES "public"."units" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-152
ALTER TABLE "public"."loans" ADD CONSTRAINT "loans_lender_id_fkey" FOREIGN KEY ("lender_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-153
ALTER TABLE "public"."loans" ADD CONSTRAINT "loans_loan_type_id_fkey" FOREIGN KEY ("loan_type_id") REFERENCES "public"."loan_types" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-154
ALTER TABLE "public"."notifications" ADD CONSTRAINT "notifications_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-155
ALTER TABLE "public"."order_ratings" ADD CONSTRAINT "order_ratings_order_id_fkey" FOREIGN KEY ("order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-156
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_item_category_id_fkey" FOREIGN KEY ("item_category_id") REFERENCES "public"."item_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-157
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_tax_id_fkey" FOREIGN KEY ("tax_id") REFERENCES "public"."taxes" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-158
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_unit_id_fkey" FOREIGN KEY ("unit_id") REFERENCES "public"."units" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-159
ALTER TABLE "public"."purchase_order_items" ADD CONSTRAINT "purchase_order_items_item_id_fkey" FOREIGN KEY ("item_id") REFERENCES "public"."items" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-160
ALTER TABLE "public"."purchase_order_items" ADD CONSTRAINT "purchase_order_items_purchase_order_id_fkey" FOREIGN KEY ("purchase_order_id") REFERENCES "public"."purchase_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-161
ALTER TABLE "public"."purchase_order_items" ADD CONSTRAINT "purchase_order_items_tax_id_fkey" FOREIGN KEY ("tax_id") REFERENCES "public"."taxes" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-162
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_branch_id_fkey" FOREIGN KEY ("branch_id") REFERENCES "public"."branches" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-163
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-164
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-165
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_processed_by_fkey" FOREIGN KEY ("processed_by") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-166
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_vendor_id_fkey" FOREIGN KEY ("vendor_id") REFERENCES "public"."vendors" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-167
ALTER TABLE "public"."refresh_tokens" ADD CONSTRAINT "refresh_tokens_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-168
ALTER TABLE "public"."sales_order_items" ADD CONSTRAINT "sales_order_items_item_id_fkey" FOREIGN KEY ("item_id") REFERENCES "public"."price_lists" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-169
ALTER TABLE "public"."sales_order_items" ADD CONSTRAINT "sales_order_items_sales_order_id_fkey" FOREIGN KEY ("sales_order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-170
ALTER TABLE "public"."sales_order_items" ADD CONSTRAINT "sales_order_items_tax_id_fkey" FOREIGN KEY ("tax_id") REFERENCES "public"."taxes" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-171
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "public"."customers" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-172
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_delivery_charge_id_fkey" FOREIGN KEY ("delivery_charge_id") REFERENCES "public"."delivery_charges" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-173
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_discount_id_fkey" FOREIGN KEY ("discount_id") REFERENCES "public"."discounts" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-174
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-175
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-176
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_processed_by_fkey" FOREIGN KEY ("processed_by") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-177
ALTER TABLE "public"."user_leaves" ADD CONSTRAINT "user_leaves_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-178
ALTER TABLE "public"."user_permissions" ADD CONSTRAINT "user_permissions_permission_id_fkey" FOREIGN KEY ("permission_id") REFERENCES "public"."permissions" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-179
ALTER TABLE "public"."user_permissions" ADD CONSTRAINT "user_permissions_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-180
ALTER TABLE "public"."users" ADD CONSTRAINT "users_department_id_fkey" FOREIGN KEY ("department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-181
ALTER TABLE "public"."users" ADD CONSTRAINT "users_no_of_working_days_id_fkey" FOREIGN KEY ("no_of_working_days_id") REFERENCES "public"."no_of_working_days" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-182
ALTER TABLE "public"."users" ADD CONSTRAINT "users_role_id_fkey" FOREIGN KEY ("role_id") REFERENCES "public"."roles" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-183
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_cash_account_id_fkey" FOREIGN KEY ("cash_account_id") REFERENCES "public"."cash_accounts" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-184
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-185
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745384592554-186
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_vendor_id_fkey" FOREIGN KEY ("vendor_id") REFERENCES "public"."vendors" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745384592554-187
ALTER TABLE "public"."vendors" ADD CONSTRAINT "vendors_category_id_fkey" FOREIGN KEY ("category_id") REFERENCES "public"."vendor_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-188
ALTER TABLE "public"."vendors" ADD CONSTRAINT "vendors_currency_id_fkey" FOREIGN KEY ("currency_id") REFERENCES "public"."currencies" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745384592554-189
ALTER TABLE "public"."vendors" ADD CONSTRAINT "vendors_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

