<?php

// Default pic if no user is logged in
$profilePic = './assets/default.jfif';
$isLoggedIn = false;

if (!empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
    if (!empty($_SESSION['profile_pic'])) {
        $profilePic = $_SESSION['profile_pic'];
    }
}
?>
<header class="p-3 mb-3 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
            <a href="/carriemart/index.php" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                <img src="/carriemart/assets/Logo.svg" alt="Carriemart logo" width="40" height="40" class="me-2">
            </a>

            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="/carriemart/index.php" class="nav-link px-2 link-secondary">Home</a></li>
                <li><a href="/carriemart/main/categories.php" class="nav-link px-2 link-body-emphasis">Categories</a></li>
                <li><a href="/carriemart/main/products.php" class="nav-link px-2 link-body-emphasis">Products</a></li>
                <li><a href="/carriemart/main/vouchers.php" class="nav-link px-2 link-body-emphasis">Vouchers</a></li>
            </ul>

            <form class="search-form d-flex mb-0 me-2 me-lg-3" role="search" method="GET" action="/carriemart/main/search.php">
    <input type="search" class="form-control w-100" name="q" placeholder="Search..." aria-label="Search">
</form>

            <div class="dropdown text-end avatar-dropdown align-self-center">
                <a href="#"
                    class="d-inline-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?= $profilePic ?>" alt="User" width="32" height="32" class="rounded-circle">
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