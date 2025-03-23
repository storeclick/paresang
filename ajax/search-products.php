<?php
require_once '../includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// دریافت داده‌های جستجو
$query = $_GET['query'] ?? '';

// جستجوی محصولات
$db = Database::getInstance();
$products = $db->query("SELECT id, name FROM products WHERE name LIKE ? LIMIT 10", ['%' . $query . '%'])->fetchAll(PDO::FETCH_ASSOC);

// ارسال داده‌ها به صورت JSON
echo json_encode($products);