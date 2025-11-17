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

// Ensure cart
$cartId = null;
$sc = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$sc->bind_param('i', $userId);
$sc->execute();
$sc->bind_result($cartId);
if (!$sc->fetch()) $cartId = null;
$sc->close();
if ($cartId === null) {
    header('Location: /carriemart/user/cart/cart.php?error=no_cart');
    exit;
}

// Selected product ids (optional). If none, use all cart items.
$selected = [];
if (isset($_POST['sel']) && is_array($_POST['sel'])) {
    foreach ($_POST['sel'] as $raw) if (ctype_digit((string)$raw)) $selected[] = (int)$raw;
} elseif (isset($_GET['ids'])) {
    $parts = explode(',', trim($_GET['ids']));
    foreach ($parts as $raw) if (ctype_digit($raw)) $selected[] = (int)$raw;
}

// Build WHERE condition
$whereExtra = '';
$params = [$userId];
$types = 'i';
if (!empty($selected)) {
    $ph = implode(',', array_fill(0, count($selected), '?'));
    $whereExtra = " AND cp.product_id IN ($ph)";
    foreach ($selected as $id) { $params[] = $id; $types .= 'i'; }
}

$sql = "
SELECT
  cp.product_id,
  cp.quantity,
  p.product_name,
  p.retail_price,
  COALESCE(ph.photo_url, '/carriemart/assets/default-product.png') AS photo_url
FROM cart_product cp
JOIN cart c ON c.cart_id = cp.cart_id
JOIN products p ON p.product_id = cp.product_id
LEFT JOIN (
    SELECT product_id, photo_url
    FROM product_photos
    WHERE is_primary = 1
    GROUP BY product_id
) ph ON ph.product_id = p.product_id
WHERE c.user_id = ? $whereExtra
ORDER BY cp.cart_product_id DESC
";

$items = [];
$subtotal = 0.00;
$stmt = $conn->prepare($sql);
if ($stmt) {
    $refs = [];
    $refs[0] = &$types;
    for ($i=1; $i<=count($params); $i++) { $refs[$i] = &$params[$i-1]; }
    call_user_func_array([$stmt,'bind_param'], $refs);
    $stmt->execute();
    $stmt->bind_result($pid, $qty, $pname, $price, $photo);
    while ($stmt->fetch()) {
        $line = (float)$price * (int)$qty;
        $subtotal += $line;
        $items[] = [
            'product_id' => $pid,
            'quantity' => $qty,
            'product_name' => $pname,
            'retail_price' => (float)$price,
            'photo_url' => $photo
        ];
    }
    $stmt->close();
}

$deliveryFee = 50.00; // Flat rate for demo purposes
// Voucher redeem logic
$promoCode = '';
$discount = 0.00;

// Read voucher and discount from GET if present
if (isset($_GET['voucher'])) {
    $promoCode = $_GET['voucher'];
}
if (isset($_GET['discount']) && is_numeric($_GET['discount'])) {
    $discount = (float)$_GET['discount'];
}

// Calculate total
$total = $subtotal + $deliveryFee - $discount;

function peso($v){ return '₱' . number_format((float)$v, 2, '.', ','); }
$countBadge = count($items);

// Accept and show error/status codes coming from cart.php or checkout.php
function cm_map_error_checkout($code) {
    if (strpos($code, 'stock_') === 0) return 'Insufficient stock for product #' . substr($code, 6) . '.';
    if (strpos($code, 'not_found_') === 0) return 'Product not found #' . substr($code, 10) . '.';
    if (strpos($code, 'inactive_') === 0) return 'Product inactive #' . substr($code, 9) . '.';
    switch ($code) {
        case 'no_items': return 'No items selected.';
        case 'no_cart': return 'No cart found.';
        case 'login_required': return 'Please log in to continue.';
        case 'invalid_id': return 'Invalid ID.';
        case 'out_of_stock': return 'Item is out of stock.';
        case 'none_selected': return 'No items were selected.';
        case 'server': return 'A server error occurred.';
        default: return $code;
    }
}
function cm_map_status_checkout($code) {
    switch ($code) {
        case 'qty_updated': return 'Quantity updated.';
        case 'qty_set': return 'Quantity set.';
        case 'removed': return 'Item removed from cart.';
        case 'deleted': return 'Selected items removed from cart.';
        // Note: successful orders redirect to cart.php, but we still map just in case
        case 'order_placed': return 'Order placed successfully.';
        default: return '';
    }
}
$cm_errors = [];
$cm_success = [];
if (isset($_GET['error']) && $_GET['error'] !== '') {
    $codes = explode(',', trim($_GET['error']));
    foreach ($codes as $c) {
        $m = cm_map_error_checkout(trim($c));
        if ($m !== '') $cm_errors[] = $m;
    }
}
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $codes = explode(',', trim($_GET['status']));
    foreach ($codes as $c) {
        $m = cm_map_status_checkout(trim($c));
        if ($m !== '') $cm_success[] = $m;
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 960px; }
        .btn-icon-inverted img { width: 1.125rem; height: 1.125rem; filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%); opacity: .95; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($cm_errors)): ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php foreach ($cm_errors as $e): ?>
                    <div>- <?php echo $e; ?></div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($cm_success)): ?>
            <div class="alert alert-success mt-3" role="alert">
                <?php foreach ($cm_success as $s): ?>
                    <div><?php echo $s; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <main>
            <div class="py-4 text-center"> <img class="d-block mx-auto mb-0"
                    src="/carriemart/assets/Header-Logo-01.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-5 col-lg-4 order-md-last my-5">
                    <h4 class="d-flex justify-content-between align-items-center mb-3"> <span
                            class="text-primary">Your cart</span> <span
                            class="badge bg-primary rounded-pill"><?php echo $countBadge; ?></span> </h4>
                    <ul class="list-group mb-3">
                        <?php if (empty($items)): ?>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div><h6 class="my-0">No items selected</h6></div>
                                <span class="text-body-secondary">0</span>
                            </li>
                        <?php else: foreach ($items as $it): ?>
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0"><?php echo $it['product_name']; ?></h6>
                                    <small class="text-body-secondary">Qty: <?php echo $it['quantity']; ?></small>
                            </div>
                            <span class="text-body-secondary"><?php echo peso($it['retail_price'] * $it['quantity']); ?></span>
                        </li>
                    <?php endforeach; endif; ?>

                    <?php if ($discount > 0): ?>
                        <li class="list-group-item d-flex justify-content-between bg-body-tertiary">
                            <div class="text-success">
                                <h6 class="my-0">Promo code</h6>
                                <small><?php echo $promoCode; ?></small>
                            </div>
                            <span class="text-success">-<?php echo peso($discount); ?></span>
                        </li>
                    <?php endif; ?>

                    <?php if ($deliveryFee > 0): ?>
                        <li class="list-group-item d-flex justify-content-between bg-body-tertiary">
                            <div class="text-danger">
                                <h6 class="my-0">Delivery Fee</h6>
                            </div>
                            <span class="text-danger"><?php echo peso($deliveryFee); ?></span>
                        </li>
                    <?php endif; ?>

                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total</span>
                        <strong><?php echo peso($total); ?></strong> </li>
                    </ul>

                    <form method="post" action="redeem.php">
                        <div class="input-group">
                            <input type="text" class="form-control" name="voucher" placeholder="Promo code" value="<?php echo $promoCode; ?>">
                            <button type="submit" class="btn btn-secondary">Redeem</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-7 col-lg-8 my-5 ">
                    <h4 class="mb-3">Order Information</h4>
                    <form method="post" action="/carriemart/user/cart/checkout.php">
                        <?php foreach ($items as $it): ?>
                            <input type="hidden" name="items[<?php echo $it['product_id']; ?>]" value="<?php echo $it['quantity']; ?>">
                        <?php endforeach; ?>
                        <input type="hidden" name="voucher" value="<?php echo $promoCode; ?>">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Delivery Recipient</label>
                                <input type="text" class="form-control" name="delivery_recipient">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Delivery Address</label>
                                <input type="text" class="form-control" name="delivery_address">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="delivery_phone">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h4 class="mb-3">Payment Option</h4>
                        <div class="my-3">
                            <div class="form-check">
                                <input id="cod" name="payment_option" type="radio" class="form-check-input" value="cash_on_delivery" checked>
                                <label class="form-check-label" for="cod">Cash on Delivery</label>
                            </div>
                            <div class="form-check">
                                <input id="card" name="payment_option" type="radio" class="form-check-input" value="credit_card">
                                <label class="form-check-label" for="card">Credit Card</label>
                            </div>
                            <div class="form-check">
                                <input id="gcash" name="payment_option" type="radio" class="form-check-input" value="gcash">
                                <label class="form-check-label" for="gcash">GCash</label>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex w-100 gap-2">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center btn-icon-inverted" type="submit" style="flex:2 1 0%;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-check me-2" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
  <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
</svg>
                                Continue to checkout
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted" style="flex:1 1 0%;" onclick="history.back()">
                                Go back
                                <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
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