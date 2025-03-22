<?php
require_once 'includes/header.php';

// اگر کاربر لاگین کرده است، به داشبورد هدایت شود
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h4>ورود به سیستم حسابداری پاره سنگ</h4>
                </div>
                <div class="card-body">
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">رمز عبور</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">ورود</button>
                            <a href="register.php" class="btn btn-secondary">ثبت نام</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>