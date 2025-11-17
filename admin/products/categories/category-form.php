<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if (!$conn) {
    die('Database connection failed.');
}

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$category = [
    'category_id'   => '',
    'category_name' => '',
    'description'   => '',
    'photo_url'     => '',
    'is_active'     => 1,
    'created_at'    => ''
];

if ($isEdit) {
    $stmt = $conn->prepare("SELECT category_id, category_name, description, photo_url, is_active, created_at FROM categories WHERE category_id = ?");
    if (!$stmt) {
        header('Location: index.php?error=server');
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($category['category_id'], $category['category_name'], $category['description'], $category['photo_url'], $category['is_active'], $category['created_at']);
    if (!$stmt->fetch()) {
        $stmt->close();
        header('Location: index.php?error=not_found');
        exit;
    }
    $stmt->close();
}

$formAction = $isEdit ? 'update.php' : 'create.php';

$errors = [];
if (isset($_GET['error'])) {
    $codes = explode(',', $_GET['error']);
    foreach ($codes as $e) {
        $e = trim($e);
        if ($e === 'invalid_id')     $errors[] = 'Invalid category ID.';
        if ($e === 'not_found')      $errors[] = 'Category not found.';
        if ($e === 'name_required')  $errors[] = 'Category name is required.';
        if ($e === 'status_invalid') $errors[] = 'Status value is invalid.';
        if ($e === 'photo_type')     $errors[] = 'Only JPG, PNG, and GIF images allowed.';
        if ($e === 'photo_size')     $errors[] = 'Image too large (max 5MB).';
        if ($e === 'duplicate')      $errors[] = 'A category with the same name already exists.';
        if ($e === 'server')         $errors[] = 'Server error. Please try again.';
    }
}

$success = false;
$successText = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        $success = true;
        $successText = 'Category created.';
    } elseif ($_GET['status'] === 'updated') {
        $success = true;
        $successText = 'Category updated.';
    }
}

$sel = function($a, $b) { return ((string)$a === (string)$b) ? 'selected' : ''; };

$photoPreview = $category['photo_url'] !== '' ? $category['photo_url'] : '/carriemart/assets/person-circle.svg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    .form-register {
        background-color: #ffffff;
        border-radius: 1rem;
        padding: 1rem;
    }

    .btn-icon-inverted img {
        width: 1.125rem;
        height: 1.125rem;
        filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
        opacity: .95;
    }

    .btn-icon img {
        width: 1.125rem;
        height: 1.125rem;
        filter: brightness(0) invert(1);
    }

    .avatar-lg {
        width: 96px;
        height: 96px;
        border-radius: 8px;
        object-fit: cover;
        background: #f1f3f5;
    }

    .label-small {
        font-size: .8rem;
        color: var(--bs-secondary-color);
    }
    </style>
</head>

<body>
    <div class="container">
        <main class="form-register">
            <div class="py-4 text-center">
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Logo.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-8 col-lg-7 mx-auto">
                    <h4 class="mb-3"><?php echo $isEdit ? 'Edit Category' : 'Add Category'; ?></h4>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-0" role="alert">
                        <?php foreach ($errors as $e): ?>
                        <div>- <?php echo $e; ?></div>
                        <?php endforeach; ?>
                    </div class="mb-2">
                    <?php elseif ($success): ?>
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-0" role="alert">
                        <span><?php echo $successText; ?></span>
                        <a class="btn btn-success"
                            href="/carriemart/admin/products/categories/index.php" role="button">OK</a>
                    </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" action="<?php echo $formAction; ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Category ID</label>
                                <input type="text" class="form-control"
                                    value="<?php echo $category['category_id']; ?>" disabled>
                                <?php if ($isEdit): ?>
                                <input type="hidden" name="category_id"
                                    value="<?php echo $category['category_id']; ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control"
                                    value="<?php echo $category['created_at']; ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" <?php echo $sel($category['is_active'], '1'); ?>>active
                                    </option>
                                    <option value="0" <?php echo $sel($category['is_active'], '0'); ?>>inactive
                                    </option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Category name</label>
                                <input type="text" name="category_name" class="form-control"
                                    value="<?php echo $category['category_name']; ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control"><?php echo $category['description']; ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label d-block">Category photo</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img id="avatarPreview" class="avatar-lg border"
                                        src="<?php echo $photoPreview; ?>" alt="category photo">
                                    <div class="flex-grow-1">
                                        <input class="form-control" type="file" id="formFile" name="photo_file"
                                            accept="image/*">
                                        <input type="hidden" name="photo_url_current"
                                            value="<?php echo $category['photo_url']; ?>">
                                        <small class="text-body-secondary d-block">JPG, PNG, or GIF. Max 5MB.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Fields: name, description, photo, status.</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                type="submit" style="flex:2 1 0%;">
                                Save Category
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </button>
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                style="flex:1 1 0%;" onclick="history.back()">
                                Go back
                                <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
    // Simple preview for profile picture
    const fileInput = document.getElementById('formFile');
    const avatarPreview = document.getElementById('avatarPreview');
    fileInput?.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        avatarPreview.src = URL.createObjectURL(file);
    });
    </script>
</body>

</html>



