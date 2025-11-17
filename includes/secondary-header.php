<?php
// Default pic if no user is logged in
$profilePic = '/carriemart/assets/default.png';
$isLoggedIn = false;

if (!empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
    if (!empty($_SESSION['profile_pic'])) {
        $profilePic = $_SESSION['profile_pic'];
    }
}
?>

<header class="p-3 mb-2 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-center">
            <a href="/carriemart/index.php" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                <img src="/carriemart/assets/Logo.svg" alt="Carriemart logo" width="40" height="40" class="me-2">
            </a>
            <!-- Updated form action to point to search.php -->
            <form method="get" action="/carriemart/main/search.php" class="d-flex mb-0 me-2 me-lg-3 flex-grow-1" style="max-width:540px;">
                <input type="text" class="form-control w-100" name="q" value="<?php echo isset($q) ? ($q) : ''; ?>" placeholder="Search products" >
                <?php if (isset($categoryId) && $categoryId !== ''): ?>
                    <input type="hidden" name="category" value="<?php echo ($categoryId); ?>">
                <?php endif; ?>
                <?php if (isset($brandId) && $brandId !== ''): ?>
                    <input type="hidden" name="brand" value="<?php echo ($brandId); ?>">
                <?php endif; ?>
                <?php if (isset($minPrice) && $minPrice !== ''): ?>
                    <input type="hidden" name="min_price" value="<?php echo ($minPrice); ?>">
                <?php endif; ?>
                <?php if (isset($maxPrice) && $maxPrice !== ''): ?>
                    <input type="hidden" name="max_price" value="<?php echo ($maxPrice); ?>">
                <?php endif; ?>
                <?php if (isset($minRating) && $minRating !== ''): ?>
                    <input type="hidden" name="min_rating" value="<?php echo ($minRating); ?>">
                <?php endif; ?>
                <?php if (isset($sort) && $sort !== ''): ?>
                    <input type="hidden" name="sort" value="<?php echo ($sort); ?>">
                <?php endif; ?>
            </form>
            <div class="dropdown text-end avatar-dropdown align-self-center">
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