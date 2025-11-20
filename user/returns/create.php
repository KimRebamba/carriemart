<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      
    if (!$conn) { die('DB error'); }
    if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
        header('Location: /carriemart/main/products.php?error=login_required');
        exit;
    }
    
    $userId = (int)$_SESSION['user_id'];
    $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    
    if ($order_id <= 0) {
        header('Location: /carriemart/user/orders/orders.php?error=invalid_order');
        exit;
    }
    
      
    $chk = $conn->prepare("SELECT order_id, order_status FROM orders WHERE order_id = ? AND user_id = ?");
    if (!$chk) {
        header('Location: /carriemart/user/orders/orders.php?error=server');
        exit;
    }
    $chk->bind_param('ii', $order_id, $userId);
    $chk->execute();
    $chk->bind_result($oid, $ostatus);
    if (!$chk->fetch()) {
        $chk->close();
        header('Location: /carriemart/user/orders/orders.php?error=not_found');
        exit;
    }
    $chk->close();
    
    if ($ostatus !== 'completed') {
        header('Location: /carriemart/user/orders/orders.php?error=order_not_completed');
        exit;
    }
    
      
    $chkRet = $conn->prepare("SELECT order_return_id FROM order_return WHERE order_id = ?");
    if ($chkRet) {
        $chkRet->bind_param('i', $order_id);
        $chkRet->execute();
        $chkRet->store_result();
        if ($chkRet->num_rows > 0) {
            $chkRet->close();
            header('Location: /carriemart/user/returns/returns.php?error=return_exists');
            exit;
        }
        $chkRet->close();
    }
    
      
    $products = [];
    $total = 0.0;
    $ps = $conn->prepare("
        SELECT po.product_order_id, po.product_id, po.quantity, po.unit_price, p.product_name,
               COALESCE(b.brand_name, 'Unknown') AS brand_name
        FROM product_order po
        INNER JOIN products p ON po.product_id = p.product_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE po.order_id = ?
    ");
    if ($ps) {
        $ps->bind_param('i', $order_id);
        $ps->execute();
        $ps->bind_result($poid, $pid, $qty, $unit, $pname, $brand);
        while ($ps->fetch()) {
            $lineTotal = (float)$unit * (int)$qty;
            $total += $lineTotal;
            $products[] = [
                'product_order_id' => $poid,
                'product_id' => $pid,
                'product_name' => $pname,
                'brand_name' => $brand,
                'quantity' => (int)$qty,
                'unit_price' => (float)$unit,
                'line_total' => $lineTotal
            ];
        }
        $ps->close();
    }
    
    function fmtPrice($v) { return '₱' . number_format((float)$v, 2, '.', ','); }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CarrieMart: Create Return</title>
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
                                <span class="text-success"><?php echo fmtPrice($total); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total Items</span>
                                <strong><?php echo count($products); ?></strong>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-7 col-lg-8 my-5">
                        <h4 class="mb-3">Return Information</h4>
                        <form method="POST" action="create.php">
                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="reason" class="form-label">Reason for Return</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Describe the issue..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="cond" class="form-label">Item Condition</label>
                                    <select class="form-select" id="cond" name="cond">
                                        <option value="new">New</option>
                                        <option value="opened">Opened</option>
                                        <option value="damaged" selected>Damaged</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <div class="label-small">Refund Amount</div>
                                    <div class="readonly-box"><?php echo fmtPrice($total); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="label-small">Date</div>
                                    <div class="readonly-box"><?php echo date('Y-m-d H:i'); ?></div>
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
                                    Submit Return Request
                                </button>
                                <button type="button"
                                        class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                        style="flex:1 1 0%;" onclick="history.back()">
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
                integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"></script>
    </body>
    </html>
    <?php
    exit;
}

   
if (!$conn) { die('DB error'); }
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$cond = isset($_POST['cond']) ? trim($_POST['cond']) : 'other';

if ($order_id <= 0) {
    header('Location: /carriemart/user/orders/orders.php?error=invalid_order');
    exit;
}

if (!in_array($cond, ['new','opened','damaged','other'])) {
    $cond = 'other';
}

   
$chk = $conn->prepare("SELECT order_id, order_status FROM orders WHERE order_id = ? AND user_id = ?");
if (!$chk) {
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$chk->bind_param('ii', $order_id, $userId);
$chk->execute();
$chk->bind_result($oid, $ostatus);
if (!$chk->fetch()) {
    $chk->close();
    header('Location: /carriemart/user/orders/orders.php?error=not_found');
    exit;
}
$chk->close();

if ($ostatus !== 'completed') {
    header('Location: /carriemart/user/orders/orders.php?error=order_not_completed');
    exit;
}

   
$chkRet = $conn->prepare("SELECT order_return_id FROM order_return WHERE order_id = ?");
if ($chkRet) {
    $chkRet->bind_param('i', $order_id);
    $chkRet->execute();
    $chkRet->store_result();
    if ($chkRet->num_rows > 0) {
        $chkRet->close();
        header('Location: /carriemart/user/returns/returns.php?error=return_exists');
        exit;
    }
    $chkRet->close();
}

   
$refundAmount = 0.0;
$calc = $conn->prepare("SELECT SUM(quantity * unit_price) FROM product_order WHERE order_id = ?");
if ($calc) {
    $calc->bind_param('i', $order_id);
    $calc->execute();
    $calc->bind_result($total);
    if ($calc->fetch()) {
        $refundAmount = (float)$total;
    }
    $calc->close();
}

   
$reason_clean = $reason !== '' ? $reason : null;
$ins = $conn->prepare("INSERT INTO order_return (order_id, reason, cond, return_status, refund_amount) VALUES (?, ?, ?, 'requested', ?)");
if (!$ins) {
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$ins->bind_param('issd', $order_id, $reason_clean, $cond, $refundAmount);
if (!$ins->execute()) {
    $ins->close();
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$ins->close();

   
$updOrder = $conn->prepare("UPDATE orders SET order_status = 'requested_refund' WHERE order_id = ?");
if ($updOrder) {
    $updOrder->bind_param('i', $order_id);
    $updOrder->execute();
    $updOrder->close();
}

header('Location: /carriemart/user/returns/returns.php?status=created');
exit;
?>
