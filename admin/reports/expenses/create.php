<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: expense-form.php');
    exit;
}

$errors = [];

   
$expense_type = isset($_POST['expense_type']) ? trim($_POST['expense_type']) : '';
$description  = isset($_POST['description']) ? trim($_POST['description']) : '';
$amount_raw   = isset($_POST['amount']) ? trim($_POST['amount']) : '';
$status       = isset($_POST['status']) ? trim($_POST['status']) : '';
$due_date     = isset($_POST['due_date']) ? trim($_POST['due_date']) : '';
$paid_date    = isset($_POST['paid_date']) ? trim($_POST['paid_date']) : '';

$allowedTypes  = ['inventory_purchase','shipping','maintenance','rent','utilities','other'];
$allowedStatus = ['pending','paid'];

if ($expense_type === '' || !in_array($expense_type, $allowedTypes, true)) {
    $errors[] = 'type_invalid';
}

if ($amount_raw === '') {
    $errors[] = 'amount_required';
} else {
    if (!is_numeric($amount_raw)) {
        $errors[] = 'amount_invalid';
    } else {
        $amount = (float)$amount_raw;
        if ($amount < 0) $errors[] = 'amount_invalid';
    }
}

if ($status === '' || !in_array($status, $allowedStatus, true)) {
    $errors[] = 'status_invalid';
}

$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };

$dueValid  = true;
$paidValid = true;

if ($due_date !== '') {
    if (!$validDate($due_date)) {
        $errors[] = 'due_invalid';
        $dueValid = false;
    }
}
if ($paid_date !== '') {
    if (!$validDate($paid_date)) {
        $errors[] = 'paid_invalid';
        $paidValid = false;
    }
}

   
if ($due_date !== '' && $paid_date !== '' && $dueValid && $paidValid) {
    if (strtotime($paid_date) < strtotime($due_date)) {
        $errors[] = 'date_order';
    }
}

if (!empty($errors)) {
    header('Location: expense-form.php?error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

   
$descParam = ($description !== '' ? $description : null);
$dueParam  = ($due_date !== '' ? $due_date : null);
$paidParam = ($paid_date !== '' ? $paid_date : null);
$amountParam = isset($amount) ? $amount : 0.00;

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO expenses (expense_type, description, amount, status, due_date, paid_date) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception('prep');

    $stmt->bind_param(
        'ssdsss',
        $expense_type,
        $descParam,
        $amountParam,
        $status,
        $dueParam,
        $paidParam
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('exec');
    }
    $newId = $stmt->insert_id;
    $stmt->close();
    $conn->commit();

    header('Location: expense-form.php?id=' . $newId . '&status=created');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: expense-form.php?error=server');
    exit;
}