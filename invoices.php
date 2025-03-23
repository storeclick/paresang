<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست محصولات و مشتریان
$products = $db->query("SELECT * FROM products")->fetchAll();
$customers = $db->query("SELECT * FROM customers")->fetchAll();

// اگر درخواست ایجاد فاکتور باشد
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerId = $_POST['customer_id'];
    $invoiceItems = $_POST['items'];
    $totalAmount = 0;

    foreach ($invoiceItems as $item) {
        $totalAmount += $item['quantity'] * $item['price'];
    }

    // محاسبه مالیات و تخفیف
    $taxRate = $_POST['tax_rate'];
    $discountAmount = $_POST['discount_amount'];
    $finalAmount = $totalAmount + ($totalAmount * $taxRate / 100) - $discountAmount;

    // ایجاد فاکتور
    $db->insert('invoices', [
        'customer_id' => $customerId,
        'total_amount' => $totalAmount,
        'tax_rate' => $taxRate,
        'discount_amount' => $discountAmount,
        'final_amount' => $finalAmount,
        'created_by' => $_SESSION['user_id']
    ]);

    $invoiceId = $db->lastInsertId();

    // افزودن آیتم‌های فاکتور
    foreach ($invoiceItems as $item) {
        $db->insert('invoice_items', [
            'invoice_id' => $invoiceId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'total_amount' => $item['quantity'] * $item['price']
        ]);
    }

    flashMessage('فاکتور با موفقیت ایجاد شد', 'success');
    header('Location: invoices.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ایجاد فاکتور - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .form-group label {
            font-weight: bold;
        }
        .invoice-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .invoice-item input {
            max-width: 150px;
        }
        .invoice-item .remove-item {
            color: red;
            cursor: pointer;
        }
    </style>
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
                                <h4 class="card-title">ایجاد فاکتور</h4>
                                <p class="card-category">مدیریت فاکتورها</p>
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="customer_id">انتخاب مشتری</label>
                                        <select name="customer_id" id="customer_id" class="form-control">
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?php echo $customer['id']; ?>"><?php echo $customer['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tax_rate">نرخ مالیات (%)</label>
                                        <input type="number" name="tax_rate" id="tax_rate" class="form-control" value="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="discount_amount">مقدار تخفیف (تومان)</label>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" value="0" required>
                                    </div>
                                    <hr>
                                    <h5>افزودن آیتم‌ها به فاکتور</h5>
                                    <div id="invoice-items">
                                        <div class="invoice-item">
                                            <select name="items[0][product_id]" class="form-control">
                                                <?php foreach ($products as $product): ?>
                                                    <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="number" name="items[0][quantity]" class="form-control" placeholder="تعداد" required>
                                            <input type="number" name="items[0][price]" class="form-control" placeholder="قیمت" required>
                                            <span class="remove-item">&times;</span>
                                        </div>
                                    </div>
                                    <button type="button" id="add-item" class="btn btn-secondary mt-3">افزودن آیتم جدید</button>
                                    <hr>
                                    <button type="submit" class="btn btn-primary">ایجاد فاکتور</button>
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
    <script>
        $(document).ready(function() {
            let itemIndex = 1;
            $('#add-item').click(function() {
                let newItem = `<div class="invoice-item">
                    <select name="items[${itemIndex}][product_id]" class="form-control">
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="تعداد" required>
                    <input type="number" name="items[${itemIndex}][price]" class="form-control" placeholder="قیمت" required>
                    <span class="remove-item">&times;</span>
                </div>`;
                $('#invoice-items').append(newItem);
                itemIndex++;
            });

            $(document).on('click', '.remove-item', function() {
                $(this).closest('.invoice-item').remove();
            });
        });
    </script>
</body>
</html>