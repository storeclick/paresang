<?php
session_start();

// تنظیم charset به UTF-8
header('Content-Type: text/html; charset=utf-8');

// لود کردن فایل‌های اصلی
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// ایجاد نمونه از کلاس دیتابیس
$db = Database::getInstance();

// تنظیم error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بررسی وضعیت لاگین کاربر
if (!isset($_SESSION['user_id']) && !in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php'])) {
    header('Location: login.php');
    exit;
}

// مقداردهی متغیرهای مورد نیاز
$auth = new Auth();
$user = $auth->getCurrentUser();
$lowStock = $db->query("SELECT COUNT(*) as total FROM products WHERE quantity <= min_quantity AND status = 'active'")->fetch()['total'];