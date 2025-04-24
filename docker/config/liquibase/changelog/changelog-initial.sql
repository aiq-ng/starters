-- liquibase formatted sql

-- changeset liquibase:1745470300697-1
CREATE TABLE "public"."roles" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, CONSTRAINT "roles_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-2
CREATE TABLE "public"."permissions" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, CONSTRAINT "permissions_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-3
CREATE TABLE "public"."salutations" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, CONSTRAINT "salutations_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-4
CREATE TABLE "public"."delivery_charges" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50), "amount" numeric(20, 2) NOT NULL, "description" TEXT, CONSTRAINT "delivery_charges_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-5
CREATE TABLE "public"."discounts" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50), "discount_type" VARCHAR(20), "value" numeric(20, 2) NOT NULL, "description" TEXT, CONSTRAINT "discounts_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-6
CREATE TABLE "public"."currencies" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "symbol" VARCHAR(10), "code" VARCHAR(10), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "currencies_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-7
CREATE TABLE "public"."base_pay_types" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, CONSTRAINT "base_pay_types_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-8
CREATE TABLE "public"."work_leave_qualifications" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, CONSTRAINT "work_leave_qualifications_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-9
CREATE TABLE "public"."branches" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "branches_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-10
CREATE TABLE "public"."item_categories" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_categories_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-11
CREATE TABLE "public"."units" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "abbreviation" VARCHAR(10), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "units_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-12
CREATE TABLE "public"."no_of_working_days" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "no_of_working_days_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-13
CREATE TABLE "public"."vendor_categories" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "vendor_categories_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-14
CREATE TABLE "public"."payment_methods" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "payment_methods_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-15
CREATE TABLE "public"."loan_types" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, CONSTRAINT "loan_types_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-16
CREATE TABLE "public"."payment_terms" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "payment_terms_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-17
CREATE TABLE "public"."taxes" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(50) NOT NULL, "rate" numeric(5, 2) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "taxes_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-18
CREATE TABLE "public"."item_manufacturers" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(255) NOT NULL, "website" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_manufacturers_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-19
CREATE TABLE "public"."cash_accounts" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "balance" numeric(20, 2) DEFAULT 0, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "cash_accounts_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-20
CREATE TABLE "public"."departments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100), "salary_type" VARCHAR(50), "base_type_id" UUID, "base_rate" numeric(20, 2), "base_salary" numeric(20, 2), "work_leave_qualification" UUID, "work_leave_period" VARCHAR(50), "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "departments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-21
CREATE TABLE "public"."users" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "email" VARCHAR(255), "username" VARCHAR(100), "password" VARCHAR(255), "firstname" VARCHAR(100), "lastname" VARCHAR(100), "name" VARCHAR(255) GENERATED ALWAYS AS (((firstname)::text || ' '::text) || (lastname)::text) STORED, "avatar_url" VARCHAR(255), "date_of_birth" date, "address" TEXT, "next_of_kin" VARCHAR(100), "emergency_contact" TEXT, "date_of_employment" date, "department_id" UUID, "role_id" UUID, "no_of_working_days_id" UUID, "salary" numeric(20, 2), "bank_details" JSONB, "leave" date, "nin" VARCHAR(255), "passport" VARCHAR(255), "status" VARCHAR(50) DEFAULT 'active', "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "users_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-22
INSERT INTO "public"."users" ("id", "email", "username", "password", "firstname", "lastname", "name", "avatar_url", "date_of_birth", "address", "next_of_kin", "emergency_contact", "date_of_employment", "department_id", "role_id", "no_of_working_days_id", "salary", "bank_details", "leave", "nin", "passport", "status", "created_at", "updated_at") VALUES ('25d36e74-fbb4-45ec-8ec2-4f8abc7d79ca', 'starters@admin.com', NULL, '$2a$06$gLoWPLCs.XvaNNiQJOl.YeKi5UyZwNjSnrkchB7B.LPgwotgOy/S6', 'Starters', 'Admin', 'Starters Admin', 'https://i.imgur.com/0GY9tnz.jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://i.imgur.com/AB1234567C.jpeg', 'https://i.imgur.com/A1234567.jpeg', 'active', '2025-04-24 04:50:29.528417', '2025-04-24 04:50:29.528417');

-- changeset liquibase:1745470300697-23
CREATE TABLE "public"."refresh_tokens" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID NOT NULL, "token" TEXT NOT NULL, "created_at" TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "refresh_tokens_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-24
CREATE TABLE "public"."user_leaves" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "leave_type" VARCHAR(50), "start_date" date DEFAULT CURRENT_DATE, "end_date" date, "days" INTEGER GENERATED ALWAYS AS CASE
    WHEN (end_date IS NOT NULL) THEN (end_date - start_date)
    ELSE NULL::integer
END STORED, "status" VARCHAR(50) DEFAULT 'booked', "notes" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "user_leaves_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-25
CREATE TABLE "public"."loans" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "lender_id" UUID, "lender_type" VARCHAR(50) NOT NULL, "amount" numeric(20, 2), "interest_rate" numeric(5, 2), "start_date" date, "end_date" date, "loan_type_id" UUID, "status" VARCHAR(50) DEFAULT 'pending', "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "loans_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-26
CREATE TABLE "public"."price_lists" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "item_category_id" UUID, "unit_id" UUID, "item_details" VARCHAR(100) NOT NULL, "tax_id" UUID, "tax" numeric(5, 2) DEFAULT 0, "unit_price" numeric(20, 2), "minimum_order" INTEGER, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "price_lists_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-27
CREATE TABLE "public"."vendors" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "salutation" VARCHAR(50), "first_name" VARCHAR(255), "last_name" VARCHAR(255), "company_name" VARCHAR(255), "display_name" VARCHAR(255) GENERATED ALWAYS AS CASE
    WHEN (company_name IS NOT NULL) THEN (company_name)::text
    ELSE (((first_name)::text || ' '::text) || (last_name)::text)
END STORED, "email" VARCHAR(255), "work_phone" VARCHAR(20), "mobile_phone" VARCHAR(20), "address" TEXT, "website" VARCHAR(255), "social_media" JSONB, "payment_term_id" UUID, "payment_term" VARCHAR(50), "currency_id" UUID, "category_id" UUID, "balance" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) GENERATED ALWAYS AS CASE
    WHEN (balance > (0)::numeric) THEN 'owing'::text
    ELSE 'active'::text
END STORED, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "vendors_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-28
CREATE TABLE "public"."vendor_transactions" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "vendor_id" UUID, "transaction_type" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_term_id" UUID, "payment_term" VARCHAR(50), "cash_account_id" UUID, "amount" numeric(20, 2), "reference_number" VARCHAR(50), "notes" TEXT, "invoice_sent" BOOLEAN DEFAULT FALSE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "vendor_transactions_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-29
CREATE TABLE "public"."customers" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "customer_type" VARCHAR(50), "salutation" VARCHAR(10), "first_name" VARCHAR(255), "last_name" VARCHAR(255), "display_name" VARCHAR(255) GENERATED ALWAYS AS CASE
    WHEN ((customer_type)::text = 'individual'::text) THEN ((((first_name)::text || ' '::text) || (last_name)::text))::character varying
    ELSE first_name
END STORED, "company_name" VARCHAR(255), "email" VARCHAR(255), "work_phone" VARCHAR(20), "mobile_phone" VARCHAR(20), "address" TEXT, "website" VARCHAR(255), "social_media" JSONB, "payment_term_id" UUID, "payment_term" VARCHAR(50), "currency_id" UUID, "balance" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) GENERATED ALWAYS AS CASE
    WHEN (balance > (0)::numeric) THEN 'owing'::text
    ELSE 'active'::text
END STORED, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "customers_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-30
CREATE TABLE "public"."customer_transactions" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "customer_id" UUID, "transaction_type" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_term_id" UUID, "payment_term" VARCHAR(50), "cash_account_id" UUID, "amount" numeric(20, 2), "reference_number" VARCHAR(50), "notes" TEXT, "invoice_sent" BOOLEAN DEFAULT FALSE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "customer_transactions_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-31
CREATE TABLE "public"."items" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "name" VARCHAR(255) NOT NULL, "description" TEXT, "sku" VARCHAR(100), "barcode" VARCHAR(255), "unit_id" UUID, "category_id" UUID, "price" numeric(20, 2), "opening_stock" INTEGER DEFAULT 0, "threshold_value" INTEGER DEFAULT 0, "availability" VARCHAR(50), "media" JSONB, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "items_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-32
CREATE TABLE "public"."item_stocks" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "item_id" UUID, "stock_code" VARCHAR(100) GENERATED ALWAYS AS ('STK-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "quantity" INTEGER DEFAULT 0 NOT NULL, "date_received" date DEFAULT CURRENT_DATE, "expiry_date" date, "branch_id" UUID, "branch" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_stocks_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-33
CREATE TABLE "public"."item_stock_adjustments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "stock_id" UUID, "manager_id" UUID, "manager" VARCHAR(255), "source_type" VARCHAR(10) NOT NULL, "source_id" UUID, "source_department_id" UUID, "source_department" VARCHAR(100), "quantity" INTEGER NOT NULL, "adjustment_type" VARCHAR(50), "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "item_stock_adjustments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-34
CREATE TABLE "public"."purchase_orders" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "vendor_id" UUID, "vendor" VARCHAR(255), "branch_id" UUID, "branch" VARCHAR(255), "purchase_order_number" VARCHAR(50) GENERATED ALWAYS AS ('PO-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "reference_number" VARCHAR(50) GENERATED ALWAYS AS ('REF'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "invoice_number" VARCHAR(50) GENERATED ALWAYS AS ('INV-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "delivery_date" date, "payment_term_id" UUID, "payment_term" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_due_date" date, "subject" TEXT, "notes" TEXT, "terms_and_conditions" TEXT, "discount" numeric(20, 2) DEFAULT 0, "shipping_charge" numeric(20, 2) DEFAULT 0, "total" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) DEFAULT 'issued', "processed_by" UUID, "manager" VARCHAR(255), "date_received" date DEFAULT CURRENT_DATE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "purchase_orders_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-35
CREATE TABLE "public"."purchase_order_items" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "purchase_order_id" UUID, "item_id" UUID, "item" VARCHAR(255), "quantity" INTEGER NOT NULL, "price" numeric(20, 2), "tax_id" UUID, "tax" numeric(5, 2) DEFAULT 0, "total" numeric(20, 2) DEFAULT 0 NOT NULL, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "purchase_order_items_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-36
CREATE TABLE "public"."sales_orders" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "order_type" VARCHAR(50), "order_title" VARCHAR(255), "order_id" VARCHAR(255) GENERATED ALWAYS AS CASE
    WHEN ((order_type)::text = 'order'::text) THEN ('SLO-'::text || lpad((order_sequence)::text, 3, '0'::text))
    ELSE ('SLS-'::text || lpad((order_sequence)::text, 3, '0'::text))
END STORED, "invoice_number" VARCHAR(50) GENERATED ALWAYS AS ('INV-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "reference_number" VARCHAR(50) GENERATED ALWAYS AS ('REF'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "customer_id" UUID, "customer" VARCHAR(255), "payment_term_id" UUID, "payment_term" VARCHAR(50), "payment_method_id" UUID, "payment_method" VARCHAR(50), "delivery_option" VARCHAR(50), "delivery_date" date, "delivery_time" time(6) WITHOUT TIME ZONE DEFAULT CURRENT_TIME + '00:30:00'::interval, "delivery_address" TEXT, "additional_note" TEXT, "customer_note" TEXT, "discount" numeric(20, 2) DEFAULT 0, "discount_id" UUID, "delivery_charge_id" UUID, "delivery_charge" numeric(20, 2) DEFAULT 0, "total_boxes" INTEGER DEFAULT 1, "total" numeric(20, 2) DEFAULT 0, "status" VARCHAR(50) DEFAULT 'pending', "payment_status" VARCHAR(50) DEFAULT 'unpaid', "sent_to_kitchen" BOOLEAN DEFAULT FALSE, "processed_by" UUID, "manager" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "sales_orders_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-37
CREATE TABLE "public"."sales_order_items" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "sales_order_id" UUID, "item_id" UUID, "item_name" VARCHAR(255), "platter_items" TEXT, "quantity" INTEGER NOT NULL, "price" numeric(20, 2) NOT NULL, "tax_id" UUID, "tax" numeric(5, 2) DEFAULT 0 NOT NULL, "total" numeric(20, 2) DEFAULT 0 NOT NULL, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "sales_order_items_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-38
CREATE TABLE "public"."audit_logs" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "manager" VARCHAR(255), "entity_id" UUID, "entity_type" VARCHAR(50), "action" VARCHAR(50), "entity_data" JSONB, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "audit_logs_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-39
CREATE TABLE "public"."order_ratings" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_id" UUID, "name" VARCHAR(255), "rating" INTEGER NOT NULL, "review" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "order_ratings_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-40
CREATE TABLE "public"."chef_assignments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "chef_id" UUID, "order_id" UUID, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "chef_assignments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-41
CREATE TABLE "public"."driver_assignments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "driver_id" UUID, "order_id" UUID, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "driver_assignments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-42
CREATE TABLE "public"."expenses_categories" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "name" VARCHAR(100) NOT NULL, "description" TEXT, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "expenses_categories_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-43
CREATE TABLE "public"."expenses" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "order_sequence" BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, "expense_title" VARCHAR(255), "expense_category" UUID, "expense_id" VARCHAR(255) GENERATED ALWAYS AS ('EXP-'::text || lpad((order_sequence)::text, 5, '0'::text)) STORED, "payment_method_id" UUID, "payment_method" VARCHAR(50), "payment_term_id" UUID, "payment_term" VARCHAR(50), "department_id" UUID, "department" VARCHAR(255), "amount" numeric(20, 2), "bank_charges" numeric(20, 2) DEFAULT 0, "date_of_expense" date DEFAULT CURRENT_DATE, "notes" TEXT, "status" VARCHAR(50) DEFAULT 'pending', "processed_by" UUID, "manager" VARCHAR(255), "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), "updated_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "expenses_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-44
CREATE TABLE "public"."comments" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "handler" VARCHAR(255), "parent_id" UUID, "entity_id" UUID NOT NULL, "entity_type" VARCHAR(50) NOT NULL, "comment" TEXT NOT NULL, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "comments_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-45
CREATE TABLE "public"."notifications" ("id" UUID DEFAULT gen_random_uuid() NOT NULL, "user_id" UUID, "handler" VARCHAR(255), "entity_id" UUID, "entity_type" VARCHAR(50), "title" VARCHAR(255) NOT NULL, "body" TEXT NOT NULL, "is_read" BOOLEAN DEFAULT FALSE, "created_at" TIMESTAMP WITH TIME ZONE DEFAULT clock_timestamp(), CONSTRAINT "notifications_pkey" PRIMARY KEY ("id"));

-- changeset liquibase:1745470300697-46
ALTER TABLE "public"."roles" ADD CONSTRAINT "roles_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-47
ALTER TABLE "public"."permissions" ADD CONSTRAINT "permissions_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-48
ALTER TABLE "public"."salutations" ADD CONSTRAINT "salutations_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-49
ALTER TABLE "public"."delivery_charges" ADD CONSTRAINT "delivery_charges_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-50
ALTER TABLE "public"."discounts" ADD CONSTRAINT "discounts_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-51
ALTER TABLE "public"."currencies" ADD CONSTRAINT "currencies_code_key" UNIQUE ("code");

-- changeset liquibase:1745470300697-52
ALTER TABLE "public"."currencies" ADD CONSTRAINT "currencies_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-53
ALTER TABLE "public"."currencies" ADD CONSTRAINT "currencies_symbol_key" UNIQUE ("symbol");

-- changeset liquibase:1745470300697-54
ALTER TABLE "public"."base_pay_types" ADD CONSTRAINT "base_pay_types_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-55
ALTER TABLE "public"."work_leave_qualifications" ADD CONSTRAINT "work_leave_qualifications_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-56
ALTER TABLE "public"."branches" ADD CONSTRAINT "branches_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-57
ALTER TABLE "public"."item_categories" ADD CONSTRAINT "item_categories_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-58
ALTER TABLE "public"."units" ADD CONSTRAINT "units_abbreviation_key" UNIQUE ("abbreviation");

-- changeset liquibase:1745470300697-59
ALTER TABLE "public"."units" ADD CONSTRAINT "units_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-60
ALTER TABLE "public"."no_of_working_days" ADD CONSTRAINT "no_of_working_days_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-61
ALTER TABLE "public"."vendor_categories" ADD CONSTRAINT "vendor_categories_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-62
ALTER TABLE "public"."payment_methods" ADD CONSTRAINT "payment_methods_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-63
ALTER TABLE "public"."loan_types" ADD CONSTRAINT "loan_types_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-64
ALTER TABLE "public"."payment_terms" ADD CONSTRAINT "payment_terms_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-65
ALTER TABLE "public"."taxes" ADD CONSTRAINT "taxes_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-66
ALTER TABLE "public"."cash_accounts" ADD CONSTRAINT "cash_accounts_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-67
ALTER TABLE "public"."departments" ADD CONSTRAINT "departments_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-68
ALTER TABLE "public"."users" ADD CONSTRAINT "users_email_key" UNIQUE ("email");

-- changeset liquibase:1745470300697-69
ALTER TABLE "public"."users" ADD CONSTRAINT "users_username_key" UNIQUE ("username");

-- changeset liquibase:1745470300697-70
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_item_details_key" UNIQUE ("item_details");

-- changeset liquibase:1745470300697-71
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-72
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-73
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745470300697-74
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-75
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745470300697-76
ALTER TABLE "public"."items" ADD CONSTRAINT "items_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-77
ALTER TABLE "public"."items" ADD CONSTRAINT "items_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-78
ALTER TABLE "public"."item_stocks" ADD CONSTRAINT "item_stocks_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-79
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_invoice_number_key" UNIQUE ("invoice_number");

-- changeset liquibase:1745470300697-80
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-81
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_purchase_order_number_key" UNIQUE ("purchase_order_number");

-- changeset liquibase:1745470300697-82
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745470300697-83
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_invoice_number_key" UNIQUE ("invoice_number");

-- changeset liquibase:1745470300697-84
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_order_id_key" UNIQUE ("order_id");

-- changeset liquibase:1745470300697-85
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-86
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_reference_number_key" UNIQUE ("reference_number");

-- changeset liquibase:1745470300697-87
ALTER TABLE "public"."chef_assignments" ADD CONSTRAINT "unique_chef_order" UNIQUE ("chef_id", "order_id");

-- changeset liquibase:1745470300697-88
ALTER TABLE "public"."driver_assignments" ADD CONSTRAINT "unique_driver_order" UNIQUE ("driver_id", "order_id");

-- changeset liquibase:1745470300697-89
ALTER TABLE "public"."expenses_categories" ADD CONSTRAINT "expenses_categories_name_key" UNIQUE ("name");

-- changeset liquibase:1745470300697-90
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_order_sequence_key" UNIQUE ("order_sequence");

-- changeset liquibase:1745470300697-91
CREATE TABLE "public"."item_stock_branches" ("stock_id" UUID NOT NULL, "branch_id" UUID NOT NULL, CONSTRAINT "item_stock_branches_pkey" PRIMARY KEY ("stock_id", "branch_id"));

-- changeset liquibase:1745470300697-92
CREATE TABLE "public"."item_stock_departments" ("stock_id" UUID NOT NULL, "department_id" UUID NOT NULL, CONSTRAINT "item_stock_departments_pkey" PRIMARY KEY ("stock_id", "department_id"));

-- changeset liquibase:1745470300697-93
CREATE TABLE "public"."item_stock_manufacturers" ("stock_id" UUID NOT NULL, "manufacturer_id" UUID NOT NULL, CONSTRAINT "item_stock_manufacturers_pkey" PRIMARY KEY ("stock_id", "manufacturer_id"));

-- changeset liquibase:1745470300697-94
CREATE TABLE "public"."item_stock_vendors" ("stock_id" UUID NOT NULL, "vendor_id" UUID NOT NULL, CONSTRAINT "item_stock_vendors_pkey" PRIMARY KEY ("stock_id", "vendor_id"));

-- changeset liquibase:1745470300697-95
CREATE TABLE "public"."user_permissions" ("user_id" UUID NOT NULL, "permission_id" UUID NOT NULL, CONSTRAINT "user_permissions_pkey" PRIMARY KEY ("user_id", "permission_id"));

-- changeset liquibase:1745470300697-96
ALTER TABLE "public"."audit_logs" ADD CONSTRAINT "audit_logs_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-97
ALTER TABLE "public"."chef_assignments" ADD CONSTRAINT "chef_assignments_chef_id_fkey" FOREIGN KEY ("chef_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-98
ALTER TABLE "public"."chef_assignments" ADD CONSTRAINT "chef_assignments_order_id_fkey" FOREIGN KEY ("order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-99
ALTER TABLE "public"."comments" ADD CONSTRAINT "comments_parent_id_fkey" FOREIGN KEY ("parent_id") REFERENCES "public"."comments" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-100
ALTER TABLE "public"."comments" ADD CONSTRAINT "comments_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-101
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_cash_account_id_fkey" FOREIGN KEY ("cash_account_id") REFERENCES "public"."cash_accounts" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-102
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "public"."customers" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-103
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-104
ALTER TABLE "public"."customer_transactions" ADD CONSTRAINT "customer_transactions_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-105
ALTER TABLE "public"."customers" ADD CONSTRAINT "customers_currency_id_fkey" FOREIGN KEY ("currency_id") REFERENCES "public"."currencies" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-106
ALTER TABLE "public"."customers" ADD CONSTRAINT "customers_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-107
ALTER TABLE "public"."departments" ADD CONSTRAINT "departments_base_type_id_fkey" FOREIGN KEY ("base_type_id") REFERENCES "public"."base_pay_types" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-108
ALTER TABLE "public"."departments" ADD CONSTRAINT "departments_work_leave_qualification_fkey" FOREIGN KEY ("work_leave_qualification") REFERENCES "public"."work_leave_qualifications" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-109
ALTER TABLE "public"."driver_assignments" ADD CONSTRAINT "driver_assignments_driver_id_fkey" FOREIGN KEY ("driver_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-110
ALTER TABLE "public"."driver_assignments" ADD CONSTRAINT "driver_assignments_order_id_fkey" FOREIGN KEY ("order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-111
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_department_id_fkey" FOREIGN KEY ("department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-112
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_expense_category_fkey" FOREIGN KEY ("expense_category") REFERENCES "public"."expenses_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-113
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-114
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-115
ALTER TABLE "public"."expenses" ADD CONSTRAINT "expenses_processed_by_fkey" FOREIGN KEY ("processed_by") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-116
ALTER TABLE "public"."item_stock_adjustments" ADD CONSTRAINT "item_stock_adjustments_manager_id_fkey" FOREIGN KEY ("manager_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-117
ALTER TABLE "public"."item_stock_adjustments" ADD CONSTRAINT "item_stock_adjustments_source_department_id_fkey" FOREIGN KEY ("source_department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-118
ALTER TABLE "public"."item_stock_adjustments" ADD CONSTRAINT "item_stock_adjustments_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-119
ALTER TABLE "public"."item_stock_branches" ADD CONSTRAINT "item_stock_branches_branch_id_fkey" FOREIGN KEY ("branch_id") REFERENCES "public"."branches" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-120
ALTER TABLE "public"."item_stock_branches" ADD CONSTRAINT "item_stock_branches_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-121
ALTER TABLE "public"."item_stock_departments" ADD CONSTRAINT "item_stock_departments_department_id_fkey" FOREIGN KEY ("department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-122
ALTER TABLE "public"."item_stock_departments" ADD CONSTRAINT "item_stock_departments_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-123
ALTER TABLE "public"."item_stock_manufacturers" ADD CONSTRAINT "item_stock_manufacturers_manufacturer_id_fkey" FOREIGN KEY ("manufacturer_id") REFERENCES "public"."item_manufacturers" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-124
ALTER TABLE "public"."item_stock_manufacturers" ADD CONSTRAINT "item_stock_manufacturers_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-125
ALTER TABLE "public"."item_stock_vendors" ADD CONSTRAINT "item_stock_vendors_stock_id_fkey" FOREIGN KEY ("stock_id") REFERENCES "public"."item_stocks" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-126
ALTER TABLE "public"."item_stock_vendors" ADD CONSTRAINT "item_stock_vendors_vendor_id_fkey" FOREIGN KEY ("vendor_id") REFERENCES "public"."vendors" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-127
ALTER TABLE "public"."item_stocks" ADD CONSTRAINT "item_stocks_branch_id_fkey" FOREIGN KEY ("branch_id") REFERENCES "public"."branches" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-128
ALTER TABLE "public"."item_stocks" ADD CONSTRAINT "item_stocks_item_id_fkey" FOREIGN KEY ("item_id") REFERENCES "public"."items" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-129
ALTER TABLE "public"."items" ADD CONSTRAINT "items_category_id_fkey" FOREIGN KEY ("category_id") REFERENCES "public"."item_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-130
ALTER TABLE "public"."items" ADD CONSTRAINT "items_unit_id_fkey" FOREIGN KEY ("unit_id") REFERENCES "public"."units" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-131
ALTER TABLE "public"."loans" ADD CONSTRAINT "loans_lender_id_fkey" FOREIGN KEY ("lender_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-132
ALTER TABLE "public"."loans" ADD CONSTRAINT "loans_loan_type_id_fkey" FOREIGN KEY ("loan_type_id") REFERENCES "public"."loan_types" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-133
ALTER TABLE "public"."notifications" ADD CONSTRAINT "notifications_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-134
ALTER TABLE "public"."order_ratings" ADD CONSTRAINT "order_ratings_order_id_fkey" FOREIGN KEY ("order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-135
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_item_category_id_fkey" FOREIGN KEY ("item_category_id") REFERENCES "public"."item_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-136
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_tax_id_fkey" FOREIGN KEY ("tax_id") REFERENCES "public"."taxes" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-137
ALTER TABLE "public"."price_lists" ADD CONSTRAINT "price_lists_unit_id_fkey" FOREIGN KEY ("unit_id") REFERENCES "public"."units" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-138
ALTER TABLE "public"."purchase_order_items" ADD CONSTRAINT "purchase_order_items_item_id_fkey" FOREIGN KEY ("item_id") REFERENCES "public"."items" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-139
ALTER TABLE "public"."purchase_order_items" ADD CONSTRAINT "purchase_order_items_purchase_order_id_fkey" FOREIGN KEY ("purchase_order_id") REFERENCES "public"."purchase_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-140
ALTER TABLE "public"."purchase_order_items" ADD CONSTRAINT "purchase_order_items_tax_id_fkey" FOREIGN KEY ("tax_id") REFERENCES "public"."taxes" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-141
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_branch_id_fkey" FOREIGN KEY ("branch_id") REFERENCES "public"."branches" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-142
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-143
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-144
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_processed_by_fkey" FOREIGN KEY ("processed_by") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-145
ALTER TABLE "public"."purchase_orders" ADD CONSTRAINT "purchase_orders_vendor_id_fkey" FOREIGN KEY ("vendor_id") REFERENCES "public"."vendors" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-146
ALTER TABLE "public"."refresh_tokens" ADD CONSTRAINT "refresh_tokens_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-147
ALTER TABLE "public"."sales_order_items" ADD CONSTRAINT "sales_order_items_item_id_fkey" FOREIGN KEY ("item_id") REFERENCES "public"."price_lists" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-148
ALTER TABLE "public"."sales_order_items" ADD CONSTRAINT "sales_order_items_sales_order_id_fkey" FOREIGN KEY ("sales_order_id") REFERENCES "public"."sales_orders" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-149
ALTER TABLE "public"."sales_order_items" ADD CONSTRAINT "sales_order_items_tax_id_fkey" FOREIGN KEY ("tax_id") REFERENCES "public"."taxes" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-150
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_customer_id_fkey" FOREIGN KEY ("customer_id") REFERENCES "public"."customers" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-151
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_delivery_charge_id_fkey" FOREIGN KEY ("delivery_charge_id") REFERENCES "public"."delivery_charges" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-152
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_discount_id_fkey" FOREIGN KEY ("discount_id") REFERENCES "public"."discounts" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-153
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-154
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-155
ALTER TABLE "public"."sales_orders" ADD CONSTRAINT "sales_orders_processed_by_fkey" FOREIGN KEY ("processed_by") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-156
ALTER TABLE "public"."user_leaves" ADD CONSTRAINT "user_leaves_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-157
ALTER TABLE "public"."user_permissions" ADD CONSTRAINT "user_permissions_permission_id_fkey" FOREIGN KEY ("permission_id") REFERENCES "public"."permissions" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-158
ALTER TABLE "public"."user_permissions" ADD CONSTRAINT "user_permissions_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-159
ALTER TABLE "public"."users" ADD CONSTRAINT "users_department_id_fkey" FOREIGN KEY ("department_id") REFERENCES "public"."departments" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-160
ALTER TABLE "public"."users" ADD CONSTRAINT "users_no_of_working_days_id_fkey" FOREIGN KEY ("no_of_working_days_id") REFERENCES "public"."no_of_working_days" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-161
ALTER TABLE "public"."users" ADD CONSTRAINT "users_role_id_fkey" FOREIGN KEY ("role_id") REFERENCES "public"."roles" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-162
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_cash_account_id_fkey" FOREIGN KEY ("cash_account_id") REFERENCES "public"."cash_accounts" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-163
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_payment_method_id_fkey" FOREIGN KEY ("payment_method_id") REFERENCES "public"."payment_methods" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-164
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

-- changeset liquibase:1745470300697-165
ALTER TABLE "public"."vendor_transactions" ADD CONSTRAINT "vendor_transactions_vendor_id_fkey" FOREIGN KEY ("vendor_id") REFERENCES "public"."vendors" ("id") ON UPDATE NO ACTION ON DELETE CASCADE;

-- changeset liquibase:1745470300697-166
ALTER TABLE "public"."vendors" ADD CONSTRAINT "vendors_category_id_fkey" FOREIGN KEY ("category_id") REFERENCES "public"."vendor_categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-167
ALTER TABLE "public"."vendors" ADD CONSTRAINT "vendors_currency_id_fkey" FOREIGN KEY ("currency_id") REFERENCES "public"."currencies" ("id") ON UPDATE NO ACTION ON DELETE SET NULL;

-- changeset liquibase:1745470300697-168
ALTER TABLE "public"."vendors" ADD CONSTRAINT "vendors_payment_term_id_fkey" FOREIGN KEY ("payment_term_id") REFERENCES "public"."payment_terms" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION;

