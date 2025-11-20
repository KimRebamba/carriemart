<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if (!$conn) {
    die('Database connection failed.');
}

   
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

   
$brands = [];
$categories = [];
$suppliers = [];

$loadList = function($sql, $bindTypes = '', $bindValues = []) use ($conn) {
    $rows = [];
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if ($bindTypes !== '') $stmt->bind_param($bindTypes, ...$bindValues);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
    }
    return $rows;
};

$brands = $loadList("SELECT brand_id, brand_name FROM brands WHERE is_active = 1 ORDER BY brand_name");
$categories = $loadList("SELECT category_id, category_name FROM categories WHERE is_active = 1 ORDER BY category_name");
$suppliers = $loadList("SELECT supplier_id, supplier_name FROM suppliers WHERE is_active = 1 ORDER BY supplier_name");

   
$product = [
    'product_id' => '',
    'product_name' => '',
    'brand_id' => '',
    'model' => '',
    'category_id' => '',
    'retail_price' => '',
    'cost_price' => '',
    'supplier_id' => '',
    'description' => '',
    'specifications' => '',
    'product_condition' => 'new',
    'warranty_months' => '12',
    'is_active' => '1',
    'stock_level' => '0',
    'created_at' => ''
];
$product_photos = [];

if ($isEdit) {
    $stmt = $conn->prepare("SELECT product_id, product_name, brand_id, model, category_id, retail_price, cost_price, supplier_id, description, specifications, product_condition, warranty_months, is_active, stock_level, created_at FROM products WHERE product_id = ?");
    if (!$stmt) {
        header('Location: index.php?error=server');
        exit;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result(
        $product['product_id'], $product['product_name'], $product['brand_id'], $product['model'],
        $product['category_id'], $product['retail_price'], $product['cost_price'], $product['supplier_id'],
        $product['description'], $product['specifications'], $product['product_condition'],
        $product['warranty_months'], $product['is_active'], $product['stock_level'], $product['created_at']
    );
    if (!$stmt->fetch()) {
        $stmt->close();
        header('Location: index.php?error=not_found');
        exit;
    }
    $stmt->close();

      
    $photosStmt = $conn->prepare("SELECT product_photo_id, photo_url, is_primary, sort_order FROM product_photos WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, product_photo_id ASC");
    if ($photosStmt) {
        $photosStmt->bind_param('i', $product['product_id']);
        $photosStmt->execute();
        $photosStmt->bind_result($ppid, $purl, $pprimary, $psort);
        while ($photosStmt->fetch()) {
            $product_photos[] = [
                'product_photo_id' => $ppid,
                'photo_url' => $purl,
                'is_primary' => (int)$pprimary,
                'sort_order' => (int)$psort
            ];
        }
        $photosStmt->close();
    }
}

   
$formAction = $isEdit ? 'update.php' : 'create.php';

   
$errors = [];
if (isset($_GET['error'])) {
    foreach (explode(',', $_GET['error']) as $e) {
        $e = trim($e);
        if ($e === 'invalid_id')        $errors[] = 'Invalid product ID.';
        if ($e === 'not_found')         $errors[] = 'Product not found.';
        if ($e === 'name_required')     $errors[] = 'Product name is required.';
        if ($e === 'price_invalid')     $errors[] = 'Retail price is invalid.';
        if ($e === 'cost_invalid')      $errors[] = 'Cost price is invalid.';
        if ($e === 'stock_invalid')     $errors[] = 'Stock level is invalid.';
        if ($e === 'condition_invalid') $errors[] = 'Condition value is invalid.';
        if ($e === 'warranty_invalid')  $errors[] = 'Warranty months is invalid.';
        if ($e === 'status_invalid')    $errors[] = 'Status value is invalid.';
        if ($e === 'brand_invalid')     $errors[] = 'Brand selection is invalid.';
        if ($e === 'category_invalid')  $errors[] = 'Category selection is invalid.';
        if ($e === 'supplier_invalid')  $errors[] = 'Supplier selection is invalid.';
        if ($e === 'photos_type')       $errors[] = 'Only JPG, PNG, and GIF images allowed.';
        if ($e === 'photos_size')       $errors[] = 'One or more images exceed the size limit.';
        if ($e === 'photos_count')      $errors[] = 'Too many images selected.';
        if ($e === 'server')            $errors[] = 'Server error. Please try again.';
        if ($e === 'duplicate')         $errors[] = 'A product with the same name/model already exists.';
    }
}

$success = false;
$successText = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        $success = true;
        $successText = 'Product created.';
    } elseif ($_GET['status'] === 'updated') {
        $success = true;
        $successText = 'Product updated.';
    }
}

   
$sel = function($a, $b) { return (string)$a === (string)$b ? 'selected' : ''; };
$chk = function($a) { return (string)$a === '1' ? 'selected' : ''; };   

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CM: Product-Form</title>
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
                    <h4 class="mb-3"><?php echo $isEdit ? 'Edit Product' : 'Add Product'; ?></h4>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-0" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div>- <?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div class ="mb-2">
                    <?php elseif ($success): ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0" role="alert">
                            <span><?php echo $successText; ?></span>
                            <a class="btn btn-success" href="/carriemart/admin/products/index.php" role="button">OK</a>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" action="<?php echo $formAction; ?>">
                           
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Product ID</label>
                                <input type="text" class="form-control" value="<?php echo $isEdit ? $product['product_id'] : ''; ?>" disabled>
                                <?php if ($isEdit): ?>
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="<?php echo $isEdit ? $product['created_at'] : ''; ?>" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                           
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="product_name" class="form-label">Product name</label>
                                <input type="text" id="product_name" name="product_name" class="form-control"
                                       value="<?php echo $product['product_name']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" id="model" name="model" class="form-control"
                                       value="<?php echo $product['model']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select id="brand_id" name="brand_id" class="form-select">
                                    <option value="">None</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?php echo $b['brand_id']; ?>" <?php echo $sel($product['brand_id'], $b['brand_id']); ?>>
                                            <?php echo $b['brand_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label">Category</label>
                                <select id="category_id" name="category_id" class="form-select">
                                    <option value="">None</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?php echo $c['category_id']; ?>" <?php echo $sel($product['category_id'], $c['category_id']); ?>>
                                            <?php echo $c['category_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select id="supplier_id" name="supplier_id" class="form-select">
                                    <option value="">None</option>
                                    <?php foreach ($suppliers as $s): ?>
                                        <option value="<?php echo $s['supplier_id']; ?>" <?php echo $sel($product['supplier_id'], $s['supplier_id']); ?>>
                                            <?php echo $s['supplier_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="retail_price" class="form-label">Retail price (₱)</label>
                                <input type="text" id="retail_price" name="retail_price" class="form-control"
                                       value="<?php echo $product['retail_price']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="cost_price" class="form-label">Cost price (₱)</label>
                                <input type="text" id="cost_price" name="cost_price" class="form-control"
                                       value="<?php echo $product['cost_price']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="stock_level" class="form-label">Stock level</label>
                                <input type="text" id="stock_level" name="stock_level" class="form-control"
                                       value="<?php echo $product['stock_level']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="product_condition" class="form-label">Condition</label>
                                <select id="product_condition" name="product_condition" class="form-select">
                                    <option value="new" <?php echo $sel($product['product_condition'], 'new'); ?>>new</option>
                                    <option value="used" <?php echo $sel($product['product_condition'], 'used'); ?>>used</option>
                                    <option value="refurbished" <?php echo $sel($product['product_condition'], 'refurbished'); ?>>refurbished</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="warranty_months" class="form-label">Warranty (months)</label>
                                <input type="text" id="warranty_months" name="warranty_months" class="form-control"
                                       value="<?php echo $product['warranty_months']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="is_active" class="form-label">Status</label>
                                <select id="is_active" name="is_active" class="form-select">
                                    <option value="1" <?php echo $sel($product['is_active'], '1'); ?>>active</option>
                                    <option value="0" <?php echo $sel($product['is_active'], '0'); ?>>inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4"><?php echo $product['description']; ?></textarea>
                            </div>
                            <div class="col-12">
                                <label for="specifications" class="form-label">Specifications</label>
                                <textarea id="specifications" name="specifications" class="form-control" rows="4"><?php echo $product['specifications']; ?></textarea>
                            </div>

                               
                            <div class="col-12">
                                <label class="form-label d-block">Product photos</label>
                                <div class="mb-2">
                                    <input class="form-control" type="file" id="photos_new" name="photos_new[]" accept="image/*" multiple>
                                    <small class="text-body-secondary d-block">Select multiple images (JPG, PNG, GIF). Max 5MB each.</small>
                                </div>
                                <div id="photosNewPreview" class="d-flex flex-wrap gap-2"></div>
                            </div>

                              
<?php if (!empty($product_photos)): ?>
<div class="col-12">
    <label class="form-label d-block mb-3">Existing photos</label>
    <div class="row g-3">
        <?php foreach ($product_photos as $p): ?>
        <div class="col-6 col-sm-4 col-md-3">
            <div class="border rounded p-3 h-100">
                <div class="ms-auto d-flex align-items-center gap-1 mb-2">
                        <label class="form-label small mb-0">Order</label>
                        <input type="text" class="form-control form-control-sm"
                               style="width:60px;"
                               name="photos_existing[<?php echo (int)$p['product_photo_id']; ?>][sort_order]"
                               value="<?php echo (int)$p['sort_order']; ?>">
                    </div>
                <img src="<?php echo $p['photo_url']; ?>" class="img-fluid rounded mb-3" alt="photo">
              

                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio"
                               name="primary_photo_id"
                               value="<?php echo (int)$p['product_photo_id']; ?>"
                               <?php echo !empty($p['is_primary']) ? 'checked' : ''; ?>>
                        <label class="form-check-label small">Primary</label>
                    </div>
                    
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox"
                           name="photos_existing[<?php echo (int)$p['product_photo_id']; ?>][remove]"
                           value="1" id="remove<?php echo (int)$p['product_photo_id']; ?>">
                    <label class="form-check-label small text-danger"
                           for="remove<?php echo (int)$p['product_photo_id']; ?>">Remove</label>
                </div>
                <input type="hidden"
                       name="photos_existing[<?php echo (int)$p['product_photo_id']; ?>][photo_url]"
                       value="<?php echo $p['photo_url']; ?>">
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                 type="submit" style="flex: 2 1 0%;">
                                Save Product
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
      
    const photosInput = document.getElementById('photos_new');
    const photosPreview = document.getElementById('photosNewPreview');
    photosInput?.addEventListener('change', (e) => {
        const files = Array.from(e.target.files || []);
        photosPreview.innerHTML = '';
        files.forEach(file => {
            const url = URL.createObjectURL(file);
            const wrap = document.createElement('div');
            wrap.className = 'border rounded';
            wrap.style.width = '96px';
            wrap.style.height = '96px';
            wrap.style.overflow = 'hidden';
            const img = document.createElement('img');
            img.src = url;
            img.alt = file.name;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            wrap.appendChild(img);
            photosPreview.appendChild(wrap);
        });
    });
    </script>
</body>

</html>



