<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$salary_id = isset($_POST['salary_id']) ? (int)$_POST['salary_id'] : 0;
$emp_id    = isset($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;
$pay_date  = trim($_POST['pay_date'] ?? '');
$rate_raw  = trim($_POST['rate_used'] ?? '');
$from_date = trim($_POST['from_date'] ?? '');
$to_date   = trim($_POST['to_date'] ?? '');
$status    = trim($_POST['status'] ?? 'pending');

if ($salary_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Ensure salary exists
$chk = $conn->prepare("SELECT salary_id FROM salaries WHERE salary_id = ?");
if (!$chk) {
    header('Location: salary-form.php?id=' . $salary_id . '&error=server');
    exit;
}
$chk->bind_param('i', $salary_id);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    header('Location: index.php?error=not_found');
    exit;
}
$chk->close();

$errors = [];

// Employee required and must exist
if ($emp_id <= 0) {
    $errors[] = 'emp_required';
} else {
    $e = $conn->prepare("SELECT emp_id FROM employees WHERE emp_id = ? LIMIT 1");
    if ($e) {
        $e->bind_param('i', $emp_id);
        $e->execute();
        $e->store_result();
        if ($e->num_rows === 0) $errors[] = 'emp_not_found';
        $e->close();
    } else {
        $errors[] = 'server';
    }
}

// Pay date required (YYYY-MM-DD)
if ($pay_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $pay_date)) {
    $errors[] = 'pay_date_required';
}

// Rate required + numeric >= 0
if ($rate_raw === '') {
    $errors[] = 'rate_required';
}
$rate_clean = str_replace(['₱', ',', ' '], '', $rate_raw);
if ($rate_raw !== '' && (!is_numeric($rate_clean) || (float)$rate_clean < 0)) {
    $errors[] = 'rate_invalid';
}
$rate_used = ($rate_raw === '' ? 0.00 : (float)$rate_clean);

// Status enum
$allowedStatus = ['pending','paid','cancelled'];
if (!in_array($status, $allowedStatus, true)) {
    $errors[] = 'status_invalid';
    $status = 'pending';
}

// Period validation (optional)
$periodProvided = ($from_date !== '' || $to_date !== '');
if ($periodProvided) {
    $validFmt = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
    if ($from_date === '' || $to_date === '' || !$validFmt($from_date) || !$validFmt($to_date) || $from_date > $to_date) {
        $errors[] = 'period_invalid';
    }
}

if (!empty($errors)) {
    header('Location: salary-form.php?id=' . $salary_id . '&error=' . implode(',', $errors));
    exit;
}

// Prepare nullable dates
$from_val = ($periodProvided ? $from_date : null);
$to_val   = ($periodProvided ? $to_date   : null);

// Update salary
$stmt = $conn->prepare("UPDATE salaries
                        SET emp_id = ?, pay_date = ?, rate_used = ?, status = ?, from_date = ?, to_date = ?
                        WHERE salary_id = ?");
if (!$stmt) {
    header('Location: salary-form.php?id=' . $salary_id . '&error=server');
    exit;
}
$stmt->bind_param(
    'isdsssi',
    $emp_id,
    $pay_date,
    $rate_used,
    $status,
    $from_val,
    $to_val,
    $salary_id
);
if (!$stmt->execute()) {
    $stmt->close();
    header('Location: salary-form.php?id=' . $salary_id . '&error=server');
    exit;
}
$stmt->close();

header('Location: index.php?id=' . $salary_id . '&status=updated');
exit;