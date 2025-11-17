<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: voucher-form.php');
    exit;
}

$voucher_code       = trim($_POST['voucher_code'] ?? '');
$percent_sale_raw   = trim($_POST['percent_sale'] ?? '');
$min_purchase_raw   = trim($_POST['min_purchase_amount'] ?? '');
$max_discount_raw   = trim($_POST['max_discount_amount'] ?? '');
$from_date          = trim($_POST['from_date'] ?? '');
$to_date            = trim($_POST['to_date'] ?? '');
$is_active_raw      = trim($_POST['is_active'] ?? '1');

$errors = [];

// Code required + basic pattern
if ($voucher_code === '') {
    $errors[] = 'code_required';
} else {
    if (!preg_match('/^[A-Z0-9_-]{3,20}$/i', $voucher_code)) {
        $errors[] = 'code_invalid';
    } else {
        $dup = $conn->prepare("SELECT voucher_id FROM vouchers WHERE voucher_code = ? LIMIT 1");
        if ($dup) {
            $dup->bind_param('s', $voucher_code);
            $dup->execute();
            $dup->store_result();
            if ($dup->num_rows > 0) $errors[] = 'code_duplicate';
            $dup->close();
        } else {
            $errors[] = 'server';
        }
    }
}

// Percent sale (optional; if provided must be 0–100 integer)
$percent_sale = null;
if ($percent_sale_raw !== '') {
    if (!ctype_digit($percent_sale_raw)) {
        $errors[] = 'percent_invalid';
    } else {
        $percent_sale = (int)$percent_sale_raw;
        if ($percent_sale < 0 || $percent_sale > 100) $errors[] = 'percent_invalid';
    }
}

// Min purchase amount (required numeric >=0)
$min_clean = str_replace(['₱',',',' '],'',$min_purchase_raw);
if ($min_clean === '') $min_clean = '0';
if (!is_numeric($min_clean) || (float)$min_clean < 0) {
    $errors[] = 'min_purchase_invalid';
}
$min_purchase_amount = (float)$min_clean;

// Max discount amount (optional numeric >=0)
$max_discount_amount = null;
if ($max_discount_raw !== '') {
    $max_clean = str_replace(['₱',',',' '],'',$max_discount_raw);
    if ($max_clean === '' || !is_numeric($max_clean) || (float)$max_clean < 0) {
        $errors[] = 'max_discount_invalid';
    } else {
        $max_discount_amount = (float)$max_clean;
    }
}

// Active status
if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = ($is_active_raw === '1') ? 1 : 0;

// Date range validation (only if both provided)
$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
if ($from_date !== '' && !$validDate($from_date)) $errors[] = 'date_range_invalid';
if ($to_date !== '' && !$validDate($to_date)) $errors[] = 'date_range_invalid';
if (!in_array('date_range_invalid',$errors,true) && $from_date !== '' && $to_date !== '' && $from_date > $to_date) {
    $errors[] = 'date_range_invalid';
}

// Abort on errors
if (!empty($errors)) {
    header('Location: voucher-form.php?error=' . implode(',', $errors));
    exit;
}

// Prepare nullable fields
$percent_val = $percent_sale; // may be null
$from_val    = ($from_date !== '' ? $from_date : null);
$to_val      = ($to_date !== '' ? $to_date : null);
$max_val     = $max_discount_amount; // may be null

$stmt = $conn->prepare("INSERT INTO vouchers
    (voucher_code, percent_sale, min_purchase_amount, max_discount_amount, from_date, to_date, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    header('Location: voucher-form.php?error=server');
    exit;
}

// Bind (i for int, d for decimal, s for string; nulls handled via set_null with appropriate type)
$stmt->bind_param(
    'siddssi',
    $voucher_code,
    $percent_val,
    $min_purchase_amount,
    $max_val,
    $from_val,
    $to_val,
    $is_active
);

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: voucher-form.php?error=server');
    exit;
}
$newId = $stmt->insert_id;
$stmt->close();

header('Location: index.php?id=' . $newId . '&status=created');
exit;