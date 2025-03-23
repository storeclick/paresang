<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست مشتریان
$customers = $db->query("SELECT * FROM customers")->fetchAll();

// افزودن مشتری جدید
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $db->insert('customers', [
        'name' => $name,
        'phone' => $phone,
        'email' => $email
    ]);

    flashMessage('مشتری با موفقیت اضافه شد', 'success');
    header('Location: customers.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت مشتریان - <?php echo SITE_NAME; ?></title>
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
                                <h4 class="card-title">مدیریت مشتریان</h4>
                                <p class="card-category">افزودن و مدیریت مشتریان</p>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">نام مشتری</label>
                                                <input type="text" name="name" id="name" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="phone">تلفن</label>
                                                <input type="text" name="phone" id="phone" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="email">ایمیل</label>
                                                <input type="email" name="email" id="email" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="submit" name="add_customer" class="btn btn-primary">افزودن مشتری</button>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                                <h4>لیست مشتریان</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>تلفن</th>
                                            <th>ایمیل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td><?php echo $customer['name']; ?></td>
                                                <td><?php echo $customer['phone']; ?></td>
                                                <td><?php echo $customer['email']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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