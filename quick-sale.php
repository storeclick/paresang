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

// دریافت لیست مشتریان
$customers = $db->query("SELECT * FROM customers")->fetchAll();

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.css">
    <link rel="stylesheet" href="assets/css/quick-sale.css">
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
                                <h4 class="card-title">فروش سریع</h4>
                                <p class="card-category">مدیریت فروش سریع و صدور فاکتور</p>
                            </div>
                            <div class="card-body">
                                <form id="quick-sale-form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="product">محصول</label>
                                                <select name="product" id="product" class="form-select select2">
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="customer">مشتری</label>
                                                <select name="customer" id="customer" class="form-select select2">
                                                    <?php foreach ($customers as $customer): ?>
                                                        <option value="<?php echo $customer['id']; ?>"><?php echo $customer['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <a href="customers.php" class="btn btn-link">مدیریت مشتریان</a> <!-- لینک به صفحه مدیریت مشتریان -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="quantity">تعداد</label>
                                                <input type="number" name="quantity" id="quantity" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="payment-method">روش پرداخت</label>
                                                <select name="payment-method" id="payment-method" class="form-select">
                                                    <option value="cash">نقدی</option>
                                                    <option value="card">کارت بانکی</option>
                                                    <option value="cheque">چک</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="button" id="generate-invoice" class="btn btn-primary">صدور فاکتور</button>
                                            <button type="button" id="print-invoice" class="btn btn-secondary">چاپ فاکتور</button>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                                <h4>لیست فاکتورها</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>شماره فاکتور</th>
                                            <th>مشتری</th>
                                            <th>محصول</th>
                                            <th>تعداد</th>
                                            <th>قیمت کل</th>
                                            <th>تاریخ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoice-list">
                                        <!-- لیست فاکتورها به صورت زنده بروزرسانی می‌شود -->
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/quick-sale.js"></script>
    <script>
    $(document).ready(function() {
        // فعال‌سازی select2 برای انتخاب محصول و مشتری
        $('.select2').select2();

        // صدور فاکتور
        $('#generate-invoice').on('click', function() {
            // دریافت اطلاعات فرم
            var product = $('#product').val();
            var customer = $('#customer').val();
            var quantity = $('#quantity').val();
            var paymentMethod = $('#payment-method').val();

            // محاسبه قیمت کل (مثال)
            var totalPrice = quantity * 10000; // قیمت هر محصول فرضی: 10000 تومان

            // ایجاد فاکتور جدید
            var invoice = {
                id: Date.now(),
                product: $('#product option:selected').text(),
                customer: $('#customer option:selected').text(),
                quantity: quantity,
                totalPrice: totalPrice,
                date: new Date().toLocaleString('fa-IR')
            };

            // افزودن فاکتور به لیست
            var invoiceRow = '<tr>' +
                '<td>' + invoice.id + '</td>' +
                '<td>' + invoice.customer + '</td>' +
                '<td>' + invoice.product + '</td>' +
                '<td>' + invoice.quantity + '</td>' +
                '<td>' + invoice.totalPrice + '</td>' +
                '<td>' + invoice.date + '</td>' +
                '</tr>';
            $('#invoice-list').append(invoiceRow);
        });

        // چاپ فاکتور
        $('#print-invoice').on('click', function() {
            // اینجا کدهای مربوط به چاپ فاکتور قرار می‌گیرد
            window.print();
        });
    });
    </script>
</body>
</html>