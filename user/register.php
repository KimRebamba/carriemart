<?php
//PDO was not used because I don't.. understand it HAHAHA
//if something goes wrong with it, eeerr yeah, nope. No PDO.

require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die('Database connection failed.');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $email      = trim($_POST['email'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');

    // error messages to show user
    if (empty($first_name)) {
        $errors[] = 'First name is required.';
    }
    if (empty($last_name)) {
        $errors[] = 'Last name is required.';
    }
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (empty($address)) {
        $errors[] = 'Address is required.';
    }
   if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    } else {
        // Regex: 09XX-XXXX-XXX
        if (!preg_match('/^09\d{2}\d{4}\d{3}$/', $phone)) {
            $errors[] = 'Phone must match format 09XX-XXXX-XXX.';
        }
    }
    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    //works
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM accounts WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();

        if ($count > 0) {
            $errors[] = 'Username already taken.';
        }
        $stmt->close();
    }

     //works
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM accounts WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();

      if ($count > 0) {
            $errors[] = 'Email already registered.';
        }
        $stmt->close();
    }

    
    $photo_url = NULL;
    if (empty($errors) && !empty($_FILES['profile_photo']['tmp_name'])) {
        $file = $_FILES['profile_photo'];
        
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image too large (max 5MB).';
        } else {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($file['tmp_name']);
            
            if (in_array($file_type, $allowed)) {
                $ext = ($file_type == 'image/jpeg') ? 'jpg' : (($file_type == 'image/png') ? 'png' : 'gif');
                
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/carriemart/uploads/profiles';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0775, true);
                }
                //if two pics have the same name, fck em
                $filename = 'pp_' . uniqid() . '.' . $ext;
                $destination = $upload_dir . '/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $photo_url = '/carriemart/uploads/profiles/' . $filename;
                } else {
                    $errors[] = 'Failed to save image.';
                }
            } else {
                $errors[] = 'Only JPG, PNG, and GIF images allowed.';
            }
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO accounts 
                (username, password, email, address, phone_number, role, first_name, last_name, profile_photo_url) 
                VALUES (?, ?, ?, ?, ?, 'customer', ?, ?, ?)");
        
        $stmt->bind_param("ssssssss", $username, $hashed_password, $email, $address, $phone, $first_name, $last_name, $photo_url);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();
            
            $cart_stmt = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
            $cart_stmt->bind_param("i", $user_id);
            $cart_stmt->execute();
            $cart_stmt->close();
            
            $success = true;
        } else {
            $errors[] = 'Registration failed. Please try again.';
            $stmt->close();
            
            if ($photo_url) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $photo_url);
            }
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <div class="container">
        <main class="form-register">
            <div class="py-4 text-center">
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Header-Logo-01.svg" alt="" width="72" height="57">
            </div>
            
            <div class="row g-5">
                <div class="col-md-7 col-lg-8 mx-auto">
                    <h4 class="mb-3">Account Information</h4>


                    <form method="post" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="firstName" class="form-label">First name</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" <?php echo (isset($_POST['first_name']) ? 'value="' . ($_POST['first_name']) . '"' : ''); ?>>
                            </div>
                            
                            <div class="col-sm-6">
                                <label for="lastName" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" <?php echo (isset($_POST['last_name']) ? 'value="' . ($_POST['last_name']) . '"' : ''); ?>>
                            </div>
                            
                            <div class="col-12">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" class="form-control" id="username" name="username"
                                    <?php echo (isset($_POST['username']) ? 'value="' . ($_POST['username']) . '"' : ''); ?>>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="password" class="form-label">Password</label>
                                <input type="text" class="form-control" id="password" name="password"
                                <?php echo (isset($_POST['password']) ? 'value="' . ($_POST['password']) . '"' : ''); ?>
                                >
                                <div class="form-text">At least 8 characters. (For better security, duh.)</div>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="you@example.com"
                                 <?php echo (isset($_POST['email']) ? 'value="' . ($_POST['email']) . '"' : ''); ?>>
                            </div>
                            
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="1234 Main St"
                                 <?php echo (isset($_POST['address']) ? 'value="' . ($_POST['address']) . '"' : ''); ?>>
                            </div>

                            <div class="col-12">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone_number" placeholder="09##-####-###"
                                <?php echo (isset($_POST['phone_number']) ? 'value="' . ($_POST['phone_number']) . '"' : ''); ?>>
                            </div>

                            <div class="col-12">
                                <label for="formFile" class="form-label">Upload a profile picture (optional)</label>
                                <input class="form-control" type="file" id="formFile" name="profile_photo" accept="image/*">
                            </div>

                            <?php if (!empty($errors)): ?> <!-- display ERRORS -->
                        <div class="alert alert-danger mb-0" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div>- <?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div class ="mb-2">
                    <?php elseif ($success): ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-0" role="alert">
    <span>Registration successful! You can now log in.</span>
    <a class="btn btn-success" href="/carriemart/user/login.php" role="button">Success</a>
</div>
                    <?php endif; ?>

                            <div class="col-12 mt-0">
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-lg flex-grow-1 d-flex align-items-center justify-content-center gap-2 btn-icon" type="submit">
                                        Submit
                                        <img src="/carriemart/assets/person-circle.svg" alt="">
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2 btn-icon-inverted" onclick="history.back()">
                                        Go back
                                        <img src="/carriemart/assets/caret-right-square.svg" alt="">
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>