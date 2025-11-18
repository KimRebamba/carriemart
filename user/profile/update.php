<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /carriemart/user/profile/profile-settings.php');
    exit;
}

if (!$conn) { die('DB error'); }
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Get form data
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$deactivate = isset($_POST['deactivate']) && $_POST['deactivate'] === '1';
$clearCart = isset($_POST['clear_cart']) && $_POST['clear_cart'] === '1';

$errors = [];

// Validate required fields
if ($username === '') {
    $errors[] = 'invalid_data';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'invalid_data';
}

// Check username uniqueness (if changed)
$chkUser = $conn->prepare("SELECT user_id FROM accounts WHERE username = ? AND user_id != ?");
if (!$chkUser) {
    error_log('Failed to prepare username check query: ' . $conn->error);
    $errors[] = 'server_error';
} else {
    $chkUser->bind_param('si', $username, $userId);
    $chkUser->execute();
    $chkUser->store_result();
    if ($chkUser->num_rows > 0) {
        $errors[] = 'username_exists';
    }
    $chkUser->close();
}

// Check email uniqueness (if changed)
$chkEmail = $conn->prepare("SELECT user_id FROM accounts WHERE email = ? AND user_id != ?");
if (!$chkEmail) {
    error_log('Failed to prepare email check query: ' . $conn->error);
    $errors[] = 'server_error';
} else {
    $chkEmail->bind_param('si', $email, $userId);
    $chkEmail->execute();
    $chkEmail->store_result();
    if ($chkEmail->num_rows > 0) {
        $errors[] = 'email_exists';
    }
    $chkEmail->close();
}

if (!empty($errors)) {
    header('Location: /carriemart/user/profile/profile-settings.php?error=' . implode(',', $errors));
    exit;
}

// Handle profile photo upload
$newPhotoUrl = null;
if (!empty($_FILES['profile_photo']['name']) && is_uploaded_file($_FILES['profile_photo']['tmp_name'])) {
    $file = $_FILES['profile_photo'];
    $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= $maxSize && isset($allowedTypes[$file['type']])) {
        $ext = $allowedTypes[$file['type']];
        $uploadDirFs = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/carriemart/uploads/profile_photos';
        if (!is_dir($uploadDirFs)) {
            @mkdir($uploadDirFs, 0777, true);
        }
        $safeBase = 'user_' . $userId . '_' . date('Ymd_His');
        $destName = $safeBase . '.' . $ext;
        $destFs = $uploadDirFs . DIRECTORY_SEPARATOR . $destName;
        if (move_uploaded_file($file['tmp_name'], $destFs)) {
            $newPhotoUrl = '/carriemart/uploads/profile_photos/' . $destName;
        } else {
            header('Location: /carriemart/user/profile/profile-settings.php?error=upload_failed');
            exit;
        }
    }
}

// Get current photo URL if not uploading new one
if ($newPhotoUrl === null) {
    $getPhoto = $conn->prepare("SELECT profile_photo_url FROM accounts WHERE user_id = ?");
    if ($getPhoto) {
        $getPhoto->bind_param('i', $userId);
        $getPhoto->execute();
        $getPhoto->bind_result($currentPhoto);
        $getPhoto->fetch();
        $newPhotoUrl = $currentPhoto !== null ? $currentPhoto : null;
        $getPhoto->close();
    }
}

// Handle NULL values for optional fields
$address_clean = $address !== '' ? $address : null;
$phone_clean = $phone_number !== '' ? $phone_number : null;
$first_name_clean = $first_name !== '' ? $first_name : null;
$last_name_clean = $last_name !== '' ? $last_name : null;

// Determine is_active value
$is_active = $deactivate ? 0 : 1;

// Build update query
if ($password !== '') {
    // Update with password
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE accounts SET username=?, email=?, address=?, phone_number=?, first_name=?, last_name=?, profile_photo_url=?, is_active=?, password=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('sssssssisi', $username, $email, $address_clean, $phone_clean, $first_name_clean, $last_name_clean, $newPhotoUrl, $is_active, $hashed, $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            header('Location: /carriemart/user/profile/profile-settings.php?error=server');
            exit;
        }
        $stmt->close();
    } else {
        header('Location: /carriemart/user/profile/profile-settings.php?error=server');
        exit;
    }
} else {
    // Update without password
    $sql = "UPDATE accounts SET username=?, email=?, address=?, phone_number=?, first_name=?, last_name=?, profile_photo_url=?, is_active=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('sssssssii', $username, $email, $address_clean, $phone_clean, $first_name_clean, $last_name_clean, $newPhotoUrl, $is_active, $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            header('Location: /carriemart/user/profile/profile-settings.php?error=server');
            exit;
        }
        $stmt->close();
    } else {
        header('Location: /carriemart/user/profile/profile-settings.php?error=server');
        exit;
    }
}

// Handle clear cart if requested
if ($clearCart) {
    // Get user's cart_id
    $getCart = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    if ($getCart) {
        $getCart->bind_param('i', $userId);
        $getCart->execute();
        $getCart->bind_result($cart_id);
        if ($getCart->fetch()) {
            $getCart->close();
            // Delete all cart products
            $delCart = $conn->prepare("DELETE FROM cart_product WHERE cart_id = ?");
            if ($delCart) {
                $delCart->bind_param('i', $cart_id);
                $delCart->execute();
                $delCart->close();
            }
        } else {
            $getCart->close();
        }
    }
}

// Update session if username changed
if (isset($_SESSION['username']) && $_SESSION['username'] !== $username) {
    $_SESSION['username'] = $username;
}

// Update session if profile photo changed
if ($newPhotoUrl !== null) {
    $_SESSION['profile_pic'] = $newPhotoUrl;
}

// If account was deactivated, logout
if ($deactivate) {
    session_destroy();
    header('Location: /carriemart/main/products.php?status=account_deactivated');
    exit;
}

header('Location: /carriemart/user/profile/profile-settings.php?status=updated');
exit;
?>

