<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <?php
    if (isset($customCss) && is_array($customCss)) {
        foreach ($customCss as $cssFile) {
            echo '<link rel="stylesheet" href="' . $cssFile . '">';
        }
    }
    ?>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php if(isset($_SESSION['user_id'])): ?>
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="assets/images/logo.png" alt="پاره سنگ" class="logo">
                <h3>پاره سنگ</h3>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        داشبورد
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#productsSubmenu">
                        <i class="fas fa-box"></i>
                        محصولات
                    </a>
                    <div class="collapse" id="productsSubmenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="add-product.php" class="nav-link">افزودن محصول</a>
                            </li>
                            <li class="nav-item">
                                <a href="products-list.php" class="nav-link">لیست محصولات</a>
                            </li>
                            <li class="nav-item">
                                <a href="categories.php" class="nav-link">دسته‌بندی</a>
                            </li>
                            <li class="nav-item">
                                <a href="inventory.php" class="nav-link">انبارداری</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#salesSubmenu">
                        <i class="fas fa-shopping-cart"></i>
                        فروش
                    </a>
                    <div class="collapse" id="salesSubmenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="quick-sale.php" class="nav-link">فروش سریع</a>
                            </li>
                            <li class="nav-item">
                                <a href="invoice.php" class="nav-link">فاکتور فروش</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#settingsSubmenu">
                        <i class="fas fa-cog"></i>
                        تنظیمات
                    </a>
                    <div class="collapse" id="settingsSubmenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="general-settings.php" class="nav-link">تنظیمات عمومی</a>
                            </li>
                            <li class="nav-item">
                                <a href="backup.php" class="nav-link">پشتیبان‌گیری</a>
                            </li>
                            <li class="nav-item">
                                <a href="update.php" class="nav-link">بروزرسانی برنامه</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#profileSubmenu">
                        <i class="fas fa-user"></i>
                        پروفایل
                    </a>
                    <div class="collapse" id="profileSubmenu">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="profile.php" class="nav-link">مشاهده پروفایل</a>
                            </li>
                            <li class="nav-item">
                                <a href="change-password.php" class="nav-link">تغییر رمز عبور</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        خروج
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-link sidebar-toggle">
                            <i class="fas fa-bars"></i>
                        </button>
                    <?php endif; ?>
                    <div class="navbar-brand">سیستم حسابداری پاره سنگ</div>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="navbar-text">
                            خوش آمدید، <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid mt-3">