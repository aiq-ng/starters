-- Sales Orders and Items
DROP TABLE IF EXISTS sales_order_items;
DROP TABLE IF EXISTS sales_orders;

-- Purchase Orders and Items
DROP TABLE IF EXISTS purchase_order_items;
DROP TABLE IF EXISTS purchase_orders;

-- Customers and Vendors
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS vendors;

-- Items and Related Entities
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS item_manufacturers;

-- Supporting Entities
DROP TABLE IF EXISTS taxes;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS payment_terms;
DROP TABLE IF EXISTS payment_methods;
DROP TABLE IF EXISTS vendor_categories;
DROP TABLE IF EXISTS item_categories;

-- Branches and Departments
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS departments;

-- Core Entities
DROP TABLE IF EXISTS currencies;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
