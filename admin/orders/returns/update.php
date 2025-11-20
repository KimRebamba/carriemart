<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$order_return_id = isset($_POST['order_return_id']) ? (int)$_POST['order_return_id'] : 0;
$cond            = trim($_POST['cond'] ?? '');
$return_status   = trim($_POST['return_status'] ?? '');
$refund_raw      = trim($_POST['refund_amount'] ?? '');

if ($order_return_id <= 0) {
    header('Location: return-form.php?id='.$order_return_id.'&error=invalid_id');
    exit;
}

   
$sel = $conn->prepare("SELECT cond, return_status, refund_amount, processed_at FROM order_return WHERE order_return_id = ?");
if (!$sel) {
    header('Location: return-form.php?id='.$order_return_id.'&error=server');
    exit;
}
$sel->bind_param('i', $order_return_id);
$sel->execute();
$sel->bind_result($existing_cond, $existing_status, $existing_refund, $existing_processed_at);
if (!$sel->fetch()) {
    $sel->close();
    header('Location: index.php?error=not_found');
    exit;
}
$sel->close();

$errors = [];

   
$allowedCond = ['new','opened','damaged','other'];
$allowedStatus = ['requested','approved','rejected','processed'];

if (!in_array($cond, $allowedCond, true)) {
    $errors[] = 'cond_invalid';
}
if (!in_array($return_status, $allowedStatus, true)) {
    $errors[] = 'status_invalid';
}

   
$refund_clean = str_replace(['₱',',',' '], '', $refund_raw);
if ($refund_clean === '') $refund_clean = '0';
if (!is_numeric($refund_clean) || (float)$refund_clean < 0) {
    $errors[] = 'refund_invalid';
}
$refund_amount = (float)$refund_clean;

   
if ($existing_status === 'processed') {
    $changed = ($cond !== $existing_cond) ||
               ($return_status !== $existing_status) ||
               (abs($refund_amount - (float)$existing_refund) > 0.00001);
    if ($changed) {
        $errors[] = 'processed_lock';
    }
}

if (!empty($errors)) {
    header('Location: return-form.php?id='.$order_return_id.'&error='.implode(',', $errors));
    exit;
}

   
$processed_at_new = ($return_status === 'processed')
    ? ($existing_processed_at ? $existing_processed_at : date('Y-m-d H:i:s'))
    : '';

if ($existing_status === 'processed') {
      
    header('Location: return-form.php?id='.$order_return_id.'&status=updated');
    exit;
}

$sql = "UPDATE order_return
        SET cond = ?, return_status = ?, refund_amount = ?, processed_at = NULLIF(?, '')
        WHERE order_return_id = ?";
$upd = $conn->prepare($sql);
if (!$upd) {
    header('Location: return-form.php?id='.$order_return_id.'&error=server');
    exit;
}
$upd->bind_param('ssdsi', $cond, $return_status, $refund_amount, $processed_at_new, $order_return_id);
if (!$upd->execute()) {
    $upd->close();
    header('Location: return-form.php?id='.$order_return_id.'&error=server');
    exit;
}
$upd->close();

header('Location: return-form.php?id='.$order_return_id.'&status=updated');
exit;
?>