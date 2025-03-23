<?php
require_once 'includes/init.php';

$settings = $db->query("SELECT * FROM settings")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // جمع‌آوری داده‌های فرم
    $companyName = $_POST['company_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $currency = $_POST['currency'];
    $taxRate = $_POST['tax_rate'];
    $discountRate = $_POST['discount_rate'];
    $invoiceTemplate = $_POST['invoice_template'];
    $smtpServer = $_POST['smtp_server'];
    $smtpPort = $_POST['smtp_port'];
    $smtpUsername = $_POST['smtp_username'];
    $smtpPassword = $_POST['smtp_password'];

    // به‌روزرسانی تنظیمات در دیتابیس
    $db->update('settings', [
        'company_name' => $companyName,
        'address' => $address,
        'phone' => $phone,
        'email' => $email,
        'currency' => $currency,
        'tax_rate' => $taxRate,
        'discount_rate' => $discountRate,
        'invoice_template' => $invoiceTemplate,
        'smtp_server' => $smtpServer,
        'smtp_port' => $smtpPort,
        'smtp_username' => $smtpUsername,
        'smtp_password' => $smtpPassword
    ]);

    flashMessage('تنظیمات با موفقیت به‌روزرسانی شد', 'success');
    header('Location: settings.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <?php include 'includes/navbar.php'; ?>

            <!-- Page Content -->
            <div class="container-fluid px-4">
                <?php echo showFlashMessage(); ?>
                <div class="row g-4 my-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header card-header-primary">
                                <h4 class="card-title">تنظیمات</h4>
                                <p class="card-category">مدیریت تنظیمات عمومی</p>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="bmd-label-floating">نام شرکت</label>
                                                <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($settings['company_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">آدرس</label>
                                                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($settings['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">شماره تماس</label>
                                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($settings['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">ایمیل</label>
                                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($settings['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="bmd-label-floating">واحد پولی</label>
                                                <input type="text" name="currency" class="form-control" value="<?php echo htmlspecialchars($settings['currency'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">نرخ مالیات</label>
                                                <input type="text" name="tax_rate" class="form-control" value="<?php echo htmlspecialchars($settings['tax_rate'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">نرخ تخفیف</label>
                                                <input type="text" name="discount_rate" class="form-control" value="<?php echo htmlspecialchars($settings['discount_rate'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">قالب فاکتور</label>
                                                <input type="text" name="invoice_template" class="form-control" value="<?php echo htmlspecialchars($settings['invoice_template'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">سرور SMTP</label>
                                                <input type="text" name="smtp_server" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_server'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">پورت SMTP</label>
                                                <input type="text" name="smtp_port" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">نام کاربری SMTP</label>
                                                <input type="text" name="smtp_username" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label class="bmd-label-floating">رمز عبور SMTP</label>
                                                <input type="password" name="smtp_password" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_password'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary pull-right">ذخیره تنظیمات</button>
                                    <div class="clearfix"></div>
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
    <script src="assets/js/dashboard.js"></script>
</body>
</html>