
<?php

// Default pic if no user is logged in
$profilePic = '/carriemart/assets/default.png';
$isLoggedIn = false;

if (($_SESSION['role'] == 'admin') || empty($_SESSION['user_id'])) {
    $_SESSION['warning'] = "Create/Login Account to access this page.";
    header("Location: /carriemart/index.php");
    exit;
}

if (!empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
    if (!empty($_SESSION['profile_pic'])) {
        $profilePic = $_SESSION['profile_pic'];
    }
}

?>

<header class="p-3 mb-2 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="min-height:48px;">
            <a href="/carriemart/main/index.php" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                <img src="/carriemart/assets/Logo.svg" alt="Carriemart logo" width="40" height="40" class="me-2">
            </a>
            <div class="dropdown text-end avatar-dropdown">
                <a href="#"
                    class="d-inline-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= ($profilePic) ?>" alt="User" width="32" height="32" class="rounded-circle">
                </a>
                <ul class="dropdown-menu text-small">                
                    <?php if ($isLoggedIn): ?>
                        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                            <!-- Admin menu -->
                            <li><a class="dropdown-item" href="/carriemart/admin/index.php">Admin Panel</a></li>
                        
                        <?php else: ?>
                            <!-- Customer menu -->
                            <li><a class="dropdown-item" href="/carriemart/user/profile/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/carriemart/user/cart/cart.php">CarrieCart</a></li>
                            <li><a class="dropdown-item" href="/carriemart/user/orders/orders.php">Orders</a></li>
                            <li><a class="dropdown-item" href="/carriemart/user/reviews/reviews.php">Reviews</a></li>
                            <li><a class="dropdown-item" href="/carriemart/user/returns/returns.php">Returns</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/carriemart/user/logout.php">Log out</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item" href="/carriemart/user/login.php">Sign in</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</header>