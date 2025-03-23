<?php
require_once 'includes/init.php';

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>پروفایل</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/material-dashboard.css">
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
                                    <h4 class="card-title">پروفایل شما</h4>
                                    <p class="card-category">اطلاعات کاربری</p>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">نام شرکت</label>
                                                    <input type="text" class="form-control" disabled value="شرکت شما">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">نام کاربری</label>
                                                    <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($user['username']); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">ایمیل</label>
                                                    <input type="email" class="form-control" disabled value="<?php echo htmlspecialchars($user['email']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">نام</label>
                                                    <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="bmd-label-floating">نام خانوادگی</label>
                                                    <input type="text" class="form-control" disabled value="<?php echo htmlspecialchars($user['last_name']); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary pull-right">بروزرسانی پروفایل</button>
                                        <div class="clearfix"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-profile">
                                <div class="card-avatar">
                                    <a href="#">
                                        <img class="img" src="<?php echo empty($user['avatar']) ? 'assets/images/default-avatar.png' : $user['avatar']; ?>" />
                                    </a>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-category text-gray"><?php echo htmlspecialchars($user['role']); ?></h6>
                                    <h4 class="card-title"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                                    <p class="card-description">
                                        توضیحاتی درباره کاربر
                                    </p>
                                    <a href="#pablo" class="btn btn-primary btn-round">دنبال کردن</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/material-dashboard.js"></script>
</body>
</html>