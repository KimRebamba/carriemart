<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if (empty($_SESSION['user_id'])) {
    return;
}

$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT is_active FROM accounts WHERE user_id = ? LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($is_active);
if ($stmt->fetch()) {
    if ((int)$is_active !== 1) {
        header('Location: /carriemart/user/logout.php');
        exit;
    }
}
$stmt->close();

?>