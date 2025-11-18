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
$orderStatus   = isset($_GET['order_status']) ? trim($_GET['order_status']) : '';
$paymentStatus = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';
$recipient     = isset($_GET['recipient']) ? trim($_GET['recipient']) : '';
$dateFrom      = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo        = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$minSubtotal   = isset($_GET['min_subtotal']) ? trim($_GET['min_subtotal']) : '';
$sort          = isset($_GET['sort']) ? trim($_GET['sort']) : '';

$conditions = ['o.user_id = ?'];
$params = [$userId];
$types  = 'i';

if ($orderStatus !== '' && in_array($orderStatus, ['pending','processing','shipped','completed','cancelled','requested_refund','returned'])) {
    $conditions[] = 'o.order_status = ?';
    $params[] = $orderStatus; $types .= 's';
}
if ($paymentStatus !== '' && in_array($paymentStatus, ['pending','paid','refunded'])) {
    $conditions[] = 'o.payment_status = ?';
    $params[] = $paymentStatus; $types .= 's';
}
if ($recipient !== '') {
    $conditions[] = 'o.delivery_recipient LIKE ?';
    $params[] = '%'.$recipient.'%'; $types .= 's';
}
if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $conditions[] = 'DATE(o.date_ordered) >= ?';
    $params[] = $dateFrom; $types .= 's';
}
if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $conditions[] = 'DATE(o.date_ordered) <= ?';
    $params[] = $dateTo; $types .= 's';
}

$whereSql = 'WHERE ' . implode(' AND ', $conditions);

$orderSql = 'ORDER BY o.order_id DESC';
switch ($sort) {
    case 'recent':
        $orderSql = 'ORDER BY o.date_ordered DESC, o.order_id DESC';
        break;
    case 'status':
        $orderSql = 'ORDER BY o.order_status ASC, o.order_id DESC';
        break;
    case 'recipientAZ':
        $orderSql = 'ORDER BY o.delivery_recipient ASC, o.order_id DESC';
        break;
}

$sql = "
SELECT 
  o.order_id, o.date_ordered, o.payment_status, o.order_status,
  o.voucher_code, o.percent_sale, o.delivery_fee,
  o.delivery_recipient, o.delivery_address, o.delivery_phone,
  o.payment_option,
  COALESCE(SUM(po.quantity * po.unit_price), 0) AS subtotal
FROM orders o
LEFT JOIN product_order po ON po.order_id = o.order_id
$whereSql
GROUP BY o.order_id
";

if ($minSubtotal !== '' && is_numeric($minSubtotal)) {
    $sql .= " HAVING subtotal >= " . (float)$minSubtotal;
}

$sql .= " $orderSql";

$user_orders = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->bind_result($oid, $dateOrd, $payStat, $ordStat, $vcode, $percent, $delFee, $recip, $addr, $phone, $payOpt, $sub);
    while ($stmt->fetch()) {
        $discount = ((int)$percent > 0) ? ($sub * ((int)$percent / 100.0)) : 0.0;
        $total = $sub - $discount + (float)$delFee;
        $user_orders[] = [
            'order_id' => $oid,
            'date_ordered' => $dateOrd,
            'payment_status' => $payStat,
            'order_status' => $ordStat,
            'voucher_code' => $vcode,
            'percent_sale' => (int)$percent,
            'delivery_fee' => (float)$delFee,
            'delivery_recipient' => $recip,
            'delivery_address' => $addr,
            'delivery_phone' => $phone,
            'payment_option' => $payOpt,
            'subtotal' => (float)$sub,
            'discount' => (float)$discount,
            'total' => (float)$total
        ];
    }
    $stmt->close();
}

// For each order, fetch lines and check for return
foreach ($user_orders as $key => $ord) {
    $lines = [];
    $ls = $conn->prepare("
        SELECT po.product_order_id, po.product_id, po.quantity, po.unit_price, p.product_name, b.brand_name,
               EXISTS(SELECT 1 FROM product_review pr WHERE pr.product_order_id = po.product_order_id AND pr.user_id = ?) AS has_review
        FROM product_order po
        JOIN products p ON p.product_id = po.product_id
        LEFT JOIN brands b ON b.brand_id = p.brand_id
        WHERE po.order_id = ?
    ");
    if ($ls) {
        $ls->bind_param('ii', $userId, $ord['order_id']);
        $ls->execute();
        $ls->bind_result($poid, $pid, $qty, $unit, $pname, $brand, $hasRev);
        while ($ls->fetch()) {
            $lines[] = [
                'product_order_id' => $poid,
                'product_id' => $pid,
                'product_name' => $pname,
                'brand_name' => $brand ? $brand : 'Unknown',
                'quantity' => (int)$qty,
                'unit_price' => (float)$unit,
                'line_total' => (float)$unit * (int)$qty,
                'has_review' => (int)$hasRev
            ];
        }
        $ls->close();
    }
    $user_orders[$key]['lines'] = $lines;
    
    // Check if order has a return request
    $retChk = $conn->prepare("SELECT order_return_id, return_status FROM order_return WHERE order_id = ?");
    if ($retChk) {
        $retChk->bind_param('i', $ord['order_id']);
        $retChk->execute();
        $retChk->bind_result($ret_id, $ret_status);
        if ($retChk->fetch()) {
            $user_orders[$key]['has_return'] = true;
            $user_orders[$key]['return_id'] = $ret_id;
            $user_orders[$key]['return_status'] = $ret_status;
        } else {
            $user_orders[$key]['has_return'] = false;
            $user_orders[$key]['return_id'] = null;
            $user_orders[$key]['return_status'] = null;
        }
        $retChk->close();
    } else {
        $user_orders[$key]['has_return'] = false;
        $user_orders[$key]['return_id'] = null;
        $user_orders[$key]['return_status'] = null;
    }
}

$order_count = count($user_orders);

function fmtPrice($v) { return '₱' . number_format((float)$v, 2, '.', ','); }
function fmtDate($d) { return date('Y-m-d H:i', strtotime($d)); }
function statusBadge($status) {
    $map = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'completed' => 'success',
        'cancelled' => 'danger',
        'requested_refund' => 'secondary',
        'returned' => 'dark'
    ];
    $class = isset($map[$status]) ? $map[$status] : 'secondary';
    return '<span class="badge bg-'.$class.'">'.$status.'</span>';
}
function paymentBadge($status) {
    $map = ['pending'=>'warning','paid'=>'success','refunded'=>'info'];
    $class = isset($map[$status]) ? $map[$status] : 'secondary';
    return '<span class="badge bg-'.$class.'">'.$status.'</span>';
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
/* Back line */
.back-line {
    display: flex; align-items: center; gap: .5rem;
    padding: .5rem .75rem; border-bottom: 1px solid var(--bs-border-color);
    color: var(--bs-body-color); text-decoration: none;
}
.back-line:hover { background-color: rgba(var(--bs-primary-rgb), .06); text-decoration: none; }
.back-line .icon { width: 20px; height: 20px; opacity: .9; }

/* Cart layout */
.cart-list { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.cart-card {
    border: 1px solid transparent;
    transition: border-color .15s ease;
    background: #fff;
}
.cart-card:hover { border-color: rgba(0,0,0,.15);  }

.card-header { background: transparent; }

.cart-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    column-gap: 1rem;
    padding-top: .5rem;
}
.cell-check { display: flex; align-items: center; justify-content: center; min-width: 42px; }
.cell-product { min-width: 0; }
.product-content {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: .75rem;
    align-items: center;
}
.product-img {
    width: 110px;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: .5rem;
    background: #f8f9fa;
}
.product-meta h6 { margin: 0 0 .125rem 0; font-size: .95rem; }
.brand { color: var(--bs-secondary-color); font-size: .75rem; }
.price { font-weight: 600; margin-top: .25rem; font-size: .85rem; }

.cell-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    gap: .5rem;
    padding-right: .75rem;
    min-width: 115px;
}
.qty-wrapper { text-align: right; }
.qty-wrapper .label { font-size: .65rem; text-transform: uppercase; letter-spacing: .5px; color: var(--bs-secondary-color); }
.qty { display: inline-flex; align-items: center; gap: .5rem; }
.qty .btn { width: 34px; padding: .125rem 0; }
.qty-value { min-width: 2rem; text-align: center; font-weight: 600; }


.item-tabs { border: none; }
.item-tabs .nav-link {
    border: none;
    border-radius: 0;
    font-size: .9rem;
    color: #dc3545;
}
.item-tabs .nav-link:hover { background: rgba(220,53,69,.1); }
.item-tabs .nav-link:focus { background: rgba(220,53,69,.18); }

/* Orders layout */
.order-list { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.order-card { background: #fff; border-radius: .5rem; border:1px solid transparent; transition:border-color .15s ease; }
.order-card:hover { border-color: rgba(0,0,0,.2); }

.order-card { max-width: 900px; margin: 0 auto; }

.order-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06);
    flex-wrap: wrap; gap: .5rem;
}
.order-id { font-weight: 600; }
.order-date { color: var(--bs-secondary-color); font-size: .875rem; }

.order-left { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.order-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.order-actions .btn-sm { padding:.25rem .5rem; }

.order-grid {
     display: grid;
    grid-template-columns: 1fr;
     gap: 1rem;
     padding: 1rem;
}
@media (max-width: 576px) {
    .order-grid { grid-template-columns: 1fr; }
}

.info-sections { display: grid; gap: .75rem; }
.section-title {
    font-size: .75rem; letter-spacing: .5px; text-transform: uppercase;
    color: var(--bs-secondary-color); font-weight: 600; margin-bottom: .25rem;
}

.product-list { display: grid; gap: .5rem; }
.product-row {
    display: grid;
    grid-template-columns: 2fr 1.2fr 110px 80px 180px; 
    column-gap: .75rem;
    row-gap: .25rem;
    padding: .5rem .75rem; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: .375rem;
    align-items: center;
}
.product-row .price { font-weight: 600; text-align: right; }
.product-row .qty { text-align: right; white-space: nowrap; }

@media (max-width: 768px) {
  .product-row { grid-template-columns: 1.6fr 1.1fr 90px 60px 160px; column-gap:.5rem; }
}
@media (max-width: 576px) {
  .product-row { grid-template-columns: 1fr; }
  .product-actions { margin-top: .25rem; }
  .product-row .price, .product-row .qty { text-align: left; }
}


.kv {
    display: grid; grid-template-columns: 180px 1fr; gap: .5rem;
    padding: .5rem .75rem; border: 1px solid #e9ecef; border-radius: .375rem; background: #fcfcfd;
}
.kv .k { color: var(--bs-secondary-color); font-size: .85rem; }
.kv .v { font-weight: 500; }

.summary {
    display: grid; gap: 0;
}
.summary .line {
    display: grid; grid-template-columns: 1fr auto; gap: .75rem;
    padding: .5rem .75rem; border: 1px solid #e9ecef; border-radius: .375rem; background: #fcfcfd;
}
.summary .muted { color: var(--bs-secondary-color); font-size: .875rem; }

.actions {
    display: flex; flex-direction: column; gap: .5rem; align-items: stretch; justify-content: flex-start;
}
.actions .btn { width: 100%; }

.split-two { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
@media (max-width: 576px) { .split-two { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/third-header.php'); ?>

<!-- Go Back line -->
<div class="container mb-3">
    <a href="#" class="back-line rounded-2" onclick="history.back(); return false;">
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
                <input type="hidden" name="order_status" value="<?php echo $orderStatus; ?>">
                <input type="hidden" name="payment_status" value="<?php echo $paymentStatus; ?>">
                <input type="hidden" name="recipient" value="<?php echo $recipient; ?>">
                <input type="hidden" name="date_from" value="<?php echo $dateFrom; ?>">
                <input type="hidden" name="date_to" value="<?php echo $dateTo; ?>">
                <input type="hidden" name="min_subtotal" value="<?php echo $minSubtotal; ?>">
                <select name="sort" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">Sort by</option>
                    <option value="recent" <?php if($sort==='recent') echo 'selected'; ?>>Most Recent</option>
                    <option value="status" <?php if($sort==='status') echo 'selected'; ?>>Status</option>
                    <option value="recipientAZ" <?php if($sort==='recipientAZ') echo 'selected'; ?>>Recipient A–Z</option>
                </select>
            </form>
        </div>
        <small class="text-muted" style="margin-left: 1rem;">Showing <?php echo $order_count; ?> orders</small>
    </div>
</div>

<!-- Offcanvas: Filters -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Orders</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="vstack gap-3" method="get">
            <input type="hidden" name="sort" value="<?php echo $sort; ?>">
            <div>
                <label class="form-label">Order status</label>
                <select class="form-select" name="order_status">
                    <option value="">Any</option>
                    <option value="pending" <?php if($orderStatus==='pending') echo 'selected'; ?>>Pending</option>
                    <option value="processing" <?php if($orderStatus==='processing') echo 'selected'; ?>>Processing</option>
                    <option value="shipped" <?php if($orderStatus==='shipped') echo 'selected'; ?>>Shipped</option>
                    <option value="completed" <?php if($orderStatus==='completed') echo 'selected'; ?>>Completed</option>
                    <option value="cancelled" <?php if($orderStatus==='cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>
            <div>
                <label class="form-label">Payment status</label>
                <select class="form-select" name="payment_status">
                    <option value="">Any</option>
                    <option value="pending" <?php if($paymentStatus==='pending') echo 'selected'; ?>>Pending</option>
                    <option value="paid" <?php if($paymentStatus==='paid') echo 'selected'; ?>>Paid</option>
                    <option value="refunded" <?php if($paymentStatus==='refunded') echo 'selected'; ?>>Refunded</option>
                </select>
            </div>
            <div>
                <label class="form-label">Recipient</label>
                <input type="text" class="form-control" name="recipient" value="<?php echo $recipient; ?>" placeholder="Search recipient">
            </div>
            <div>
                <label class="form-label">Date range</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
                    <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
                </div>
            </div>
            <div>
                <label class="form-label">Min subtotal</label>
                <input type="number" step="0.01" class="form-control" name="min_subtotal" value="<?php echo $minSubtotal; ?>" placeholder="0.00">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div class="order-list">
        <?php if (empty($user_orders)): ?>
            <div class="text-muted">No orders found.</div>
        <?php else: foreach ($user_orders as $o): ?>
        <div class="order-card">
            <div class="order-header">
                <div class="order-left">
                    <div class="order-id">Order #<?php echo $o['order_id']; ?></div>
                    <div class="order-actions">
                        <?php if ($o['order_status'] === 'pending' || $o['order_status'] === 'processing'): ?>
                            <a href="/carriemart/user/orders/order-form.php?id=<?php echo $o['order_id']; ?>" class="btn btn-primary btn-sm">Edit Delivery Details</a>
                        <?php endif; ?>
                        <?php if ($o['order_status'] === 'completed'): ?>
                            <?php if (isset($o['has_return']) && $o['has_return']): ?>
                                <a href="/carriemart/user/returns/return-details.php?mode=view&order_return_id=<?php echo $o['return_id']; ?>" class="btn btn-outline-info btn-sm">View Return (<?php echo $o['return_status']; ?>)</a>
                            <?php else: ?>
                                <a href="/carriemart/user/returns/create.php?order_id=<?php echo $o['order_id']; ?>" class="btn btn-outline-secondary btn-sm">Return/Refund</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($o['order_status'] === 'pending' || $o['order_status'] === 'processing'): ?>
                            <form method="post" action="/carriemart/user/orders/update.php" class="d-inline-block mb-0">
                                <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button class="btn btn-outline-danger btn-sm" type="submit">Cancel Order</button>
                            </form>
                        <?php endif; ?>
        
                    </div>
                </div>
                <div class="order-date">Date Ordered: <?php echo fmtDate($o['date_ordered']); ?></div>
            </div>
            <div class="order-grid">
                <div class="info-sections">
                    <div>
                        <div class="section-title">Products</div>
                        <div class="product-list">
                            <?php foreach ($o['lines'] as $ln): ?>
                            <div class="product-row">
    <div class="title"><?php echo $ln['product_name']; ?></div>
    <div class="label"><?php echo $ln['brand_name']; ?></div>
    <div class="price"><?php echo fmtPrice($ln['unit_price']); ?></div>
    <div class="qty">Qty: <?php echo $ln['quantity']; ?></div>
    <div class="product-actions">
        <?php if ((int)$ln['has_review'] === 1): ?>
            <a class="btn btn-outline-secondary btn-sm w-100 my-1" href="/carriemart/user/reviews/review-details.php?mode=edit&product_order_id=<?php echo $ln['product_order_id']; ?>">Edit Review</a>
        <?php else: ?>
            <a class="btn btn-outline-secondary btn-sm w-100 my-1" href="/carriemart/user/reviews/review-details.php?mode=add&product_order_id=<?php echo $ln['product_order_id']; ?>">Add Review</a>
        <?php endif; ?>
    </div>
</div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div>
                        <div class="section-title">Delivery</div>
                        <div class="kv"><div class="k">Recipient</div><div class="v"><?php echo $o['delivery_recipient'] ? $o['delivery_recipient'] : '—'; ?></div></div>
                        <div class="kv"><div class="k">Address</div><div class="v"><?php echo $o['delivery_address'] ? $o['delivery_address'] : '—'; ?></div></div>
                        <div class="kv"><div class="k">Phone</div><div class="v"><?php echo $o['delivery_phone'] ? $o['delivery_phone'] : '—'; ?></div></div>
                    </div>

                    <div class="split-two">
                        <div>
                            <div class="section-title">Payment</div>
                            <div class="kv"><div class="k">Payment Option</div><div class="v"><?php echo $o['payment_option'] ? $o['payment_option'] : '—'; ?></div></div>
                            <div class="kv"><div class="k">Payment Status</div><div class="v"><?php echo paymentBadge($o['payment_status']); ?></div></div>
                            <div class="kv"><div class="k">Order Status</div><div class="v"><?php echo statusBadge($o['order_status']); ?></div></div>
                            <?php if (isset($o['has_return']) && $o['has_return']): ?>
                            <div class="kv">
                                <div class="k">Return Status</div>
                                <div class="v">
                                    <span class="badge bg-info">Return <?php echo $o['return_status']; ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="section-title">Summary</div>
                            <div class="summary">
                                <div class="line"><span>Subtotal</span><span><?php echo fmtPrice($o['subtotal']); ?></span></div>
                                <div class="line"><span>Delivery Fee</span><span><?php echo fmtPrice($o['delivery_fee']); ?></span></div>
                                <?php if ($o['percent_sale'] > 0): ?>
                                <div class="line"><span>Discount (<?php echo $o['percent_sale']; ?>%)</span><span>-<?php echo fmtPrice($o['discount']); ?></span></div>
                                <?php endif; ?>
                                <div class="line"><strong>Total</strong><strong><?php echo fmtPrice($o['total']); ?></strong></div>
                            </div>
                        </div>
                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>