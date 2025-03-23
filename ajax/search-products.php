<?php
require_once '../includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// دریافت و تمیز کردن داده‌های جستجو
$query = trim($_GET['query'] ?? '');

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    // جستجوی محصولات
    $db = Database::getInstance();
    $stmt = $db->prepare("
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
            (p.name LIKE :query OR p.code LIKE :query)
            AND p.active = 1 
            AND p.deleted_at IS NULL
        ORDER BY p.name ASC
        LIMIT 10
    ");

    $searchTerm = '%' . $query . '%';
    $stmt->execute(['query' => $searchTerm]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // فرمت‌بندی قیمت‌ها و اضافه کردن اطلاعات اضافی
    foreach ($products as &$product) {
        $product['formatted_price'] = number_format($product['price']) . ' تومان';
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