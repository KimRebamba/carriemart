<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// Inputs (GET)
$q           = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort        = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$active      = isset($_GET['active']) ? trim($_GET['active']) : '';
$createdFrom = isset($_GET['created_from']) ? trim($_GET['created_from']) : '';
$createdTo   = isset($_GET['created_to']) ? trim($_GET['created_to']) : '';
$nameLike    = isset($_GET['name']) ? trim($_GET['name']) : '';
$siteLike    = isset($_GET['website']) ? trim($_GET['website']) : '';

$sql = "SELECT brand_id, brand_name, website, is_active, created_at
        FROM brands
        WHERE 1";
$types = '';
$params = [];

// Free-text search (by ID, name, website)
if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND (CAST(brand_id AS CHAR) LIKE ? OR brand_name LIKE ? OR website LIKE ?)";
    $types .= 'sss';
    $params[] = $like; $params[] = $like; $params[] = $like;
}

// Name contains
if ($nameLike !== '') {
    $sql .= " AND brand_name LIKE ?";
    $types .= 's';
    $params[] = '%'.$nameLike.'%';
}

// Website contains
if ($siteLike !== '') {
    $sql .= " AND website LIKE ?";
    $types .= 's';
    $params[] = '%'.$siteLike.'%';
}

// Active filter
if ($active === '1') {
    $sql .= " AND is_active = 1";
} elseif ($active === '0') {
    $sql .= " AND is_active = 0";
}

// Created date range (YYYY-MM-DD)
$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
if ($createdFrom !== '' && $validDate($createdFrom)) {
    $sql .= " AND DATE(created_at) >= ?";
    $types .= 's';
    $params[] = $createdFrom;
}
if ($createdTo !== '' && $validDate($createdTo)) {
    $sql .= " AND DATE(created_at) <= ?";
    $types .= 's';
    $params[] = $createdTo;
}

// Sorting
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC, brand_id ASC";
        break;
    case 'nameAZ':
        $sql .= " ORDER BY brand_name ASC, created_at DESC";
        break;
    case 'nameZA':
        $sql .= " ORDER BY brand_name DESC, created_at DESC";
        break;
    case 'active':
        $sql .= " ORDER BY is_active DESC, created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC, brand_id DESC";
        break;
}

// Fetch brands
$brands = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') { $stmt->bind_param($types, ...$params); }
    $stmt->execute();
    $stmt->bind_result($bid, $bname, $bsite, $bactive, $bcreated);
    while ($stmt->fetch()) {
        $brands[] = [
            'brand_id' => $bid,
            'brand_name' => $bname,
            'website' => $bsite,
            'is_active' => (int)$bactive,
            'created_at' => $bcreated,
        ];
    }
    $stmt->close();
}

function badgeClass($isActive) {
    return $isActive ? 'brand-active' : 'brand-inactive';
}
function websiteHref($url) {
    if ($url === null || $url === '') return '';
    if (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0) return $url;
    return 'http://' . $url;
}
function websiteLabel($url) {
    if ($url === null || $url === '') return '—';
    $u = preg_replace('#^https?://#i', '', $url);
    $u = rtrim($u, '/');
    return $u;
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Brands</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .brand-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .brand-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        .logo-thumb { width:40px; height:40px; object-fit:cover; border-radius:.35rem; background:#f1f3f5; border:1px solid #dee2e6; }
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
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/tags.svg" alt="" width="22" height="22" class="mt-1">
            Brands
        </h3>

        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                        <div class="input-group input-group-sm" style="width:340px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search brand ID / name / website">
                        </div>
                        <button class="btn btn-outline-secondary btn-sm"
                                type="button" data-bs-toggle="offcanvas"
                                data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                            </svg>
                            Filters
                        </button>
                        <select class="form-select form-select-sm" name="sort" aria-label="Sort by" style="width:180px;" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="newest" <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="oldest" <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
                            <option value="nameAZ" <?php if($sort==='nameAZ') echo 'selected'; ?>>Name A–Z</option>
                            <option value="nameZA" <?php if($sort==='nameZA') echo 'selected'; ?>>Name Z–A</option>
                            <option value="active" <?php if($sort==='active') echo 'selected'; ?>>Active first</option>
                        </select>
                        <!-- preserve filters -->
                        <input type="hidden" name="active" value="<?php echo $active; ?>">
                        <input type="hidden" name="created_from" value="<?php echo $createdFrom; ?>">
                        <input type="hidden" name="created_to" value="<?php echo $createdTo; ?>">
                        <input type="hidden" name="name" value="<?php echo $nameLike; ?>">
                        <input type="hidden" name="website" value="<?php echo $siteLike; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($brands); ?> brands</small>
                    <a href="create.php" class="btn btn-primary btn-sm">Add Brand</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Brand ID</th>
                                <th>Name</th>
                                <th>Website</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" style="width:160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($brands)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No brands found.</td></tr>
                        <?php else: foreach ($brands as $b):
                            $statusCls = badgeClass($b['is_active']);
                            $href = websiteHref($b['website']);
                            $label = websiteLabel($b['website']);
                            $createdDisp = substr($b['created_at'], 0, 10);
                        ?>
                            <tr>
                                <td><?php echo $b['brand_id']; ?></td>
                                <td><?php echo $b['brand_name']; ?></td>
                                <td>
                                    <?php if ($href !== ''): ?>
                                        <a href="<?php echo $href; ?>" target="_blank" rel="noopener" class="small text-decoration-none"><?php echo $label; ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="status-badge <?php echo $statusCls; ?>"><?php echo $b['is_active'] ? 'active' : 'inactive'; ?></span></td>
                                <td><span class="small text-muted"><?php echo $createdDisp; ?></span></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo $b['brand_id']; ?>" class="btn btn-outline-primary btn-sm my-1">View</a>
                                    <a href="brand-form.php?id=<?php echo $b['brand_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $b['brand_id']; ?>" class="btn btn-outline-danger btn-sm mb-1"
                                       onclick="return confirm('Delete brand #<?php echo $b['brand_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Offcanvas: Filters (Brands) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Brands</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <div>
                        <label class="form-label">Active status</label>
                        <select class="form-select" name="active">
                            <option value="">Any</option>
                            <option value="1" <?php if($active==='1') echo 'selected'; ?>>active</option>
                            <option value="0" <?php if($active==='0') echo 'selected'; ?>>inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Created date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="created_from" value="<?php echo $createdFrom; ?>">
                            <input type="date" class="form-control" name="created_to" value="<?php echo $createdTo; ?>">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Name contains</label>
                        <input type="text" class="form-control" name="name" value="<?php echo $nameLike; ?>" placeholder="Keyword">
                    </div>
                    <div>
                        <label class="form-label">Website contains</label>
                        <input type="text" class="form-control" name="website" value="<?php echo $siteLike; ?>" placeholder="domain">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
</script>

</body>
</html>