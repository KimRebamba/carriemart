<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if ($role === 'admin' || empty($userId)) {
    $_SESSION['warning'] = "Create/Login Customer Account to access this page.";
    header("Location: /carriemart/index.php");
    exit;
}
?>