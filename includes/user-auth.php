<?php
if (($_SESSION['role'] == 'admin') || empty($_SESSION['user_id'])) {
    $_SESSION['warning'] = "Create/Login Customer Account to access this page.";
    header("Location: /carriemart/index.php");
    exit;
}
?>