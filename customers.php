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
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $mobile = $_POST['mobile'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $description = $_POST['description'];
    $image = '';

    // آپلود تصویر
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $uploadFile;
        } else {
            flashMessage('خطا در آپلود تصویر', 'danger');
        }
    }

    $errors = [];
    if (empty($first_name)) {
        $errors[] = 'نام الزامی است.';
    }
    if (empty($last_name)) {
        $errors[] = 'نام خانوادگی الزامی است.';
    }
    if (empty($mobile)) {
        $errors[] = 'شماره موبایل الزامی است.';
    }

    if (empty($errors)) {
        $db->insert('customers', [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'mobile' => $mobile,
            'address' => $address,
            'email' => $email,
            'description' => $description,
            'image' => $image
        ]);

        flashMessage('مشتری با موفقیت اضافه شد', 'success');
        header('Location: customers.php');
        exit;
    } else {
        foreach ($errors as $error) {
            flashMessage($error, 'danger');
        }
    }
}

// حذف مشتری
if (isset($_GET['delete'])) {
    $customer_id = $_GET['delete'];
    $db->delete('customers', 'id = ?', [$customer_id]);

    flashMessage('مشتری با موفقیت حذف شد', 'success');
    header('Location: customers.php');
    exit;
}

// دریافت اطلاعات مشتری برای ویرایش
if (isset($_GET['edit'])) {
    $customer_id = $_GET['edit'];
    $customer = $db->get('customers', '*', ['id' => $customer_id]);
}

// ویرایش مشتری
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_customer'])) {
    $customer_id = $_POST['customer_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $mobile = $_POST['mobile'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $description = $_POST['description'];
    $image = $customer['image'];

    // آپلود تصویر جدید در صورت وجود
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $uploadFile;
        } else {
            flashMessage('خطا در آپلود تصویر', 'danger');
        }
    }

    $errors = [];
    if (empty($first_name)) {
        $errors[] = 'نام الزامی است.';
    }
    if (empty($last_name)) {
        $errors[] = 'نام خانوادگی الزامی است.';
    }
    if (empty($mobile)) {
        $errors[] = 'شماره موبایل الزامی است.';
    }

    if (empty($errors)) {
        $db->update('customers', [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'mobile' => $mobile,
            'address' => $address,
            'email' => $email,
            'description' => $description,
            'image' => $image
        ], ['id' => $customer_id]);

        flashMessage('مشتری با موفقیت ویرایش شد', 'success');
        header('Location: customers.php');
        exit;
    } else {
        foreach ($errors as $error) {
            flashMessage($error, 'danger');
        }
    }
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
                                <form method="post" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="customer_id" value="<?php echo isset($customer['id']) ? $customer['id'] : ''; ?>">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="first_name">نام</label>
                                                <input type="text" name="first_name" id="first_name" class="form-control" value="<?php echo isset($customer['first_name']) ? $customer['first_name'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="last_name">نام خانوادگی</label>
                                                <input type="text" name="last_name" id="last_name" class="form-control" value="<?php echo isset($customer['last_name']) ? $customer['last_name'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="phone">تلفن</label>
                                                <input type="text" name="phone" id="phone" class="form-control" value="<?php echo isset($customer['phone']) ? $customer['phone'] : ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mobile">موبایل</label>
                                                <input type="text" name="mobile" id="mobile" class="form-control" value="<?php echo isset($customer['mobile']) ? $customer['mobile'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="address">آدرس</label>
                                                <input type="text" name="address" id="address" class="form-control" value="<?php echo isset($customer['address']) ? $customer['address'] : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="email">ایمیل</label>
                                                <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($customer['email']) ? $customer['email'] : ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="description">توضیحات</label>
                                                <textarea name="description" id="description" class="form-control" rows="3"><?php echo isset($customer['description']) ? $customer['description'] : ''; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="image">تصویر</label>
                                                <input type="file" name="image" id="image" class="form-control">
                                                <?php if (isset($customer['image']) && !empty($customer['image'])): ?>
                                                    <img src="<?php echo $customer['image']; ?>" alt="تصویر مشتری" class="img-thumbnail mt-3" width="100">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <?php if (isset($customer)): ?>
                                                <button type="submit" name="edit_customer" class="btn btn-primary">ویرایش مشتری</button>
                                            <?php else: ?>
                                                <button type="submit" name="add_customer" class="btn btn-primary">افزودن مشتری</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>
                                <hr>
                                <h4>لیست مشتریان</h4>
                                <input type="text" id="search" class="form-control mb-3" placeholder="جستجو در مشتریان...">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>تصویر</th>
                                            <th>نام</th>
                                            <th>نام خانوادگی</th>
                                            <th>تلفن</th>
                                            <th>موبایل</th>
                                            <th>آدرس</th>
                                            <th>ایمیل</th>
                                            <th>توضیحات</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customer-list">
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td><img src="<?php echo $customer['image']; ?>" alt="تصویر مشتری" class="img-thumbnail" width="50"></td>
                                                <td><?php echo $customer['first_name']; ?></td>
                                                <td><?php echo $customer['last_name']; ?></td>
                                                <td><?php echo $customer['phone']; ?></td>
                                                <td><?php echo $customer['mobile']; ?></td>
                                                <td><?php echo $customer['address']; ?></td>
                                                <td><?php echo $customer['email']; ?></td>
                                                <td><?php echo $customer['description']; ?></td>
                                                <td>
                                                    <a href="customers.php?edit=<?php echo $customer['id']; ?>" class="btn btn-sm btn-warning">ویرایش</a>
                                                    <a href="customers.php?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('آیا از حذف این مشتری مطمئن هستید؟')">حذف</a>
                                                </td>
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
    <script>
    $(document).ready(function() {
        // جستجو در لیست مشتریان
        $('#search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#customer-list tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
    </script>
</body>
</html>