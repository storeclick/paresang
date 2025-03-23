<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست محصولات
$products = $db->query("SELECT * FROM products")->fetchAll();

// اگر درخواست فروش سریع باشد
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $totalAmount = 0;

    // محاسبه مبلغ کل
    $product = $db->query("SELECT * FROM products WHERE id = ?", [$productId])->fetch();
    $totalAmount = $product['sale_price'] * $quantity;

    // ایجاد فاکتور سریع
    $db->insert('invoices', [
        'customer_name' => 'مشتری سریع',
        'total_amount' => $totalAmount,
        'tax_rate' => 0,
        'discount_amount' => 0,
        'final_amount' => $totalAmount,
        'created_by' => $_SESSION['user_id']
    ]);

    $invoiceId = $db->lastInsertId();

    // افزودن آیتم‌های فاکتور
    $db->insert('invoice_items', [
        'invoice_id' => $invoiceId,
        'product_id' => $productId,
        'quantity' => $quantity,
        'price' => $product['sale_price'],
        'total_amount' => $totalAmount
    ]);

    // بروز رسانی موجودی انبار
    $db->update('products', [
        'quantity' => $product['quantity'] - $quantity
    ], 'id = ' . $productId);

    flashMessage('فروش سریع با موفقیت انجام شد', 'success');
    header('Location: quick-sale.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فروش سریع - <?php echo SITE_NAME; ?></title>
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
                                <h4 class="card-title">فروش سریع</h4>
                                <p class="card-category">مدیریت فروش سریع</p>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="product_id">انتخاب محصول</label>
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
                                    <button type="submit" class="btn btn-primary mt-3">فروش سریع</button>
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