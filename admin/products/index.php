<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// Inputs (GET)
$q            = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort         = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$active       = isset($_GET['active']) ? trim($_GET['active']) : '';
$condFilter   = isset($_GET['condition']) ? trim($_GET['condition']) : '';
$stockMinRaw  = isset($_GET['stock_min']) ? trim($_GET['stock_min']) : '';
$priceMinRaw  = isset($_GET['price_min']) ? trim($_GET['price_min']) : '';
$priceMaxRaw  = isset($_GET['price_max']) ? trim($_GET['price_max']) : '';
$createdFrom  = isset($_GET['created_from']) ? trim($_GET['created_from']) : '';
$createdTo    = isset($_GET['created_to']) ? trim($_GET['created_to']) : '';

$sql = "SELECT p.product_id, p.product_name, b.brand_name, c.category_name,
               p.retail_price, p.stock_level, p.product_condition, p.is_active, p.created_at
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE 1";
$types = '';
$params = [];

// Search: by id, name, model
if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND (CAST(p.product_id AS CHAR) LIKE ? OR p.product_name LIKE ? OR p.model LIKE ?)";
    $types .= 'sss';
    $params[] = $like; $params[] = $like; $params[] = $like;
}

// Active filter
if ($active === '1') {
    $sql .= " AND p.is_active = 1";
} elseif ($active === '0') {
    $sql .= " AND p.is_active = 0";
}

// Condition filter
$allowedCond = ['new','used','refurbished'];
if ($condFilter !== '' && in_array($condFilter, $allowedCond, true)) {
    $sql .= " AND p.product_condition = ?";
    $types .= 's';
    $params[] = $condFilter;
}

// Stock minimum
if ($stockMinRaw !== '' && ctype_digit($stockMinRaw)) {
    $sql .= " AND p.stock_level >= ?";
    $types .= 'i';
    $params[] = (int)$stockMinRaw;
}

// Price range
$cleanMoney = function($v) {
    $v = str_replace(['₱',',',' '],'',$v);
    if ($v === '') return null;
    if (!is_numeric($v)) return null;
    return (float)$v;
};
$priceMin = $cleanMoney($priceMinRaw);
$priceMax = $cleanMoney($priceMaxRaw);
if ($priceMin !== null) {
    $sql .= " AND p.retail_price >= ?";
    $types .= 'd';
    $params[] = $priceMin;
}
if ($priceMax !== null) {
    $sql .= " AND p.retail_price <= ?";
    $types .= 'd';
    $params[] = $priceMax;
}

// Created date range
$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
if ($createdFrom !== '' && $validDate($createdFrom)) {
    $sql .= " AND DATE(p.created_at) >= ?";
    $types .= 's';
    $params[] = $createdFrom;
}
if ($createdTo !== '' && $validDate($createdTo)) {
    $sql .= " AND DATE(p.created_at) <= ?";
    $types .= 's';
    $params[] = $createdTo;
}

// Sorting
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY p.created_at ASC, p.product_id ASC";
        break;
    case 'priceHigh':
        $sql .= " ORDER BY p.retail_price DESC, p.created_at DESC";
        break;
    case 'priceLow':
        $sql .= " ORDER BY p.retail_price ASC, p.created_at DESC";
        break;
    case 'stockHigh':
        $sql .= " ORDER BY p.stock_level DESC, p.created_at DESC";
        break;
    case 'active':
        $sql .= " ORDER BY p.is_active DESC, p.created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC, p.product_id DESC";
        break;
}

// Fetch
$products = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($pid, $name, $brand, $category, $price, $stock, $pcond, $is_active, $created_at);
    while ($stmt->fetch()) {
        $products[] = [
            'product_id' => $pid,
            'product_name' => $name,
            'brand_name' => $brand,
            'category_name' => $category,
            'retail_price' => $price,
            'stock_level' => $stock,
            'product_condition' => $pcond,
            'is_active' => $is_active,
            'created_at' => $created_at
        ];
    }
    $stmt->close();
}

function condClass($c) {
    if ($c === 'new') return 'cond-new';
    if ($c === 'used') return 'cond-used';
    if ($c === 'refurbished') return 'cond-refurbished';
    return '';
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Products</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .prod-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .prod-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        .cond-new { background:#e7f5ff; color:#09527a; border:1px solid #d0ebff; }
        .cond-used { background:#fff3cd; color:#664d03; border:1px solid #ffe69c; }
        .cond-refurbished { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        @media (max-width: 992px){
            .table-responsive { font-size:.85rem; }
            .actions-cell .btn { font-size:.65rem; }
        }
    </style>
</head>

<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php'); ?>

<div class="flex-grow-1 p-3">
    <div class="container-fluid">

        <h3 class="mb-3 d-flex align-items-center gap-2">
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/box-seam.svg" alt="" width="22" height="22" class="mt-1">
            Products
        </h3>

        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                        <div class="input-group input-group-sm" style="width:340px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search product ID / name / model">
                        </div>
                        <button class="btn btn-outline-secondary btn-sm"
                                type="button" data-bs-toggle="offcanvas"
                                data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                            </svg>
                            Filters
                        </button>
                        <select class="form-select form-select-sm" name="sort" aria-label="Sort by" style="width:180px;" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="newest" <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="oldest" <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
                            <option value="priceHigh" <?php if($sort==='priceHigh') echo 'selected'; ?>>Price: High→Low</option>
                            <option value="priceLow" <?php if($sort==='priceLow') echo 'selected'; ?>>Price: Low→High</option>
                            <option value="stockHigh" <?php if($sort==='stockHigh') echo 'selected'; ?>>Stock: High→Low</option>
                            <option value="active" <?php if($sort==='active') echo 'selected'; ?>>Active first</option>
                        </select>
                        <!-- preserve filters -->
                        <input type="hidden" name="active" value="<?php echo $active; ?>">
                        <input type="hidden" name="condition" value="<?php echo $condFilter; ?>">
                        <input type="hidden" name="stock_min" value="<?php echo $stockMinRaw; ?>">
                        <input type="hidden" name="price_min" value="<?php echo $priceMinRaw; ?>">
                        <input type="hidden" name="price_max" value="<?php echo $priceMaxRaw; ?>">
                        <input type="hidden" name="created_from" value="<?php echo $createdFrom; ?>">
                        <input type="hidden" name="created_to" value="<?php echo $createdTo; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($products); ?> products</small>
                    <a href="product-form.php" class="btn btn-primary btn-sm">Add Product</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Prod ID</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th class="text-end">Retail Price</th>
                                <th class="text-end">Stock</th>
                                <th>Condition</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" style="width:180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="10" class="text-center text-muted py-4">No products found.</td></tr>
                        <?php else: foreach ($products as $p):
                            $condCls = condClass($p['product_condition']);
                            $statusCls = $p['is_active'] ? 'prod-active' : 'prod-inactive';
                            $brand = ($p['brand_name'] !== null ? $p['brand_name'] : '—');
                            $cat = ($p['category_name'] !== null ? $p['category_name'] : '—');
                            $priceDisp = '₱' . number_format((float)$p['retail_price'], 2);
                            $createdDisp = substr($p['created_at'], 0, 10);
                        ?>
                            <tr>
                                <td><?php echo $p['product_id']; ?></td>
                                <td><?php echo $p['product_name']; ?></td>
                                <td><?php echo $brand; ?></td>
                                <td><?php echo $cat; ?></td>
                                <td class="text-end"><?php echo $priceDisp; ?></td>
                                <td class="text-end"><?php echo (int)$p['stock_level']; ?></td>
                                <td><span class="status-badge <?php echo $condCls; ?>"><?php echo $p['product_condition']; ?></span></td>
                                <td><span class="status-badge <?php echo $statusCls; ?>"><?php echo $p['is_active'] ? 'active' : 'inactive'; ?></span></td>
                                <td><span class="small text-muted"><?php echo $createdDisp; ?></span></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo $p['product_id']; ?>" class="btn btn-outline-primary btn-sm">View</a>
                                    <a href="product-form.php?id=<?php echo $p['product_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $p['product_id']; ?>" class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Delete product #<?php echo $p['product_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Offcanvas: Filters (Products) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <div>
                        <label class="form-label">Active status</label>
                        <select class="form-select" name="active">
                            <option value="">Any</option>
                            <option value="1" <?php if($active==='1') echo 'selected'; ?>>Active</option>
                            <option value="0" <?php if($active==='0') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Condition</label>
                        <select class="form-select" name="condition">
                            <option value="">Any</option>
                            <?php foreach ($allowedCond as $c): ?>
                                <option value="<?php echo $c; ?>" <?php if($condFilter===$c) echo 'selected'; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Stock minimum</label>
                        <input type="number" min="0" class="form-control" name="stock_min" value="<?php echo $stockMinRaw; ?>" placeholder="0">
                    </div>
                    <div>
                        <label class="form-label">Retail price range</label>
                        <div class="d-flex gap-2">
                            <input type="number" step="0.01" class="form-control" name="price_min" value="<?php echo $priceMinRaw; ?>" placeholder="Min ₱">
                            <input type="number" step="0.01" class="form-control" name="price_max" value="<?php echo $priceMaxRaw; ?>" placeholder="Max ₱">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Created date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="created_from" value="<?php echo $createdFrom; ?>">
                            <input type="date" class="form-control" name="created_to" value="<?php echo $createdTo; ?>">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
</script>

</body>
</html>