<?php
require_once '../includes/init.php';

if (!isAjax()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$query = sanitize($_GET['query'] ?? '');
if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    $db = Database::getInstance();
    $stmt = $db->query("
        SELECT 
            p.id,
            p.name,
            p.code,
            p.price,
            p.quantity as stock,
            c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 
            (p.name LIKE ? OR p.code LIKE ?)
            AND p.status = 'active'
            AND p.deleted_at IS NULL
        ORDER BY p.name ASC
        LIMIT 10
    ", ["%$query%", "%$query%"]);

    $products = $stmt->fetchAll();

    // فرمت‌بندی قیمت‌ها و اضافه کردن اطلاعات اضافی
    foreach ($products as &$product) {
        $product['formatted_price'] = number_format($product['price']);
        $product['stock_status'] = $product['stock'] > 0 ? 'موجود' : 'ناموجود';
        $product['display_text'] = $product['name'];
        if (!empty($product['code'])) {
            $product['display_text'] .= " (کد: {$product['code']})";
        }
        if (!empty($product['category_name'])) {
            $product['display_text'] .= " - {$product['category_name']}";
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($products, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'خطا در جستجوی محصولات',
        'message' => $e->getMessage()
    ]);
}