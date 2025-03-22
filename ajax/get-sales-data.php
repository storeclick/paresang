<?php
require_once '../includes/init.php';

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

$period = $_GET['period'] ?? 'week';
$db = Database::getInstance();

switch ($period) {
    case 'week':
        $sql = "SELECT 
                    DATE(created_at) as date,
                    SUM(final_amount) as total
                FROM invoices
                WHERE 
                    status = 'confirmed' AND
                    created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        break;
        
    case 'month':
        $sql = "SELECT 
                    DATE(created_at) as date,
                    SUM(final_amount) as total
                FROM invoices
                WHERE 
                    status = 'confirmed' AND
                    created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        break;
        
    case 'year':
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as date,
                    SUM(final_amount) as total
                FROM invoices
                WHERE 
                    status = 'confirmed' AND
                    created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY date ASC";
        break;
        
    default:
        exit(json_encode(['error' => 'Invalid period']));
}

$result = $db->query($sql)->fetchAll();

// تبدیل تاریخ‌ها به شمسی و فرمت‌بندی داده‌ها برای نمودار
$labels = [];
$data = [];

foreach ($result as $row) {
    if ($period == 'year') {
        // تبدیل تاریخ سال-ماه به شمسی
        $date = explode('-', $row['date']);
        $jDate = gregorian_to_jalali($date[0], $date[1], 1);
        $labels[] = $jDate[0] . '/' . str_pad($jDate[1], 2, '0', STR_PAD_LEFT);
    } else {
        // تبدیل تاریخ کامل به شمسی
        $timestamp = strtotime($row['date']);
        $labels[] = jdate('Y/m/d', $timestamp);
    }
    $data[] = (float) $row['total'];
}

// اگر داده‌ای وجود نداشت، مقدار پیش‌فرض برگردانده شود
if (empty($labels)) {
    switch ($period) {
        case 'week':
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = jdate('Y/m/d', strtotime($date));
                $data[] = 0;
            }
            break;
            
        case 'month':
            for ($i = 29; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $labels[] = jdate('Y/m/d', strtotime($date));
                $data[] = 0;
            }
            break;
            
        case 'year':
            for ($i = 11; $i >= 0; $i--) {
                $date = date('Y-m', strtotime("-$i months"));
                $dateParts = explode('-', $date);
                $jDate = gregorian_to_jalali($dateParts[0], $dateParts[1], 1);
                $labels[] = $jDate[0] . '/' . str_pad($jDate[1], 2, '0', STR_PAD_LEFT);
                $data[] = 0;
            }
            break;
    }
}

echo json_encode([
    'labels' => $labels,
    'data' => $data
]);