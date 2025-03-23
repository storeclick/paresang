<?php
require_once 'includes/init.php';

$settings = $db->query("SELECT * FROM settings")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تنظیمات</title>
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
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header card-header-primary">
                                    <h4 class="card-title">تنظیمات</h4>
                                    <p class="card-category">مدیریت تنظیمات عمومی</p>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <?php foreach ($settings as $setting): ?>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="bmd-label-floating"><?php echo htmlspecialchars($setting['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($setting['value'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <button type="submit" class="btn btn-primary pull-right">ذخیره تنظیمات</button>
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