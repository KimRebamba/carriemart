<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$q          = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort       = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$categoryId = isset($_GET['category']) ? trim($_GET['category']) : '';
$brandId    = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$dateFrom   = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo     = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$minQty     = isset($_GET['min_qty']) ? trim($_GET['min_qty']) : '';
$minRev     = isset($_GET['min_rev']) ? trim($_GET['min_rev']) : '';

$baseSql = "
SELECT
  p.product_id,
  p.product_name,
  COALESCE(SUM(po.quantity),0) AS total_qty_sold,
  COALESCE(SUM(po.unit_price * po.quantity),0) AS total_revenue,
  COALESCE(COUNT(DISTINCT orr.order_return_id),0) AS return_count
FROM products p
LEFT JOIN product_order po ON po.product_id = p.product_id
LEFT JOIN orders o ON o.order_id = po.order_id
LEFT JOIN order_return orr ON orr.order_id = o.order_id
";

$where = [];
$params = [];
$types  = '';

if ($q !== '') {
    $where[] = "(CAST(p.product_id AS CHAR) LIKE ? OR p.product_name LIKE ?)";
    $like = '%'.$q.'%';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
}

if (ctype_digit($categoryId) && (int)$categoryId > 0) {
    $where[] = "p.category_id = ?";
    $params[] = (int)$categoryId; $types .= 'i';
}
if (ctype_digit($brandId) && (int)$brandId > 0) {
    $where[] = "p.brand_id = ?";
    $params[] = (int)$brandId; $types .= 'i';
}

$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
$dateFilterParts = [];
if ($dateFrom !== '' && $validDate($dateFrom)) {
    $dateFilterParts[] = "DATE(o.date_ordered) >= ?";
    $params[] = $dateFrom; $types .= 's';
}
if ($dateTo !== '' && $validDate($dateTo)) {
    $dateFilterParts[] = "DATE(o.date_ordered) <= ?";
    $params[] = $dateTo; $types .= 's';
}
if (!empty($dateFilterParts)) {
      
    $where[] = "(" . implode(" AND ", $dateFilterParts) . " OR o.order_id IS NULL)";
}

$groupSql = " GROUP BY p.product_id ";

$having = [];
if ($minQty !== '' && ctype_digit($minQty)) {
    $having[] = "total_qty_sold >= ?";
    $params[] = (int)$minQty; $types .= 'i';
}
if ($minRev !== '' && is_numeric($minRev)) {
    $having[] = "total_revenue >= ?";
    $params[] = (float)$minRev; $types .= 'd';
}
$havingSql = '';
if (!empty($having)) {
    $havingSql = " HAVING " . implode(" AND ", $having);
}

$orderSql = " ORDER BY total_qty_sold DESC, total_revenue DESC";
switch ($sort) {
    case 'qtyDesc': $orderSql = " ORDER BY total_qty_sold DESC, p.product_id DESC"; break;
    case 'qtyAsc':  $orderSql = " ORDER BY total_qty_sold ASC, p.product_id ASC"; break;
    case 'revDesc': $orderSql = " ORDER BY total_revenue DESC, total_qty_sold DESC"; break;
    case 'revAsc':  $orderSql = " ORDER BY total_revenue ASC, total_qty_sold ASC"; break;
    case 'retDesc': $orderSql = " ORDER BY return_count DESC, total_qty_sold DESC"; break;
}

$sql = $baseSql;
if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= $groupSql . $havingSql . $orderSql;

$items = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($pid, $pname, $qtySold, $rev, $retCount);
    while ($stmt->fetch()) {
        $items[] = [
            'product_id' => $pid,
            'product_name' => $pname,
            'total_qty_sold' => (int)$qtySold,
            'total_revenue' => (float)$rev,
            'return_count' => (int)$retCount
        ];
    }
    $stmt->close();
}

   
$catList = [];
$brandList = [];
$catStmt = $conn->prepare("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
if ($catStmt) {
    $catStmt->execute();
    $catStmt->bind_result($cid, $cname);
    while ($catStmt->fetch()) $catList[] = ['id'=>$cid,'name'=>$cname];
    $catStmt->close();
}
$brandStmt = $conn->prepare("SELECT brand_id, brand_name FROM brands ORDER BY brand_name ASC");
if ($brandStmt) {
    $brandStmt->execute();
    $brandStmt->bind_result($bid, $bname);
    while ($brandStmt->fetch()) $brandList[] = ['id'=>$bid,'name'=>$bname];
    $brandStmt->close();
}

$showCount = count($items);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>CM: Item-View</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .amount-cell { font-variant-numeric: tabular-nums; }
        @media (max-width: 992px){ .table-responsive { font-size:.85rem; } }
    </style>
</head>

<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php'); ?>

<div class="flex-grow-1 p-3">
    <div class="container-fluid">

        <h3 class="mb-3 d-flex align-items-center gap-2">
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/box-seam.svg" alt="" width="22" height="22" class="mt-1">
            Items
        </h3>

        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group input-group-sm" style="width:340px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search product ID / name">
                        </div>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                            </svg>
                            Filters
                        </button>
                        <select class="form-select form-select-sm" name="sort" style="width:220px;" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="qtyDesc" <?php if($sort==='qtyDesc'||$sort==='') echo 'selected'; ?>>Most sold (qty)</option>
                            <option value="qtyAsc" <?php if($sort==='qtyAsc') echo 'selected'; ?>>Least sold (qty)</option>
                            <option value="revDesc" <?php if($sort==='revDesc') echo 'selected'; ?>>Highest revenue</option>
                            <option value="revAsc" <?php if($sort==='revAsc') echo 'selected'; ?>>Lowest revenue</option>
                            <option value="retDesc" <?php if($sort==='retDesc') echo 'selected'; ?>>Most returns</option>
                        </select>
                           
                        <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <input type="hidden" name="brand" value="<?php echo $brandId; ?>">
                        <input type="hidden" name="date_from" value="<?php echo $dateFrom; ?>">
                        <input type="hidden" name="date_to" value="<?php echo $dateTo; ?>">
                        <input type="hidden" name="min_qty" value="<?php echo $minQty; ?>">
                        <input type="hidden" name="min_rev" value="<?php echo $minRev; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo $showCount; ?> items</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product ID</th>
                                <th>Product name</th>
                                <th class="text-end">Total qty sold</th>
                                <th class="text-end">Total revenue (₱)</th>
                                <th class="text-end">Return count</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No items found.</td></tr>
                        <?php else: foreach ($items as $it):
                            $revFmt = number_format($it['total_revenue'], 2, '.', ',');
                        ?>
                            <tr>
                                <td><?php echo $it['product_id']; ?></td>
                                <td><?php echo $it['product_name']; ?></td>
                                <td class="text-end"><?php echo $it['total_qty_sold']; ?></td>
                                <td class="text-end amount-cell">₱<?php echo $revFmt; ?></td>
                                <td class="text-end"><?php echo $it['return_count']; ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <div>
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category">
                            <option value="">Any</option>
                            <?php foreach ($catList as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php if($categoryId !== '' && (int)$categoryId === (int)$c['id']) echo 'selected'; ?>>
                                    <?php echo $c['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Brand</label>
                        <select class="form-select" name="brand">
                            <option value="">Any</option>
                            <?php foreach ($brandList as $b): ?>
                                <option value="<?php echo $b['id']; ?>" <?php if($brandId !== '' && (int)$brandId === (int)$b['id']) echo 'selected'; ?>>
                                    <?php echo $b['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Ordered date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
                            <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Min qty sold</label>
                        <input type="text" class="form-control" name="min_qty" value="<?php echo $minQty; ?>" placeholder="0">
                    </div>
                    <div>
                        <label class="form-label">Min revenue (₱)</label>
                        <input type="text" class="form-control" name="min_rev" value="<?php echo $minRev; ?>" placeholder="0.00">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>