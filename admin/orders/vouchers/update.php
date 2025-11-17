<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$voucher_id         = isset($_POST['voucher_id']) ? (int)$_POST['voucher_id'] : 0;
$voucher_code       = trim($_POST['voucher_code'] ?? '');
$percent_sale_raw   = trim($_POST['percent_sale'] ?? '');
$min_purchase_raw   = trim($_POST['min_purchase_amount'] ?? '');
$max_discount_raw   = trim($_POST['max_discount_amount'] ?? '');
$from_date          = trim($_POST['from_date'] ?? '');
$to_date            = trim($_POST['to_date'] ?? '');
$is_active_raw      = trim($_POST['is_active'] ?? '1');

if ($voucher_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Ensure existing voucher
$ex = $conn->prepare("SELECT voucher_id FROM vouchers WHERE voucher_id = ?");
if (!$ex) {
    header('Location: voucher-form.php?id='.$voucher_id.'&error=server');
    exit;
}
$ex->bind_param('i', $voucher_id);
$ex->execute();
$ex->store_result();
if ($ex->num_rows === 0) {
    $ex->close();
    header('Location: index.php?error=not_found');
    exit;
}
$ex->close();

$errors = [];

// Code required + pattern + uniqueness
if ($voucher_code === '') {
    $errors[] = 'code_required';
} else {
    if (!preg_match('/^[A-Z0-9_-]{3,20}$/i', $voucher_code)) {
        $errors[] = 'code_invalid';
    } else {
        $dup = $conn->prepare("SELECT voucher_id FROM vouchers WHERE voucher_code = ? AND voucher_id <> ? LIMIT 1");
        if ($dup) {
            $dup->bind_param('si', $voucher_code, $voucher_id);
            $dup->execute();
            $dup->store_result();
            if ($dup->num_rows > 0) $errors[] = 'code_duplicate';
            $dup->close();
        } else {
            $errors[] = 'server';
        }
    }
}

// Percent sale (optional)
$percent_sale = null;
if ($percent_sale_raw !== '') {
    if (!ctype_digit($percent_sale_raw)) {
        $errors[] = 'percent_invalid';
    } else {
        $percent_sale = (int)$percent_sale_raw;
        if ($percent_sale < 0 || $percent_sale > 100) $errors[] = 'percent_invalid';
    }
}

// Min purchase (required)
$min_clean = str_replace(['₱',',',' '],'',$min_purchase_raw);
if ($min_clean === '') $min_clean = '0';
if (!is_numeric($min_clean) || (float)$min_clean < 0) {
    $errors[] = 'min_purchase_invalid';
}
$min_purchase_amount = (float)$min_clean;

// Max discount (optional)
$max_discount_amount = null;
if ($max_discount_raw !== '') {
    $max_clean = str_replace(['₱',',',' '],'',$max_discount_raw);
    if ($max_clean === '' || !is_numeric($max_clean) || (float)$max_clean < 0) {
        $errors[] = 'max_discount_invalid';
    } else {
        $max_discount_amount = (float)$max_clean;
    }
}

// Status
if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = ($is_active_raw === '1') ? 1 : 0;

// Dates
$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
if ($from_date !== '' && !$validDate($from_date)) $errors[] = 'date_range_invalid';
if ($to_date !== '' && !$validDate($to_date)) $errors[] = 'date_range_invalid';
if (!in_array('date_range_invalid',$errors,true) && $from_date !== '' && $to_date !== '' && $from_date > $to_date) {
    $errors[] = 'date_range_invalid';
}

if (!empty($errors)) {
    header('Location: voucher-form.php?id='.$voucher_id.'&error='.implode(',', $errors));
    exit;
}

$percent_val = $percent_sale; // may be null
$from_val    = ($from_date !== '' ? $from_date : null);
$to_val      = ($to_date !== '' ? $to_date : null);
$max_val     = $max_discount_amount; // may be null

$stmt = $conn->prepare("UPDATE vouchers
    SET voucher_code = ?,
        percent_sale = ?,
        min_purchase_amount = ?,
        max_discount_amount = ?,
        from_date = ?,
        to_date = ?,
        is_active = ?
    WHERE voucher_id = ?");
if (!$stmt) {
    header('Location: voucher-form.php?id='.$voucher_id.'&error=server');
    exit;
}

$stmt->bind_param(
    'siddssii',
    $voucher_code,
    $percent_val,
    $min_purchase_amount,
    $max_val,
    $from_val,
    $to_val,
    $is_active,
    $voucher_id
);

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: voucher-form.php?id='.$voucher_id.'&error=server');
    exit;
}
$stmt->close();

header('Location: index.php?id='.$voucher_id.'&status=updated');
exit;