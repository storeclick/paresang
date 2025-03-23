<?php
require_once 'includes/init.php';

// بررسی وضعیت لاگین کاربر
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// دریافت لیست مشتریان با اطلاعات بیشتر
$customers = $db->query("
    SELECT c.*, 
           COUNT(i.id) as total_invoices,
           COALESCE(SUM(i.final_amount), 0) as total_purchases
    FROM customers c
    LEFT JOIN invoices i ON c.id = i.customer_id
    WHERE c.deleted_at IS NULL
    GROUP BY c.id
    ORDER BY c.name ASC
")->fetchAll();

// دریافت تنظیمات پیش‌فرض فاکتور
$settings = $db->query("SELECT * FROM settings WHERE category = 'invoice' LIMIT 1")->fetch();
$default_tax_rate = $settings['default_tax_rate'] ?? 9;

// اگر درخواست ایجاد فاکتور باشد
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        $customerId = $_POST['customer_id'];
        $invoiceItems = $_POST['items'];
        $totalAmount = 0;
        $totalQuantity = 0;

        // محاسبه مجموع
        foreach ($invoiceItems as $item) {
            if (!empty($item['product_id']) && !empty($item['quantity']) && !empty($item['price'])) {
                $totalAmount += $item['quantity'] * $item['price'];
                $totalQuantity += $item['quantity'];
            }
        }

        // محاسبه مالیات و تخفیف
        $taxRate = $_POST['tax_rate'];
        $discountAmount = $_POST['discount_amount'];
        $discountDescription = $_POST['discount_description'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $finalAmount = $totalAmount + ($totalAmount * $taxRate / 100) - $discountAmount;

        // ایجاد فاکتور
        $invoiceData = [
            'customer_id' => $customerId,
            'invoice_number' => generateInvoiceNumber(), // تابع کمکی برای تولید شماره فاکتور
            'total_amount' => $totalAmount,
            'tax_rate' => $taxRate,
            'tax_amount' => $totalAmount * $taxRate / 100,
            'discount_amount' => $discountAmount,
            'discount_description' => $discountDescription,
            'final_amount' => $finalAmount,
            'total_quantity' => $totalQuantity,
            'notes' => $notes,
            'status' => 'draft',
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->insert('invoices', $invoiceData);
        $invoiceId = $db->lastInsertId();

        // افزودن آیتم‌های فاکتور
        foreach ($invoiceItems as $item) {
            if (!empty($item['product_id']) && !empty($item['quantity']) && !empty($item['price'])) {
                // بررسی موجودی
                $product = $db->query("SELECT quantity, name FROM products WHERE id = ?", [$item['product_id']])->fetch();
                
                if ($product['quantity'] < $item['quantity']) {
                    throw new Exception("موجودی محصول {$product['name']} کافی نیست.");
                }

                $db->insert('invoice_items', [
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'tax_amount' => ($item['price'] * $item['quantity']) * ($taxRate / 100),
                    'total_amount' => $item['quantity'] * $item['price']
                ]);

                // بروزرسانی موجودی محصول
                $db->query(
                    "UPDATE products SET quantity = quantity - ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }
        }

        $db->commit();
        flashMessage('فاکتور با موفقیت ایجاد شد', 'success');
        header('Location: invoice-view.php?id=' . $invoiceId);
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        flashMessage($e->getMessage(), 'error');
    }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
            padding: 0.375rem 0.75rem;
        }
        .search-results {
            max-height: 300px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1050;
        }
        .product-search.loading {
            background-image: url('assets/img/loading.gif');
            background-repeat: no-repeat;
            background-position: left 10px center;
            background-size: 20px;
        }
        .invoice-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .invoice-item:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-info {
            background: #fff;
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin-top: 0.5rem;
            border: 1px solid #e9ecef;
        }
        .remove-item {
            transition: all 0.2s ease;
        }
        .remove-item:hover {
            transform: scale(1.1);
        }
        .totals-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-top: 2rem;
        }
        .totals-section .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #198754;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .card-header {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-bottom: none;
        }
    </style>
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
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="card-title mb-0">ایجاد فاکتور جدید</h4>
                                        <small class="text-white-50">همه مبالغ به تومان می‌باشد</small>
                                    </div>
                                    <a href="invoices-list.php" class="btn btn-light">
                                        <i class="fas fa-list me-1"></i>
                                        لیست فاکتورها
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="post" id="invoice-form" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="customer_id">انتخاب مشتری</label>
                                                <select name="customer_id" id="customer_id" class="form-select select2" required>
                                                    <option value="">انتخاب کنید</option>
                                                    <?php foreach ($customers as $customer): ?>
                                                        <option value="<?php echo $customer['id']; ?>" data-info="<?php echo htmlspecialchars(json_encode([
                                                            'total_invoices' => $customer['total_invoices'],
                                                            'total_purchases' => number_format($customer['total_purchases'])
                                                        ])); ?>">
                                                            <?php echo htmlspecialchars($customer['name']); ?>
                                                            <?php if (!empty($customer['company'])): ?>
                                                                (<?php echo htmlspecialchars($customer['company']); ?>)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div id="customer-info" class="mt-2 small text-muted d-none">
                                                    <div class="alert alert-info py-2">
                                                        <div class="d-flex justify-content-between">
                                                            <span>تعداد فاکتورها:</span>
                                                            <strong id="customer-invoices">0</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>مجموع خرید:</span>
                                                            <strong id="customer-purchases">0 تومان</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label" for="tax_rate">نرخ مالیات (%)</label>
                                                        <input type="number" name="tax_rate" id="tax_rate" 
                                                               class="form-control" min="0" max="100" 
                                                               value="<?php echo $default_tax_rate; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label" for="discount_amount">مبلغ تخفیف</label>
                                                        <input type="number" name="discount_amount" id="discount_amount" 
                                                               class="form-control" min="0" value="0" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="form-label" for="discount_description">توضیحات تخفیف</label>
                                                <input type="text" name="discount_description" id="discount_description" 
                                                       class="form-control" placeholder="مثال: تخفیف عید">
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">آیتم‌های فاکتور</h5>
                                        <button type="button" id="add-item" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i>
                                            افزودن آیتم
                                        </button>
                                    </div>

                                    <div id="invoice-items">
                                        <!-- آیتم‌های فاکتور اینجا اضافه می‌شوند -->
                                    </div>

                                    <div class="totals-section">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="notes">یادداشت</label>
                                                    <textarea name="notes" id="notes" class="form-control" 
                                                              placeholder="هر گونه توضیحات اضافی..." rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>جمع کل:</span>
                                                            <strong id="subtotal">0 تومان</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>مالیات:</span>
                                                            <strong id="tax-amount">0 تومان</strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>تخفیف:</span>
                                                            <strong id="discount">0 تومان</strong>
                                                        </div>
                                                        <hr>
                                                        <div class="d-flex justify-content-between">
                                                            <span>مبلغ نهایی:</span>
                                                            <strong class="total-amount" id="final-amount">0 تومان</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end mt-4">
                                        <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back()">
                                            <i class="fas fa-times me-1"></i>
                                            انصراف
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-1"></i>
                                            ثبت فاکتور
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/dashboard.js"></script>
    <script>
        $(document).ready(function() {
            let itemIndex = 0;
            let searchTimer;
            const itemTemplate = `
                <div class="invoice-item">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="position-relative">
                                <input type="text" name="items[{index}][product_search]" 
                                       class="form-control product-search" 
                                       placeholder="نام یا کد محصول را وارد کنید..." required>
                                <input type="hidden" name="items[{index}][product_id]" 
                                       class="product-id" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[{index}][quantity]" 
                                   class="form-control quantity-input" 
                                   placeholder="تعداد" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="number" name="items[{index}][price]" 
                                       class="form-control price-input" 
                                       placeholder="قیمت واحد" required>
                                <span class="input-group-text">تومان</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger remove-item w-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="col-12 product-info d-none">
                            <div class="row">
                                <div class="col-auto">
                                    <small class="text-muted stock-info"></small>
                                </div>
                                <div class="col text-end">
                                    <small class="text-muted row-total"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // فعال‌سازی Select2 برای انتخاب مشتری
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'مشتری را انتخاب کنید',
                language: {
                    noResults: function() {
                        return "نتیجه‌ای یافت نشد";
                    }
                }
            });

            // نمایش اطلاعات مشتری
            $('#customer_id').on('change', function() {
                const $selected = $(this).find(':selected');
                const $info = $('#customer-info');
                
                if ($selected.val()) {
                    const customerInfo = $selected.data('info');
                    $('#customer-invoices').text(customerInfo.total_invoices);
                    $('#customer-purchases').text(customerInfo.total_purchases + ' تومان');
                    $info.removeClass('d-none');
                } else {
                    $info.addClass('d-none');
                }
            });

            // افزودن آیتم جدید
            $('#add-item').click(function() {
                const newItem = itemTemplate.replace(/{index}/g, itemIndex++);
                $('#invoice-items').append(newItem);
                $('#invoice-items .product-search').last().focus();
            });

            // اضافه کردن اولین آیتم به صورت خودکار
            if ($('#invoice-items').is(':empty')) {
                $('#add-item').click();
            }

            // حذف آیتم
            $(document).on('click', '.remove-item', function() {
                const $item = $(this).closest('.invoice-item');
                
                if ($('#invoice-items .invoice-item').length > 1) {
                    $item.fadeOut(300, function() {
                        $(this).remove();
                        updateTotals();
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'خطا',
                        text: 'حداقل یک آیتم باید در فاکتور وجود داشته باشد',
                        confirmButtonText: 'باشه'
                    });
                }
            });

            // جستجوی محصول
            $(document).on('input', '.product-search', function() {
                const $input = $(this);
                const $item = $input.closest('.invoice-item');
                
                clearTimeout(searchTimer);
                $('.search-results').remove();
                
                const query = $input.val().trim();
                if (query.length < 2) return;

                searchTimer = setTimeout(() => {
                    $input.addClass('loading');
                    
                    $.ajax({
                        url: 'ajax/search-products.php',
                        method: 'GET',
                        data: { query: query },
                        success: function(response) {
                            $('.search-results').remove();
                            
                            if (response.length > 0) {
                                const $results = $('<div class="search-results position-absolute w-100 mt-1"></div>');
                                const $list = $('<div class="list-group list-group-flush"></div>');
                                
                                response.forEach(product => {
                                    $list.append(`
                                        <button type="button" 
                                                class="list-group-item list-group-item-action product-item" 
                                                data-id="${product.id}"
                                                data-name="${product.name}"
                                                data-price="${product.price}"
                                                data-stock="${product.stock}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>${product.name}</strong>
                                                <span class="text-primary">${numberFormat(product.price)} تومان</span>
                                            </div>
                                            <small class="text-muted d-block">
                                                کد: ${product.code} | موجودی: ${product.stock} عدد
                                            </small>
                                        </button>
                                    `);
                                });
                                
                                $results.append($list);
                                $input.after($results);
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا',
                                text: 'خطا در جستجوی محصولات',
                                confirmButtonText: 'باشه'
                            });
                        },
                        complete: function() {
                            $input.removeClass('loading');
                        }
                    });
                }, 300);
            });

            // انتخاب محصول از نتایج جستجو
            $(document).on('click', '.product-item', function(e) {
                e.preventDefault();
                const $button = $(this);
                const $item = $button.closest('.invoice-item');
                const $search = $item.find('.product-search');
                const $productId = $item.find('.product-id');
                const $price = $item.find('.price-input');
                const $quantity = $item.find('.quantity-input');
                const $productInfo = $item.find('.product-info');
                const $stockInfo = $productInfo.find('.stock-info');
                
                // پر کردن اطلاعات محصول
                $search.val($button.data('name'));
                $productId.val($button.data('id'));
                $price.val($button.data('price'));
                
                // نمایش اطلاعات محصول
                $stockInfo.html(`
                    <i class="fas fa-boxes me-1"></i>
                    موجودی: ${$button.data('stock')} عدد
                `);
                
                $productInfo.removeClass('d-none');
                $('.search-results').remove();
                
                // فوکوس روی فیلد تعداد
                $quantity.focus();
                
                updateRowTotal($item);
            });

            // به‌روزرسانی جمع ردیف
            function updateRowTotal($item) {
                const quantity = parseFloat($item.find('.quantity-input').val()) || 0;
                const price = parseFloat($item.find('.price-input').val()) || 0;
                const total = quantity * price;
                
                $item.find('.row-total').html(`
                    <i class="fas fa-calculator me-1"></i>
                    جمع: ${numberFormat(total)} تومان
                `);
                
                updateTotals();
            }

            // به‌روزرسانی مبالغ کل
            function updateTotals() {
                let subtotal = 0;
                
                $('.invoice-item').each(function() {
                    const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                    const price = parseFloat($(this).find('.price-input').val()) || 0;
                    subtotal += quantity * price;
                });
                
                const taxRate = parseFloat($('#tax_rate').val()) || 0;
                const discountAmount = parseFloat($('#discount_amount').val()) || 0;
                
                const taxAmount = subtotal * (taxRate / 100);
                const finalAmount = subtotal + taxAmount - discountAmount;
                
                $('#subtotal').text(numberFormat(subtotal) + ' تومان');
                $('#tax-amount').text(numberFormat(taxAmount) + ' تومان');
                $('#discount').text(numberFormat(discountAmount) + ' تومان');
                $('#final-amount').text(numberFormat(finalAmount) + ' تومان');
            }

            // به‌روزرسانی محاسبات در تغییر مقادیر
            $(document).on('input', '.quantity-input, .price-input', function() {
                updateRowTotal($(this).closest('.invoice-item'));
            });

            $('#tax_rate, #discount_amount').on('input', updateTotals);

            // تبدیل اعداد به فرمت فارسی با جداکننده هزارگان
            function numberFormat(num) {
                return new Intl.NumberFormat('fa-IR').format(num);
            }

            // بستن نتایج جستجو با کلیک خارج از آن
            $(document).click(function(e) {
                if (!$(e.target).closest('.invoice-item').length) {
                    $('.search-results').remove();
                }
            });

            // اعتبارسنجی فرم قبل از ارسال
            $('#invoice-form').on('submit', function(e) {
                e.preventDefault();
                
                // بررسی انتخاب مشتری
                if (!$('#customer_id').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: 'لطفاً مشتری را انتخاب کنید',
                        confirmButtonText: 'باشه'
                    });
                    return;
                }

                // بررسی آیتم‌های فاکتور
                let isValid = true;
                $('.invoice-item').each(function() {
                    const $item = $(this);
                    if (!$item.find('.product-id').val() || 
                        !$item.find('.quantity-input').val() || 
                        !$item.find('.price-input').val()) {
                        isValid = false;
                        return false;
                    }
                });

                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text: 'لطفاً تمام موارد مربوط به آیتم‌های فاکتور را تکمیل کنید',
                        confirmButtonText: 'باشه'
                    });
                    return;
                }

                // تأیید نهایی
                Swal.fire({
                    title: 'تأیید ثبت فاکتور',
                    text: 'آیا از صحت اطلاعات وارد شده اطمینان دارید؟',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'بله، ثبت شود',
                    cancelButtonText: 'خیر'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    </script>
</body>
</html>