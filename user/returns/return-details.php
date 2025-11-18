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
$mode = isset($_GET['mode']) ? trim($_GET['mode']) : 'view';
$order_return_id = isset($_GET['order_return_id']) ? (int)$_GET['order_return_id'] : 0;

if ($order_return_id <= 0) {
    header('Location: /carriemart/user/returns/returns.php?error=invalid_id');
    exit;
}

// Load return data
$returnData = [];
$stmt = $conn->prepare("
    SELECT ord_ret.order_return_id, ord_ret.order_id, ord_ret.reason, ord_ret.cond, ord_ret.return_status, 
           ord_ret.refund_amount, ord_ret.processed_at, ord_ret.created_at
    FROM order_return ord_ret
    INNER JOIN orders o ON ord_ret.order_id = o.order_id
    WHERE ord_ret.order_return_id = ? AND o.user_id = ?
");
if (!$stmt) {
    header('Location: /carriemart/user/returns/returns.php?error=server');
    exit;
}
$stmt->bind_param('ii', $order_return_id, $userId);
$stmt->execute();
$stmt->bind_result($orid, $oid, $reason, $cond, $rstat, $refund, $proc_at, $created);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: /carriemart/user/returns/returns.php?error=not_found');
    exit;
}
$stmt->close();

$returnData = [
    'order_return_id' => $orid,
    'order_id' => $oid,
    'reason' => $reason,
    'cond' => $cond,
    'return_status' => $rstat,
    'refund_amount' => (float)$refund,
    'processed_at' => $proc_at,
    'created_at' => $created
];

// Get order products
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
    $ps->bind_param('i', $returnData['order_id']);
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

$canEdit = ($returnData['return_status'] === 'requested' && $mode === 'edit');
$reason = $returnData['reason'] ? $returnData['reason'] : '';
$cond = $returnData['cond'] ? $returnData['cond'] : 'other';

function fmtPrice($v) { return '₱' . number_format((float)$v, 2, '.', ','); }
function fmtDate($d) { return date('Y-m-d H:i', strtotime($d)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Return Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .container { max-width: 960px; }
        .btn-icon-inverted img { width:1.125rem; height:1.125rem; filter:invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%); opacity:.95; }
        .readonly-box { background:#f8f9fa; border:1px solid #dee2e6; padding:.65rem .75rem; border-radius:.375rem; font-weight:500; }
        .label-small { font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:var(--bs-secondary-color); margin-bottom:.25rem; }
    </style>
</head>
<body>
    <div class="container">
        <main>
            <div class="py-4 text-center">
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Logo.svg" alt="" width="72" height="57">
            </div>
            <div class="row g-5">
                <div class="col-md-5 col-lg-4 order-md-last my-5">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary">Your items</span>
                        <span class="badge bg-primary rounded-pill"><?php echo count($products); ?></span>
                    </h4>
                    <ul class="list-group mb-3">
                        <?php foreach ($products as $prod): ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0"><?php echo $prod['product_name']; ?></h6>
                                <small class="text-body-secondary"><?php echo $prod['brand_name']; ?> • Qty: <?php echo $prod['quantity']; ?></small>
                            </div>
                            <span class="text-body-secondary"><?php echo fmtPrice($prod['line_total']); ?></span>
                        </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between bg-body-tertiary">
                            <div class="text-success">
                                <h6 class="my-0">Refund Amount</h6>
                            </div>
                            <span class="text-success"><?php echo fmtPrice($returnData['refund_amount']); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total Items</span>
                            <strong><?php echo count($products); ?></strong>
                        </li>
                    </ul>
                </div>
                <div class="col-md-7 col-lg-8 my-5">
                    <h4 class="mb-3">Return Information</h4>
                    <?php if ($canEdit): ?>
                    <form method="POST" action="update.php">
                        <input type="hidden" name="order_return_id" value="<?php echo $returnData['order_return_id']; ?>">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="reason" class="form-label">Reason for Return</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Describe the issue..."><?php echo $reason; ?></textarea>
                            </div>
                            <div class="col-12">
                                <label for="cond" class="form-label">Item Condition</label>
                                <select class="form-select" id="cond" name="cond">
                                    <option value="new" <?php if($cond==='new') echo 'selected'; ?>>New</option>
                                    <option value="opened" <?php if($cond==='opened') echo 'selected'; ?>>Opened</option>
                                    <option value="damaged" <?php if($cond==='damaged') echo 'selected'; ?>>Damaged</option>
                                    <option value="other" <?php if($cond==='other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <div class="label-small">Refund Amount</div>
                                <div class="readonly-box"><?php echo fmtPrice($returnData['refund_amount']); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="label-small">Date</div>
                                <div class="readonly-box"><?php echo fmtDate($returnData['created_at']); ?></div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="d-flex w-100 gap-2">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center btn-icon-inverted"
                                    type="submit" style="flex:2 1 0%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                     class="bi bi-bag-check me-2" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                                </svg>
                                Save Return Changes
                            </button>
                            <button type="button"
                                    class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                    style="flex:1 1 0%;" onclick="history.back()">
                                Go back
                                <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Reason for Return</label>
                            <div class="readonly-box"><?php echo $reason ? $reason : '—'; ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Item Condition</label>
                            <div class="readonly-box"><?php echo $cond ? $cond : '—'; ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Return Status</label>
                            <div class="readonly-box"><?php echo $returnData['return_status']; ?></div>
                        </div>
                        <div class="col-6">
                            <div class="label-small">Refund Amount</div>
                            <div class="readonly-box"><?php echo fmtPrice($returnData['refund_amount']); ?></div>
                        </div>
                        <div class="col-6">
                            <div class="label-small">Date</div>
                            <div class="readonly-box"><?php echo fmtDate($returnData['created_at']); ?></div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="d-flex w-100 gap-2">
                        <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                style="flex:1 1 0%;" onclick="history.back()">
                            Go back
                            <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"></script>
</body>
</html>
