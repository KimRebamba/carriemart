<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// Inputs (GET)
$q          = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort       = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$verified   = isset($_GET['verified']) ? trim($_GET['verified']) : '';
$minRating  = isset($_GET['min_rating']) ? trim($_GET['min_rating']) : '';
$dateFrom   = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo     = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$sql = "SELECT r.review_id, r.product_order_id, r.user_id, r.rating,
               r.review_title, r.is_verified, r.created_at
        FROM product_review r
        WHERE 1";
$types = '';
$params = [];

// Search (review_id / product_order_id / user_id / title)
if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND (CAST(r.review_id AS CHAR) LIKE ?
                  OR CAST(r.product_order_id AS CHAR) LIKE ?
                  OR CAST(r.user_id AS CHAR) LIKE ?
                  OR r.review_title LIKE ?)";
    $types .= 'ssss';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}

// Verified filter
if ($verified === '1') {
    $sql .= " AND r.is_verified = 1";
} elseif ($verified === '0') {
    $sql .= " AND r.is_verified = 0";
}

// Min rating filter
if ($minRating !== '' && ctype_digit($minRating)) {
    $mr = (int)$minRating;
    if ($mr >= 1 && $mr <= 5) {
        $sql .= " AND r.rating >= ?";
        $types .= 'i';
        $params[] = $mr;
    }
}

// Date range (created_at date portion)
$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
if ($dateFrom !== '' && $validDate($dateFrom)) {
    $sql .= " AND DATE(r.created_at) >= ?";
    $types .= 's';
    $params[] = $dateFrom;
}
if ($dateTo !== '' && $validDate($dateTo)) {
    $sql .= " AND DATE(r.created_at) <= ?";
    $types .= 's';
    $params[] = $dateTo;
}

// Sorting
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY r.created_at ASC, r.review_id ASC";
        break;
    case 'ratingHigh':
        $sql .= " ORDER BY r.rating DESC, r.created_at DESC";
        break;
    case 'ratingLow':
        $sql .= " ORDER BY r.rating ASC, r.created_at DESC";
        break;
    case 'verified':
        $sql .= " ORDER BY r.is_verified DESC, r.created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY r.created_at DESC, r.review_id DESC";
        break;
}

// Fetch reviews
$reviews = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($review_id, $product_order_id, $user_id, $rating, $review_title, $is_verified, $created_at);
    while ($stmt->fetch()) {
        $reviews[] = [
            'review_id' => $review_id,
            'product_order_id' => $product_order_id,
            'user_id' => $user_id,
            'rating' => $rating,
            'review_title' => $review_title,
            'is_verified' => $is_verified,
            'created_at' => $created_at
        ];
    }
    $stmt->close();
}

// Messages
$errorMsg = '';
if (isset($_GET['error'])) {
    $code = trim($_GET['error']);
    if ($code === 'invalid_id') $errorMsg = 'Invalid review ID.';
    if ($code === 'not_found') $errorMsg = 'Review not found.';
    if ($code === 'server') $errorMsg = 'Server error. Please try again.';
}
$statusMsg = '';
if (isset($_GET['status'])) {
    $st = trim($_GET['status']);
    if ($st === 'deleted') $statusMsg = 'Review deleted.';
    if ($st === 'updated') $statusMsg = 'Review updated.';
    if ($st === 'created') $statusMsg = 'Review created.';
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Reviews</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .ver-yes { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .ver-no { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        @media (max-width: 992px){
            .table-responsive { font-size:.85rem; }
            .actions-cell .btn { font-size:.65rem; }
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php'); ?>
<div class="flex-grow-1 p-3">
    <div class="container-fluid">

        <h3 class="mb-3 d-flex align-items-center gap-2">
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/star-half.svg" alt="" width="22" height="22" class="mt-1">
            Reviews
        </h3>

        <?php if ($errorMsg !== ''): ?>
            <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
        <?php elseif ($statusMsg !== ''): ?>
            <div class="alert alert-success"><?php echo $statusMsg; ?></div>
        <?php endif; ?>

        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                        <div class="input-group input-group-sm" style="width:320px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search review ID / product order / user">
                        </div>
                        <button class="btn btn-outline-secondary btn-sm"
                                type="button" data-bs-toggle="offcanvas"
                                data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                            </svg>
                            Filters
                        </button>
                        <select class="form-select form-select-sm" name="sort" style="width:180px;" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="newest"     <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="oldest"     <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
                            <option value="ratingHigh" <?php if($sort==='ratingHigh') echo 'selected'; ?>>Rating: High→Low</option>
                            <option value="ratingLow"  <?php if($sort==='ratingLow') echo 'selected'; ?>>Rating: Low→High</option>
                            <option value="verified"   <?php if($sort==='verified') echo 'selected'; ?>>Verified first</option>
                        </select>
                        <!-- preserve filters -->
                        <input type="hidden" name="verified" value="<?php echo $verified; ?>">
                        <input type="hidden" name="min_rating" value="<?php echo $minRating; ?>">
                        <input type="hidden" name="date_from" value="<?php echo $dateFrom; ?>">
                        <input type="hidden" name="date_to" value="<?php echo $dateTo; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($reviews); ?> reviews</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Review ID</th>
                                <th>Prod Order ID</th>
                                <th>User ID</th>
                                <th class="text-end">Rating</th>
                                <th>Title</th>
                                <th>Verified</th>
                                <th>Created</th>
                                <th class="text-center" style="width:150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No reviews found.</td></tr>
                        <?php else: foreach ($reviews as $r):
                            $verClass = $r['is_verified'] ? 'ver-yes' : 'ver-no';
                            $createdDisplay = substr($r['created_at'],0,10);
                            $title = ($r['review_title'] !== null && $r['review_title'] !== '') ? $r['review_title'] : '—';
                        ?>
                            <tr>
                                <td><?php echo $r['review_id']; ?></td>
                                <td><?php echo $r['product_order_id']; ?></td>
                                <td><?php echo ($r['user_id'] !== null ? $r['user_id'] : '—'); ?></td>
                                <td class="text-end"><?php echo (int)$r['rating']; ?></td>
                                <td><?php echo $title; ?></td>
                                <td><span class="status-badge <?php echo $verClass; ?>"><?php echo $r['is_verified'] ? 'yes' : 'no'; ?></span></td>
                                <td><span class="small text-muted"><?php echo $createdDisplay; ?></span></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo $r['review_id']; ?>" class="btn btn-outline-primary btn-sm my-1">View</a>
                                    <a href="edit.php?id=<?php echo $r['review_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $r['review_id']; ?>" class="btn btn-outline-danger btn-sm mb-1"
                                       onclick="return confirm('Delete review #<?php echo $r['review_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Offcanvas: Filters (Reviews) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Reviews</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <div>
                        <label class="form-label">Verified</label>
                        <select class="form-select" name="verified">
                            <option value="">Any</option>
                            <option value="1" <?php if($verified==='1') echo 'selected'; ?>>Verified</option>
                            <option value="0" <?php if($verified==='0') echo 'selected'; ?>>Unverified</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Min rating</label>
                        <input type="text" class="form-control" name="min_rating" value="<?php echo $minRating; ?>" placeholder="1–5">
                    </div>
                    <div>
                        <label class="form-label">Created date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="date_from" value="<?php echo $dateFrom; ?>">
                            <input type="date" class="form-control" name="date_to" value="<?php echo $dateTo; ?>">
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>