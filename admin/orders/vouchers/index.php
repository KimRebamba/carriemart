<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$q          = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort       = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$active     = isset($_GET['active']) ? trim($_GET['active']) : '';
$percentMin = isset($_GET['percent_min']) ? trim($_GET['percent_min']) : '';
$dateFrom   = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
$dateTo     = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

$sql = "SELECT voucher_id, voucher_code, percent_sale, min_purchase_amount, max_discount_amount,
               from_date, to_date, is_active, created_at
        FROM vouchers
        WHERE 1";
$types = '';
$params = [];

   
if ($q !== '') {
    $like = "%$q%";
    $sql .= " AND voucher_code LIKE ?";
    $types .= 's';
    $params[] = $like;
}

   
if ($active === 'active') {
    $sql .= " AND is_active = 1";
} elseif ($active === 'inactive') {
    $sql .= " AND is_active = 0";
}

   
if ($percentMin !== '' && ctype_digit($percentMin)) {
    $sql .= " AND (percent_sale IS NOT NULL AND percent_sale >= ?)";
    $types .= 'i';
    $params[] = (int)$percentMin;
}

   
$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
if ($dateFrom !== '' && $validDate($dateFrom)) {
    $sql .= " AND (from_date IS NULL OR from_date <= ?)";
    $types .= 's';
    $params[] = $dateFrom;
}
if ($dateTo !== '' && $validDate($dateTo)) {
    $sql .= " AND (to_date IS NULL OR to_date >= ?)";
    $types .= 's';
    $params[] = $dateTo;
}

   
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC, voucher_id ASC";
        break;
    case 'percentHigh':
        $sql .= " ORDER BY percent_sale DESC, voucher_id DESC";
        break;
    case 'percentLow':
        $sql .= " ORDER BY percent_sale ASC, voucher_id DESC";
        break;
    case 'active':
        $sql .= " ORDER BY is_active DESC, created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC, voucher_id DESC";
        break;
}

   
$vouchers = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($voucher_id, $voucher_code, $percent_sale, $min_purchase_amount, $max_discount_amount,
                       $from_date, $to_date, $is_active, $created_at);
    while ($stmt->fetch()) {
        $vouchers[] = [
            'voucher_id' => $voucher_id,
            'voucher_code' => $voucher_code,
            'percent_sale' => $percent_sale,
            'min_purchase_amount' => $min_purchase_amount,
            'max_discount_amount' => $max_discount_amount,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'is_active' => $is_active,
            'created_at' => $created_at
        ];
    }
    $stmt->close();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CM: Vouchers</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .v-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .v-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        @media (max-width: 992px){ .table-responsive { font-size:.85rem; } .actions-cell .btn { font-size:.65rem; } }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php'); ?>
<div class="flex-grow-1 p-3">
    <div class="container-fluid">
        <h3 class="mb-3 d-flex align-items-center gap-2">
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/ticket-perforated.svg" alt="" width="22" height="22" class="mt-1">
            Vouchers
        </h3>
        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                        <div class="input-group input-group-sm" style="width:320px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search voucher code">
                        </div>
                        <select class="form-select form-select-sm" name="sort" style="width:180px;" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="newest" <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="oldest" <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
                            <option value="percentHigh" <?php if($sort==='percentHigh') echo 'selected'; ?>>Percent: High→Low</option>
                            <option value="percentLow" <?php if($sort==='percentLow') echo 'selected'; ?>>Percent: Low→High</option>
                            <option value="active" <?php if($sort==='active') echo 'selected'; ?>>Active first</option>
                        </select>
                        <input type="hidden" name="active" value="<?php echo $active; ?>">
                        <input type="hidden" name="percent_min" value="<?php echo $percentMin; ?>">
                        <input type="hidden" name="from_date" value="<?php echo $dateFrom; ?>">
                        <input type="hidden" name="to_date" value="<?php echo $dateTo; ?>">
                    </form>
                    <button class="btn btn-outline-secondary btn-sm"
                            type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                        Filters
                    </button>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($vouchers); ?> vouchers</small>
                    <a href="voucher-form.php" class="btn btn-primary btn-sm">Add Voucher</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th class="text-end">% Sale</th>
                                <th class="text-end">Min Purchase</th>
                                <th class="text-end">Max Discount</th>
                                <th>Date Range</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" style="width:160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($vouchers)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No vouchers found.</td></tr>
                        <?php else: foreach ($vouchers as $v):
                            $range = ($v['from_date'] || $v['to_date']) ? ($v['from_date'].' to '.$v['to_date']) : '—';
                            $statusClass = $v['is_active'] ? 'v-active' : 'v-inactive';
                            $percentDisplay = ($v['percent_sale'] !== null ? (int)$v['percent_sale'].'%' : '—');
                            $minPurchase = '₱'.number_format((float)$v['min_purchase_amount'],2);
                            $maxDiscount = ($v['max_discount_amount'] !== null ? '₱'.number_format((float)$v['max_discount_amount'],2) : '—');
                        ?>
                            <tr>
                                <td><?php echo $v['voucher_id']; ?></td>
                                <td><?php echo $v['voucher_code']; ?></td>
                                <td class="text-end"><?php echo $percentDisplay; ?></td>
                                <td class="text-end"><?php echo $minPurchase; ?></td>
                                <td class="text-end"><?php echo $maxDiscount; ?></td>
                                <td><span class="small text-muted"><?php echo $range; ?></span></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $v['is_active'] ? 'active' : 'inactive'; ?></span></td>
                                <td><span class="small text-muted"><?php echo substr($v['created_at'],0,10); ?></span></td>
                                <td class="text-center actions-cell">
                                    <a href="voucher-form.php?id=<?php echo $v['voucher_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $v['voucher_id']; ?>" class="btn btn-outline-danger btn-sm mb-1"
                                       onclick="return confirm('Delete voucher #<?php echo $v['voucher_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Vouchers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <div>
                        <label class="form-label">Active status</label>
                        <select class="form-select" name="active">
                            <option value="">Any</option>
                            <option value="active" <?php if($active==='active') echo 'selected'; ?>>active</option>
                            <option value="inactive" <?php if($active==='inactive') echo 'selected'; ?>>inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">% Sale minimum</label>
                        <input type="text" class="form-control" name="percent_min" value="<?php echo $percentMin; ?>" placeholder="e.g. 10">
                    </div>
                    <div>
                        <label class="form-label">Validity overlap (date)</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="from_date" value="<?php echo $dateFrom; ?>">
                            <input type="date" class="form-control" name="to_date" value="<?php echo $dateTo; ?>">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>