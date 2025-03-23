<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت تنظیمات فعلی
$settings = $db->get('settings', '*', ['id' => 1]);

// ذخیره تنظیمات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $organization_name = $_POST['organization_name'];
    $organization_address = $_POST['organization_address'];
    $organization_phone = $_POST['organization_phone'];
    $organization_email = $_POST['organization_email'];
    $organization_logo = ''; // فرض می‌کنیم که تصویر به صورت URL ذخیره می‌شود

    // آپلود لوگو
    if (isset($_FILES['organization_logo']) && $_FILES['organization_logo']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['organization_logo']['name']);
        if (move_uploaded_file($_FILES['organization_logo']['tmp_name'], $uploadFile)) {
            $organization_logo = $uploadFile;
        } else {
            flashMessage('خطا در آپلود لوگو', 'danger');
        }
    }

    // ذخیره تنظیمات در دیتابیس
    $db->update('settings', [
        'organization_name' => $organization_name,
        'organization_address' => $organization_address,
        'organization_phone' => $organization_phone,
        'organization_email' => $organization_email,
        'organization_logo' => $organization_logo,
    ], ['id' => 1]);

    flashMessage('تنظیمات با موفقیت ذخیره شد', 'success');
    header('Location: settings.php');
    exit;
}

// دریافت تنظیمات فعلی
$settings = $db->get('settings', '*', ['id' => 1]);

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات عمومی - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content w-100">
            <!-- Top Navbar -->
            <?php include 'includes/navbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4">
                <?php echo showFlashMessage(); ?>
                <div class="row g-4 my-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-primary">
                                <h4 class="card-title">تنظیمات عمومی</h4>
                                <p class="card-category">مدیریت تنظیمات عمومی سیستم</p>
                            </div>
                            <div class="card-body">
                                <form method="post" action="" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="organization_name">نام سازمان</label>
                                                <input type="text" name="organization_name" id="organization_name" class="form-control" value="<?php echo htmlspecialchars($settings['organization_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="organization_address">آدرس</label>
                                                <input type="text" name="organization_address" id="organization_address" class="form-control" value="<?php echo htmlspecialchars($settings['organization_address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="organization_phone">شماره تماس</label>
                                                <input type="text" name="organization_phone" id="organization_phone" class="form-control" value="<?php echo htmlspecialchars($settings['organization_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="organization_email">ایمیل</label>
                                                <input type="email" name="organization_email" id="organization_email" class="form-control" value="<?php echo htmlspecialchars($settings['organization_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="organization_logo">لوگو</label>
                                                <input type="file" name="organization_logo" id="organization_logo" class="form-control">
                                                <?php if (!empty($settings['organization_logo'])): ?>
                                                    <img src="<?php echo $settings['organization_logo']; ?>" alt="لوگو سازمان" class="img-thumbnail mt-3" width="100">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary">ذخیره تنظیمات</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>