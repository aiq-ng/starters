<?php

namespace Models;

use Database\Database;

class Search
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

    }

    public function globalSearch($filters)
    {
        try {
            $page = $filters['page'] ?? 1;
            $pageSize = $filters['pageSize'] ?? 10;
            $search = $filters['search'] ?? null;
            $offset = ($page - 1) * $pageSize;

            if (empty($search)) {
                return [];
            }

            $query = "WITH search_results AS (" .
                $this->getPurchaseInvoiceSearchQuery($search) . " UNION ALL " .
                $this->getSalesInvoiceSearchQuery($search) . " UNION ALL " .
                $this->getSalesOrderSearchQuery($search) . " UNION ALL " .
                $this->getPurchaseOrderSearchQuery($search) . " UNION ALL " .
                $this->getInventorySearchQuery($search) . " UNION ALL " .
                $this->getCustomerSearchQuery($search) . " UNION ALL " .
                $this->getVendorSearchQuery($search) . " UNION ALL " .
                $this->getEmployeeSearchQuery($search) . " UNION ALL " .
                $this->getExpenseSearchQuery($search) . " UNION ALL " .
                $this->getPriceListSearchQuery($search) . ")
        SELECT * FROM search_results 
        LIMIT :pageSize OFFSET :offset";

            $countQuery = "WITH search_results AS (" .
                $this->getPurchaseInvoiceSearchQuery($search) . " UNION ALL " .
                $this->getSalesInvoiceSearchQuery($search) . " UNION ALL " .
                $this->getSalesOrderSearchQuery($search) . " UNION ALL " .
                $this->getPurchaseOrderSearchQuery($search) . " UNION ALL " .
                $this->getInventorySearchQuery($search) . " UNION ALL " .
                $this->getCustomerSearchQuery($search) . " UNION ALL " .
                $this->getVendorSearchQuery($search) . " UNION ALL " .
                $this->getEmployeeSearchQuery($search) . " UNION ALL " .
                $this->getExpenseSearchQuery($search) . " UNION ALL " .
                $this->getPriceListSearchQuery($search) . ")
        SELECT COUNT(*) AS total FROM search_results";

            $bindings = [
                'pageSize' => $pageSize,
                'offset' => $offset
            ];

            if ($search) {
                $bindings['search'] = "%$search%";
            }

            $stmt = $this->db->prepare($query);
            foreach ($bindings as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($data as &$row) {
                if (isset($row['data'])) {
                    $decodedData = json_decode($row['data'], true);
                    unset($row['data']);
                    $row = array_merge(['type' => $row['type']], $decodedData);
                }
            }
            unset($row);

            $countStmt = $this->db->prepare($countQuery);
            foreach ($bindings as $key => $value) {
                if ($key !== 'pageSize' && $key !== 'offset') {
                    $countStmt->bindValue(":$key", $value);
                }
            }
            $countStmt->execute();
            $total = $countStmt->fetchColumn();

            return [
                'data' => $data,
                'meta' => [
                    'total_data' => (int) $total,
                    'total_pages' => ceil($total / $pageSize),
                    'pageSize' => (int) $pageSize,
                    'previous_page' => $page > 1 ? (int) $page - 1 : null,
                    'current_page' => (int) $page,
                    'next_page' => (int) $page + 1,
                ]
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return ['data' => [], 'meta' => []];
        }
    }

    private function getExpenseSearchQuery($search)
    {
        return "SELECT 'expenses' AS type, 
            jsonb_build_object(
                'id', e.id,
                'expense_id', e.expense_id,
                'expense_title', e.expense_title,
                'date_of_expense', e.date_of_expense,
                'payment_method', pm.name,
                'expense_category', ec.name,
                'department', d.name,
                'amount', e.amount
            ) AS data
        FROM expenses e
        LEFT JOIN payment_methods pm ON e.payment_method_id = pm.id
        LEFT JOIN expenses_categories ec ON e.expense_category = ec.id
        LEFT JOIN departments d ON e.department_id = d.id
        " . ($search ? " WHERE e.expense_title ILIKE :search 
        OR ec.name ILIKE :search 
        OR d.name ILIKE :search 
        OR pm.name ILIKE :search" : "");
    }

    private function getCustomerSearchQuery($search)
    {
        return "SELECT 'customers' AS type, 
            jsonb_build_object(
                'id', c.id,
                'name', CONCAT(c.first_name, ' ', c.last_name),
                'company_name', c.company_name,
                'email', c.email,
                'work_phone', c.work_phone,
                'address', c.address,
                'total_transaction', COALESCE(SUM(t.amount), 0),
                'balance', c.balance,
                'status', c.status
            ) AS data
        FROM customers c
        LEFT JOIN customer_transactions t ON c.id = t.customer_id
        " . ($search ? " WHERE c.first_name ILIKE :search 
        OR c.last_name ILIKE :search 
        OR c.company_name ILIKE :search 
        OR c.display_name ILIKE :search 
        OR c.email ILIKE :search 
        OR c.address ILIKE :search 
        OR c.website ILIKE :search 
        OR c.social_media::TEXT LIKE :search" : "") . "
        GROUP BY c.id, c.first_name, c.last_name, c.company_name, 
                 c.email, c.work_phone, c.address, c.balance, c.status";
    }

    private function getVendorSearchQuery($search)
    {
        return "SELECT 'vendors' AS type, 
            jsonb_build_object(
                'id', v.id,
                'name', CONCAT(v.first_name, ' ', v.last_name),
                'category', vc.name,
                'company_name', v.company_name,
                'email', v.email,
                'work_phone', v.work_phone,
                'address', v.address,
                'total_transaction', COALESCE(SUM(t.amount), 0),
                'balance', v.balance,
                'status', v.status
            ) AS data
        FROM vendors v
        LEFT JOIN vendor_transactions t ON v.id = t.vendor_id
        LEFT JOIN vendor_categories vc ON v.category_id = vc.id
        " . ($search ? " WHERE v.first_name ILIKE :search 
        OR v.last_name ILIKE :search 
        OR v.company_name ILIKE :search 
        OR v.display_name ILIKE :search 
        OR v.email ILIKE :search 
        OR v.address ILIKE :search 
        OR v.website ILIKE :search 
        OR v.social_media::TEXT LIKE :search" : "") . "
        GROUP BY v.id, v.first_name, v.last_name, v.company_name, 
                 v.email, v.work_phone, v.address, v.balance, v.status, vc.name";
    }

    private function getEmployeeSearchQuery($search)
    {
        return "SELECT 'employees' AS type, 
            jsonb_build_object(
                'id', u.id,
                'name', u.name,
                'avatar_url', u.avatar_url,
                'department', d.name,
                'position', r.name,
                'salary', u.salary,
                'bank_details', u.bank_details,
                'leave_status', COALESCE(ul.status, 'none')
            ) AS data
        FROM users u
            LEFT JOIN departments d ON u.department_id = d.id
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN user_leaves ul ON u.id = ul.user_id
        " . ($search ? " WHERE u.name ILIKE :search 
            OR r.name ILIKE :search 
            OR d.name ILIKE :search 
            OR u.bank_details::TEXT ILIKE :search" : "");
    }

    private function getInventorySearchQuery($search)
    {
        return "SELECT 'inventory' AS type, 
            jsonb_build_object(
                'id', i.id, 
                'name', i.name, 
                'quantity', CONCAT(COALESCE(SUM(item_stocks.quantity), 0), ' ', u.abbreviation),
                'threshold_value', CONCAT(i.threshold_value, ' ', u.abbreviation),
                'buying_price', i.price, 
                'expiry_date', MAX(item_stocks.expiry_date),
                'sku', i.sku,
                'barcode', i.barcode,
                'availability', i.availability, 
                'media', i.media
            ) AS data
        FROM item_stocks
            JOIN items i ON item_stocks.item_id = i.id
            LEFT JOIN units u ON i.unit_id = u.id
        " . ($search ? " WHERE i.name ILIKE :search 
            OR i.description ILIKE :search 
            OR i.sku ILIKE :search" : "") . "
        GROUP BY i.id, i.name, i.threshold_value, i.price,
                 i.sku, i.availability, i.media, u.abbreviation";
    }

    private function getPurchaseOrderSearchQuery($search)
    {
        return "SELECT 'purchase_orders' AS type, 
            jsonb_build_object(
                'id', po.id,
                'serial_number', po.order_sequence,
                'purchase_order_number', po.purchase_order_number,
                'reference_number', po.reference_number,
                'invoice_number', po.invoice_number,
                'vendor_name', CONCAT_WS(' ', v.salutation, v.first_name, v.last_name),
                'order_date', po.created_at::DATE,
                'delivery_date', po.delivery_date,
                'total', COALESCE(po.total, 0.00),
                'status', po.status,
                'payment', CASE
                    WHEN po.status = 'issued' THEN 'Issued'
                    ELSE pt.name
                END
            ) AS data
        FROM purchase_orders po
            LEFT JOIN vendors v ON po.vendor_id = v.id
            LEFT JOIN payment_terms pt ON po.payment_term_id = pt.id
        " . ($search ? " WHERE po.purchase_order_number ILIKE :search 
            OR po.reference_number ILIKE :search
            OR po.invoice_number ILIKE :search 
            OR CONCAT_WS(' ', v.salutation, v.first_name, v.last_name) ILIKE :search" : "") . "
        GROUP BY po.id, po.order_sequence, po.purchase_order_number, 
                 po.reference_number, po.invoice_number, v.salutation, 
                 v.first_name, v.last_name, po.created_at, po.delivery_date, 
                 po.total, po.status, pt.name";
    }

    private function getSalesOrderSearchQuery($search)
    {
        return "SELECT 'sales_orders' AS type, 
            jsonb_build_object(
                'id', so.id, 
                'order_id', so.order_id, 
                'order_title', so.order_title, 
                'quantity', COALESCE(SUM(soi.quantity), 0),
                'customer_name', CONCAT_WS(' ', c.salutation, c.first_name, c.last_name),
                'date', so.created_at::DATE,
                'order_type', so.order_type, 
                'amount', COALESCE(SUM(so.total), 0.00),
                'status', so.status,
                'payment_status', so.payment_status
            ) AS data
        FROM sales_orders so
            LEFT JOIN sales_order_items soi ON so.id = soi.sales_order_id
            LEFT JOIN customers c ON so.customer_id = c.id
        " . ($search ? " WHERE so.order_title ILIKE :search 
            OR so.order_id::TEXT ILIKE :search 
            OR CONCAT_WS(' ', c.salutation, c.first_name, c.last_name) ILIKE :search" : "") . "
        GROUP BY so.id, so.order_id, so.order_title, c.salutation, c.first_name, 
                 c.last_name, so.created_at, so.order_type, so.status, so.payment_status";
    }

    private function getPriceListSearchQuery($search)
    {
        return "SELECT 'price_lists' AS type, 
            jsonb_build_object(
                'id', pl.id,
                'serial_number', pl.order_sequence,
                'item_category', ic.name, 
                'item_details', pl.item_details, 
                'unit_price', pl.unit_price, 
                'minimum_order', pl.minimum_order, 
                'unit', u.abbreviation
            ) AS data
        FROM price_lists pl
            LEFT JOIN item_categories ic ON pl.item_category_id = ic.id
            LEFT JOIN units u ON pl.unit_id = u.id
        " . ($search ? " WHERE pl.item_details ILIKE :search 
            OR ic.name ILIKE :search" : "");
    }

    private function getPurchaseInvoiceSearchQuery($search)
    {
        return "SELECT 'purchase_invoices' AS type, 
            jsonb_build_object(
                'id', po.id,
                'subject', po.subject,
                'vendor_name', v.display_name,
                'vendor_email', v.email,
                'vendor_address', v.address,
                'vendor_phone', v.mobile_phone,
                'vendor_balance', v.balance,
                'invoice_number', po.invoice_number,
                'purchase_order_number', po.purchase_order_number, 
                'reference_number', po.reference_number,
                'discount', po.discount,
                'shipping_charge', po.shipping_charge,
                'notes', po.notes,
                'total', COALESCE(po.total, 0.00),
                'order_date', po.created_at::DATE,
                'delivery_date', po.delivery_date,
                'items', json_agg(
                    json_build_object(
                        'item_id', poi.item_id,
                        'item_name', i.name,
                        'item_description', i.description,
                        'quantity', poi.quantity,
                        'price', poi.price,
                        'amount', poi.quantity * poi.price,
                        'tax_id', poi.tax_id,
                        'tax_rate', tr.rate
                    )
                )
            ) AS data
        FROM purchase_orders po
            LEFT JOIN vendors v ON po.vendor_id = v.id
            LEFT JOIN purchase_order_items poi ON poi.purchase_order_id = po.id
            LEFT JOIN taxes tr ON poi.tax_id = tr.id
            LEFT JOIN items i ON poi.item_id = i.id
        " . ($search ? " WHERE po.invoice_number ILIKE :search
            OR po.purchase_order_number ILIKE :search
            OR po.reference_number ILIKE :search
            OR v.display_name ILIKE :search" : "") . "
        GROUP BY po.id, po.purchase_order_number, po.reference_number,
                v.display_name, v.email, v.address, v.mobile_phone, v.balance, 
                po.discount, po.shipping_charge, po.notes, po.total, 
                po.created_at, po.delivery_date";
    }

    private function getSalesInvoiceSearchQuery($search)
    {
        return "SELECT 'sales_invoices' AS type, 
            jsonb_build_object(
                'id', so.id,
                'subject', so.order_title,
                'customer_name', CONCAT_WS(' ', c.salutation, c.first_name, c.last_name),
                'customer_email', c.email,
                'customer_address', c.address,
                'customer_phone', c.mobile_phone,
                'invoice_number', so.invoice_number,
                'sales_order_number', so.order_id, 
                'reference_number', so.reference_number,
                'discount', so.discount,
                'shipping_charge', so.delivery_charge,
                'notes', so.additional_note,
                'total', COALESCE(so.total, 0.00),
                'order_date', so.created_at::DATE,
                'delivery_date', so.delivery_date,
                'items', json_agg(
                    json_build_object(
                        'item_id', p.id,
                        'item_name', p.item_details,
                        'quantity', soi.quantity,
                        'price', soi.price,
                        'amount', soi.quantity * soi.price,
                        'tax_id', soi.tax_id,
                        'tax_rate', t.rate
                    )
                )
            ) AS data
        FROM sales_orders so
            LEFT JOIN customers c ON so.customer_id = c.id
            LEFT JOIN sales_order_items soi ON soi.sales_order_id = so.id
            LEFT JOIN price_lists p ON soi.item_id = p.id
            LEFT JOIN taxes t ON soi.tax_id = t.id
        " . ($search ? " WHERE so.invoice_number ILIKE :search
            OR so.order_id::TEXT ILIKE :search
            OR c.first_name ILIKE :search
            OR c.last_name ILIKE :search
            OR so.reference_number ILIKE :search" : "") . "
        GROUP BY so.id, c.display_name, so.invoice_number, so.order_title,
                so.order_type, c.id, so.payment_term_id, so.payment_method_id,
                so.assigned_driver_id, so.delivery_option, so.additional_note,
                so.customer_note, so.discount, so.delivery_charge, so.total,
                c.email, so.created_at, so.delivery_date";
    }


}
