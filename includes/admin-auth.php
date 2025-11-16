<?php
ini_set('session.cookie_lifetime', 0);

session_start();

if (($_SESSION['role'] !== 'admin') || empty($_SESSION['user_id'])) {
    $_SESSION['warning'] = "You are not allowed to access that page.";
    header("Location: /carriemart/index.php");
    exit;
}

?>