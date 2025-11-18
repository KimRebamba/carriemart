<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');
if (!$conn) { die('DB error'); }
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}
$userId = (int)$_SESSION['user_id'];

// Filters
$returnStatus = isset($_GET['return_status']) ? trim($_GET['return_status']) : '';
$orderSearch = isset($_GET['order_search']) ? trim($_GET['order_search']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$minRefund = isset($_GET['min_refund']) ? trim($_GET['min_refund']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';

$conditions = ['o.user_id = ?'];
$params = [$userId];
$types = 'i';

if ($returnStatus !== '' && in_array($returnStatus, ['requested','approved','rejected','processed'])) {
    $conditions[] = 'ord_ret.return_status = ?';
    $params[] = $returnStatus;
    $types .= 's';
}

if ($orderSearch !== '') {
    $conditions[] = 'CAST(o.order_id AS CHAR) LIKE ?';
    $params[] = '%'.$orderSearch.'%';
    $types .= 's';
}

if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $conditions[] = 'DATE(ord_ret.created_at) >= ?';
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $conditions[] = 'DATE(ord_ret.created_at) <= ?';
    $params[] = $dateTo;
    $types .= 's';
}

$whereSql = 'WHERE ' . implode(' AND ', $conditions);

$orderSql = 'ORDER BY ord_ret.order_return_id DESC';
switch ($sort) {
    case 'recent':
        $orderSql = 'ORDER BY ord_ret.created_at DESC, ord_ret.order_return_id DESC';
        break;
    case 'amountHigh':
        $orderSql = 'ORDER BY ord_ret.refund_amount DESC, ord_ret.order_return_id DESC';
        break;
    case 'amountLow':
        $orderSql = 'ORDER BY ord_ret.refund_amount ASC, ord_ret.order_return_id DESC';
        break;
    case 'status':
        $orderSql = 'ORDER BY ord_ret.return_status ASC, ord_ret.order_return_id DESC';
        break;
}

$sql = "
SELECT 
    ord_ret.order_return_id,
    ord_ret.order_id,
    ord_ret.reason,
    ord_ret.cond,
    ord_ret.return_status,
    ord_ret.refund_amount,
    ord_ret.processed_at,
    ord_ret.created_at,
    o.date_ordered
FROM order_return ord_ret
INNER JOIN orders o ON ord_ret.order_id = o.order_id
$whereSql
";

if ($minRefund !== '' && is_numeric($minRefund)) {
    $sql .= " AND ord_ret.refund_amount >= " . (float)$minRefund;
}

$sql .= " $orderSql";

$user_returns = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->bind_result($orid, $oid, $reason, $cond, $rstat, $refund, $proc_at, $created, $date_ord);
    while ($stmt->fetch()) {
        $user_returns[] = [
            'order_return_id' => $orid,
            'order_id' => $oid,
            'reason' => $reason,
            'cond' => $cond,
            'return_status' => $rstat,
            'refund_amount' => (float)$refund,
            'processed_at' => $proc_at,
            'created_at' => $created,
            'date_ordered' => $date_ord
        ];
    }
    $stmt->close();
}

// For each return, fetch order products
foreach ($user_returns as $key => $ret) {
    $products = [];
    $ps = $conn->prepare("
        SELECT po.product_order_id, po.product_id, po.quantity, po.unit_price, p.product_name,
               COALESCE(b.brand_name, 'Unknown') AS brand_name
        FROM product_order po
        INNER JOIN products p ON po.product_id = p.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE po.order_id = ?
    ");
    if ($ps) {
        $ps->bind_param('i', $ret['order_id']);
        $ps->execute();
        $ps->bind_result($poid, $pid, $qty, $unit, $pname, $brand);
        while ($ps->fetch()) {
            $products[] = [
                'product_order_id' => $poid,
                'product_id' => $pid,
                'product_name' => $pname,
                'brand_name' => $brand,
                'quantity' => (int)$qty,
                'unit_price' => (float)$unit,
                'line_total' => (float)$unit * (int)$qty
            ];
        }
        $ps->close();
    }
    $user_returns[$key]['products'] = $products;
}

$return_count = count($user_returns);

function fmtPrice($v) { return '₱' . number_format((float)$v, 2, '.', ','); }
function fmtDate($d) { return date('Y-m-d H:i', strtotime($d)); }
function statusBadge($status) {
    $map = [
        'requested' => 'status-requested',
        'approved' => 'status-approved',
        'rejected' => 'status-rejected',
        'processed' => 'status-processed'
    ];
    $class = isset($map[$status]) ? $map[$status] : 'status-requested';
    return '<span class="status-badge ' . $class . '">' . $status . '</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>CarrieMart: Returns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<style>
.back-line {display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border-bottom:1px solid var(--bs-border-color);color:var(--bs-body-color);text-decoration:none;}
.back-line:hover {background-color:rgba(var(--bs-primary-rgb), .06);text-decoration:none;}
.back-line .icon {width:20px;height:20px;opacity:.9;}

.order-list {display:grid;grid-template-columns:1fr;gap:1rem;}
.order-card {background:#fff;border-radius:.5rem;border:1px solid transparent;transition:border-color .15s ease;}
.order-card:hover {border-color:rgba(0,0,0,.2);}
.order-header {display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid rgba(0,0,0,.06);flex-wrap:wrap;gap:.5rem;}
.order-left {display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.order-actions {display:flex;gap:.5rem;flex-wrap:wrap;}
.order-actions .btn-sm {padding:.25rem .5rem;}
.order-id {font-weight:600;}
.order-date {color:var(--bs-secondary-color);font-size:.875rem;}

.order-grid {display:grid;grid-template-columns:1fr;gap:1rem;padding:1rem;}
.info-sections {display:grid;gap:.75rem;}
.section-title {font-size:.75rem;letter-spacing:.5px;text-transform:uppercase;color:var(--bs-secondary-color);font-weight:600;margin-bottom:.25rem;}
.kv {display:grid;grid-template-columns:180px 1fr;gap:.5rem;padding:.5rem .75rem;border:1px solid #e9ecef;border-radius:.375rem;background:#fcfcfd;}
.kv .k {color:var(--bs-secondary-color);font-size:.85rem;}
.kv .v {font-weight:500;}

.status-badge {font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.25rem .45rem;border-radius:.35rem;display:inline-block;}
.status-requested {background:#fff3cd;color:#856404;border:1px solid #ffe8a1;}
.status-approved {background:#d1e7dd;color:#0f5132;border:1px solid #badbcc;}
.status-rejected {background:#f8d7da;color:#842029;border:1px solid #f5c2c7;}
.status-processed {background:#cfe2ff;color:#084298;border:1px solid #b6d4fe;}

.order-items { display:grid; gap:.5rem; }
.order-item {
  display:grid;
  grid-template-columns: 2fr 1fr 80px; 
  column-gap:.75rem; row-gap:.25rem;
  padding:.5rem .75rem; background:#f8f9fa; border:1px solid #e9ecef; border-radius:.375rem; align-items:center;
}
.order-item .title, .order-item .label { min-width:0; overflow:hidden; text-overflow:ellipsis; }
.order-item .qty { text-align:right; white-space:nowrap; }

.order-items-header{
  display:grid;
  grid-template-columns: 2fr 1fr 80px; 
  column-gap:.75rem;
  padding:.25rem .75rem;
  color: var(--bs-secondary-color);
  font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;
}

@media (max-width:576px){
  .order-item{ grid-template-columns:1fr; }
  .order-item .qty{ text-align:left; }
  .order-items-header{ display:none; }
  .order-item [data-label]::before{
    content: attr(data-label) ": ";
    display:block;
    color: var(--bs-secondary-color);
    font-size:.8em;
    margin-bottom:2px;
    font-weight:600;
    letter-spacing:.3px;
  }
}
</style>
</head>
<body>
<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/third-header.php'); ?>

<div class="container mb-3">
    <a href="#" class="back-line rounded-2" onclick="history.back();return false;">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
        </svg>
        <span>Go Back</span>
    </a>
</div>

<!-- Filters toolbar -->
<div class="container mb-3">
    <div class="d-flex align-items-center justify-content-start">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm"
                    type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                    <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                </svg>
                Filters
            </button>
            <form method="get" class="d-inline-block mb-0">
                <input type="hidden" name="return_status" value="<?php echo $returnStatus; ?>">
                <input type="hidden" name="order_search" value="<?php echo $orderSearch; ?>">
                <input type="hidden" name="date_from" value="<?php echo $dateFrom; ?>">
                <input type="hidden" name="date_to" value="<?php echo $dateTo; ?>">
                <input type="hidden" name="min_refund" value="<?php echo $minRefund; ?>">
                <select name="sort" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">Sort by</option>
                    <option value="recent" <?php if($sort==='recent') echo 'selected'; ?>>Most Recent</option>
                    <option value="amountHigh" <?php if($sort==='amountHigh') echo 'selected'; ?>>Amount: High to Low</option>
                    <option value="amountLow" <?php if($sort==='amountLow') echo 'selected'; ?>>Amount: Low to High</option>
                    <option value="status" <?php if($sort==='status') echo 'selected'; ?>>Status</option>
                </select>
            </form>
        </div>
        <small class="text-muted" style="margin-left: 1rem;">Showing <?php echo $return_count; ?> return<?php echo $return_count != 1 ? 's' : ''; ?></small>
    </div>
</div>

<!-- Offcanvas: Filters -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Returns</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="vstack gap-3" method="get">
            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
            <div>
                <label class="form-label">Return status</label>
                <select class="form-select" name="return_status">
                    <option value="">Any</option>
                    <option value="requested" <?php if($returnStatus==='requested') echo 'selected'; ?>>Requested</option>
                    <option value="approved" <?php if($returnStatus==='approved') echo 'selected'; ?>>Approved</option>
                    <option value="rejected" <?php if($returnStatus==='rejected') echo 'selected'; ?>>Rejected</option>
                    <option value="processed" <?php if($returnStatus==='processed') echo 'selected'; ?>>Processed</option>
                </select>
            </div>
            <div>
                <label class="form-label">Order number</label>
                <input type="text" class="form-control" name="order_search" placeholder="#1001" value="<?php echo $orderSearch; ?>">
            </div>
            <div>
                <label class="form-label">Date range</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
                    <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
                </div>
            </div>
            <div>
                <label class="form-label">Min return amount</label>
                <input type="number" step="0.01" class="form-control" name="min_refund" value="<?php echo $minRefund; ?>" placeholder="0.00">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div class="order-list">
        <?php if (empty($user_returns)): ?>
            <div class="text-muted">No returns found.</div>
        <?php else: foreach ($user_returns as $ret): ?>
        <div class="order-card">
            <div class="order-header">
                <div class="order-left">
                    <div class="order-id">Return • Order #<?php echo $ret['order_id']; ?></div>
                    <div class="order-actions">
                        <?php if ($ret['return_status'] === 'requested'): ?>
                            <a class="btn btn-primary btn-sm" href="/carriemart/user/returns/return-details.php?mode=edit&order_return_id=<?php echo $ret['order_return_id']; ?>">Edit Return Details</a>
                            <form method="post" action="/carriemart/user/returns/update.php" class="d-inline-block mb-0">
                                <input type="hidden" name="order_return_id" value="<?php echo $ret['order_return_id']; ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button class="btn btn-danger btn-sm" type="submit">Cancel Return</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="order-date">Date: <?php echo fmtDate($ret['created_at']); ?></div>
            </div>
            <div class="order-grid">
                <div class="info-sections">
                    <div class="kv"><div class="k">Order number</div><div class="v">#<?php echo $ret['order_id']; ?></div></div>

                    <div>    
                        <div class="order-items-header mb-1">
                            <div>Product</div>
                            <div>Brand</div>
                            <div class="text-end">Qty</div>
                        </div>
                        <div class="order-items">
                            <?php foreach ($ret['products'] as $prod): ?>
                            <div class="order-item">
                                <div class="title" data-label="Product"><?php echo $prod['product_name']; ?></div>
                                <div class="label" data-label="Brand"><?php echo $prod['brand_name']; ?></div>
                                <div class="qty" data-label="Qty">x<?php echo $prod['quantity']; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="kv"><div class="k">Reason</div><div class="v"><?php echo $ret['reason'] ? $ret['reason'] : '—'; ?></div></div>
                    <div class="kv"><div class="k">Condition</div><div class="v"><?php echo $ret['cond'] ? $ret['cond'] : '—'; ?></div></div>
                    <div class="kv">
                        <div class="k">Return status</div>
                        <div class="v"><?php echo statusBadge($ret['return_status']); ?></div>
                    </div>
                    <div class="kv"><div class="k">Return amount</div><div class="v"><?php echo fmtPrice($ret['refund_amount']); ?></div></div>
                    <div class="kv"><div class="k">Date</div><div class="v"><?php echo fmtDate($ret['created_at']); ?></div></div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<hr>
<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
