<?php
$host = 'localhost';
$db   = 'carriemart';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

define("MAILTRAP_TOKEN", "a11ee90df35ec69c6923d2a2fba5f55e");
define("MAILTRAP_HOST", "sandbox.smtp.mailtrap.io");
define("MAILTRAP_PORT", 2525);
define("MAILTRAP_USERNAME", "da9dbc54de8e7e");
define("MAILTRAP_PASSWORD", "d1cfc747ddfe4b");