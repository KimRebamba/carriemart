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
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Header-Logo-01.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-8 col-lg-7 mx-auto">
                    <h4 class="mb-3">Edit Product</h4>

                    <form class="needs-validation" method="post" novalidate enctype="multipart/form-data">
                        <!-- Product IDs & timestamps (read-only) -->
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Product ID</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Core product fields -->
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="product_name" class="form-label">Product name</label>
                                <input type="text" id="product_name" name="product_name" class="form-control" required>
                                <div class="invalid-feedback">Required.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" id="model" name="model" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="brand_id" class="form-label">Brand</label>
                                <select id="brand_id" name="brand_id" class="form-select">
                                    <option value="">None</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label">Category</label>
                                <select id="category_id" name="category_id" class="form-select">
                                    <option value="">None</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select id="supplier_id" name="supplier_id" class="form-select">
                                    <option value="">None</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="retail_price" class="form-label">Retail price (₱)</label>
                                <input type="number" step="0.01" min="0" id="retail_price" name="retail_price" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cost_price" class="form-label">Cost price (₱)</label>
                                <input type="number" step="0.01" min="0" id="cost_price" name="cost_price" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="stock_level" class="form-label">Stock level</label>
                                <input type="number" min="0" id="stock_level" name="stock_level" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label for="product_condition" class="form-label">Condition</label>
                                <select id="product_condition" name="product_condition" class="form-select">
                                    <option value="new" selected>new</option>
                                    <option value="used">used</option>
                                    <option value="refurbished">refurbished</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="warranty_months" class="form-label">Warranty (months)</label>
                                <input type="number" min="0" id="warranty_months" name="warranty_months" class="form-control" value="12">
                            </div>
                            <div class="col-md-4">
                                <label for="is_active" class="form-label">Status</label>
                                <select id="is_active" name="is_active" class="form-select">
                                    <option value="1" selected>active</option>
                                    <option value="0">inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="specifications" class="form-label">Specifications</label>
                                <textarea id="specifications" name="specifications" class="form-control" rows="4"></textarea>
                            </div>

                            <!-- Product photos: upload multiple -->
                            <div class="col-12">
                                <label class="form-label d-block">Product photos</label>
                                <div class="mb-2">
                                    <input class="form-control" type="file" id="photos_new" name="photos_new[]" accept="image/*" multiple>
                                    <small class="text-body-secondary d-block">Select multiple images (JPG, PNG, GIF). Max 5MB each.</small>
                                </div>
                                <div id="photosNewPreview" class="d-flex flex-wrap gap-2"></div>
                            </div>

                            <!-- Existing photos (if editing) -->
                            <?php $product_photos = $product_photos ?? []; if (!empty($product_photos)): ?>
                            <div class="col-12">
                                <label class="form-label d-block">Existing photos</label>
                                <div class="row g-2">
                                    <?php foreach ($product_photos as $p): ?>
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="border rounded p-2 h-100">
                                            <img src="<?= htmlspecialchars($p['photo_url'] ?? '') ?>" class="img-fluid rounded mb-2" alt="photo">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                           name="primary_photo_id"
                                                           value="<?= (int)($p['product_photo_id'] ?? 0) ?>"
                                                           <?= !empty($p['is_primary']) ? 'checked' : '' ?>>
                                                    <label class="form-check-label small">Primary</label>
                                                </div>
                                                <div class="ms-auto">
                                                    <label class="form-label small mb-0 me-1">Order</label>
                                                    <input type="number" class="form-control form-control-sm d-inline-block"
                                                           style="width:72px;"
                                                           name="photos_existing[<?= (int)($p['product_photo_id'] ?? 0) ?>][sort_order]"
                                                           value="<?= (int)($p['sort_order'] ?? 0) ?>">
                                                </div>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox"
                                                       name="photos_existing[<?= (int)($p['product_photo_id'] ?? 0) ?>][remove]"
                                                       value="1" id="remove<?= (int)($p['product_photo_id'] ?? 0) ?>">
                                                <label class="form-check-label small text-danger"
                                                       for="remove<?= (int)($p['product_photo_id'] ?? 0) ?>">Remove</label>
                                            </div>
                                            <input type="hidden"
                                                   name="photos_existing[<?= (int)($p['product_photo_id'] ?? 0) ?>][photo_url]"
                                                   value="<?= htmlspecialchars($p['photo_url'] ?? '') ?>">
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
    // Preview newly selected product photos
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



