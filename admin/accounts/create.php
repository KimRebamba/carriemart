<?php
$profileUrl = '/carriemart/uploads/profile_photos/default.jpg'; 
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$first_name  = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name   = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$username    = isset($_POST['username']) ? trim($_POST['username']) : '';
$email       = isset($_POST['email']) ? trim($_POST['email']) : '';
$password    = isset($_POST['password']) ? (string)$_POST['password'] : '';
$address     = isset($_POST['address']) ? trim($_POST['address']) : '';
$phone       = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$role        = isset($_POST['role']) ? trim($_POST['role']) : 'customer';
$is_active   = isset($_POST['is_active']) ? 1 : 0;

// Warnings/validation (mirrors register.php)
if ($first_name === '') { header('Location: account-form.php?error=first_name_required'); exit; }
if ($last_name === '')  { header('Location: account-form.php?error=last_name_required'); exit; }
if ($username === '' || strlen($username) < 3) { header('Location: account-form.php?error=username_short'); exit; }
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { header('Location: account-form.php?error=invalid_email'); exit; }
if ($address === '') { header('Location: account-form.php?error=address_required'); exit; }
if ($phone === '') { header('Location: account-form.php?error=phone_required'); exit; }
// Same intent as register.php warning: Phone must match format 09XX-XXXX-XXX (digits-only check)
$digitsPhone = preg_replace('/\D/', '', $phone);
if (!preg_match('/^09\d{2}\d{4}\d{3}$/', $digitsPhone)) { header('Location: account-form.php?error=invalid_phone'); exit; }
// Password hint: At least 8 characters (as warned in register.php)
if ($password === '' || strlen($password) < 8) { header('Location: account-form.php?error=weak_password'); exit; }

if ($role !== 'admin' && $role !== 'customer') {
    $role = 'customer';
}

// Unique username/email check
$dup = $conn->prepare("SELECT user_id FROM accounts WHERE username = ? OR email = ? LIMIT 1");
if ($dup) {
    $dup->bind_param('ss', $username, $email);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) {
        $dup->close();
        header('Location: account-form.php?error=duplicate');
        exit;
    }
    $dup->close();
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$profileUrl = '/carriemart/uploads/profile_photos/default.jpg'; 

$sql = "INSERT INTO accounts (username, password, email, address, phone_number, role, first_name, last_name, profile_photo_url, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: account-form.php?error=server');
    exit;
}
$stmt->bind_param(
    'sssssssssi',
    $username,
    $hashed,
    $email,
    $address,
    $phone,
    $role,
    $first_name,
    $last_name,
    $profileUrl,
    $is_active
);
$stmt->execute();
$newId = $stmt->insert_id;
$stmt->close();

// Optional profile photo upload
if ($newId > 0 && !empty($_FILES['profile_photo']['name']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
    $file = $_FILES['profile_photo'];
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $maxSize = 5 * 1024 * 1024;

    if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= $maxSize && isset($allowedTypes[$file['type']])) {
        $ext = $allowedTypes[$file['type']];
        $uploadDirFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . DIRECTORY_SEPARATOR . 'carriemart' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile_photos';
        if (!is_dir($uploadDirFs)) {
            @mkdir($uploadDirFs, 0777, true);
        }
        $destName = 'user_' . $newId . '_' . date('Ymd_His') . '.' . $ext;
        $destFs = $uploadDirFs . DIRECTORY_SEPARATOR . $destName;
        if (move_uploaded_file($file['tmp_name'], $destFs)) {
            $photoUrl = '/carriemart/uploads/profile_photos/' . $destName;
            $up = $conn->prepare("UPDATE accounts SET profile_photo_url = ? WHERE user_id = ?");
            if ($up) {
                $up->bind_param('si', $photoUrl, $newId);
                $up->execute();
                $up->close();
            }
        }
    }
}

header('Location: index.php');
exit;