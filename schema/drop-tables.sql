-- Sales Orders and Items
DROP TABLE IF EXISTS sales_order_items CASCADE;
DROP TABLE IF EXISTS sales_orders CASCADE;

-- Purchase Orders and Items
DROP TABLE IF EXISTS purchase_order_items CASCADE;
DROP TABLE IF EXISTS purchase_orders CASCADE;

-- Items and Related Entities
DROP TABLE IF EXISTS item_stock_adjustments CASCADE;
DROP TABLE IF EXISTS item_stocks CASCADE;
DROP TABLE IF EXISTS items CASCADE;
DROP TABLE IF EXISTS item_manufacturers CASCADE;
DROP TABLE IF EXISTS item_categories CASCADE;

-- Customers and Vendors
DROP TABLE IF EXISTS customer_transactions CASCADE;
DROP TABLE IF EXISTS vendor_transactions CASCADE;
DROP TABLE IF EXISTS customers CASCADE;
DROP TABLE IF EXISTS vendors CASCADE;

-- Supporting Entities
DROP TABLE IF EXISTS taxes CASCADE;
DROP TABLE IF EXISTS units CASCADE;
DROP TABLE IF EXISTS payment_terms CASCADE;
DROP TABLE IF EXISTS payment_methods CASCADE;
DROP TABLE IF EXISTS vendor_categories CASCADE;

-- Branches and Departments
DROP TABLE IF EXISTS branches CASCADE;
DROP TABLE IF EXISTS departments CASCADE;

-- Core Entities
DROP TABLE IF EXISTS currencies CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

