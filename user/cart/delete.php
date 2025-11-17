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

// Ensure cart
$cartId = null;
$sc = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$sc->bind_param('i', $userId);
$sc->execute();
$sc->bind_result($cartId);
if (!$sc->fetch()) $cartId = null;
$sc->close();
if ($cartId === null) {
    header('Location: /carriemart/user/cart/cart.php?error=no_cart');
    exit;
}

// Collect selected product ids (POST preferred)
$ids = [];
if (isset($_POST['sel']) && is_array($_POST['sel'])) {
    foreach ($_POST['sel'] as $raw) {
        if (ctype_digit((string)$raw)) $ids[] = (int)$raw;
    }
} elseif (isset($_GET['ids'])) {
    // ids=1,2,3 variant
    $parts = explode(',', trim($_GET['ids']));
    foreach ($parts as $raw) {
        if (ctype_digit($raw)) $ids[] = (int)$raw;
    }
}

if (empty($ids)) {
    header('Location: /carriemart/user/cart/cart.php?error=none_selected');
    exit;
}

// Dynamic delete
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "DELETE FROM cart_product WHERE cart_id = ? AND product_id IN ($placeholders)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: /carriemart/user/cart/cart.php?error=server');
    exit;
}
$types = str_repeat('i', count($ids) + 1);
$params = array_merge([$types, $cartId], $ids);
$refs = [];
foreach ($params as $k => $v) { $refs[$k] = &$params[$k]; }
call_user_func_array([$stmt, 'bind_param'], $refs);
$stmt->execute();
$stmt->close();

header('Location: /carriemart/user/cart/cart.php?status=deleted');
exit;
?>