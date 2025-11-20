<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if (!$conn) { die('DB error'); }

   
$q            = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoryId   = isset($_GET['category_id']) ? trim($_GET['category_id']) : (isset($_GET['category']) ? trim($_GET['category']) : '');
$brandId      = isset($_GET['brand_id']) ? trim($_GET['brand_id']) : (isset($_GET['brand']) ? trim($_GET['brand']) : '');
$minPrice     = isset($_GET['min_price']) ? trim($_GET['min_price']) : '';
$maxPrice     = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
$minRating    = isset($_GET['min_rating']) ? trim($_GET['min_rating']) : '';
$sort         = isset($_GET['sort']) ? trim($_GET['sort']) : '';

$conditions = [];
$params = [];
$types  = '';

$conditions[] = 'p.is_active = 1';

if ($q !== '') {
    $conditions[] = '(p.product_name LIKE ? OR CAST(p.product_id AS CHAR) LIKE ?)';

    $like = '%'.$q.'%';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
}
if (ctype_digit($categoryId) && (int)$categoryId > 0) {
    $conditions[] = 'p.category_id = ?';
    $params[] = (int)$categoryId; $types .= 'i';
}
if (ctype_digit($brandId) && (int)$brandId > 0) {
    $conditions[] = 'p.brand_id = ?';
    $params[] = (int)$brandId; $types .= 'i';
}

$isNum = function($v){ return $v !== '' && is_numeric(str_replace([',','₱',' '],'',$v)); };
$cleanMoney = function($v){ return str_replace(['₱',',',' '],'',$v); };

if ($isNum($minPrice)) {
    $conditions[] = 'p.retail_price >= ?';
    $params[] = (float)$cleanMoney($minPrice); $types .= 'd';
}
if ($isNum($maxPrice)) {
    $conditions[] = 'p.retail_price <= ?';
    $params[] = (float)$cleanMoney($maxPrice); $types .= 'd';
}
if ($isNum($minRating)) {
    $conditions[] = '(COALESCE(r.avg_rating,0) >= ?)';

    $params[] = (float)$minRating; $types .= 'd';
}

$whereSql = '';
if (!empty($conditions)) {
    $whereSql = 'WHERE ' . implode(' AND ', $conditions);
}

$orderSql = 'ORDER BY p.created_at DESC';
switch ($sort) {
    case 'popular':
        $orderSql = 'ORDER BY COALESCE(s.total_sold,0) DESC, p.product_id DESC';
        break;
    case 'rating':
        $orderSql = 'ORDER BY COALESCE(r.avg_rating,0) DESC, COALESCE(r.rating_count,0) DESC';
        break;
    case 'priceLow':
        $orderSql = 'ORDER BY p.retail_price ASC, p.product_id ASC';
        break;
    case 'priceHigh':
        $orderSql = 'ORDER BY p.retail_price DESC, p.product_id DESC';
        break;
    case 'newest':
        $orderSql = 'ORDER BY p.created_at DESC, p.product_id DESC';
        break;
}

$sql = "
SELECT
  p.product_id,
  p.product_name,
  p.brand_id,
  p.category_id,
  p.retail_price,
  b.brand_name,
  COALESCE(ph.photo_url,'/carriemart/assets/default-product.png') AS photo_url,
  COALESCE(r.avg_rating,0) AS avg_rating,
  COALESCE(r.rating_count,0) AS rating_count,
  COALESCE(s.total_sold,0) AS total_sold
FROM products p
LEFT JOIN brands b ON b.brand_id = p.brand_id
LEFT JOIN (
    SELECT product_id, photo_url
    FROM product_photos
    WHERE is_primary = 1
    GROUP BY product_id
) ph ON ph.product_id = p.product_id
LEFT JOIN (
    SELECT po.product_id,
           AVG(pr.rating) AS avg_rating,
           COUNT(pr.review_id) AS rating_count
    FROM product_review pr
    JOIN product_order po ON po.product_order_id = pr.product_order_id
    WHERE pr.is_verified = 1
    GROUP BY po.product_id
) r ON r.product_id = p.product_id
LEFT JOIN (
    SELECT product_id, SUM(quantity) AS total_sold
    FROM product_order
    GROUP BY product_id
) s ON s.product_id = p.product_id
$whereSql
$orderSql
LIMIT 200
";

$products = [];
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Failed to prepare products query: ' . $conn->error);
    $products = [];
} else {
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->bind_result($pid, $pname, $p_brand_id, $p_category_id, $price, $brandName, $photoUrl, $avgRating, $ratingCount, $totalSold);
    while ($stmt->fetch()) {
        $products[] = [
            'product_id'   => $pid,
            'product_name' => $pname,
            'brand_id'     => $p_brand_id,
            'category_id'  => $p_category_id,
            'retail_price' => (float)$price,
            'brand_name'   => $brandName,
            'photo_url'    => $photoUrl ?: '/carriemart/assets/default-product.png',
            'avg_rating'   => (float)$avgRating,
            'rating_count' => (int)$ratingCount,
            'total_sold'   => (int)$totalSold
        ];
    }
    $stmt->close();
}

   
$categories = [];
$catStmt = $conn->prepare("SELECT category_id, category_name FROM categories WHERE is_active=1 ORDER BY category_name ASC");
if ($catStmt) {
    $catStmt->execute();
    $catStmt->bind_result($cid, $cname);
    while ($catStmt->fetch()) $categories[] = ['id'=>$cid,'name'=>$cname];
    $catStmt->close();
} else {
    error_log('Failed to prepare categories query: ' . $conn->error);
    $categories = [];
}
   
$brands = [];
$brandStmt = $conn->prepare("SELECT brand_id, brand_name FROM brands WHERE is_active=1 ORDER BY brand_name ASC");
if ($brandStmt) {
    $brandStmt->execute();
    $brandStmt->bind_result($bid, $bname);
    while ($brandStmt->fetch()) $brands[] = ['id'=>$bid,'name'=>$bname];
    $brandStmt->close();
} else {
    error_log('Failed to prepare brands query: ' . $conn->error);
    $brands = [];
}

$totalShown = count($products);
function fmtPrice($v){ return '₱' . number_format((float)$v, 2, '.', ','); }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="icon" href="/carriemart/assets/Logo.svg" type="image/svg+xml">
    <style>
           
        .back-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .75rem;
            border-bottom: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            text-decoration: none;
        }
        .back-line:hover {
            background-color: rgba(var(--bs-primary-rgb), .06);
            text-decoration: none;
        }
        .back-line .icon {
            width: 20px; height: 20px; opacity: .9;
        }

           
        .product-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(2, minmax(0, 1fr)); 
        }
        @media (min-width: 768px) { .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 992px) { .product-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

           
        .product-card {
            position: relative; 
            border: 1px solid transparent;
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            transition: border-color .2s ease, transform .2s ease, background-color .2s ease;
            cursor: pointer;    
        }
        .product-card:hover {
            border-color: rgba(0,0,0,.15);
            transform: translateY(-3px);
        }

        .brand-link,
        .brand-link:hover,
        .brand-link:focus,
        .brand-link:active {
            text-decoration: none;
            color: inherit;
        }

        .brand-link:hover{
            text-decoration: underline;
        }
           
        .product-card button,
        .product-card a.brand-link { position: relative; z-index: 2; }
        .product-card .stretched-link { z-index: 1; }    

        .product-img {
            width: 100%;
            height: 200px;
            aspect-ratio: 3 / 2; 
            object-fit: cover;
            border-radius: .9rem;
            display: block;
        }
        .price {
            font-size: 1.2rem;
            font-weight: 700;
        }
        .rating { display:inline-flex; align-items:center; }
        .rating .star { width:14px; height:14px; }
        .badge-sold {
            font-size: .65rem;
            background: #eef;
            color: #223;
            padding: .25rem .45rem;
            border-radius: .4rem;
        }
    </style>
</head>
<body>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/secondary-header.php'); ?>

       
    <div class="container mb-3">
        <a href="#" class="back-line rounded-2"
           onclick="history.back(); return false;">
            <svg class="icons" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            <span>Go Back</span>
        </a>
    </div>

       
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
                <form method="get" class="d-inline-block mb-0">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                    <input type="hidden" name="brand_id" value="<?php echo $brandId; ?>">
                    <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                    <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                    <input type="hidden" name="min_rating" value="<?php echo $minRating; ?>">
                    <select name="sort" class="form-select form-select-sm" style="width:180px;" onchange="this.form.submit()">
                        <option value="">Sort by</option>
                        <option value="popular"   <?php if($sort==='popular')   echo 'selected'; ?>>Most Popular</option>
                        <option value="rating"    <?php if($sort==='rating')    echo 'selected'; ?>>Highest Rated</option>
                        <option value="priceLow"  <?php if($sort==='priceLow')  echo 'selected'; ?>>Price: Low to High</option>
                        <option value="priceHigh" <?php if($sort==='priceHigh') echo 'selected'; ?>>Price: High to Low</option>
                        <option value="newest"    <?php if($sort==='newest')    echo 'selected'; ?>>Newest</option>
                    </select>
                </form>
            </div>
            <small class="text-muted ms-3">Showing <?php echo $totalShown; ?> products</small>
        </div>
    </div>

       
    <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form class="vstack gap-3" method="get">
                <input type="hidden" name="q" value="<?php echo $q; ?>">
                <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                <div>
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category_id">
                        <option value="">All</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php if($categoryId!=='' && (int)$categoryId===$c['id']) echo 'selected'; ?>><?php echo $c['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Brand</label>
                    <select class="form-select" name="brand_id">
                        <option value="">All</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?php echo $b['id']; ?>" <?php if($brandId!=='' && (int)$brandId===$b['id']) echo 'selected'; ?>><?php echo $b['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Price range</label>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control" name="min_price" value="<?php echo $minPrice; ?>" placeholder="Min">
                        <input type="text" class="form-control" name="max_price" value="<?php echo $maxPrice; ?>" placeholder="Max">
                    </div>
                </div>
                <div>
                    <label class="form-label">Minimum rating</label>
                    <select class="form-select" name="min_rating">
                        <option value="">Any</option>
                        <option value="4.5" <?php if($minRating==='4.5') echo 'selected'; ?>>4.5+</option>
                        <option value="4.0" <?php if($minRating==='4.0') echo 'selected'; ?>>4.0+</option>
                        <option value="3.5" <?php if($minRating==='3.5') echo 'selected'; ?>>3.5+</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

       
    <div class="container pb-5">
        <div class="product-grid">
            <?php if (empty($products)): ?>
                <div class="col-12 text-muted">No products found.</div>
            <?php else: foreach ($products as $p):
                $ratingText = $p['avg_rating'] > 0 ? number_format($p['avg_rating'],1) : '—';
                $brandDisp  = $p['brand_name'] !== null ? $p['brand_name'] : 'Unknown';
            ?>
            <div class="product-card">
                <img class="product-img" src="<?php echo $p['photo_url']; ?>" alt="<?php echo $p['product_name']; ?>">
                <h6 class="mt-2 mb-1"><?php echo $p['product_name']; ?></h6>
                <div class="d-flex align-items-center justify-content-between mb-2 small">
                    <span class="text-muted">Brand:
                        <a href="products.php?brand_id=<?php echo $p['brand_id']; ?>" class="brand-link"><?php echo $brandDisp; ?></a>
                    </span>
                    <span class="rating fw-semibold d-inline-flex align-items-center gap-1">
                        <span><?php echo $ratingText; ?></span>
                        <svg class="star" viewBox="0 0 16 16" fill="currentColor"><path d="M3.612 15.443 4.6 9.97.825 6.765l5.059-.736L8 1.5l2.116 4.529 5.059.736L11.4 9.97l.988 5.473L8 12.897l-4.388 2.546z"/></svg>
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="price"><?php echo fmtPrice($p['retail_price']); ?></span>
                    <?php if ($p['total_sold'] > 0): ?>
                        <span class="badge-sold">Sold <?php echo $p['total_sold']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-2">
                    <form method="post" action="/carriemart/user/cart/create.php" class="flex-grow-1">
                        <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                        <button class="btn btn-outline-secondary btn-sm w-100" type="submit">Add to Cart</button>
                    </form>
                </div>
                <a href="product-details.php?id=<?php echo $p['product_id']; ?>" class="stretched-link"></a>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

     <?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>
</html>