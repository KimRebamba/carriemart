<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

// Determine mode: add or edit
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'add';
$product_order_id = isset($_GET['product_order_id']) ? (int)$_GET['product_order_id'] : 0;

if (!$product_order_id) {
    header('Location: /carriemart/user/orders/orders.php');
    exit;
}

// Get product order details
$sql = "SELECT 
    po.product_order_id,
    po.order_id,
    po.quantity,
    po.unit_price,
    p.product_name,
    COALESCE(b.brand_name, 'Unknown') AS brand_name
FROM product_order po
INNER JOIN products p ON po.product_id = p.product_id
LEFT JOIN brands b ON p.brand_id = b.brand_id
INNER JOIN orders o ON po.order_id = o.order_id
WHERE po.product_order_id = ? AND o.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$product_order = $result->fetch_assoc();
$stmt->close();

if (!$product_order) {
    header('Location: /carriemart/user/orders/orders.php');
    exit;
}

// Initialize review data
$review_title = '';
$review_text = '';
$rating = 3;
$review_id = null;
$created_at = date('Y-m-d H:i');

// Check if review exists (regardless of mode)
$sql = "SELECT review_id, review_title, review_text, rating, created_at 
        FROM product_review 
        WHERE product_order_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$existing_review = $result->fetch_assoc();
$stmt->close();

if ($existing_review) {
    // Review exists - load it
    $review_title = $existing_review['review_title'] !== null ? $existing_review['review_title'] : '';
    $review_text = $existing_review['review_text'] !== null ? $existing_review['review_text'] : '';
    $rating = $existing_review['rating'];
    $review_id = $existing_review['review_id'];
    $created_at = date('Y-m-d H:i', strtotime($existing_review['created_at']));
    $mode = 'edit'; // Force edit mode if review exists
}

$total_price = $product_order['unit_price'] * $product_order['quantity'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CarrieMart: Review Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <style>
    .container { max-width: 960px; }
    .btn-icon-inverted img { width:1.125rem; height:1.125rem; filter:invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%); opacity:.95; }
    .readonly-box { background:#f8f9fa; border:1px solid #dee2e6; padding:.65rem .75rem; border-radius:.375rem; font-weight:500; }
    .label-small { font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; color:var(--bs-secondary-color); margin-bottom:.25rem; }
    .rating-label .star { width:16px; height:16px; color:#f5c518; margin-left:.25rem; vertical-align:middle;  margin-bottom:.35rem;}
  </style>
</head>
<body>
  <div class="container">
    <main>
      <div class="py-4 text-center">
        <img class="d-block mx-auto mb-0" src="/carriemart/assets/Logo.svg" alt="" width="72" height="57">
      </div>

      <div class="row g-5">

        <div class="col-md-5 col-lg-4 order-md-last my-5">
          <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-primary">Your item</span>
            <span class="badge bg-primary rounded-pill">1</span>
          </h4>
          <ul class="list-group mb-3">
            <li class="list-group-item d-flex justify-content-between lh-sm">
              <div>
                <h6 class="my-0"><?php echo $product_order['product_name']; ?></h6>
                <small class="text-body-secondary"><?php echo $product_order['brand_name']; ?> • Qty: <?php echo $product_order['quantity']; ?></small>
              </div>
              <span class="text-body-secondary">₱<?php echo number_format($total_price, 2); ?></span>
            </li>
          </ul>
        </div>

        <!-- Main: Review Information -->
        <div class="col-md-7 col-lg-8 my-5">
          <h4 class="mb-3"><?php echo $review_id ? 'Edit Review' : 'Add Review'; ?></h4>
          <form method="POST" action="<?php echo $review_id ? 'update.php' : 'create.php'; ?>">
            <input type="hidden" name="product_order_id" value="<?php echo $product_order_id; ?>">
            <?php if ($review_id): ?>
            <input type="hidden" name="review_id" value="<?php echo $review_id; ?>">
            <?php endif; ?>
            <div class="row g-3">

              <div class="col-12">
                <label for="review_title" class="form-label">Review title</label>
                <input type="text" class="form-control" id="review_title" name="review_title"
                       placeholder="Summarize your experience"
                       value="<?php echo $review_title; ?>">
              </div>


              <div class="col-12">
                <label for="review_text" class="form-label">Review description</label>
                <textarea class="form-control" id="review_text" name="review_text" rows="4"
                          placeholder="Share details about what you liked or disliked."><?php echo $review_text; ?></textarea>
              </div>

        
              <div class="col-12">
                <label for="range2" class="form-label rating-label">
                  Rating (1–5)
                  <svg class="star" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                    <path d="M3.612 15.443 4.6 9.97.825 6.765l5.059-.736L8 1.5l2.116 4.529 5.059.736L11.4 9.97l.988 5.473L8 12.897l-4.388 2.546z"/>
                  </svg> 
                </label>
                <input type="range" class="form-range" min="1" max="5" step="1" id="range2" name="rating" value="<?php echo $rating; ?>">
              </div>

              <div class="col-6">
                <div class="label-small">Order number</div>
                <div class="readonly-box">#<?php echo $product_order['order_id']; ?></div>
              </div>

        
              <div class="col-6">
                <div class="label-small">Date</div>
                <div class="readonly-box"><?php echo $created_at; ?></div>
              </div>
            </div>

            <hr class="my-4">
            <div class="d-flex w-100 gap-2">
              <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center btn-icon-inverted"
                      type="submit" style="flex:2 1 0%;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                     class="bi bi-bag-check me-2" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                  <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                </svg>
                Save Review
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
          integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"></script>
</body>
</html>