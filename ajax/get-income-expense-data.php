<?php
require_once '../includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// دریافت داده‌های درآمد و هزینه‌ها
$db = Database::getInstance();
$incomeData = $db->query("
    SELECT COALESCE(SUM(amount), 0) as total
    FROM transactions
    WHERE type = 'income'
")->fetch(PDO::FETCH_ASSOC)['total'];

$expenseData = $db->query("
    SELECT COALESCE(SUM(amount), 0) as total
    FROM transactions
    WHERE type = 'expense'
")->fetch(PDO::FETCH_ASSOC)['total'];

// آماده‌سازی داده‌ها برای نمودار
$labels = ['درآمد', 'هزینه‌ها'];
$data = [$incomeData, $expenseData];

// ارسال داده‌ها به صورت JSON
echo json_encode([
    'labels' => $labels,
    'data' => $data
]);