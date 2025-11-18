<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$account = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$stmt = $conn->prepare("SELECT user_id, username, email, address, phone_number, role, first_name, last_name, profile_photo_url, created_at, is_active FROM accounts WHERE user_id = ?");
if (!$stmt) {
    header('Location: index.php?error=server');
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($user_id, $username, $email, $address, $phone_number, $role, $first_name, $last_name, $profile_photo_url, $created_at, $is_active);
if ($stmt->fetch()) {
    $account = [
        'user_id' => $user_id,
        'username' => $username,
        'email' => $email,
        'address' => $address,
        'phone_number' => $phone_number,
        'role' => $role,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'profile_photo_url' => $profile_photo_url,
        'created_at' => $created_at,
        'is_active' => (int)$is_active,
    ];
} else {
    $stmt->close();
    header('Location: index.php?error=not_found');
    exit;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: View Account</title>
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

    .label-small {
        font-size: .8rem;
        color: var(--bs-secondary-color);
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
                <div class="col-md-8 col-lg-7 mx-auto">
                    <h4 class="mb-3">View Account</h4>

                    <form method="post" enctype="multipart/form-data">
                        <!-- IDs & timestamps -->
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">User ID</label>
                                <input type="text" class="form-control" value="<?=
($account['user_id'] ?? '') ?>" disabled>
                                <input type="hidden" name="user_id" value="<?=
($account['user_id'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date created</label>
                                <input type="text" class="form-control" value="<?=
($account['created_at'] ?? '') ?>" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Name -->
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="first_name" class="form-label">First name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                       value="<?= ($account['first_name'] ?? '') ?>" readonly>
                            </div>

                            <div class="col-sm-6">
                                <label for="last_name" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                       value="<?= ($account['last_name'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <!-- Username / Email -->
                        <div class="row g-3 mt-0">
                            <div class="col-12">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">@</span>
                                    <input type="text" class="form-control" id="username" name="username"
                         placeholder="Username"
                         value="<?= ($account['username'] ?? '') ?>" readonly>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                       placeholder="you@example.com"
                       value="<?= ($account['email'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row g-3 mt-0">
                            <div class="col-12">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password" class="form-control"
                       aria-describedby="passwordHelpBlock" placeholder="********" disabled>
                            </div>
                        </div>

                        <!-- Contact & Address -->
                        <div class="row g-3 mt-0">
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address"
                       placeholder="1234 Main St"
                       value="<?= ($account['address'] ?? '') ?>" readonly>
                            </div>

                            <div class="col-12">
                                <label for="phone_number" class="form-label">Phone number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number"
                       placeholder="09##-###-####"
                       value="<?= ($account['phone_number'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <!-- Role & Status -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select id="role" name="role" class="form-select" disabled>
                  <option value="customer" <?= (isset($account['role']) && $account['role']==='customer')?'selected':''; ?>>customer</option>
                  <option value="admin" <?= (isset($account['role']) && $account['role']==='admin')?'selected':''; ?>>admin</option>
                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                         <?= (!isset($account['is_active']) || (int)$account['is_active']===1)?'checked':''; ?> disabled>
                                    <label class="form-check-label" for="is_active">Active account</label>
                                </div>
                            </div>
                        </div>

                        <!-- Profile picture -->
                        <div class="mb-4 mt-4">
                            <label class="form-label d-block">Profile picture</label>
                            <div class="d-flex align-items-center gap-3">
                                <img id="avatarPreview" class="avatar-lg border"
                     src="<?= ($account['profile_photo_url'] ?? '/carriemart/assets/person-circle.svg') ?>"
                     alt="avatar">
                                <div class="flex-grow-1">
                                    <input class="form-control" type="file" id="formFile" name="profile_photo" accept="image/*" disabled>
                                    <input type="hidden" name="profile_photo_url_current"
                         value="<?= ($account['profile_photo_url'] ?? '') ?>">
                                    <small class="text-body-secondary d-block">JPG, PNG, or GIF. Max 5MB.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 mb-3">
                            <a class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                               style="flex: 2 1 0%;"
                               href="account-form.php?id=<?= urlencode($account['user_id'] ?? '') ?>">
                                Edit Account
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </a>

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
</body>

</html>



