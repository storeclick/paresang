<?php
require_once 'includes/init.php';

// بررسی لاگین بودن کاربر
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$db = Database::getInstance();

// فیلترها و جستجو
$search = sanitize($_GET['search'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'id_desc';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;

// ساخت کوئری
$params = [];
$where = ['1=1'];

if ($search) {
    $where[] = "(name LIKE ? OR code LIKE ? OR description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($category) {
    $where[] = "category_id = ?";
    $params[] = $category;
}

if ($status !== 'all') {
    $where[] = "status = ?";
    $params[] = $status;
}

$where = implode(' AND ', $where);

// مرتب‌سازی
$order_by = match($sort) {
    'name_asc' => 'name ASC',
    'name_desc' => 'name DESC',
    'price_asc' => 'sale_price ASC',
    'price_desc' => 'sale_price DESC',
    'stock_asc' => 'quantity ASC',
    'stock_desc' => 'quantity DESC',
    'id_asc' => 'id ASC',
    default => 'id DESC'
};

// تعداد کل رکوردها
$total = $db->query(
    "SELECT COUNT(*) as total FROM products WHERE {$where}",
    $params
)->fetch()['total'];

// دریافت رکوردها
$offset = ($page - 1) * $per_page;
$products = $db->query(
    "SELECT p.*, c.name as category_name,
    (SELECT COUNT(*) FROM invoice_items WHERE product_id = p.id) as total_sales
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE {$where}
    ORDER BY {$order_by}
    LIMIT {$per_page} OFFSET {$offset}",
    $params
)->fetchAll();

// دریافت دسته‌بندی‌ها
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت محصولات - <?php echo SITE_NAME; ?></title>
    <link href="assets/css/fontiran.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/products.css">
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
                <div class="row align-items-center g-4 mb-4">
                    <div class="col">
                        <h4 class="mb-0">مدیریت محصولات</h4>
                    </div>
                    <div class="col-auto">
                        <a href="product-add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            افزودن محصول جدید
                        </a>
                    </div>
                </div>

                <!-- فیلترها -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control" placeholder="جستجو..." value="<?php echo $search; ?>">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="0">همه دسته‌بندی‌ها</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>همه وضعیت‌ها</option>
                                    <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>فعال</option>
                                    <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>غیرفعال</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <select name="sort" class="form-select">
                                    <option value="id_desc" <?php echo $sort == 'id_desc' ? 'selected' : ''; ?>>جدیدترین</option>
                                    <option value="id_asc" <?php echo $sort == 'id_asc' ? 'selected' : ''; ?>>قدیمی‌ترین</option>
                                    <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>نام (صعودی)</option>
                                    <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>نام (نزولی)</option>
                                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>قیمت (کم به زیاد)</option>
                                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>قیمت (زیاد به کم)</option>
                                    <option value="stock_asc" <?php echo $sort == 'stock_asc' ? 'selected' : ''; ?>>موجودی (کم به زیاد)</option>
                                    <option value="stock_desc" <?php echo $sort == 'stock_desc' ? 'selected' : ''; ?>>موجودی (زیاد به کم)</option>
                                </select>
                            </div>

                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- لیست محصولات -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="80">تصویر</th>
                                    <th>نام محصول</th>
                                    <th>کد</th>
                                    <th>دسته‌بندی</th>
                                    <th>قیمت فروش</th>
                                    <th>موجودی</th>
                                    <th>تعداد فروش</th>
                                    <th>وضعیت</th>
                                    <th width="150">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo !empty($product['image']) ? $product['image'] : 'assets/images/no-image.png'; ?>"
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-thumbnail">
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($product['name']); ?>
                                        <?php if ($product['quantity'] <= $product['min_quantity']): ?>
                                            <span class="badge bg-warning" data-bs-toggle="tooltip" title="کم موجود">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['code']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                                    <td><?php echo number_format($product['sale_price']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $product['quantity'] <= $product['min_quantity'] ? 'bg-warning' : 'bg-success'; ?>">
                                            <?php echo number_format($product['quantity']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($product['total_sales']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $product['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $product['status'] == 'active' ? 'فعال' : 'غیرفعال'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-info" 
                                               data-bs-toggle="tooltip" 
                                               title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="inventory.php?product_id=<?php echo $product['id']; ?>" 
                                               class="btn btn-sm btn-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="موجودی">
                                                <i class="fas fa-box"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-product" 
                                                    data-id="<?php echo $product['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                    data-bs-toggle="tooltip" 
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-box fa-3x mb-3"></i>
                                            <p>هیچ محصولی یافت نشد!</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- صفحه‌بندی -->
                <?php if ($total > $per_page): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php
                    $total_pages = ceil($total / $per_page);
                    $url = '?' . http_build_query(array_merge($_GET, ['page' => '']));
                    echo createPagination($total, $per_page, $page, $url);
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- مودال حذف محصول -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">حذف محصول</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>آیا از حذف محصول <strong id="productName"></strong> اطمینان دارید؟</p>
                    <p class="text-danger small">این عملیات قابل بازگشت نیست!</p>
                </div>
                <div class="modal-footer">
                    <form action="ajax/delete-product.php" method="POST">
                        <input type="hidden" name="product_id" id="productId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // فعال‌سازی tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // مدیریت حذف محصول
    $('.delete-product').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#productId').val(id);
        $('#productName').text(name);
        $('#deleteModal').modal('show');
    });
    </script>
</body>
</html>