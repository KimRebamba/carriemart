<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$order_id        = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$payment_status  = trim($_POST['payment_status'] ?? '');
$order_status    = trim($_POST['order_status'] ?? '');
$payment_option  = trim($_POST['payment_option'] ?? '');
$percent_sale_raw= trim($_POST['percent_sale'] ?? '');
$delivery_fee_raw= trim($_POST['delivery_fee'] ?? '');
$delivery_recipient = trim($_POST['delivery_recipient'] ?? '');
$delivery_address   = trim($_POST['delivery_address'] ?? '');
$delivery_phone     = trim($_POST['delivery_phone'] ?? '');
$posted_voucher     = trim($_POST['voucher_code'] ?? ''); // read-only on form

if ($order_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$errors = [];

// Load existing order
$sel = $conn->prepare("SELECT voucher_code, completed_at FROM orders WHERE order_id = ?");
if (!$sel) {
    header('Location: order-form.php?id='.$order_id.'&error=server');
    exit;
}
$sel->bind_param('i', $order_id);
$sel->execute();
$sel->bind_result($existing_voucher, $existing_completed_at);
if (!$sel->fetch()) {
    $sel->close();
    header('Location: index.php?error=not_found');
    exit;
}
$sel->close();

// Validate payment_status enum
$allowedPay = ['pending','paid','refunded'];
if (!in_array($payment_status, $allowedPay, true)) {
    $errors[] = 'payment_status_invalid';
}

// Validate order_status enum
$allowedOrder = ['pending','processing','shipped','completed','cancelled','requested_refund','returned'];
if (!in_array($order_status, $allowedOrder, true)) {
    $errors[] = 'order_status_invalid';
}

// Voucher read-only check
if ($posted_voucher !== '' && $posted_voucher !== $existing_voucher) {
    $errors[] = 'voucher_invalid';
}

// Percent sale
$percent_sale_raw = ($percent_sale_raw === '' ? '0' : $percent_sale_raw);
if (!ctype_digit($percent_sale_raw)) {
    $errors[] = 'percent_sale_invalid';
} else {
    $percent_sale = (int)$percent_sale_raw;
    if ($percent_sale < 0 || $percent_sale > 100) $errors[] = 'percent_sale_invalid';
}
if (!isset($percent_sale)) $percent_sale = 0;

// Delivery fee
$fee_clean = str_replace(['₱',',',' '], '', $delivery_fee_raw);
if ($fee_clean === '') $fee_clean = '0';
if (!is_numeric($fee_clean) || (float)$fee_clean < 0) {
    $errors[] = 'delivery_fee_invalid';
}
$delivery_fee = (float)$fee_clean;

// Recipient
if ($delivery_recipient === '') $errors[] = 'recipient_required';

// Address
if ($delivery_address === '') $errors[] = 'address_required';

// Phone
if ($delivery_phone === '') {
    $errors[] = 'phone_required';
} else {
    $digits = preg_replace('/\D/','',$delivery_phone);
    if (!preg_match('/^09\d{9}$/', $digits)) $errors[] = 'phone_invalid';
    $delivery_phone = $delivery_phone; // keep original formatting
}

if (!empty($errors)) {
    header('Location: order-form.php?id='.$order_id.'&error='.implode(',', $errors));
    exit;
}

// completed_at logic
$completed_at_new = ($order_status === 'completed')
    ? ($existing_completed_at ? $existing_completed_at : date('Y-m-d H:i:s'))
    : null;

// Update
$sql = "UPDATE orders
        SET payment_status = ?,
            order_status = ?,
            payment_option = NULLIF(?, ''),
            delivery_recipient = ?,
            delivery_address = ?,
            delivery_phone = ?,
            percent_sale = ?,
            delivery_fee = ?,
            completed_at = ?
        WHERE order_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: order-form.php?id='.$order_id.'&error=server');
    exit;
}
$stmt->bind_param(
    'sssssssds i' === '' ? 'sssssssds i' : 'sss', // dummy to avoid syntax highlight confusion
    $payment_status,
    $order_status,
    $payment_option,
    $delivery_recipient,
    $delivery_address,
    $delivery_phone,
    $percent_sale,
    $delivery_fee,
    $completed_at_new,
    $order_id
);
// Correct bind_param (fix previous hack)
$stmt->close();
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'sssssssds i' === '' ? 'sssssssds i' : 'sss', // placeholder will never trigger
    $payment_status,
    $order_status,
    $payment_option,
    $delivery_recipient,
    $delivery_address,
    $delivery_phone,
    $percent_sale,
    $delivery_fee,
    $completed_at_new,
    $order_id
);
// Real binding (manual due to hack removal)
$stmt->close();
$stmt = $conn->prepare($sql);
$stmt->bind_param(
    'ssssssidsi',
    $payment_status,
    $order_status,
    $payment_option,
    $delivery_recipient,
    $delivery_address,
    $delivery_phone,
    $percent_sale,
    $delivery_fee,
    $completed_at_new,
    $order_id
);

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: order-form.php?id='.$order_id.'&error=server');
    exit;
}
$stmt->close();

header('Location: order-form.php?id='.$order_id.'&status=updated');
exit;