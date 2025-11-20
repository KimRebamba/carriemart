<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user_id     = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$first_name  = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name   = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$username    = isset($_POST['username']) ? trim($_POST['username']) : '';
$email       = isset($_POST['email']) ? trim($_POST['email']) : '';
$password    = isset($_POST['password']) ? (string)$_POST['password'] : '';
$address     = isset($_POST['address']) ? trim($_POST['address']) : '';
$phone       = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$role        = isset($_POST['role']) ? trim($_POST['role']) : 'customer';
$is_active   = isset($_POST['is_active']) ? 1 : 0;

$currentPhotoUrl = isset($_POST['profile_photo_url_current']) ? trim($_POST['profile_photo_url_current']) : '';
$newPhotoUrl = $currentPhotoUrl;

   
if ($user_id <= 0) {
    header('Location: account-form.php?error=server');
    exit;
}
if ($first_name === '') {
    header('Location: account-form.php?id=' . $user_id . '&error=first_name_required');
    exit;
}
if ($last_name === '') {
    header('Location: account-form.php?id=' . $user_id . '&error=last_name_required');
    exit;
}
if ($username === '' || $email === '') {
    header('Location: account-form.php?id=' . $user_id . '&error=missing_fields');
    exit;
}
if (strlen($username) < 3) {
    header('Location: account-form.php?id=' . $user_id . '&error=username_short');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: account-form.php?id=' . $user_id . '&error=invalid_email');
    exit;
}
if ($address === '') {
    header('Location: account-form.php?id=' . $user_id . '&error=address_required');
    exit;
}
if ($phone === '') {
    header('Location: account-form.php?id=' . $user_id . '&error=phone_required');
    exit;
}
$digitsPhone = preg_replace('/\D/', '', $phone);
if (!preg_match('/^09\d{9}$/', $digitsPhone)) {
    header('Location: account-form.php?id=' . $user_id . '&error=invalid_phone');
    exit;
}
if ($password !== '' && strlen($password) < 8) {
    header('Location: account-form.php?id=' . $user_id . '&error=weak_password');
    exit;
}
if ($role !== 'admin' && $role !== 'customer') {
    $role = 'customer';
}

   
$dup = $conn->prepare("SELECT user_id FROM accounts WHERE (username = ? OR email = ?) AND user_id <> ?");
if ($dup) {
    $dup->bind_param('ssi', $username, $email, $user_id);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) {
        $dup->close();
        header('Location: account-form.php?id=' . $user_id . '&error=duplicate');
        exit;
    }
    $dup->close();
} else {
    header('Location: account-form.php?id=' . $user_id . '&error=server');
    exit;
}

   
if (!empty($_FILES['profile_photo']['name']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
    $file = $_FILES['profile_photo'];
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $maxSize = 5 * 1024 * 1024;   

    if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= $maxSize && isset($allowedTypes[$file['type']])) {
        $ext = $allowedTypes[$file['type']];
        $uploadDirFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/carriemart/uploads/profile_photos';
        if (!is_dir($uploadDirFs)) {
            @mkdir($uploadDirFs, 0777, true);
        }
        $safeBase = 'user_' . $user_id . '_' . date('Ymd_His');
        $destName = $safeBase . '.' . $ext;
        $destFs = $uploadDirFs . DIRECTORY_SEPARATOR . $destName;
        if (move_uploaded_file($file['tmp_name'], $destFs)) {
            $newPhotoUrl = '/carriemart/uploads/profile_photos/' . $destName;
        }
    }
}

   
if ($password !== '') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE accounts
            SET username=?, email=?, address=?, phone_number=?, role=?, first_name=?, last_name=?, profile_photo_url=?, is_active=?, password=?
            WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            'ssssssssisi',
            $username,
            $email,
            $address,
            $phone,
            $role,
            $first_name,
            $last_name,
            $newPhotoUrl,
            $is_active,
            $hashed,
            $user_id
        );
        $stmt->execute();
        $stmt->close();
    } else {
        header('Location: account-form.php?id=' . $user_id . '&error=server');
        exit;
    }
} else {
    $sql = "UPDATE accounts
            SET username=?, email=?, address=?, phone_number=?, role=?, first_name=?, last_name=?, profile_photo_url=?, is_active=?
            WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            'ssssssssii',
            $username,
            $email,
            $address,
            $phone,
            $role,
            $first_name,
            $last_name,
            $newPhotoUrl,
            $is_active,
            $user_id
        );
        $stmt->execute();
        $stmt->close();
    } else {
        header('Location: account-form.php?id=' . $user_id . '&error=server');
        exit;
    }
}

header('Location: index.php?id=' . $user_id . '&status=updated');
exit;