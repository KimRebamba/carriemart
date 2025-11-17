<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if (!$conn) {
    die('Database connection failed.');
}

// Inputs
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

// Load brand for edit or set defaults
$brand = [
    'brand_id' => '',
    'brand_name' => '',
    'website' => '',
    'description' => '',
    'logo_url' => '',
    'is_active' => 1,
    'created_at' => ''
];

if ($isEdit) {
    $stmt = $conn->prepare("SELECT brand_id, brand_name, website, description, logo_url, is_active, created_at FROM brands WHERE brand_id = ?");
    if (!$stmt) {
        header('Location: index.php?error=server');
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($brand['brand_id'], $brand['brand_name'], $brand['website'], $brand['description'], $brand['logo_url'], $brand['is_active'], $brand['created_at']);
    if (!$stmt->fetch()) {
        $stmt->close();
        header('Location: index.php?error=not_found');
        exit;
    }
    $stmt->close();
}

// Determine action target
$formAction = $isEdit ? 'update.php' : 'create.php';

// Map error codes from create/update
$errors = [];
if (isset($_GET['error'])) {
    $codes = explode(',', $_GET['error']);
    foreach ($codes as $e) {
        $e = trim($e);
        if ($e === '') continue;
        if ($e === 'invalid_id')     $errors[] = 'Invalid brand ID.';
        if ($e === 'not_found')      $errors[] = 'Brand not found.';
        if ($e === 'name_required')  $errors[] = 'Brand name is required.';
        if ($e === 'status_invalid') $errors[] = 'Status value is invalid.';
        if ($e === 'website_invalid')$errors[] = 'Website URL is invalid.';
        if ($e === 'duplicate')      $errors[] = 'A brand with the same name already exists.';
        if ($e === 'logo_type')      $errors[] = 'Only JPG, PNG, and GIF images allowed for the logo.';
        if ($e === 'logo_size')      $errors[] = 'Logo image too large (max 5MB).';
        if ($e === 'server')         $errors[] = 'Server error. Please try again.';
    }
}

// Success message
$success = false;
$successText = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        $success = true;
        $successText = 'Brand created.';
    } elseif ($_GET['status'] === 'updated') {
        $success = true;
        $successText = 'Brand updated.';
    }
}

// Helpers for selected attributes (no HTML5 validation)
$sel = function($a, $b) { return ((string)$a === (string)$b) ? 'selected' : ''; };

// Pick a preview image
$logoPreview = ($brand['logo_url'] !== '') ? $brand['logo_url'] : '/carriemart/assets/person-circle.svg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Brand Form</title>
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
        border-radius: 50%;
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
                    <h4 class="mb-3"><?php echo $isEdit ? 'Edit Brand' : 'Add Brand'; ?></h4>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-0" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div>- <?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div class ="mb-2">
                    <?php elseif ($success): ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0" role="alert">
                            <span><?php echo $successText; ?></span>
                            <a class="btn btn成功 btn-success" href="/carriemart/admin/products/brands/index.php" role="button">OK</a>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" action="<?php echo $formAction; ?>">
                        <!-- Brand IDs & timestamps (read-only) -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Brand ID</label>
                                <input type="text" class="form-control" value="<?php echo $brand['brand_id']; ?>" disabled>
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="brand_id" value="<?php echo $brand['brand_id']; ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="<?php echo $brand['created_at']; ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label for="is_active" class="form-label">Status</label>
                                <select id="is_active" name="is_active" class="form-select">
                                    <option value="1" <?php echo $sel($brand['is_active'], '1'); ?>>active</option>
                                    <option value="0" <?php echo $sel($brand['is_active'], '0'); ?>>inactive</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Core brand fields -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="brand_name" class="form-label">Brand name</label>
                                <input type="text" id="brand_name" name="brand_name" class="form-control"
                                       value="<?php echo $brand['brand_name']; ?>">
                            </div>
                            <div class="col-12">
                                <label for="website" class="form-label">Website</label>
                                <input type="text" id="website" name="website" class="form-control"
                                       placeholder="https://"
                                       value="<?php echo $brand['website']; ?>">
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4"><?php echo $brand['description']; ?></textarea>
                            </div>
                            <!-- Brand logo (placed below description) -->
                            <div class="col-12">
                                <label class="form-label d-block">Brand logo</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img id="avatarPreview" class="avatar-lg border"
                                         src="<?php echo $logoPreview; ?>"
                                         alt="brand logo">
                                    <div class="flex-grow-1">
                                        <input class="form-control" type="file" id="formFile" name="logo_file" accept="image/*">
                                        <input type="hidden" name="logo_url_current"
                                               value="<?php echo $brand['logo_url']; ?>">
                                        <small class="text-body-secondary d-block">JPG, PNG, or GIF. Max 5MB.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Essential fields only: name, website, description, logo, status.</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                type="submit" style="flex: 2 1 0%;">
                                Save Brand
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </button>
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                style="flex: 1 1 0%;" onclick="history.back()">
                                Go back
                                <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Simple preview for brand logo
    const fileInput = document.getElementById('formFile');
    const avatarPreview = document.getElementById('avatarPreview');
    fileInput?.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        avatarPreview.src = URL.createObjectURL(file);
    });
    </script>
</body>

</html>



