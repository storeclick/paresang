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
            id,
            name,
            price,
            stock,
            sku
        FROM products 
        WHERE 
            (name LIKE :query OR sku LIKE :query)
            AND active = 1 
            AND deleted_at IS NULL
        LIMIT 10
    ");

    $searchTerm = '%' . $query . '%';
    $stmt->execute(['query' => $searchTerm]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // فرمت‌بندی قیمت‌ها و اضافه کردن اطلاعات اضافی
    foreach ($products as &$product) {
        $product['formatted_price'] = number_format($product['price']) . ' تومان';
        $product['stock_status'] = $product['stock'] > 0 ? 'موجود' : 'ناموجود';
        $product['display_text'] = "{$product['name']} (کد: {$product['sku']}) - {$product['formatted_price']}";
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($products);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'خطا در جستجوی محصولات']);
}