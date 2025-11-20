<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: salary-form.php');
    exit;
}

$emp_id     = isset($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;
$pay_date   = trim($_POST['pay_date'] ?? '');
$rate_raw   = trim($_POST['rate_used'] ?? '');
$from_date  = trim($_POST['from_date'] ?? '');
$to_date    = trim($_POST['to_date'] ?? '');
$status     = trim($_POST['status'] ?? 'pending');

$errors = [];

   
if ($emp_id <= 0) {
    $errors[] = 'emp_required';
} else {
    $chk = $conn->prepare("SELECT emp_id FROM employees WHERE emp_id = ? LIMIT 1");
    if ($chk) {
        $chk->bind_param('i', $emp_id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows === 0) $errors[] = 'emp_not_found';
        $chk->close();
    } else {
        $errors[] = 'server';
    }
}

   
if ($pay_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $pay_date)) {
    $errors[] = 'pay_date_required';
}

   
if ($rate_raw === '') {
    $errors[] = 'rate_required';
}
$rate_clean = str_replace(['₱', ',', ' '], '', $rate_raw);
if ($rate_raw !== '' && (!is_numeric($rate_clean) || (float)$rate_clean < 0)) {
    $errors[] = 'rate_invalid';
}
$rate_used = ($rate_raw === '' ? 0.00 : (float)$rate_clean);

   
$allowedStatus = ['pending','paid','cancelled'];
if (!in_array($status, $allowedStatus, true)) {
    $errors[] = 'status_invalid';
    $status = 'pending';
}

   
$periodProvided = ($from_date !== '' || $to_date !== '');
if ($periodProvided) {
    $validFmt = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
    if ($from_date === '' || $to_date === '' || !$validFmt($from_date) || !$validFmt($to_date) || $from_date > $to_date) {
        $errors[] = 'period_invalid';
    }
}

   
if (!empty($errors)) {
    header('Location: salary-form.php?error=' . implode(',', $errors));
    exit;
}

   
$from_val = ($periodProvided && !in_array('period_invalid', $errors, true)) ? $from_date : null;
$to_val   = ($periodProvided && !in_array('period_invalid', $errors, true)) ? $to_date   : null;

$stmt = $conn->prepare("INSERT INTO salaries (emp_id, pay_date, rate_used, status, from_date, to_date) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    header('Location: salary-form.php?error=server');
    exit;
}
$stmt->bind_param(
    'isdsss',
    $emp_id,
    $pay_date,
    $rate_used,
    $status,
    $from_val,
    $to_val
);
if (!$stmt->execute()) {
    $stmt->close();
    header('Location: salary-form.php?error=server');
    exit;
}
$newId = $stmt->insert_id;
$stmt->close();

header('Location: index.php?id=' . $newId . '&status=created');
exit;