<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');

// Get filters from URL parameters
$minRating = isset($_GET['min_rating']) ? (int)$_GET['min_rating'] : 0;
$productSearch = isset($_GET['product_search']) ? $_GET['product_search'] : '';
$brandSearch = isset($_GET['brand_search']) ? $_GET['brand_search'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$hasText = isset($_GET['has_text']) ? $_GET['has_text'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

// Build SQL query
$sql = "SELECT 
    pr.review_id,
    pr.product_order_id,
    pr.rating,
    pr.review_title,
    pr.review_text,
    pr.created_at,
    po.order_id,
    p.product_name,
    b.brand_name
FROM product_review pr
INNER JOIN product_order po ON pr.product_order_id = po.product_order_id
INNER JOIN products p ON po.product_id = p.product_id
INNER JOIN brands b ON p.brand_id = b.brand_id
WHERE pr.user_id = ?";

$params = [$_SESSION['user_id']];
$types = "i";

// Apply filters
if ($minRating > 0) {
    $sql .= " AND pr.rating >= ?";
    $params[] = $minRating;
    $types .= "i";
}

if ($productSearch) {
    $sql .= " AND p.product_name LIKE ?";
    $params[] = "%$productSearch%";
    $types .= "s";
}

if ($brandSearch) {
    $sql .= " AND b.brand_name LIKE ?";
    $params[] = "%$brandSearch%";
    $types .= "s";
}

if ($dateFrom) {
    $sql .= " AND DATE(pr.created_at) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if ($dateTo) {
    $sql .= " AND DATE(pr.created_at) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

if ($hasText === 'yes') {
    $sql .= " AND pr.review_text IS NOT NULL AND pr.review_text != ''";
} elseif ($hasText === 'no') {
    $sql .= " AND (pr.review_text IS NULL OR pr.review_text = '')";
}

// Apply sorting
switch ($sortBy) {
    case 'recent':
        $sql .= " ORDER BY pr.created_at DESC";
        break;
    case 'ratingHigh':
        $sql .= " ORDER BY pr.rating DESC, pr.created_at DESC";
        break;
    case 'ratingLow':
        $sql .= " ORDER BY pr.rating ASC, pr.created_at DESC";
        break;
    case 'productAZ':
        $sql .= " ORDER BY p.product_name ASC";
        break;
    case 'productZA':
        $sql .= " ORDER BY p.product_name DESC";
        break;
    default:
        $sql .= " ORDER BY pr.created_at DESC";
}

// Execute query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$reviewCount = count($reviews);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
/* Back line */
.back-line {
    display: flex; align-items: center; gap: .5rem;
    padding: .5rem .75rem; border-bottom: 1px solid var(--bs-border-color);
    color: var(--bs-body-color); text-decoration: none;
}
.back-line:hover { background-color: rgba(var(--bs-primary-rgb), .06); text-decoration: none; }
.back-line .icon { width: 20px; height: 20px; opacity: .9; }

/* Orders layout reused for reviews */
.order-list { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.order-card { background: #fff; border-radius: .5rem; border:1px solid transparent; transition:border-color .15s ease; }
.order-card:hover { border-color: rgba(0,0,0,.2); }

.order-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06);
    flex-wrap: wrap; gap: .5rem;
}
.order-id { font-weight: 600; }
.order-date { color: var(--bs-secondary-color); font-size: .875rem; }
/* Actions beside order number */
.order-left { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.order-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.order-actions .btn-sm { padding:.25rem .5rem; }

.order-grid {
    display: grid;
    grid-template-columns: 1fr; /* no right-side actions column */
    gap: 1rem;
    padding: 1rem;
}
@media (max-width: 576px) { .order-grid { grid-template-columns: 1fr; } }

.info-sections { display: grid; gap: .75rem; }
.section-title {
    font-size: .75rem; letter-spacing: .5px; text-transform: uppercase;
    color: var(--bs-secondary-color); font-weight: 600; margin-bottom: .25rem;
}

.kv {
    display: grid; grid-template-columns: 180px 1fr; gap: .5rem;
    padding: .5rem .75rem; border: 1px solid #e9ecef; border-radius: .375rem; background: #fcfcfd;
}
.kv .k { color: var(--bs-secondary-color); font-size: .85rem; }
.kv .v { font-weight: 500; }

.actions { display: flex; flex-direction: column; gap: .5rem; align-items: stretch; }
.actions .btn { width: 100%; }
    </style>
</head>
<body>
<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/third-header.php'); ?>

<!-- Go Back line -->
<div class="container mb-3">
    <a href="#" class="back-line rounded-2" onclick="history.back(); return false;">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
        </svg>
        <span>Go Back</span>
    </a>
</div>

<!-- Filters toolbar -->
<div class="container mb-3">
    <div class="d-flex align-items-center justify-content-start">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm"
                    type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                    <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                </svg>
                Filters
            </button>
            <select class="form-select form-select-sm" aria-label="Sort by" style="width: 180px;" onchange="applySorting(this.value)">
                <option value="">Sort by</option>
                <option value="recent" <?php echo $sortBy === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                <option value="ratingHigh" <?php echo $sortBy === 'ratingHigh' ? 'selected' : ''; ?>>Highest Rating</option>
                <option value="ratingLow" <?php echo $sortBy === 'ratingLow' ? 'selected' : ''; ?>>Lowest Rating</option>
                <option value="productAZ" <?php echo $sortBy === 'productAZ' ? 'selected' : ''; ?>>Product A–Z</option>
                <option value="productZA" <?php echo $sortBy === 'productZA' ? 'selected' : ''; ?>>Product Z–A</option>
            </select>
        </div>
        <small class="text-muted" style="margin-left: 1rem;">Showing <?php echo $reviewCount; ?> review<?php echo $reviewCount != 1 ? 's' : ''; ?></small>
    </div>
</div>

<!-- Offcanvas: Filters -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Reviews</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" class="vstack gap-3">
            <div>
                <label class="form-label">Minimum rating</label>
                <select class="form-select" name="min_rating">
                    <option value="">Any</option>
                    <option value="5" <?php echo $minRating == 5 ? 'selected' : ''; ?>>5 stars</option>
                    <option value="4" <?php echo $minRating == 4 ? 'selected' : ''; ?>>4+ stars</option>
                    <option value="3" <?php echo $minRating == 3 ? 'selected' : ''; ?>>3+ stars</option>
                </select>
            </div>
            <div>
                <label class="form-label">Product title</label>
                <input type="text" class="form-control" name="product_search" placeholder="Search product" value="<?php echo $productSearch; ?>">
            </div>
            <div>
                <label class="form-label">Brand</label>
                <input type="text" class="form-control" name="brand_search" placeholder="Search brand" value="<?php echo $brandSearch; ?>">
            </div>
            <div>
                <label class="form-label">Date range</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
                    <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
                </div>
            </div>
            <div>
                <label class="form-label">Has text review</label>
                <select class="form-select" name="has_text">
                    <option value="">Any</option>
                    <option value="yes" <?php echo $hasText === 'yes' ? 'selected' : ''; ?>>Yes</option>
                    <option value="no" <?php echo $hasText === 'no' ? 'selected' : ''; ?>>No</option>
                </select>
            </div>
            <input type="hidden" name="sort" value="<?php echo $sortBy; ?>">
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <?php if ($reviewCount > 0): ?>
    <div class="order-list">
        <?php foreach ($reviews as $review): ?>
        <!-- Review for Order #<?php echo $review['order_id']; ?> -->
        <div class="order-card">
            <div class="order-header">
                <div class="order-left">
                    <div class="order-id">Review • Order #<?php echo $review['order_id']; ?></div>
                     <div class="order-actions">
                        <a class="btn btn-primary btn-sm" href="/carriemart/user/review-details.php?mode=edit&product_order_id=<?php echo $review['product_order_id']; ?>">Edit Review Details</a>
                    </div>
                </div>
                <div class="order-date">Date: <?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?></div>
            </div>
            <div class="order-grid">
                <div class="info-sections">
                    <div class="section-title">Review details</div>

                    <div class="kv"><div class="k">Order number</div><div class="v">#<?php echo $review['order_id']; ?></div></div>
                    <div class="kv"><div class="k">Product title</div><div class="v"><?php echo $review['product_name']; ?></div></div>
                    <div class="kv"><div class="k">Brand</div><div class="v"><?php echo $review['brand_name']; ?></div></div>
                    <div class="kv"><div class="k">Review title</div><div class="v"><?php echo $review['review_title'] ? $review['review_title'] : '—'; ?></div></div>
                    <div class="kv"><div class="k">Review description</div>
                        <div class="v"><?php echo $review['review_text'] ? $review['review_text'] : '—'; ?></div>
                    </div>
                    <div class="kv"><div class="k">Rating</div><div class="v"><?php echo $review['rating']; ?>/5</div></div>
                    <div class="kv"><div class="k">Date</div><div class="v"><?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?></div></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <h5>No reviews found</h5>
        <p class="mb-0">You haven't written any reviews yet, or no reviews match your current filters.</p>
    </div>
    <?php endif; ?>
</div>

<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
function applySorting(value) {
    const url = new URL(window.location.href);
    if (value) {
        url.searchParams.set('sort', value);
    } else {
        url.searchParams.delete('sort');
    }
    window.location.href = url.toString();
}
</script>
</body>
</html>