<?php
require_once '../includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// دریافت داده‌های نقدینگی
$db = Database::getInstance();
$cashFlowData = $db->query("
    SELECT DATE(created_at) as date, SUM(amount) as total
    FROM cash_flows
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
")->fetchAll(PDO::FETCH_ASSOC);

// آماده‌سازی داده‌ها برای نمودار
$labels = [];
$data = [];
foreach ($cashFlowData as $row) {
    $labels[] = $row['date'];
    $data[] = $row['total'];
}

// ارسال داده‌ها به صورت JSON
echo json_encode([
    'labels' => $labels,
    'data' => $data
]);