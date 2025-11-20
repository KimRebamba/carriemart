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

   
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /carriemart/user/orders/orders.php?error=invalid_method');
    exit;
}

   
if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $orderId = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
    if (!ctype_digit($orderId) || (int)$orderId <= 0) {
        header('Location: /carriemart/user/orders/orders.php?error=invalid_id');
        exit;
    }
    $orderId = (int)$orderId;

      
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

      
    if (!in_array($status, ['pending', 'processing'])) {
        header('Location: /carriemart/user/orders/orders.php?error=cannot_cancel');
        exit;
    }

      
    $upd = $conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE order_id = ? AND user_id = ?");
    $upd->bind_param('ii', $orderId, $userId);
    $upd->execute();
    $upd->close();

    header('Location: /carriemart/user/orders/orders.php?status=order_cancelled');
    exit;
}

   
header('Location: /carriemart/user/orders/orders.php?error=unknown_action');
exit;