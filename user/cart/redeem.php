<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
if (!$conn) { die('DB error'); }
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}
$userId = (int)$_SESSION['user_id'];

$voucher = isset($_POST['voucher']) ? trim($_POST['voucher']) : '';
if ($voucher === '') {
    header('Location: checkout-form.php?error=no_voucher');
    exit;
}

   
$cartId = null;
$sc = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$sc->bind_param('i', $userId);
$sc->execute();
$sc->bind_result($cartId);
if (!$sc->fetch()) $cartId = null;
$sc->close();
if ($cartId === null) {
    header('Location: checkout-form.php?error=no_cart');
    exit;
}

$subtotal = 0.00;
$cs = $conn->prepare("
    SELECT cp.quantity, p.retail_price
    FROM cart_product cp
    JOIN products p ON p.product_id = cp.product_id
    WHERE cp.cart_id = ?
");
$cs->bind_param('i', $cartId);
$cs->execute();
$cs->bind_result($qty, $price);
while ($cs->fetch()) {
    $subtotal += ((float)$price * (int)$qty);
}
$cs->close();

   
$v = $conn->prepare("SELECT percent_sale, min_purchase_amount, max_discount_amount, from_date, to_date, is_active FROM vouchers WHERE voucher_code = ? LIMIT 1");
$v->bind_param('s', $voucher);
$v->execute();
$v->bind_result($percent, $min, $max, $from, $to, $active);
if ($v->fetch()) {
    $today = date('Y-m-d');
    $valid = ((int)$active === 1);
    if ($from && $today < $from) $valid = false;
    if ($to && $today > $to) $valid = false;
    if ($subtotal < (float)$min) $valid = false;
    if ($valid && (int)$percent > 0) {
        $rawDisc = $subtotal * ((int)$percent / 100.0);
        $discount = ($max !== null && (float)$max > 0) ? min($rawDisc, (float)$max) : $rawDisc;
        header('Location: checkout-form.php?status=voucher_applied&voucher=' . urlencode($voucher) . '&discount=' . $discount);
        exit;
    } else {
        header('Location: checkout-form.php?error=invalid_voucher');
        exit;
    }
} else {
    header('Location: checkout-form.php?error=not_found_voucher');
    exit;
}
$v->close();