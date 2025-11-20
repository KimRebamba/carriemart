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

   
$userData = [];
$stmt = $conn->prepare("SELECT user_id, username, email, address, phone_number, first_name, last_name, profile_photo_url, is_active, created_at FROM accounts WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($uid, $username, $email, $address, $phone, $first_name, $last_name, $photo_url, $is_active, $created_at);
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
            'is_active' => (int)$is_active,
            'created_at' => $created_at
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
$isDeactivated = $userData['is_active'] === 0;
$profilePhoto = $userData['profile_photo_url'] ? $userData['profile_photo_url'] : '/carriemart/assets/person-circle.svg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    .form-register {
        background-color: #ffffff;
        border-radius: 1rem;
        padding: 1rem;
    }

    .data-box {
        padding: .65rem .8rem;
        border: 1px solid #dee2e6;
        border-radius: .5rem;
        background: #f8f9fa;
        font-size: .95rem;
    }

    .label-heading {
        font-size: .675rem;
        letter-spacing: .05em;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: .25rem;
    }

    .avatar-lg {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        background: #f1f3f5;
    }

    .btn-icon-inverted img,
    .btn-icon img {
        width: 1.125rem;
        height: 1.125rem;
    }

    .btn-icon img {
        filter: brightness(0) invert(1);
    }

    .btn-icon-inverted img {
        filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
        opacity: .95;
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
                    <h4 class="mb-3">Account Information</h4>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="form-label">First name</div>
                            <div class="data-box"><?php echo $firstName ? $firstName : '—'; ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-label">Last name</div>
                            <div class="data-box"><?php echo $lastName ? $lastName : '—'; ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-label">Username</div>
                            <div class="data-box"><?php echo $username ? $username : '—'; ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-label">Email</div>
                            <div class="data-box"><?php echo $email ? $email : '—'; ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-label">Address</div>
                            <div class="data-box"><?php echo $address ? $address : '—'; ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-label">Phone number</div>
                            <div class="data-box"><?php echo $phone ? $phone : '—'; ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-label">Account status</div>
                            <div class="data-box">
                                <?php echo $isDeactivated ? 'Deactivated' : 'Active'; ?>
                            </div>
                        </div>
                           
                    <div class="mb-0">
                        <label class="form-label d-block">Profile picture</label>
                        <div class="d-flex align-items-center gap-3">
                            <img class="avatar-lg border" src="<?php echo $profilePhoto; ?>" alt="Profile picture">
                        </div>
                    </div>
                    </div>

                    <hr class="">

                    <div class="d-flex gap-2 mb-3">
                        <a href="/carriemart/user/profile/profile-settings.php"
                           class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                           style="flex:2 1 0%;">
                            Edit Account Information
                            <img src="/carriemart/assets/person-fill-gear.svg" alt="" aria-hidden="true">
                        </a>
                        <button type="button"
                            class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                            style="flex:1 1 0%;" onclick="history.back()">
                            Go back
                            <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>
