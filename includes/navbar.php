<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <button type="button" class="btn btn-link sidebar-toggle d-lg-none">
            <i class="fas fa-bars"></i>
        </button>
        
        <form class="d-none d-md-flex">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="جستجو...">
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
        
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <?php if ($lowStock > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $lowStock; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <h6 class="dropdown-header">اعلان‌ها</h6>
                    <?php if ($lowStock > 0): ?>
                        <a class="dropdown-item" href="inventory.php">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <?php echo $lowStock; ?> محصول کم موجود
                        </a>
                    <?php else: ?>
                        <div class="dropdown-item text-center">
                            اعلان جدیدی ندارید
                        </div>
                    <?php endif; ?>
                </div>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="<?php echo empty($user['avatar']) ? 'assets/images/default-avatar.png' : $user['avatar']; ?>" 
                         alt="<?php echo htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                         class="rounded-circle" 
                         width="32">
                    <span class="d-none d-lg-inline ms-2">
                        <?php echo htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user fa-fw me-2"></i>
                        پروفایل
                    </a>
                    <a class="dropdown-item" href="settings.php">
                        <i class="fas fa-cog fa-fw me-2"></i>
                        تنظیمات
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">
                        <i class="fas fa-sign-out-alt fa-fw me-2"></i>
                        خروج
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>