<?php
// تنظیمات پایگاه داده
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'accounting_db');

// تنظیمات برنامه
define('SITE_NAME', 'سیستم حسابداری پاره سنگ');
define('SITE_URL', 'http://localhost/accounting');
define('VERSION', '1.0.0');

// تنظیمات امنیتی
define('ENCRYPTION_KEY', 'your-secret-key-here');
define('SESSION_TIMEOUT', 3600); // 1 hour

// تنظیمات زمانی
date_default_timezone_set('Asia/Tehran');