<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$q           = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort        = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$active      = isset($_GET['active']) ? trim($_GET['active']) : '';
$createdFrom = isset($_GET['created_from']) ? trim($_GET['created_from']) : '';
$createdTo   = isset($_GET['created_to']) ? trim($_GET['created_to']) : '';
$nameLike    = isset($_GET['name']) ? trim($_GET['name']) : '';
$contactLike = isset($_GET['contact']) ? trim($_GET['contact']) : '';
$emailLike   = isset($_GET['email']) ? trim($_GET['email']) : '';
$addressLike = isset($_GET['address']) ? trim($_GET['address']) : '';

   
$sql   = "SELECT supplier_id, supplier_name, contact_person, contact_number, email, address, is_active, created_at FROM suppliers WHERE 1";
$types = '';
$params= [];

   
if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND (
        CAST(supplier_id AS CHAR) LIKE ?
        OR supplier_name LIKE ?
        OR contact_person LIKE ?
        OR contact_number LIKE ?
        OR email LIKE ?
    )";
    $types .= 'sssss';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}

   
if ($nameLike !== '') {
    $sql .= " AND supplier_name LIKE ?";
    $types .= 's';
    $params[] = '%'.$nameLike.'%';
}
if ($contactLike !== '') {
    $sql .= " AND contact_person LIKE ?";
    $types .= 's';
    $params[] = '%'.$contactLike.'%';
}
if ($emailLike !== '') {
    $sql .= " AND email LIKE ?";
    $types .= 's';
    $params[] = '%'.$emailLike.'%';
}
if ($addressLike !== '') {
    $sql .= " AND address LIKE ?";
    $types .= 's';
    $params[] = '%'.$addressLike.'%';
}

   
if ($active === '1') {
    $sql .= " AND is_active = 1";
} elseif ($active === '0') {
    $sql .= " AND is_active = 0";
}

   
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

   
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC, supplier_id ASC";
        break;
    case 'nameAZ':
        $sql .= " ORDER BY supplier_name ASC, created_at DESC";
        break;
    case 'nameZA':
        $sql .= " ORDER BY supplier_name DESC, created_at DESC";
        break;
    case 'active':
        $sql .= " ORDER BY is_active DESC, created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC, supplier_id DESC";
        break;
}

$suppliers = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->bind_result($sid, $sname, $cperson, $cnumber, $email, $addr, $activeFlag, $createdAt);
    while ($stmt->fetch()) {
        $suppliers[] = [
            'supplier_id'    => $sid,
            'supplier_name'  => $sname,
            'contact_person' => $cperson,
            'contact_number' => $cnumber,
            'email'          => $email,
            'address'        => $addr,
            'is_active'      => (int)$activeFlag,
            'created_at'     => $createdAt
        ];
    }
    $stmt->close();
}

function supBadgeClass($isActive) {
    return $isActive ? 'sup-active' : 'sup-inactive';
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Suppliers</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .sup-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .sup-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
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
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/truck.svg" alt="" width="22" height="22" class="mt-1">
            Suppliers
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
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search supplier ID / name / contact">
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
                            <option value="newest" <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="oldest" <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
                            <option value="nameAZ" <?php if($sort==='nameAZ') echo 'selected'; ?>>Name A–Z</option>
                            <option value="nameZA" <?php if($sort==='nameZA') echo 'selected'; ?>>Name Z–A</option>
                            <option value="active" <?php if($sort==='active') echo 'selected'; ?>>Active first</option>
                        </select>
                           
                        <input type="hidden" name="active" value="<?php echo $active; ?>">
                        <input type="hidden" name="created_from" value="<?php echo $createdFrom; ?>">
                        <input type="hidden" name="created_to" value="<?php echo $createdTo; ?>">
                        <input type="hidden" name="name" value="<?php echo $nameLike; ?>">
                        <input type="hidden" name="contact" value="<?php echo $contactLike; ?>">
                        <input type="hidden" name="email" value="<?php echo $emailLike; ?>">
                        <input type="hidden" name="address" value="<?php echo $addressLike; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($suppliers); ?> suppliers</small>
                    <a href="supplier-form.php" class="btn btn-primary btn-sm">Add Supplier</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Supplier ID</th>
                                <th>Name</th>
                                <th>Contact Person</th>
                                <th>Contact Number</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center" style="width:170px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No suppliers found.</td></tr>
                        <?php else: foreach ($suppliers as $s):
                            $badgeCls = supBadgeClass($s['is_active']);
                            $createdDisp = substr($s['created_at'], 0, 10);
                            $emailDisp = ($s['email'] !== '' && $s['email'] !== null) ? $s['email'] : '—';
                        ?>
                            <tr>
                                <td><?php echo $s['supplier_id']; ?></td>
                                <td><?php echo $s['supplier_name']; ?></td>
                                <td><?php echo $s['contact_person']; ?></td>
                                <td><?php echo $s['contact_number']; ?></td>
                                <td><?php echo $emailDisp; ?></td>
                                <td><span class="status-badge <?php echo $badgeCls; ?>"><?php echo $s['is_active'] ? 'active' : 'inactive'; ?></span></td>
                                <td><span class="small text-muted"><?php echo $createdDisp; ?></span></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo $s['supplier_id']; ?>" class="btn btn-outline-primary btn-sm my-1">View</a>
                                    <a href="supplier-form.php?id=<?php echo $s['supplier_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $s['supplier_id']; ?>" class="btn btn-outline-danger btn-sm mb-1"
                                       onclick="return confirm('Delete supplier #<?php echo $s['supplier_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

           
        <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Suppliers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
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
                        <input type="text" class="form-control" name="name" value="<?php echo $nameLike; ?>" placeholder="Supplier name">
                    </div>
                    <div>
                        <label class="form-label">Contact person contains</label>
                        <input type="text" class="form-control" name="contact" value="<?php echo $contactLike; ?>" placeholder="Contact person">
                    </div>
                    <div>
                        <label class="form-label">Email contains</label>
                        <input type="text" class="form-control" name="email" value="<?php echo $emailLike; ?>" placeholder="Email">
                    </div>
                    <div>
                        <label class="form-label">Address contains</label>
                        <input type="text" class="form-control" name="address" value="<?php echo $addressLike; ?>" placeholder="Address">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>