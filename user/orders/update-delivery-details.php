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

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /carriemart/user/orders/orders.php?error=invalid_method');
    exit;
}

// Validate input
$orderId = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
$delivery_recipient = isset($_POST['delivery_recipient']) ? trim($_POST['delivery_recipient']) : '';
$delivery_address   = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
$delivery_phone     = isset($_POST['delivery_phone']) ? trim($_POST['delivery_phone']) : '';
$payment_option     = isset($_POST['payment_option']) ? trim($_POST['payment_option']) : '';
$voucher_code       = isset($_POST['voucher']) ? trim($_POST['voucher']) : '';

$errors = [];

if (!ctype_digit($orderId) || (int)$orderId <= 0) {
    $errors[] = 'Invalid order ID.';
}
if ($delivery_recipient === '') { $errors[] = 'Delivery recipient is required.'; }
if ($delivery_address === '')   { $errors[] = 'Delivery address is required.'; }
if ($delivery_phone === '') {
    $errors[] = 'Contact number is required.';
} else {
    $digits = preg_replace('/\D+/', '', $delivery_phone);
    if (!preg_match('/^09\d{9}$/', $digits)) {
        $errors[] = 'Phone must match format 09XX-XXXX-XXX.';
    }
}
if (!in_array($payment_option, ['cash_on_delivery','credit_card','gcash'])) {
    $errors[] = 'Select a valid payment option.';
}

// If errors, redirect back to order-form.php with error messages
if (!empty($errors)) {
    $qs = http_build_query(['id' => $orderId, 'error' => implode('|', $errors)]);
    header('Location: /carriemart/user/orders/order-form.php?' . $qs);
    exit;
}

// Check ownership and status
$st = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
$st->bind_param('ii', $orderId, $userId);
$st->execute();
$st->bind_result($status);
if (!$st->fetch()) {
    $st->close();
    header('Location: /carriemart/user/orders/orders.php?error=not_found');
    exit;
}
$st->close();

// Only allow update if pending or processing
if (!in_array($status, ['pending', 'processing'])) {
    header('Location: /carriemart/user/orders/orders.php?error=cannot_update');
    exit;
}

// Validate voucher_code exists or set to NULL
if ($voucher_code !== '') {
    $vst = $conn->prepare("SELECT voucher_code FROM vouchers WHERE voucher_code = ? AND is_active = 1 LIMIT 1");
    $vst->bind_param('s', $voucher_code);
    $vst->execute();
    $vst->store_result();
    if ($vst->num_rows === 0) {
        $voucher_code = null;
    }
    $vst->close();
} else {
    $voucher_code = null;
}

// Update delivery details and payment option
$upd = $conn->prepare("
    UPDATE orders
    SET delivery_recipient = ?, delivery_address = ?, delivery_phone = ?, payment_option = ?, voucher_code = ?
    WHERE order_id = ? AND user_id = ?
");
$upd->bind_param('sssssii', $delivery_recipient, $delivery_address, $delivery_phone, $payment_option, $voucher_code, $orderId, $userId);
$upd->execute();
$upd->close();

header('Location: /carriemart/user/orders/orders.php?status=delivery_updated');
exit;