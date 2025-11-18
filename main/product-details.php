<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
if (!$conn) { die('DB error'); }

$id_raw = isset($_GET['id']) ? trim($_GET['id']) : '';
$product_id = ctype_digit($id_raw) ? (int)$id_raw : 0;
if ($product_id <= 0) {
    header('Location: /carriemart/main/products.php?error=invalid_id');
    exit;
}

// Fetch product core info
$sql = "
SELECT
  p.product_id, p.product_name, p.model, p.retail_price, p.cost_price,
  p.stock_level, p.description, p.specifications, p.product_condition,
  p.warranty_months, p.created_at,
  b.brand_id, b.brand_name,
  c.category_id, c.category_name,
  s.supplier_id, s.supplier_name
FROM products p
LEFT JOIN brands b ON b.brand_id = p.brand_id
LEFT JOIN categories c ON c.category_id = p.category_id
LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id
WHERE p.product_id = ?
LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) { header('Location: /carriemart/main/products.php?error=server'); exit; }
$stmt->bind_param('i', $product_id);
$stmt->execute();
$stmt->bind_result(
    $pid, $pname, $pmodel, $retail_price, $cost_price,
    $stock_level, $pdesc, $pspecs, $pcond,
    $warranty_months, $pcreated,
    $brand_id, $brand_name,
    $cat_id, $cat_name,
    $supp_id, $supp_name
);
if (!$stmt->fetch()) {
    $stmt->close();
    header('Location: /carriemart/main/products.php?error=not_found');
    exit;
}
$stmt->close();

// Photos (primary + gallery)
$photos = [];
$ph = $conn->prepare("SELECT photo_url, is_primary, sort_order FROM product_photos WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC, product_photo_id ASC");
if ($ph) {
    $ph->bind_param('i', $product_id);
    $ph->execute();
    $ph->bind_result($purl, $pprimary, $psort);
    while ($ph->fetch()) {
        $photos[] = ['url' => $purl, 'primary' => (int)$pprimary, 'sort' => (int)$psort];
    }
    $ph->close();
}
$defaultImg = '/carriemart/assets/default-product.png';
$heroImg = !empty($photos) ? $photos[0]['url'] : $defaultImg;

// Ratings summary (only verified reviews)
$avg_rating = 0.0; $rating_count = 0;
$rt = $conn->prepare("
    SELECT COALESCE(AVG(pr.rating),0), COALESCE(COUNT(pr.review_id),0)
    FROM product_review pr
    JOIN product_order po ON po.product_order_id = pr.product_order_id
    WHERE po.product_id = ? AND pr.is_verified = 1
");
if ($rt) {
    $rt->bind_param('i', $product_id);
    $rt->execute();
    $rt->bind_result($avg_rating, $rating_count);
    $rt->fetch();
    $rt->close();
}
$avg_rating = (float)$avg_rating;
$rating_display = $rating_count > 0 ? number_format($avg_rating, 1) : '—';

// Reviews list (pagination)
$limit = 10;
$page_raw = isset($_GET['page']) ? trim($_GET['page']) : '1';
$page = ctype_digit($page_raw) && (int)$page_raw > 0 ? (int)$page_raw : 1;
$offset = ($page - 1) * $limit;

$totalReviews = 0;
$rc = $conn->prepare("
    SELECT COUNT(*)
    FROM product_review pr
    JOIN product_order po ON po.product_order_id = pr.product_order_id
    WHERE po.product_id = ? AND pr.is_verified = 1
");
if ($rc) {
    $rc->bind_param('i', $product_id);
    $rc->execute();
    $rc->bind_result($totalReviews);
    $rc->fetch();
    $rc->close();
}

$reviews = [];
$rv = $conn->prepare("
    SELECT pr.review_id, pr.rating, pr.review_title, pr.review_text, pr.created_at,
           COALESCE(CONCAT(TRIM(a.first_name),' ',TRIM(a.last_name)), a.username) AS uname
    FROM product_review pr
    JOIN product_order po ON po.product_order_id = pr.product_order_id
    LEFT JOIN accounts a ON a.user_id = pr.user_id
    WHERE po.product_id = ? AND pr.is_verified = 1
    ORDER BY pr.created_at DESC
    LIMIT ? OFFSET ?
");
if ($rv) {
    $rv->bind_param('iii', $product_id, $limit, $offset);
    $rv->execute();
    $rv->bind_result($rid, $rrating, $rtitle, $rtext, $rcreated, $uname);
    while ($rv->fetch()) {
        $reviews[] = [
            'review_id' => $rid,
            'rating' => (int)$rrating,
            'review_title' => $rtitle,
            'review_text' => $rtext,
            'created_at' => $rcreated,
            'user' => $uname ? $uname : 'Anonymous'
        ];
    }
    $rv->close();
}
$hasPrev = $page > 1;
$hasNext = ($offset + $limit) < $totalReviews;

// Helpers
function peso($v){ return '₱' . number_format((float)$v, 2, '.', ','); }
$stockBadgeClass = $stock_level > 10 ? 'text-bg-success' : ($stock_level > 0 ? 'text-bg-warning' : 'text-bg-danger');
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    .back-line { display:flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-bottom:1px solid var(--bs-border-color); color:var(--bs-body-color); text-decoration:none; }
    .back-line:hover { background-color: rgba(var(--bs-primary-rgb), .06); text-decoration: none; }
    .back-line .icon { width: 20px; height: 20px; opacity: .9; }

    .section { padding-block: 1rem; }
    @media (min-width: 992px){ .section { padding-block:1.25rem; } }

    .gallery-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .gallery-grid .hero { grid-column: 1 / span 3; aspect-ratio: 16 / 9; border-radius: 1rem; overflow: hidden; }
    .gallery-grid .thumb { aspect-ratio: 4 / 3; border-radius: .75rem; overflow: hidden; }
    .gallery-grid img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .equal-row { align-items: stretch; }
    .equal-col { display: flex; }
    .equal-fill { height:100%; width:100%; }
    .carousel-inner, .carousel-item { height:100%; }
    .carousel-item img { height:100%; object-fit:cover; }

    .star { width:18px; height:18px; color:#ffb400; }
    .link-plain, .link-plain:hover, .link-plain:focus { color:inherit; text-decoration:none; }

    .qty-wrap { max-width:220px; }
    .cta-wrap { margin-top:1.25rem; }
    .cta-wrap .btn-primary { width:100%; padding:.75rem 1rem; font-size:1.05rem; }

    .section-divider { margin: 2rem auto; max-width: var(--cm-container, 1140px); width: 100%; }
    .section-divider hr { margin: 0; }
    @media (min-width:1200px){ .section-divider { --cm-container: 1140px; } }

    .section1-row { --bs-gutter-x:3rem; --bs-gutter-y:1.5rem; }

    .info-dropdowns .dropdown { position: relative; }
    .info-dropdowns .dropdown-toggle { width: 100%; text-align: left; display:flex; justify-content:space-between; align-items:center; font-weight: 600; }
    .info-dropdowns .dropdown-menu { width: 100%; position: absolute; top: 100%; left: 0; margin-top: .25rem; border-radius: .75rem; border: 1px solid black; }
    .info-dropdowns .dropdown-menu .menu-text { font-size: .875rem; line-height: 1.3; white-space: normal; }
    .info-dropdowns .dropdown-menu .heading { font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; opacity: .6; margin-bottom: .25rem; }
    .info-dropdowns .dropdown + .dropdown { margin-top: .5rem; }
    @media (min-width: 992px){ .info-dropdowns { min-height: 100%; width: auto; } }

    @media (min-width: 992px) { .section-2-row { align-items: flex-start; } .carousel-lock { height: 420px; width: 533px; } }
    @media (max-width: 991.98px) { .carousel-lock { height: 420px; width: auto; } }
    @media (min-width: 1000px) { .carousel-lock { height: 420px; width: 443px; } }
    @media (min-width: 1200px) { .carousel-lock { height: 420px; width: 533px; } }
    .carousel-lock .carousel-inner, .carousel-lock .carousel-item { height: 100%; width: auto;}
    .carousel-lock .carousel-item img { height: 100%; object-fit: cover; }
    </style>
</head>

<body>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/secondary-header.php'); ?>

    <!-- Go Back line -->
    <div class="container mb-3">
        <a href="#" class="back-line rounded-2" onclick="history.back(); return false;">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            <span>Go Back</span>
        </a>
    </div>

    <!-- SECTION 1 -->
    <section class="container section">
        <div class="row section1-row align-items-start">
            <div class="col-12 col-lg-6">
                <div class="gallery-grid">
                    <figure class="hero m-0">
                        <img src="<?php echo $heroImg ? $heroImg : $defaultImg; ?>" alt="Main photo">
                    </figure>
                    <?php
                    if (!empty($photos)) {
                        for ($i = 1; $i < count($photos) && $i < 4; $i++) {
                            $u = $photos[$i]['url'] ? $photos[$i]['url'] : $defaultImg;
                            echo '<figure class="thumb m-0"><img src="'.$u.'" alt="Photo '.$i.'"></figure>';
                        }
                    } else {
                        // Fallback thumbs
                        echo '<figure class="thumb m-0"><img src="'.$defaultImg.'" alt="Thumb"></figure>';
                        echo '<figure class="thumb m-0"><img src="'.$defaultImg.'" alt="Thumb"></figure>';
                        echo '<figure class="thumb m-0"><img src="'.$defaultImg.'" alt="Thumb"></figure>';
                    }
                    ?>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h1 class="h3 fw-bold mb-2"><?php echo $pname; ?></h1>
                <div class="mb-2 small">
                    <span class="text-muted">Brand:
                        <?php if ($brand_id): ?>
                            <a href="/carriemart/main/products.php?brand=<?php echo $brand_id; ?>" class="link-plain"><?php echo $brand_name; ?></a>
                        <?php else: ?>
                            <span class="link-plain">—</span>
                        <?php endif; ?>
                    </span>
                    <span class="ms-3 d-inline-flex align-items-center fw-semibold">
                        <?php echo $rating_display; ?>
                        <svg class="star ms-1" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                            <path d="M3.612 15.443 4.6 9.97.825 6.765l5.059-.736L8 1.5l2.116 4.529 5.059.736L11.4 9.97l.988 5.473L8 12.897l-4.388 2.546z"/>
                        </svg>
                        <span class="ms-2 text-muted">(<?php echo $rating_count; ?>)</span>
                    </span>
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <span class="badge <?php echo $stockBadgeClass; ?> fw-normal">Stock: <?php echo (int)$stock_level; ?></span>
                </div>
                <div class="h4 mb-3"><?php echo peso($retail_price); ?></div>

                <form class="qty-wrap mb-2" method="post" action="/carriemart/user/cart/create.php">
                    <label class="form-label small mb-1">Quantity</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" onclick="const i=this.nextElementSibling;i.stepDown();">-</button>
                        <input type="number" class="form-control text-center" name="quantity" value="1" min="1" max="<?php echo $stock_level; ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="const i=this.previousElementSibling;i.stepUp();">+</button>
                    </div>
                    <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                    <div class="cta-wrap">
                        <button class="btn btn-primary" type="submit" <?php echo $stock_level <= 0 ? 'disabled' : ''; ?>>Add to Cart</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="section-divider"><hr></div>

    <!-- SECTION 2 -->
    <section class="container section">
        <div class="row g-4 section-2-row">
            <div class="col-12 col-lg-6">
                <div id="prodCarousel" class="carousel slide carousel-lock" data-bs-ride="false" data-bs-interval="false">
                    <div class="carousel-inner rounded-3 overflow-hidden">
                        <?php
                        $carouselImgs = !empty($photos) ? $photos : [['url' => $defaultImg, 'primary'=>1,'sort'=>0]];
                        foreach ($carouselImgs as $idx => $ph) {
                            $active = $idx === 0 ? 'active' : '';
                            $u = $ph['url'] ? $ph['url'] : $defaultImg;
                            echo '<div class="carousel-item '.$active.'">';
                            echo '<img src="'.$u.'" class="d-block w-100" alt="Slide '.($idx+1).'">';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#prodCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#prodCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="accordion accordion-flush w-100" id="productFlushAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingCat">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseCat" aria-expanded="false" aria-controls="flush-collapseCat">
                                Categories
                            </button>
                        </h2>
                        <div id="flush-collapseCat" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingCat">
                            <div class="accordion-body"><?php echo $cat_name ? $cat_name : '—'; ?></div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingBrand">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseBrand" aria-expanded="false" aria-controls="flush-collapseBrand">
                                Brand
                            </button>
                        </h2>
                        <div id="flush-collapseBrand" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingBrand">
                            <div class="accordion-body"><?php echo $brand_name ? $brand_name : '—'; ?></div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingModel">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseModel" aria-expanded="false" aria-controls="flush-collapseModel">
                                Model
                            </button>
                        </h2>
                        <div id="flush-collapseModel" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingModel">
                            <div class="accordion-body"><?php echo $pmodel ? $pmodel : '—'; ?></div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingDesc">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseDesc" aria-expanded="false" aria-controls="flush-collapseDesc">
                                Description
                            </button>
                        </h2>
                        <div id="flush-collapseDesc" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingDesc">
                            <div class="accordion-body"><?php echo $pdesc ? $pdesc : 'No description.'; ?></div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingSpec">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseSpec" aria-expanded="false" aria-controls="flush-collapseSpec">
                                Specifications
                            </button>
                        </h2>
                        <div id="flush-collapseSpec" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingSpec">
                            <div class="accordion-body"><?php echo $pspecs ? $pspecs : '—'; ?></div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingSupp">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseSupp" aria-expanded="false" aria-controls="flush-collapseSupp">
                                Supplier
                            </button>
                        </h2>
                        <div id="flush-collapseSupp" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingSupp">
                            <div class="accordion-body"><?php echo $supp_name ? $supp_name : '—'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"><hr></div>

    <!-- SECTION 3 -->
    <section class="container section rating-reviews" id="rating-reviews">
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <h3 class="h5 fw-bold mb-2">Rating</h3>
                <div class="d-flex align-items-center">
                    <span class="display-6 fw-bold"><?php echo $rating_display; ?></span>
                    <svg class="star ms-2" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M3.612 15.443 4.6 9.97.825 6.765l5.059-.736L8 1.5l2.116 4.529 5.059.736L11.4 9.97l.988 5.473L8 12.897l-4.388 2.546z"/>
                    </svg>
                </div>
                <div class="text-muted"><?php echo $rating_count; ?> reviews</div>
            </div>

            <!-- List + pagination -->
            <div class="col-12 col-lg-8">
                <div class="list-group mb-3">
                    <?php if (empty($reviews)): ?>
                        <div class="list-group-item py-4 text-muted text-center">No reviews yet.</div>
                    <?php else: foreach ($reviews as $rvw): ?>
                        <div class="list-group-item d-flex gap-3 py-3">
                            <img src="https://picsum.photos/seed/u<?php echo $rvw['review_id']; ?>/64/64" class="rounded-circle flex-shrink-0" width="32" height="32" alt="">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <h6 class="mb-1"><?php echo $rvw['review_title'] ? $rvw['review_title'] : 'Review'; ?></h6>
                                    <div class="small mb-1 text-muted"><?php echo $rvw['user']; ?> • <?php echo $rvw['created_at']; ?> • ★<?php echo $rvw['rating']; ?></div>
                                    <p class="mb-0 text-muted"><?php echo $rvw['review_text'] ? $rvw['review_text'] : ''; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <nav class="d-flex justify-content-end" aria-label="Reviews pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?php echo !$hasPrev ? 'disabled' : ''; ?>">
                            <?php if ($hasPrev): ?>
                                <a class="page-link" href="?id=<?php echo $pid; ?>&page=<?php echo $page-1; ?>#rating-reviews">Previous</a>
                            <?php else: ?>
                                <span class="page-link">Previous</span>
                            <?php endif; ?>
                        </li>
                        <li class="page-item <?php echo !$hasNext ? 'disabled' : ''; ?>">
                            <?php if ($hasNext): ?>
                                <a class="page-link" href="?id=<?php echo $pid; ?>&page=<?php echo $page+1; ?>#rating-reviews">Next</a>
                            <?php else: ?>
                                <span class="page-link">Next</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>

    <div class="section-divider"><hr></div>
     <?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>