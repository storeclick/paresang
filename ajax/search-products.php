<?php
require_once '../includes/init.php';

// بررسی درخواست Ajax
if (!isAjax()) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'درخواست نامعتبر']);
    exit;
}

$query = sanitize($_GET['query'] ?? '');
if (empty($query)) {
    header('Content-Type: application/json; charset=utf-8');
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
            p.sale_price as price,
            p.quantity as stock,
            p.unit,
            c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 
            (p.name LIKE ? OR p.code LIKE ?)
            AND p.status = 'active'
        ORDER BY p.name ASC
        LIMIT 10
    ", ["%$query%", "%$query%"]);

    $products = $stmt->fetchAll();
    
    // بررسی نتایج
    if (empty($products)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
        exit;
    }

    // فرمت‌بندی قیمت‌ها و اضافه کردن اطلاعات اضافی
    foreach ($products as &$product) {
        // قیمت فروش
        $product['formatted_price'] = number_format($product['price']) . ' تومان';
        
        // وضعیت موجودی
        if ($product['stock'] > 0) {
            $product['stock_status'] = 'موجود';
            $product['stock_class'] = 'text-success';
        } else {
            $product['stock_status'] = 'ناموجود';
            $product['stock_class'] = 'text-danger';
        }
        
        // متن نمایشی
        $product['display_text'] = $product['name'];
        
        // اضافه کردن کد محصول
        if (!empty($product['code'])) {
            $product['display_text'] .= " (کد: {$product['code']})";
        }
        
        // اضافه کردن دسته‌بندی
        if (!empty($product['category_name'])) {
            $product['display_text'] .= " - {$product['category_name']}";
        }
        
        // واحد شمارش
        $product['unit_text'] = !empty($product['unit']) ? $product['unit'] : 'عدد';
        
        // اطلاعات کامل موجودی
        $product['stock_info'] = sprintf('%d %s', $product['stock'], $product['unit_text']);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($products, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => 'خطا در جستجوی محصولات',
        'message' => 'خطایی در سیستم رخ داده است. لطفاً دوباره تلاش کنید.'
    ]);
}