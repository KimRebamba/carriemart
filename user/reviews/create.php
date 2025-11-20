<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

   
function mask_bad_words(?string $text): ?string {
    if ($text === null || $text === '') {
        return $text;
    }
    $badWords = ['fuck','shit','bitch','asshole','damn'];
    $pattern = '/(' . implode('|', array_map('preg_quote', $badWords)) . ')/i';
    return preg_replace_callback($pattern, function ($matches) {
        return str_repeat('*', strlen($matches[0]));
    }, $text);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /carriemart/user/orders/orders.php');
    exit;
}

if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/user/orders/orders.php?error=login_required');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$product_order_id = isset($_POST['product_order_id']) ? (int)$_POST['product_order_id'] : 0;
$review_title = isset($_POST['review_title']) ? trim($_POST['review_title']) : '';
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

if ($product_order_id <= 0) {
    header('Location: /carriemart/user/orders/orders.php?error=invalid_product_order');
    exit;
}

if ($rating < 1 || $rating > 5) {
    header('Location: /carriemart/user/reviews/review-details.php?mode=add&product_order_id=' . $product_order_id . '&error=invalid_rating');
    exit;
}

   
$chk = $conn->prepare("SELECT po.product_order_id FROM product_order po 
                       INNER JOIN orders o ON po.order_id = o.order_id 
                       WHERE po.product_order_id = ? AND o.user_id = ?");
if (!$chk) {
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$chk->bind_param('ii', $product_order_id, $userId);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    header('Location: /carriemart/user/orders/orders.php?error=not_found');
    exit;
}
$chk->close();

   
$chk2 = $conn->prepare("SELECT review_id FROM product_review WHERE product_order_id = ? AND user_id = ?");
if (!$chk2) {
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$chk2->bind_param('ii', $product_order_id, $userId);
$chk2->execute();
$chk2->store_result();
if ($chk2->num_rows > 0) {
    $chk2->close();
    header('Location: /carriemart/user/orders/orders.php?error=review_exists');
    exit;
}
$chk2->close();

   
$review_title = mask_bad_words($review_title);
$review_text = mask_bad_words($review_text);

   
$review_title_clean = $review_title !== '' ? $review_title : null;
$review_text_clean = $review_text !== '' ? $review_text : null;

$ins = $conn->prepare("INSERT INTO product_review (product_order_id, user_id, rating, review_title, review_text, is_verified) 
                       VALUES (?, ?, ?, ?, ?, 0)");
if (!$ins) {
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$ins->bind_param('iiiss', $product_order_id, $userId, $rating, $review_title_clean, $review_text_clean);
if (!$ins->execute()) {
    $ins->close();
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$ins->close();

header('Location: /carriemart/user/orders/orders.php?status=review_created');
exit;
?>
