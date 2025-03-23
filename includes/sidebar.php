<div class="sidebar">
    <div class="sidebar-header">
        <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" class="logo">
        <h3><?php echo SITE_NAME; ?></h3>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                داشبورد
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#productsSubmenu" class="nav-link" data-bs-toggle="collapse">
                <i class="fas fa-box"></i>
                محصولات
                <i class="fas fa-chevron-down float-start"></i>
            </a>
            <div class="collapse <?php echo strpos($_SERVER['PHP_SELF'], 'product') !== false ? 'show' : ''; ?>" id="productsSubmenu">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="product-add.php" class="nav-link">
                            <i class="fas fa-plus"></i>
                            افزودن محصول
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="products.php" class="nav-link">
                            <i class="fas fa-list"></i>
                            لیست محصولات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="categories.php" class="nav-link">
                            <i class="fas fa-tags"></i>
                            دسته‌بندی‌ها
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="inventory.php" class="nav-link">
                            <i class="fas fa-warehouse"></i>
                            انبارداری
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="#salesSubmenu" class="nav-link" data-bs-toggle="collapse">
                <i class="fas fa-shopping-cart"></i>
                فروش
                <i class="fas fa-chevron-down float-start"></i>
            </a>
            <div class="collapse <?php echo strpos($_SERVER['PHP_SELF'], 'sale') !== false || strpos($_SERVER['PHP_SELF'], 'invoice') !== false ? 'show' : ''; ?>" id="salesSubmenu">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="quick-sale.php" class="nav-link">
                            <i class="fas fa-bolt"></i>
                            فروش سریع
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="invoices.php" class="nav-link">
                            <i class="fas fa-file-invoice"></i>
                            فاکتورهای فروش
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="customers.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                مدیریت مشتریان
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#settingsSubmenu" class="nav-link" data-bs-toggle="collapse">
                <i class="fas fa-cog"></i>
                تنظیمات
                <i class="fas fa-chevron-down float-start"></i>
            </a>
            <div class="collapse <?php echo strpos($_SERVER['PHP_SELF'], 'settings') !== false || strpos($_SERVER['PHP_SELF'], 'backup') !== false ? 'show' : ''; ?>" id="settingsSubmenu">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link">
                            <i class="fas fa-sliders-h"></i>
                            تنظیمات عمومی
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="backup.php" class="nav-link">
                            <i class="fas fa-database"></i>
                            پشتیبان‌گیری
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="update.php" class="nav-link">
                            <i class="fas fa-sync"></i>
                            بروزرسانی
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="#profileSubmenu" class="nav-link" data-bs-toggle="collapse">
                <i class="fas fa-user"></i>
                پروفایل
                <i class="fas fa-chevron-down float-start"></i>
            </a>
            <div class="collapse" id="profileSubmenu">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <i class="fas fa-id-card"></i>
                            مشاهده پروفایل
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="change-password.php" class="nav-link">
                            <i class="fas fa-key"></i>
                            تغییر رمز عبور
                        </a>
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
