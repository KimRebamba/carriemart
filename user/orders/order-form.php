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
$defaultPhoto = '/carriemart/assets/default-product.png';

function fmtPrice($v) { return '₱' . number_format((float)$v, 2, '.', ','); }

// Decide mode (create vs edit)
$oid_raw = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : '');
$isEdit = (ctype_digit((string)$oid_raw) && (int)$oid_raw > 0);
$orderId = $isEdit ? (int)$oid_raw : 0;

// Error messages from redirect
$errors = [];
if (isset($_GET['error']) && $_GET['error'] !== '') {
    $errs = explode('|', $_GET['error']);
    foreach ($errs as $e) {
        if ($e !== '') $errors[] = $e;
    }
}
$success = false;

$delivery_recipient = '';
$delivery_address   = '';
$delivery_phone     = '';
$payment_option     = 'cash_on_delivery';
$voucher_code       = '';

$items = [];
$item_count = 0;
$subtotal = 0.00;
$percent_sale = 0;
$delivery_fee = 0.00;
$discount = 0.00;
$total = 0.00;

// If editing, load order + lines
if ($isEdit) {
    $st = $conn->prepare("
        SELECT order_id, date_ordered, payment_status, order_status, voucher_code, percent_sale, delivery_fee,
               delivery_recipient, delivery_address, delivery_phone, payment_option
        FROM orders
        WHERE order_id = ? AND user_id = ?
        LIMIT 1
    ");
    $st->bind_param('ii', $orderId, $userId);
    $st->execute();
    $st->bind_result($oid, $dateOrd, $payStat, $ordStat, $vcode, $percent, $delFee, $recip, $addr, $phone, $payOpt);
    if ($st->fetch()) {
        $delivery_recipient = $recip ? $recip : '';
        $delivery_address   = $addr ? $addr : '';
        $delivery_phone     = $phone ? $phone : '';
        $payment_option     = $payOpt ? $payOpt : 'cash_on_delivery';
        $voucher_code       = $vcode ? $vcode : '';
        $percent_sale       = (int)$percent;
        $delivery_fee       = (float)$delFee;
    }
    $st->close();

    // Lines for sidebar
    $ls = $conn->prepare("
        SELECT po.product_id, po.quantity, po.unit_price, p.product_name,
               COALESCE(ph.photo_url, ?) AS photo_url
        FROM product_order po
        JOIN products p ON p.product_id = po.product_id
        LEFT JOIN (
            SELECT product_id, MIN(photo_url) AS photo_url
            FROM product_photos
            WHERE is_primary = 1
            GROUP BY product_id
        ) ph ON ph.product_id = p.product_id
        WHERE po.order_id = ?
        ORDER BY po.product_order_id DESC
    ");
    $ls->bind_param('si', $defaultPhoto, $orderId);
    $ls->execute();
    $ls->bind_result($pid, $qty, $unit, $pname, $photo);
    while ($ls->fetch()) {
        $line = (float)$unit * (int)$qty;
        $subtotal += $line;
        $items[] = [
            'product_id' => $pid,
            'name' => $pname,
            'qty' => (int)$qty,
            'unit' => (float)$unit,
            'photo' => $photo ?: $defaultPhoto
        ];
    }
    $ls->close();
    $item_count = count($items);
    $discount = $percent_sale > 0 ? ($subtotal * ($percent_sale / 100.0)) : 0.0;
    $total = $subtotal - $discount + $delivery_fee;

} else {
    // Create mode: snapshot cart
    $cartId = null;
    $sc = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ? LIMIT 1");
    $sc->bind_param('i', $userId);
    $sc->execute();
    $sc->bind_result($cartId);
    if (!$sc->fetch()) $cartId = null;
    $sc->close();

    if ($cartId) {
        $cs = $conn->prepare("
            SELECT cp.product_id, cp.quantity, p.product_name, p.retail_price,
                   COALESCE(ph.photo_url, ?) AS photo_url
            FROM cart_product cp
            JOIN products p ON p.product_id = cp.product_id
            LEFT JOIN (
                SELECT product_id, MIN(photo_url) AS photo_url
                FROM product_photos
                WHERE is_primary = 1
                GROUP BY product_id
            ) ph ON ph.product_id = p.product_id
            WHERE cp.cart_id = ?
            ORDER BY cp.cart_product_id DESC
        ");
        $cs->bind_param('si', $defaultPhoto, $cartId);
        $cs->execute();
        $cs->bind_result($pid, $qty, $pname, $price, $photo);
        while ($cs->fetch()) {
            $line = (float)$price * (int)$qty;
            $subtotal += $line;
            $items[] = [
                'product_id' => $pid,
                'name' => $pname,
                'qty' => (int)$qty,
                'unit' => (float)$price,
                'photo' => $photo ?: $defaultPhoto
            ];
        }
        $cs->close();
        $item_count = count($items);
    }
    // Optional voucher preview (if user typed one)
    if (isset($_POST['redeem']) || isset($_POST['voucher'])) {
        $voucher_code = trim($_POST['voucher'] ?? '');
    }
    if ($voucher_code !== '') {
        $v = $conn->prepare("
            SELECT percent_sale, min_purchase_amount, max_discount_amount, from_date, to_date, is_active
            FROM vouchers
            WHERE voucher_code = ?
            LIMIT 1
        ");
        $v->bind_param('s', $voucher_code);
        $v->execute();
        $v->bind_result($vperc, $vmin, $vmax, $vfrom, $vto, $vactive);
        if ($v->fetch()) {
            $today = date('Y-m-d');
            $valid = ((int)$vactive === 1);
            if ($vfrom && $today < $vfrom) $valid = false;
            if ($vto && $today > $vto) $valid = false;
            if ($subtotal < (float)$vmin) $valid = false;
            if ($valid && (int)$vperc > 0) {
                $percent_sale = (int)$vperc;
                $rawDisc = $subtotal * ($percent_sale / 100.0);
                $discount = ($vmax !== null && (float)$vmax > 0) ? min($rawDisc, (float)$vmax) : $rawDisc;
            }
        }
        $v->close();
    }
    $delivery_fee = 0.00;
    $total = $subtotal - $discount + $delivery_fee;
}

// Handle POST submit for validation then forward to update-delivery-details.php (edit mode only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order_form']) && $isEdit) {
    $delivery_recipient = isset($_POST['delivery_recipient']) ? trim($_POST['delivery_recipient']) : '';
    $delivery_address   = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
    $delivery_phone     = isset($_POST['delivery_phone']) ? trim($_POST['delivery_phone']) : '';
    $payment_option     = isset($_POST['payment_option']) ? trim($_POST['payment_option']) : $payment_option;
    $voucher_code       = isset($_POST['voucher']) ? trim($_POST['voucher']) : $voucher_code;

    // Forward via POST to update-delivery-details.php
    $forward = [
        'order_id'           => $orderId,
        'delivery_recipient' => $delivery_recipient,
        'delivery_address'   => $delivery_address,
        'delivery_phone'     => $delivery_phone,
        'payment_option'     => $payment_option,
        'voucher'            => $voucher_code
    ];
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Forwarding…</title></head><body>';
    echo '<form id="fwd" method="post" action="/carriemart/user/orders/update-delivery-details.php">';
    foreach ($forward as $k => $v) {
        echo '<input type="hidden" name="'.$k.'" value="'.$v.'">';
    }
    echo '</form><script>document.getElementById("fwd").submit();</script></body></html>';
    exit;
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CarrieMart: Order-Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 960px; }
        .btn-icon-inverted img {
            width: 1.125rem; height: 1.125rem;
            filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
            opacity: .95;
        }
        .list-thumb { width:48px; height:48px; object-fit:cover; border-radius:.35rem; margin-right:.5rem; }
        .lh-sm .small { font-size:.825rem; }
    </style>
</head>
<body>
    <div class="container">
        <main>
            <div class="py-4 text-center">
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Header-Logo-01.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-5 col-lg-4 order-md-last my-5">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary"><?php echo $isEdit ? 'Order items' : 'Your items'; ?></span>
                        <span class="badge bg-primary rounded-pill"><?php echo (int)$item_count; ?></span>
                    </h4>
                    <ul class="list-group mb-3">
                        <?php if (!empty($items)): foreach ($items as $it): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center lh-sm">
                            <div class="d-flex align-items-center">
                                <img class="list-thumb" src="<?php echo $it['photo']; ?>" alt="">
                                <div>
                                    <h6 class="my-0"><?php echo $it['name']; ?></h6>
                                    <small class="text-body-secondary">Qty: <?php echo $it['qty']; ?></small>
                                </div>
                            </div>
                            <span class="text-body-secondary"><?php echo fmtPrice($it['unit'] * $it['qty']); ?></span>
                        </li>
                        <?php endforeach; else: ?>
                        <li class="list-group-item">No items.</li>
                        <?php endif; ?>

                        <?php if ($voucher_code !== '' && $discount > 0): ?>
                        <li class="list-group-item d-flex justify-content-between bg-body-tertiary">
                            <div class="text-success">
                                <h6 class="my-0">Promo code</h6>
                                <small><?php echo $voucher_code; ?></small>
                            </div>
                            <span class="text-success">-<?php echo fmtPrice($discount); ?></span>
                        </li>
                        <?php endif; ?>

                        <?php if ($delivery_fee > 0): ?>
                        <li class="list-group-item d-flex justify-content-between bg-body-tertiary">
                            <div class="text-danger">
                                <h6 class="my-0">Delivery Fee</h6>
                            </div>
                            <span class="text-danger"><?php echo fmtPrice($delivery_fee); ?></span>
                        </li>
                        <?php endif; ?>

                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total</span>
                            <strong><?php echo fmtPrice($total); ?></strong>
                        </li>
                    </ul>
                </div>

                <div class="col-md-7 col-lg-8 my-5">
                    <h4 class="mb-3"><?php echo $isEdit ? 'Update Order' : 'Order Information'; ?></h4>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3" role="alert">
                            <?php foreach ($errors as $e): ?>
                                <div>- <?php echo $e; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Keep your existing frontend; wired names/values below -->
                    <form method="post" class="needs-validation" novalidate>
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="id" value="<?php echo $orderId; ?>">
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Delivery Recipient</label>
                                <input type="text" class="form-control" id="name" name="delivery_recipient" placeholder="e.g. Berto" value="<?php echo $delivery_recipient; ?>">
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Delivery Address</label>
                                <input type="text" class="form-control" id="address" name="delivery_address" placeholder="e.g. 1234 Main St" value="<?php echo $delivery_address; ?>">
                            </div>

                            <div class="col-12">
                                <label for="phone" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="phone" name="delivery_phone" placeholder="e.g. 09##-####-###" value="<?php echo $delivery_phone; ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-3">Payment Option</h4>
                        <div class="my-3">
                            <div class="form-check">
                                <input id="cod" name="payment_option" type="radio" class="form-check-input" value="cash_on_delivery" <?php echo ($payment_option==='cash_on_delivery' ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="cod">Cash on Delivery</label>
                            </div>
                            <div class="form-check">
                                <input id="cc" name="payment_option" type="radio" class="form-check-input" value="credit_card" <?php echo ($payment_option==='credit_card' ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="cc">Credit card</label>
                            </div>
                            <div class="form-check">
                                <input id="gcash" name="payment_option" type="radio" class="form-check-input" value="gcash" <?php echo ($payment_option==='gcash' ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="gcash">Gcash</label>
                            </div>
                        </div>

                        <input type="hidden" name="voucher" value="<?php echo $voucher_code; ?>">

                        <hr class="my-4">
                        <div class="d-flex w-100 gap-2">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center btn-icon-inverted" type="submit" name="submit_order_form" style="flex: 2 1 0%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-check me-2" viewBox="0 0 16 16" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                    <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                                </svg>
                                Submit Changes
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted" style="flex: 1 1 0%;" onclick="history.back()">
                                Go back
                                <img src="/carriemart/assets/caret-right-square.svg" alt="">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>