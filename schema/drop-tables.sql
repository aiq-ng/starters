DROP TABLE IF EXISTS expenses CASCADE;
DROP TABLE IF EXISTS expenses_categories CASCADE;

-- Sales Orders and Items
DROP TABLE IF EXISTS audit_logs CASCADE;
DROP TABLE IF EXISTS chef_assignments CASCADE;
DROP TABLE IF EXISTS order_ratings CASCADE;
DROP TABLE IF EXISTS sales_order_items CASCADE;
DROP TABLE IF EXISTS sales_orders CASCADE;

-- Purchase Orders and Items
DROP TABLE IF EXISTS purchase_order_items CASCADE;
DROP TABLE IF EXISTS purchase_orders CASCADE;

-- Items and Related Entities
DROP TABLE IF EXISTS item_stock_adjustments CASCADE;
DROP TABLE IF EXISTS item_stock_vendors CASCADE;
DROP TABLE IF EXISTS item_stock_manufacturers CASCADE;
DROP TABLE IF EXISTS item_stock_departments CASCADE;
DROP TABLE IF EXISTS item_stock_branches CASCADE;
DROP TABLE IF EXISTS item_stocks CASCADE;
DROP TABLE IF EXISTS items CASCADE;
DROP TABLE IF EXISTS item_manufacturers CASCADE;
DROP TABLE IF EXISTS price_lists CASCADE;
DROP TABLE IF EXISTS item_categories CASCADE;
DROP TABLE IF EXISTS comments CASCADE;

-- Customers and Vendors
DROP TABLE IF EXISTS customer_transactions CASCADE;
DROP TABLE IF EXISTS vendor_transactions CASCADE;
DROP TABLE IF EXISTS customers CASCADE;
DROP TABLE IF EXISTS vendors CASCADE;
DROP TABLE IF EXISTS cash_accounts CASCADE;

-- Supporting Entities
DROP TABLE IF EXISTS taxes CASCADE;
DROP TABLE IF EXISTS units CASCADE;
DROP TABLE IF EXISTS payment_terms CASCADE;
DROP TABLE IF EXISTS payment_methods CASCADE;
DROP TABLE IF EXISTS vendor_categories CASCADE;
DROP TABLE IF EXISTS user_permissions CASCADE;
DROP TABLE IF EXISTS loans CASCADE;
DROP TABLE IF EXISTS salutations CASCADE;

-- Core Entities
DROP TABLE IF EXISTS refresh_tokens CASCADE;
DROP TABLE IF EXISTS currencies CASCADE;
DROP TABLE IF EXISTS loan_types CASCADE;
DROP TABLE IF EXISTS user_leaves CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS roles CASCADE;
DROP TABLE IF EXISTS permissions CASCADE;

-- Branches and Departments
DROP TABLE IF EXISTS branches CASCADE;
DROP TABLE IF EXISTS departments CASCADE;
DROP TABLE IF EXISTS base_pay_types CASCADE;
DROP TABLE IF EXISTS no_of_working_days CASCADE;
DROP TABLE IF EXISTS work_leave_qualifications CASCADE;
