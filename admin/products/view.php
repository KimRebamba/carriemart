<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Load product with related names
$sql = "SELECT 
            p.product_id, p.product_name, p.brand_id, b.brand_name,
            p.model, p.category_id, c.category_name,
            p.retail_price, p.cost_price, p.supplier_id, s.supplier_name,
            p.description, p.specifications, p.product_condition,
            p.warranty_months, p.is_active, p.stock_level, p.created_at
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        WHERE p.product_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: index.php?error=server');
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result(
    $product_id, $product_name, $brand_id, $brand_name,
    $model, $category_id, $category_name,
    $retail_price, $cost_price, $supplier_id, $supplier_name,
    $description, $specifications, $product_condition,
    $warranty_months, $is_active, $stock_level, $created_at
);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: index.php?error=not_found');
    exit;
}
$stmt->close();

$product = [
    'product_id' => $product_id,
    'product_name' => $product_name,
    'brand_id' => $brand_id,
    'brand_name' => $brand_name,
    'model' => $model,
    'category_id' => $category_id,
    'category_name' => $category_name,
    'retail_price' => $retail_price,
    'cost_price' => $cost_price,
    'supplier_id' => $supplier_id,
    'supplier_name' => $supplier_name,
    'description' => $description,
    'specifications' => $specifications,
    'product_condition' => $product_condition,
    'warranty_months' => $warranty_months,
    'is_active' => $is_active,
    'stock_level' => $stock_level,
    'created_at' => $created_at
];

// Load photos
$product_photos = [];
$ps = $conn->prepare("SELECT product_photo_id, photo_url, is_primary, sort_order FROM product_photos WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, product_photo_id ASC");
if ($ps) {
    $ps->bind_param('i', $product_id);
    $ps->execute();
    $ps->bind_result($ppid, $purl, $primary, $sort);
    while ($ps->fetch()) {
        $product_photos[] = [
            'product_photo_id' => $ppid,
            'photo_url' => $purl,
            'is_primary' => (int)$primary,
            'sort_order' => (int)$sort
        ];
    }
    $ps->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CM: View Product</title>
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
                    <h4 class="mb-3">View Product</h4>

                    <form>
                        <!-- Product IDs & timestamps (read-only) -->
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Product ID</label>
                                <input type="text" class="form-control" value="<?= ($product['product_id'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="<?= ($product['created_at'] ?? '') ?>" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Core product fields (read-only) -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Product name</label>
                                <input type="text" class="form-control" value="<?= ($product['product_name'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Model</label>
                                <input type="text" class="form-control" value="<?= ($product['model'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Brand</label>
                                <input type="text" class="form-control"
                                    value="<?= ($product['brand_name'] ?? $product['brand_id'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control"
                                    value="<?= ($product['category_name'] ?? $product['category_id'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Supplier</label>
                                <input type="text" class="form-control"
                                    value="<?= ($product['supplier_name'] ?? $product['supplier_id'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Retail price (₱)</label>
                                <input type="text" class="form-control"
                                    value="<?= isset($product['retail_price']) ? number_format((float)$product['retail_price'], 2) : '' ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cost price (₱)</label>
                                <input type="text" class="form-control"
                                    value="<?= isset($product['cost_price']) ? number_format((float)$product['cost_price'], 2) : '' ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock level</label>
                                <input type="text" class="form-control" value="<?= ((string)($product['stock_level'] ?? '')) ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Condition</label>
                                <input type="text" class="form-control" value="<?= ($product['product_condition'] ?? '') ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Warranty (months)</label>
                                <input type="text" class="form-control" value="<?= ((string)($product['warranty_months'] ?? '')) ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="<?= (isset($product['is_active']) && (int)$product['is_active'] === 1) ? 'active' : 'inactive' ?>" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="4" disabled><?= ($product['description'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Specifications</label>
                                <textarea class="form-control" rows="4" disabled><?= ($product['specifications'] ?? '') ?></textarea>
                            </div>

                            <!-- Product photos (read-only) -->
                            <?php $product_photos = $product_photos ?? []; ?>
                            <div class="col-12">
                                <label class="form-label d-block">Product photos</label>
                                <?php if (!empty($product_photos)): ?>
                                    <div class="row g-2">
                                        <?php foreach ($product_photos as $p): ?>
                                            <div class="col-6 col-sm-4 col-md-3">
                                                <div class="border rounded p-2 h-100 text-center">
                                                    <img src="<?= ($p['photo_url'] ?? '') ?>" class="img-fluid rounded mb-2" alt="photo">
                                                    <?php if (!empty($p['is_primary'])): ?>
                                                        <span class="badge text-bg-primary">Primary</span>
                                                    <?php endif; ?>
                                                    <?php if (isset($p['sort_order'])): ?>
                                                        <div class="small text-muted mt-1">Order: <?= (int)$p['sort_order'] ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted">No photos available.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <small class="text-muted">All fields are read-only.</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2 mb-3">
                            <a class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                               style="flex: 2 1 0%;" href="/carriemart/admin/products/product-form.php?id=<?= urlencode($product['product_id'] ?? '') ?>">
                                Edit Product
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </a>
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
</body>

</html>



