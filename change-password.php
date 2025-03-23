<?php
require_once 'includes/init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        if ($auth->updatePassword($_SESSION['user_id'], $newPassword)) {
            flashMessage('رمز عبور با موفقیت تغییر یافت', 'success');
        } else {
            flashMessage('خطایی در تغییر رمز عبور رخ داد', 'danger');
        }
    } else {
        flashMessage('رمزهای عبور جدید مطابقت ندارند', 'danger');
    }
}

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تغییر رمز عبور</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/material-dashboard.css" rel="stylesheet">
</head>
<body class="rtl">
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-panel">
            <?php include 'includes/navbar.php'; ?>
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header card-header-primary">
                                    <h4 class="card-title">تغییر رمز عبور</h4>
                                    <p class="card-category">برای تغییر رمز عبور، فرم زیر را پر کنید</p>
                                </div>
                                <div class="card-body">
                                    <?php echo showFlashMessage(); ?>
                                    <form method="post" action="">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">رمز عبور فعلی</label>
                                                    <input type="password" name="current_password" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">رمز عبور جدید</label>
                                                    <input type="password" name="new_password" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">تکرار رمز عبور جدید</label>
                                                    <input type="password" name="confirm_password" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary pull-right">تغییر رمز عبور</button>
                                        <div class="clearfix"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/material-dashboard.js"></script>
</body>
</html>