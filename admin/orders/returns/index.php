<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// GET inputs
$q             = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort          = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$statusFilter  = isset($_GET['return_status']) ? trim($_GET['return_status']) : '';
$condFilter    = isset($_GET['cond']) ? trim($_GET['cond']) : '';
$procFrom      = isset($_GET['processed_from']) ? trim($_GET['processed_from']) : '';
$procTo        = isset($_GET['processed_to']) ? trim($_GET['processed_to']) : '';
$minRefundRaw  = isset($_GET['min_refund']) ? trim($_GET['min_refund']) : '';

$sql = "SELECT order_return_id, order_id, cond, return_status, refund_amount, processed_at, created_at
        FROM order_return
        WHERE 1";
$types = '';
$params = [];

// Search
if ($q !== '') {
    $like = "%$q%";
    $sql .= " AND (CAST(order_return_id AS CHAR) LIKE ?
                 OR CAST(order_id AS CHAR) LIKE ?
                 OR return_status LIKE ?
                 OR cond LIKE ?)";

    $types .= 'ssss';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}

// Status filter
$allowedStatus = ['requested','approved','rejected','processed'];
if ($statusFilter !== '' && in_array($statusFilter, $allowedStatus, true)) {
    $sql .= " AND return_status = ?";
    $types .= 's';
    $params[] = $statusFilter;
}

// Condition filter
$allowedCond = ['new','opened','damaged','other'];
if ($condFilter !== '' && in_array($condFilter, $allowedCond, true)) {
    $sql .= " AND cond = ?";
    $types .= 's';
    $params[] = $condFilter;
}

// Processed date range
$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
if ($procFrom !== '' && $validDate($procFrom)) {
    $sql .= " AND processed_at IS NOT NULL AND DATE(processed_at) >= ?";
    $types .= 's';
    $params[] = $procFrom;
}
if ($procTo !== '' && $validDate($procTo)) {
    $sql .= " AND processed_at IS NOT NULL AND DATE(processed_at) <= ?";
    $types .= 's';
    $params[] = $procTo;
}

// Min refund
$minRefundClean = str_replace(['₱',',',' '],'',$minRefundRaw);
if ($minRefundClean !== '' && is_numeric($minRefundClean) && (float)$minRefundClean >= 0) {
    $sql .= " AND refund_amount >= ?";
    $types .= 'd';
    $params[] = (float)$minRefundClean;
}

// Sorting
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC, order_return_id ASC";
        break;
    case 'status':
        $sql .= " ORDER BY FIELD(return_status,'requested','approved','rejected','processed'), created_at DESC";
        break;
    case 'refundHigh':
        $sql .= " ORDER BY refund_amount DESC, created_at DESC";
        break;
    case 'refundLow':
        $sql .= " ORDER BY refund_amount ASC, created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC, order_return_id DESC";
        break;
}

// Fetch
$returns = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($return_id, $order_id, $cond, $return_status, $refund_amount, $processed_at, $created_at);
    while ($stmt->fetch()) {
        $returns[] = [
            'order_return_id' => $return_id,
            'order_id' => $order_id,
            'cond' => $cond,
            'return_status' => $return_status,
            'refund_amount' => $refund_amount,
            'processed_at' => $processed_at,
            'created_at' => $created_at
        ];
    }
    $stmt->close();
}

function statusClass($s) {
    if ($s === 'requested') return 'ret-requested';
    if ($s === 'approved') return 'ret-approved';
    if ($s === 'rejected') return 'ret-rejected';
    if ($s === 'processed') return 'ret-processed';
    return '';
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CM: Returns</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .ret-requested { background:#fff3cd; color:#664d03; border:1px solid #ffecb5; }
        .ret-approved { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .ret-rejected { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        .ret-processed { background:#cfe2ff; color:#084298; border:1px solid #b6d4fe; }
        @media (max-width: 992px){ .table-responsive { font-size:.85rem; } .actions-cell .btn { font-size:.65rem; } }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php'); ?>
<div class="flex-grow-1 p-3">
    <div class="container-fluid">
        <h3 class="mb-3 d-flex align-items-center gap-2">
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/arrow-return-left.svg" alt="" width="22" height="22" class="mt-1">
            Returns
        </h3>

        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                        <div class="input-group input-group-sm" style="width:320px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search return ID / order / status">
                        </div>
                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas">Filters</button>
                        <select class="form-select form-select-sm" name="sort" style="width:180px;" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="newest" <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="oldest" <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
                            <option value="status" <?php if($sort==='status') echo 'selected'; ?>>Status</option>
                            <option value="refundHigh" <?php if($sort==='refundHigh') echo 'selected'; ?>>Refund: High→Low</option>
                            <option value="refundLow" <?php if($sort==='refundLow') echo 'selected'; ?>>Refund: Low→High</option>
                        </select>
                        <input type="hidden" name="return_status" value="<?php echo $statusFilter; ?>">
                        <input type="hidden" name="cond" value="<?php echo $condFilter; ?>">
                        <input type="hidden" name="processed_from" value="<?php echo $procFrom; ?>">
                        <input type="hidden" name="processed_to" value="<?php echo $procTo; ?>">
                        <input type="hidden" name="min_refund" value="<?php echo $minRefundRaw; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($returns); ?> returns</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Return ID</th>
                            <th>Order ID</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th class="text-end">Refund Amt</th>
                            <th>Processed At</th>
                            <th>Created</th>
                            <th class="text-center" style="width:150px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($returns)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No returns found.</td></tr>
                        <?php else: foreach ($returns as $r):
                            $cls = statusClass($r['return_status']);
                            $refundDisplay = '₱' . number_format((float)$r['refund_amount'], 2);
                            $processedDisplay = ($r['processed_at'] ? substr($r['processed_at'],0,16) : '—');
                            $createdDisplay = substr($r['created_at'],0,10);
                        ?>
                            <tr>
                                <td><?php echo $r['order_return_id']; ?></td>
                                <td><?php echo $r['order_id']; ?></td>
                                <td><?php echo $r['cond']; ?></td>
                                <td><span class="status-badge <?php echo $cls; ?>"><?php echo $r['return_status']; ?></span></td>
                                <td class="text-end"><?php echo $refundDisplay; ?></td>
                                <td><?php echo $processedDisplay; ?></td>
                                <td><span class="small text-muted"><?php echo $createdDisplay; ?></span></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo $r['order_return_id']; ?>" class="btn btn-outline-primary btn-sm my-1">View</a>
                                    <a href="return-form.php?id=<?php echo $r['order_return_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>                                  
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
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Returns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <div>
                        <label class="form-label">Return status</label>
                        <select class="form-select" name="return_status">
                            <option value="">Any</option>
                            <?php foreach ($allowedStatus as $s): ?>
                                <option value="<?php echo $s; ?>" <?php if($statusFilter===$s) echo 'selected'; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Condition</label>
                        <select class="form-select" name="cond">
                            <option value="">Any</option>
                            <?php foreach ($allowedCond as $c): ?>
                                <option value="<?php echo $c; ?>" <?php if($condFilter===$c) echo 'selected'; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Processed date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="processed_from" value="<?php echo $procFrom; ?>">
                            <input type="date" class="form-control" name="processed_to" value="<?php echo $procTo; ?>">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Min refund amount</label>
                        <input type="text" class="form-control" name="min_refund" value="<?php echo $minRefundRaw; ?>" placeholder="0.00">
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