<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

if (!$conn) { die('DB error'); }

// Require logged-in user
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}
$userId = (int)$_SESSION['user_id'];

// Accept product id and qty from both products.php and product-details.php (POST or GET)
$pidRaw = '';
$qtyRaw = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pidRaw = isset($_POST['product_id']) ? trim($_POST['product_id']) : (isset($_POST['id']) ? trim($_POST['id']) : '');
    $qtyRaw = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
} else {
    $pidRaw = isset($_GET['product_id']) ? trim($_GET['product_id']) : (isset($_GET['id']) ? trim($_GET['id']) : '');
    $qtyRaw = isset($_GET['quantity']) ? trim($_GET['quantity']) : '';
}

$productId = ctype_digit($pidRaw) ? (int)$pidRaw : 0;
$qty = ctype_digit($qtyRaw) ? (int)$qtyRaw : 1;
if ($qty <= 0) $qty = 1;

if ($productId <= 0) {
    header('Location: /carriemart/main/products.php?error=invalid_id');
    exit;
}

// Validate product availability
$stockLevel = 0;
$isActive = 0;
$chk = $conn->prepare("SELECT stock_level, is_active FROM products WHERE product_id = ?");
if (!$chk) { header('Location: /carriemart/main/products.php?error=server'); exit; }
$chk->bind_param('i', $productId);
$chk->execute();
$chk->bind_result($stockLevel, $isActive);
if (!$chk->fetch()) {
    $chk->close();
    header('Location: /carriemart/main/products.php?error=not_found');
    exit;
}
$chk->close();

if ((int)$isActive !== 1) {
    header('Location: /carriemart/main/products.php?error=inactive');
    exit;
}
if ($stockLevel <= 0) {
    header('Location: /carriemart/main/products.php?error=out_of_stock');
    exit;
}

// Clamp requested qty to available stock
if ($qty > $stockLevel) $qty = (int)$stockLevel;

// Ensure cart exists for user
$cartId = null;
$sc = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$sc->bind_param('i', $userId);
$sc->execute();
$sc->bind_result($cartId);
if (!$sc->fetch()) $cartId = null;
$sc->close();

if ($cartId === null) {
    $nc = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
    if (!$nc) { header('Location: /carriemart/main/products.php?error=server'); exit; }
    $nc->bind_param('i', $userId);
    if ($nc->execute()) {
        $cartId = $nc->insert_id;
    }
    $nc->close();
    if ($cartId === null) {
        header('Location: /carriemart/main/products.php?error=server');
        exit;
    }
}

// Insert or update line, capping at stock level
$ins = $conn->prepare("
    INSERT INTO cart_product (cart_id, product_id, quantity)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = LEAST(quantity + VALUES(quantity), ?)
");
if ($ins) {
    $cap = (int)$stockLevel;
    $ins->bind_param('iiii', $cartId, $productId, $qty, $cap);
    if ($ins->execute()) {
        $ins->close();
        header('Location: /carriemart/user/cart/cart.php?status=added&pid=' . $productId);
        exit;
    }
    $ins->close();
}

// Fallback
header('Location: /carriemart/main/products.php?error=server');
exit;
