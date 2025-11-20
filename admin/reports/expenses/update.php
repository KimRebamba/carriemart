<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errors = [];

   
$id_raw = isset($_POST['exp_id']) ? trim($_POST['exp_id']) : '';
$exp_id = ctype_digit($id_raw) ? (int)$id_raw : 0;
if ($exp_id <= 0) {
    header('Location: expense-form.php?id='.$exp_id.'&error=invalid_id');
    exit;
}

   
$exist = $conn->prepare("SELECT exp_id FROM expenses WHERE exp_id = ?");
if (!$exist) {
    header('Location: expense-form.php?id='.$exp_id.'&error=server');
    exit;
}
$exist->bind_param('i', $exp_id);
$exist->execute();
$exist->store_result();
if ($exist->num_rows === 0) {
    $exist->close();
    header('Location: expense-form.php?id='.$exp_id.'&error=not_found');
    exit;
}
$exist->close();

   
$expense_type = isset($_POST['expense_type']) ? trim($_POST['expense_type']) : '';
$description  = isset($_POST['description']) ? trim($_POST['description']) : '';
$amount_raw   = isset($_POST['amount']) ? trim($_POST['amount']) : '';
$status       = isset($_POST['status']) ? trim($_POST['status']) : '';
$due_date     = isset($_POST['due_date']) ? trim($_POST['due_date']) : '';
$paid_date    = isset($_POST['paid_date']) ? trim($_POST['paid_date']) : '';

$allowedTypes  = ['inventory_purchase','shipping','maintenance','rent','utilities','other'];
$allowedStatus = ['pending','paid'];

if ($expense_type === '' || !in_array($expense_type, $allowedTypes, true)) $errors[] = 'type_invalid';

if ($amount_raw === '') {
    $errors[] = 'amount_required';
} elseif (!is_numeric($amount_raw) || (float)$amount_raw < 0) {
    $errors[] = 'amount_invalid';
} else {
    $amount = (float)$amount_raw;
}

if ($status === '' || !in_array($status, $allowedStatus, true)) $errors[] = 'status_invalid';

$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
$dueValid = true; $paidValid = true;

if ($due_date !== '' && !$validDate($due_date)) { $errors[]='due_invalid'; $dueValid=false; }
if ($paid_date !== '' && !$validDate($paid_date)) { $errors[]='paid_invalid'; $paidValid=false; }

if ($due_date !== '' && $paid_date !== '' && $dueValid && $paidValid) {
    if (strtotime($paid_date) < strtotime($due_date)) $errors[] = 'date_order';
}

if (!empty($errors)) {
    header('Location: expense-form.php?id='.$exp_id.'&error='.implode(',', array_values(array_unique($errors))));
    exit;
}

   
$descParam = ($description !== '' ? $description : null);
$dueParam  = ($due_date !== '' ? $due_date : null);
$paidParam = ($paid_date !== '' ? $paid_date : null);
$amountParam = isset($amount) ? $amount : 0.00;

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE expenses SET expense_type = ?, description = ?, amount = ?, status = ?, due_date = ?, paid_date = ? WHERE exp_id = ?");
    if (!$stmt) throw new Exception('server');
    $stmt->bind_param('ssdsssi', $expense_type, $descParam, $amountParam, $status, $dueParam, $paidParam, $exp_id);
    if (!$stmt->execute()) { $stmt->close(); throw new Exception('server'); }
    $stmt->close();
    $conn->commit();
    header('Location: expense-form.php?id='.$exp_id.'&status=updated');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    header('Location: expense-form.php?id='.$exp_id.'&error=server');
    exit;
}
?>