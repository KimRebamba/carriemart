<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$review = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Load review (edit-only)
$stmt = $conn->prepare("SELECT review_id, product_order_id, user_id, rating, review_title, review_text, is_verified, created_at
                        FROM product_review WHERE review_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($review_id, $product_order_id, $user_id, $rating, $review_title, $review_text, $is_verified, $created_at);
    if ($stmt->fetch()) {
        $review = [
            'review_id' => $review_id,
            'product_order_id' => $product_order_id,
            'user_id' => $user_id,
            'rating' => $rating,
            'review_title' => $review_title,
            'review_text' => $review_text,
            'is_verified' => $is_verified,
            'created_at' => $created_at
        ];
    } else {
        $stmt->close();
        header('Location: index.php?error=not_found');
        exit;
    }
    $stmt->close();
} else {
    header('Location: index.php?error=server');
    exit;
}

// Errors (query string from update.php redirect)
$errors = [];
if (isset($_GET['error'])) {
    foreach (explode(',', $_GET['error']) as $e) {
        $e = trim($e);
        if ($e === 'invalid_id')      $errors[] = 'Invalid review ID.';
        if ($e === 'not_found')       $errors[] = 'Review not found.';
        if ($e === 'verify_invalid')  $errors[] = 'Verified value invalid.';
        if ($e === 'server')          $errors[] = 'Server error. Please try again.';
    }
}

$success = false;
if (isset($_GET['status']) && $_GET['status'] === 'updated') {
    $success = true;
}

$isVerifiedSelect = (int)$review['is_verified'] === 1 ? '1' : '0';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CM: Review-Form</title>
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
                    <h4 class="mb-3">Review Information</h4>

                    <form method="post" action="update.php">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Review ID</label>
                                <input type="text" class="form-control" value="<?php echo $review['review_id']; ?>"
                                    disabled>
                                <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="<?php echo $review['created_at']; ?>"
                                    disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Order ID</label>
                                <input type="text" class="form-control" value="<?php echo $review['product_order_id']; ?>"
                                    disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User ID</label>
                                <input type="text" class="form-control"
                                    value="<?php echo ($review['user_id'] !== null ? $review['user_id'] : '—'); ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rating</label>
                                <input type="text" class="form-control" value="<?php echo (int)$review['rating']; ?>"
                                    disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Verified</label>
                                <select name="is_verified" class="form-select">
                                    <option value="0"
                                        <?php echo ($isVerifiedSelect === '0' ? 'selected' : ''); ?>>no</option>
                                    <option value="1"
                                        <?php echo ($isVerifiedSelect === '1' ? 'selected' : ''); ?>>yes</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Review Title</label>
                                <input type="text" class="form-control"
                                    value="<?php echo ($review['review_title'] !== null ? $review['review_title'] : ''); ?>"
                                    disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Review Text</label>
                                <textarea class="form-control" rows="5"
                                    disabled><?php echo ($review['review_text'] !== null ? $review['review_text'] : ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">All fields are read-only except Verified.</small>
                            </div>
                        </div>

                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger mb-3" role="alert">
                            <?php foreach ($errors as $er): ?>
                            <div>- <?php echo $er; ?></div>
                            <?php endforeach; ?>
                        </div>
                        <?php elseif ($success): ?>
                        <div class="alert alert-success d-flex justify-content-between align-items-center mb-3" role="alert">
                            <span>Review updated.</span>
                            <a class="btn btn-success btn-sm" href="index.php">OK</a>
                        </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                type="submit" style="flex:2 1 0%;">
                                Save changes
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </button>
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                style="flex:1 1 0%;" onclick="history.back()">
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



