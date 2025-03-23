<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    
    // بررسی وجود محصول
    $db = Database::getInstance();
    $product = $db->query("SELECT * FROM products WHERE id = ?", [$product_id])->fetch();
    if (!$product) {
        exit(json_encode(['error' => 'محصول یافت نشد.']));
    }
    
    // حذف محصول
    if ($db->delete('products', 'id = ?', [$product_id])) {
        exit(json_encode(['success' => 'محصول با موفقیت حذف شد.']));
    } else {
        exit(json_encode(['error' => 'خطا در حذف محصول. لطفاً دوباره تلاش کنید.']));
    }
}

exit(json_encode(['error' => 'Invalid request.']));