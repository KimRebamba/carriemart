<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

if (!$conn) { die('DB error'); }
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Load user data
$userData = [];
$stmt = $conn->prepare("SELECT user_id, username, email, address, phone_number, first_name, last_name, profile_photo_url, is_active FROM accounts WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($uid, $username, $email, $address, $phone, $first_name, $last_name, $photo_url, $is_active);
    if ($stmt->fetch()) {
        $userData = [
            'user_id' => $uid,
            'username' => $username,
            'email' => $email,
            'address' => $address,
            'phone_number' => $phone,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'profile_photo_url' => $photo_url,
            'is_active' => (int)$is_active
        ];
    }
    $stmt->close();
}

if (empty($userData)) {
    header('Location: /carriemart/main/products.php?error=not_found');
    exit;
}

$firstName = $userData['first_name'] ? $userData['first_name'] : '';
$lastName = $userData['last_name'] ? $userData['last_name'] : '';
$username = $userData['username'] ? $userData['username'] : '';
$email = $userData['email'] ? $userData['email'] : '';
$address = $userData['address'] ? $userData['address'] : '';
$phone = $userData['phone_number'] ? $userData['phone_number'] : '';
$profilePhoto = $userData['profile_photo_url'] ? $userData['profile_photo_url'] : '/carriemart/assets/person-circle.svg';

// Check for errors/success from update.php
$errors = [];
if (isset($_GET['error'])) {
    foreach (explode(',', $_GET['error']) as $e) {
        $e = trim($e);
        if ($e === 'invalid_data') $errors[] = 'Invalid data provided.';
        if ($e === 'username_exists') $errors[] = 'Username already exists.';
        if ($e === 'email_exists') $errors[] = 'Email already exists.';
        if ($e === 'server') $errors[] = 'Server error. Please try again.';
        if ($e === 'upload_failed') $errors[] = 'Failed to upload profile photo.';
    }
}

$success = false;
if (isset($_GET['status']) && $_GET['status'] === 'updated') {
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    .form-register {
        background-color: #ffffff;
        border-radius: 1rem;
        padding: 1rem;
    }

    .btn-icon-inverted img {
        width: 1.125rem;
        height: 1.125rem;
        filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
        opacity: .95;
    }

    .btn-icon img {
        width: 1.125rem;
        height: 1.125rem;
        filter: brightness(0) invert(1);
    }

    .avatar-lg {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        background: #f1f3f5;
    }
    </style>
</head>

<body>
    <div class="container">
        <main class="form-register">
            <div class="py-4 text-center">
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Logo.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-7 col-lg-8 mx-auto">
                    <h4 class="mb-3">Profile information</h4>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-3" role="alert">
                        <?php foreach ($errors as $er): ?>
                        <div>- <?php echo $er; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif ($success): ?>
                    <div class="alert alert-success mb-3" role="alert">
                        Profile updated successfully.
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="update.php" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="firstName" class="form-label">First name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" placeholder="" value="<?php echo $firstName; ?>">
                            </div>

                            <div class="col-sm-6">
                                <label for="lastName" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" placeholder="" value="<?php echo $lastName; ?>">
                            </div>

                            <div class="col-12">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $username; ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="inputPassword5" class="form-label">Password</label>
                                <input type="password" id="inputPassword5" name="password" class="form-control" aria-describedby="passwordHelpBlock">
                                <div id="passwordHelpBlock" class="form-text">
                                    Leave blank to keep current password. 8–20 characters, letters and numbers, no spaces/special characters/emoji.
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?php echo $email; ?>">
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="1234 Main St" value="<?php echo $address; ?>">
                            </div>

                            <div class="col-12">
                                <label for="phone" class="form-label">Phone number</label>
                                <input type="text" class="form-control" id="phone" name="phone_number" placeholder="09##-####-###" value="<?php echo $phone; ?>">
                            </div>
                        </div>

                        <!-- Profile picture -->
                        <div class="mb-4 mt-4">
                            <label class="form-label d-block">Profile picture</label>
                            <div class="d-flex align-items-center gap-3">
                                <img id="avatarPreview" class="avatar-lg border" src="<?php echo $profilePhoto; ?>" alt="">
                                <div class="flex-grow-1">
                                    <input class="form-control" type="file" id="formFile" name="profile_photo" accept="image/*">
                                    <small class="text-body-secondary">JPG, PNG, or GIF. Max 5MB.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row gy-2">
                            <h4 class="mb-2">Account Option</h4>
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="deactivate" name="deactivate">
                                    <label class="form-check-label" for="deactivate">Deactivate account</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="clearCart" name="clear_cart">
                                    <label class="form-check-label" for="clearCart">Clear all products from cart</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                type="submit" style="flex: 2 1 0%;">
                                Save changes
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </button>

                            <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                style="flex: 1 1 0%;" onclick="history.back()">
                                Go back
                                <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <script>
    // Simple preview for profile picture
    const fileInput = document.getElementById('formFile');
    const avatarPreview = document.getElementById('avatarPreview');
    fileInput?.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        avatarPreview.src = url;
    });
    </script>
</body>

</html>
