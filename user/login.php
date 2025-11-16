<?php
ini_set('session.cookie_lifetime', 0);
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Database connection failed.');
}

$errors = [];
$success = false;

if (!empty($_SESSION['user_id'])) {
    $success = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id, username, password, role, is_active, profile_photo_url FROM accounts WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $errors[] = 'Invalid credentials.';
        } else {
            $stmt->bind_result($user_id, $username, $hash, $role, $is_active, $profile_photo_url);
            $stmt->fetch();
            if ((int)$is_active !== 1) {
                $errors[] = 'Account inactive.';
            } elseif (!password_verify($password, $hash)) {
                $errors[] = 'Invalid credentials.';
            } else {
         
                $_SESSION['user_id']  = (int)$user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role']     = $role;
                $_SESSION['profile_pic'] = $profile_photo_url; 
                 $_SESSION['is_active'] = (int)$is_active;

                $success = true;

                if ($remember) {
                    setcookie(session_name(), session_id(), time() + 60*60*24*30, "/");
                }

                 
            }
        }
        $stmt->close();
    }
}

mysqli_close($conn);
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    .form-signin {
        border-radius: 1rem;
        max-width: 430px;
        padding: 1rem;
        background: #fff;
    }

    .btn-icon img {
        width: 1.125rem;
        height: 1.125rem;
         filter: brightness(0) invert(1);
    }

    .btn-icon-inverted img {
        width: 1.125rem;
        height: 1.125rem;
        filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
        opacity: .95;
    }
    </style>
</head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">
    <main class="form-signin w-100 m-auto">
        <form method="post" novalidate>
            <img class="d-block mx-auto mb-4" src="/carriemart/assets/Logo.svg" alt="CarrieMart" width="72" height="57">

            <?php if ($errors): ?>
                <div class="alert alert-danger mb-3">
                    <?php foreach ($errors as $error): ?>
                        <div>- <?php echo $error; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($success): ?>
    <div class="alert alert-success d-flex justify-content-between align-items-center mb-3">
        <span><?php echo empty($_SESSION['user_id']) ? 'Already logged in.' : 'Login successful!'; ?></span>
        <a class="btn btn-success btn-sm" href="<?php echo ($_SESSION['role'] ?? '') === 'admin' ? '/carriemart/admin/index.php' : '/carriemart/index.php'; ?>">Continue</a>
    </div>
<?php endif; ?>

            <div class="row g-2 align-items-center mb-3">
                <label for="inputEmail" class="col-12 col-sm-3 col-form-label text-sm-start">Email</label>
                <div class="col-12 col-sm-9">
                    <input type="email" class="form-control mt-1 mt-sm-0" id="inputEmail" name="email"
                    
                           placeholder="you@example.com" required <?php echo $success ? 'disabled' : ''; ?>>
                </div>
            </div>

            <div class="row g-2 align-items-center mb-3">
                <label for="inputPassword" class="col-12 col-sm-3 col-form-label text-sm-start">Password</label>
                <div class="col-12 col-sm-9">
                    <input type="text" class="form-control mt-1 mt-sm-0" id="inputPassword" name="password"
                           placeholder="••••••••" <?php echo $success ? 'disabled' : ''; ?>>
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-12 offset-sm-3 col-sm-9">
                    <div class="form-check form-switch pt-1">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" <?php echo !empty($_POST['remember']) ? 'checked' : ''; ?> <?php echo $success ? 'disabled' : ''; ?>>
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mb-2">
                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 btn-icon" <?php echo $success ? 'disabled' : ''; ?>>
                    Sign in
                    <img src="/carriemart/assets/person-circle.svg" alt="" aria-hidden="true">
                </button>
            </div>

            <div class="row g-2">
                <div class="col-8">
                    <a href="/carriemart/user/register.php" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 btn-icon">
                        Create an account
                        <img src="/carriemart/assets/plus-circle.svg" alt="" aria-hidden="true">
                    </a>
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted" onclick="history.back()">
                        Go back
                        <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                    </button>
                </div>
            </div>
        </form>
    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>


</html>