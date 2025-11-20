<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$review_id   = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$is_verified = trim($_POST['is_verified'] ?? '');

if ($review_id <= 0) {
    header('Location: review-form.php?id=' . $review_id . '&error=invalid_id');
    exit;
}

   
$chk = $conn->prepare("SELECT review_id FROM product_review WHERE review_id = ?");
if (!$chk) {
    header('Location: review-form.php?id=' . $review_id . '&error=server');
    exit;
}
$chk->bind_param('i', $review_id);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    header('Location: index.php?error=not_found');
    exit;
}
$chk->close();

$errors = [];

   
if (!in_array($is_verified, ['0','1'], true)) {
    $errors[] = 'verify_invalid';
}

if (!empty($errors)) {
    header('Location: review-form.php?id=' . $review_id . '&error=' . implode(',', $errors));
    exit;
}

$val = ($is_verified === '1') ? 1 : 0;

$upd = $conn->prepare("UPDATE product_review SET is_verified = ? WHERE review_id = ?");
if (!$upd) {
    header('Location: review-form.php?id=' . $review_id . '&error=server');
    exit;
}
$upd->bind_param('ii', $val, $review_id);
if (!$upd->execute()) {
    $upd->close();
    header('Location: review-form.php?id=' . $review_id . '&error=server');
    exit;
}
$upd->close();

header('Location: review-form.php?id=' . $review_id . '&status=updated');
exit;
?>