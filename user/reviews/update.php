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
$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$product_order_id = isset($_POST['product_order_id']) ? (int)$_POST['product_order_id'] : 0;
$review_title = isset($_POST['review_title']) ? trim($_POST['review_title']) : '';
$review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

if ($review_id <= 0) {
    header('Location: /carriemart/user/orders/orders.php?error=invalid_review');
    exit;
}

if ($rating < 1 || $rating > 5) {
    header('Location: /carriemart/user/reviews/review-details.php?mode=edit&product_order_id=' . $product_order_id . '&error=invalid_rating');
    exit;
}

// Verify review belongs to user
$chk = $conn->prepare("SELECT review_id FROM product_review WHERE review_id = ? AND user_id = ?");
if (!$chk) {
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$chk->bind_param('ii', $review_id, $userId);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    header('Location: /carriemart/user/orders/orders.php?error=not_found');
    exit;
}
$chk->close();

// Mask bad words before saving
$review_title = mask_bad_words($review_title);
$review_text = mask_bad_words($review_text);

// Update review
$review_title_clean = $review_title !== '' ? $review_title : null;
$review_text_clean = $review_text !== '' ? $review_text : null;

$upd = $conn->prepare("UPDATE product_review SET review_title = ?, review_text = ?, rating = ? 
                       WHERE review_id = ? AND user_id = ?");
if (!$upd) {
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$upd->bind_param('ssiii', $review_title_clean, $review_text_clean, $rating, $review_id, $userId);
if (!$upd->execute()) {
    $upd->close();
    header('Location: /carriemart/user/orders/orders.php?error=server');
    exit;
}
$upd->close();

header('Location: /carriemart/user/orders/orders.php?status=review_updated');
exit;
?>
