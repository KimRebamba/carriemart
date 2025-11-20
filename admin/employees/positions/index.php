<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$q    = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';

   
$sql = "SELECT position_id, position_name, monthly_rate, created_at FROM positions WHERE 1";
$types = '';
$n = 0;

if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND position_name LIKE ?";
    $types .= 's'; $n++; ${"p$n"} = $like;
}

switch ($sort) {
    case 'nameAZ':
        $sql .= " ORDER BY position_name ASC, position_id ASC";
        break;
    case 'rateHigh':
        $sql .= " ORDER BY monthly_rate DESC, position_name ASC";
        break;
    case 'rateLow':
        $sql .= " ORDER BY monthly_rate ASC, position_name ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC, position_id DESC";
        break;
}

$positions = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($n > 0) {
        switch ($n) {
            case 1: $stmt->bind_param($types, $p1); break;
            case 2: $stmt->bind_param($types, $p1, $p2); break;
            case 3: $stmt->bind_param($types, $p1, $p2, $p3); break;
            case 4: $stmt->bind_param($types, $p1, $p2, $p3, $p4); break;
            case 5: $stmt->bind_param($types, $p1, $p2, $p3, $p4, $p5); break;
        }
    }
    $stmt->execute();
    $stmt->bind_result($position_id, $position_name, $monthly_rate, $created_at);
    while ($stmt->fetch()) {
        $positions[] = [
            'position_id' => $position_id,
            'position_name' => $position_name,
            'monthly_rate' => $monthly_rate,
            'created_at' => $created_at
        ];
    }
    $stmt->close();
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Positions</title>
    <?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php');
?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        @media (max-width: 992px){
            .table-responsive { font-size:.875rem; }
            .actions-cell .btn { font-size:.65rem; }
        }
    </style>
</head>

<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php'); ?>

    <div class="flex-grow-1 p-3">
        <div class="container-fluid">

            <h3 class="mb-3 d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="mt-1 bi bi-briefcase">
                    <path d="M6.5 0A1.5 1.5 0 0 0 5 1.5V3H1.5A1.5 1.5 0 0 0 0 4.5V6h16V4.5A1.5 1.5 0 0 0 14.5 3H11V1.5A1.5 1.5 0 0 0 9.5 0zM10 3H6V1.5a.5.5 0 0 1 .5-.5h3A.5.5 0 0 1 10 1.5z"/>
                    <path d="M0 7.5V13a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7.5H0m6 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3A.5.5 0 0 1 6 9.5"/>
                </svg>
                Positions
            </h3>

            <div class="card mb-4 table-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <form method="GET" action="" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                            <div class="input-group input-group-sm" style="width:260px;">
                                <span class="input-group-text bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search position name">
                            </div>
                            <select class="form-select form-select-sm" name="sort" style="width:180px;" onchange="this.form.submit()">
                                <option value="">Sort by</option>
                                <option value="newest"   <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                                <option value="nameAZ"   <?php if($sort==='nameAZ') echo 'selected'; ?>>Name A–Z</option>
                                <option value="rateHigh" <?php if($sort==='rateHigh') echo 'selected'; ?>>Rate: High to Low</option>
                                <option value="rateLow"  <?php if($sort==='rateLow') echo 'selected'; ?>>Rate: Low to High</option>
                            </select>
                        </form>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-muted">Showing <?php echo count($positions); ?> positions</small>
                        <a href="position-form.php" class="btn btn-primary btn-sm">Add Position</a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Position</th>
                                    <th class="text-end">Monthly Rate</th>
                                    <th>Created</th>
                                    <th class="text-center" style="width:180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($positions)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No positions found.</td>
                                </tr>
                            <?php else: foreach ($positions as $p): ?>
                                <tr>
                                    <td><?php echo $p['position_id']; ?></td>
                                    <td><?php echo $p['position_name']; ?></td>
                                    <td class="text-end">₱<?php echo number_format((float)$p['monthly_rate'], 2); ?></td>
                                    <td><?php echo $p['created_at']; ?></td>
                                    <td class="text-center actions-cell">
                                        <a href="position-form.php?id=<?php echo $p['position_id']; ?>" class="btn btn-outline-secondary btn-sm" data-id="<?php echo $p['position_id']; ?>">Edit</a>
                                        <a href="delete.php?id=<?php echo $p['position_id']; ?>" class="btn btn-outline-danger btn-sm" data-id="<?php echo $p['position_id']; ?>" onclick="return confirm('Delete position #<?php echo $p['position_id']; ?>?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
</body>
</html>