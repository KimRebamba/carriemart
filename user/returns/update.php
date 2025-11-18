<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /carriemart/user/returns/returns.php');
    exit;
}

if (!$conn) { die('DB error'); }
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$order_return_id = isset($_POST['order_return_id']) ? (int)$_POST['order_return_id'] : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($order_return_id <= 0) {
    header('Location: /carriemart/user/returns/returns.php?error=invalid_id');
    exit;
}

// Verify return belongs to user and get order_id
$chk = $conn->prepare("
    SELECT ord_ret.order_return_id, ord_ret.order_id, ord_ret.return_status
    FROM order_return ord_ret
    INNER JOIN orders o ON ord_ret.order_id = o.order_id
    WHERE ord_ret.order_return_id = ? AND o.user_id = ?
");
if (!$chk) {
    header('Location: /carriemart/user/returns/returns.php?error=server');
    exit;
}
$chk->bind_param('ii', $order_return_id, $userId);
$chk->execute();
$chk->bind_result($orid, $order_id, $rstat);
if (!$chk->fetch()) {
    $chk->close();
    header('Location: /carriemart/user/returns/returns.php?error=not_found');
    exit;
}
$chk->close();

// Handle cancel action
if ($action === 'cancel') {
    if ($rstat !== 'requested') {
        header('Location: /carriemart/user/returns/returns.php?error=cannot_cancel');
        exit;
    }
    
    $del = $conn->prepare("DELETE FROM order_return WHERE order_return_id = ?");
    if (!$del) {
        header('Location: /carriemart/user/returns/returns.php?error=server');
        exit;
    }
    $del->bind_param('i', $order_return_id);
    if (!$del->execute()) {
        $del->close();
        header('Location: /carriemart/user/returns/returns.php?error=server');
        exit;
    }
    $del->close();
    
    // Update order status back to 'completed' when return is cancelled
    $updOrder = $conn->prepare("UPDATE orders SET order_status = 'completed' WHERE order_id = ?");
    if ($updOrder) {
        $updOrder->bind_param('i', $order_id);
        $updOrder->execute();
        $updOrder->close();
    }
    
    header('Location: /carriemart/user/returns/returns.php?status=cancelled');
    exit;
}

// Handle update action
if ($rstat !== 'requested') {
    header('Location: /carriemart/user/returns/return-details.php?mode=view&order_return_id=' . $order_return_id . '&error=cannot_edit');
    exit;
}

$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$cond = isset($_POST['cond']) ? trim($_POST['cond']) : 'other';

if (!in_array($cond, ['new','opened','damaged','other'])) {
    $cond = 'other';
}

$reason_clean = $reason !== '' ? $reason : null;

$upd = $conn->prepare("UPDATE order_return SET reason = ?, cond = ? WHERE order_return_id = ?");
if (!$upd) {
    header('Location: /carriemart/user/returns/return-details.php?mode=edit&order_return_id=' . $order_return_id . '&error=server');
    exit;
}
$upd->bind_param('ssi', $reason_clean, $cond, $order_return_id);
if (!$upd->execute()) {
    $upd->close();
    header('Location: /carriemart/user/returns/return-details.php?mode=edit&order_return_id=' . $order_return_id . '&error=server');
    exit;
}
$upd->close();

header('Location: /carriemart/user/returns/return-details.php?mode=view&order_return_id=' . $order_return_id . '&status=updated');
exit;
?>
