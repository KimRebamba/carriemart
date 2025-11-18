<?php
$host = 'localhost';
$db   = 'carriemart';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

define("MAILTRAP_TOKEN", "30beefe8149ce6b472b64c2e7f67c275");
define("MAILTRAP_HOST", "sandbox.smtp.mailtrap.io");
define("MAILTRAP_PORT", 2525);
define("MAILTRAP_USERNAME", "d06f8760800fa0");
define("MAILTRAP_PASSWORD", "c6053775ed89ea");