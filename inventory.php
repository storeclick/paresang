<?php
require_once 'includes/init.php';
require_once 'includes/jdf.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست محصولات
$products = $db->query("SELECT * FROM products")->fetchAll();

// دریافت تراکنش‌های انبار
$transactions = $db->query("SELECT it.*, p.name as product_name FROM inventory_transactions it LEFT JOIN products p ON it.product_id = p.id ORDER BY it.created_at DESC")->fetchAll();

// اگر درخواست افزودن یا حذف محصول باشد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['transaction'])) {
    $productId = $_POST['product_id'];
    $type = $_POST['type'];
    $quantity = $_POST['quantity'];
    $createdBy = $_SESSION['user_id'];

    // ثبت تراکنش انبار
    $db->insert('inventory_transactions', [
        'product_id' => $productId,
        'type' => $type,
        'quantity' => $quantity,
        'created_by' => $createdBy
    ]);

    // بروزرسانی موجودی محصول
    if ($type == 'in') {
        $db->query("UPDATE products SET quantity = quantity + ? WHERE id = ?", [$quantity, $productId]);
    } else {
        $db->query("UPDATE products SET quantity = quantity - ? WHERE id = ?", [$quantity, $productId]);
    }

    flashMessage('تراکنش با موفقیت ثبت شد', 'success');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.css">
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
                                <h4 class="card-title">انبارداری</h4>
                                <p class="card-category">مدیریت موجودی انبار</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>ثبت تراکنش انبار</h5>
                                        <form method="post" action="">
                                            <div class="form-group">
                                                <label for="product_id">محصول</label>
                                                <select name="product_id" id="product_id" class="form-select select2">
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label for="type">نوع تراکنش</label>
                                                <select name="type" id="type" class="form-select">
                                                    <option value="in">افزودن به انبار</option>
                                                    <option value="out">حذف از انبار</option>
                                                </select>
                                            </div>
                                            <div class="form-group mt-3">
                                                <label for="quantity">تعداد</label>
                                                <input type="number" name="quantity" id="quantity" class="form-control" required>
                                            </div>
                                            <button type="submit" name="transaction" class="btn btn-primary mt-3">ثبت تراکنش</button>
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
                                <hr>
                                <h4>تاریخچه تراکنش‌های انبار</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>محصول</th>
                                            <th>نوع تراکنش</th>
                                            <th>تعداد</th>
                                            <th>زمان</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo $transaction['product_name']; ?></td>
                                                <td><?php echo $transaction['type'] == 'in' ? 'افزودن به انبار' : 'حذف از انبار'; ?></td>
                                                <td><?php echo $transaction['quantity']; ?></td>
                                                <td><?php echo jDate::jdate('Y/m/d H:i:s', strtotime($transaction['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <hr>
                                <h4>نمودار موجودی انبار</h4>
                                <canvas id="inventoryChart"></canvas>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
    $(document).ready(function() {
        // فعال‌سازی select2 برای انتخاب محصول
        $('.select2').select2();

        // داده‌های نمودار موجودی انبار
        var inventoryData = {
            labels: [<?php foreach ($products as $product) { echo '"' . $product['name'] . '",'; } ?>],
            datasets: [{
                label: 'موجودی',
                data: [<?php foreach ($products as $product) { echo $product['quantity'] . ','; } ?>],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        };

        // تنظیمات نمودار موجودی انبار
        var inventoryChartOptions = {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };

        // رسم نمودار موجودی انبار
        var ctx = document.getElementById('inventoryChart').getContext('2d');
        var inventoryChart = new Chart(ctx, {
            type: 'bar',
            data: inventoryData,
            options: inventoryChartOptions
        });
    });
    </script>
</body>
</html>