<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست محصولات
$products = $db->query("SELECT * FROM products")->fetchAll();

// اگر درخواست افزودن محصول باشد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // افزودن محصول به انبار
    $db->insert('inventory_transactions', [
        'product_id' => $productId,
        'type' => 'in',
        'quantity' => $quantity,
        'created_by' => $_SESSION['user_id']
    ]);

    flashMessage('محصول با موفقیت به انبار اضافه شد', 'success');
    header('Location: inventory.php');
    exit;
}

// اگر درخواست حذف محصول باشد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // حذف محصول از انبار
    $db->insert('inventory_transactions', [
        'product_id' => $productId,
        'type' => 'out',
        'quantity' => $quantity,
        'created_by' => $_SESSION['user_id']
    ]);

    flashMessage('محصول با موفقیت از انبار حذف شد', 'success');
    header('Location: inventory.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انبارداری - <?php echo SITE_NAME; ?></title>
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
                                <h4 class="card-title">انبارداری</h4>
                                <p class="card-category">مدیریت موجودی انبار</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>افزودن محصول به انبار</h5>
                                        <form method="post" action="">
                                            <div class="form-group">
                                                <label for="product_id">محصول</label>
                                                <select name="product_id" id="product_id" class="form-control">
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="quantity">تعداد</label>
                                                <input type="number" name="quantity" id="quantity" class="form-control" required>
                                            </div>
                                            <button type="submit" name="add_product" class="btn btn-primary mt-3">افزودن</button>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>حذف محصول از انبار</h5>
                                        <form method="post" action="">
                                            <div class="form-group">
                                                <label for="product_id">محصول</label>
                                                <select name="product_id" id="product_id" class="form-control">
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="quantity">تعداد</label>
                                                <input type="number" name="quantity" id="quantity" class="form-control" required>
                                            </div>
                                            <button type="submit" name="remove_product" class="btn btn-danger mt-3">حذف</button>
                                        </form>
                                    </div>
                                </div>
                                <hr>
                                <h4>موجودی انبار</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>کد محصول</th>
                                            <th>نام محصول</th>
                                            <th>تعداد موجود</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td><?php echo $product['code']; ?></td>
                                                <td><?php echo $product['name']; ?></td>
                                                <td><?php echo $product['quantity']; ?></td>
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
    <script src="assets/js/dashboard.js"></script>
</body>
</html>