<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$accounts = [];

$search        = isset($_GET['search_url']) ? trim($_GET['search_url']) : '';
$roleFilter    = isset($_GET['role']) ? trim($_GET['role']) : '';
$statusFilter  = isset($_GET['status']) ? trim($_GET['status']) : '';
$joinedFrom    = isset($_GET['joined_from']) ? trim($_GET['joined_from']) : '';
$joinedTo      = isset($_GET['joined_to']) ? trim($_GET['joined_to']) : '';
$emailContains = isset($_GET['email_contains']) ? trim($_GET['email_contains']) : '';
$sort          = isset($_GET['sort']) ? trim($_GET['sort']) : '';

$sql = "SELECT user_id, username, first_name, last_name, email, role, is_active, profile_photo_url, created_at FROM accounts WHERE 1";
$types = '';
$n = 0;   

   
if ($search !== '') {
    $like = '%' . $search . '%';
    $sql .= " AND (username LIKE ? OR email LIKE ? OR CONCAT_WS(' ', first_name, last_name) LIKE ?)";
    $types .= 's'; $n++; ${"p$n"} = $like;
    $types .= 's'; $n++; ${"p$n"} = $like;
    $types .= 's'; $n++; ${"p$n"} = $like;
}

if ($roleFilter === 'admin' || $roleFilter === 'customer') {
    $sql .= " AND role = ?";
    $types .= 's'; $n++; ${"p$n"} = $roleFilter;
}

if ($statusFilter === 'active') {
    $sql .= " AND is_active = 1";
} elseif ($statusFilter === 'inactive') {
    $sql .= " AND is_active = 0";
}

if ($joinedFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $joinedFrom)) {
    $sql .= " AND DATE(created_at) >= ?";
    $types .= 's'; $n++; ${"p$n"} = $joinedFrom;
}
if ($joinedTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $joinedTo)) {
    $sql .= " AND DATE(created_at) <= ?";
    $types .= 's'; $n++; ${"p$n"} = $joinedTo;
}

if ($emailContains !== '') {
    $sql .= " AND email LIKE ?";
    $types .= 's'; $n++; ${"p$n"} = '%' . $emailContains . '%';
}
   
switch ($sort) {
    case 'nameAZ':
        $sql .= " ORDER BY first_name, last_name, username";
        break;
    case 'roleAZ':
        $sql .= " ORDER BY role, username";
        break;
    case 'recentActive':
        $sql .= " ORDER BY is_active DESC, created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC";
        break;
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($n > 0) {
        switch ($n) {
            case 1:  $stmt->bind_param($types, $p1); break;
            case 2:  $stmt->bind_param($types, $p1, $p2); break;
            case 3:  $stmt->bind_param($types, $p1, $p2, $p3); break;
            case 4:  $stmt->bind_param($types, $p1, $p2, $p3, $p4); break;
            case 5:  $stmt->bind_param($types, $p1, $p2, $p3, $p4, $p5); break;
            case 6:  $stmt->bind_param($types, $p1, $p2, $p3, $p4, $p5, $p6); break;
            case 7:  $stmt->bind_param($types, $p1, $p2, $p3, $p4, $p5, $p6, $p7); break;
            case 8:  $stmt->bind_param($types, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8); break;
        }
    }
    $stmt->execute();
    $stmt->bind_result($user_id, $username, $first_name, $last_name, $email, $role, $is_active, $profile_photo_url, $created_at);
    while ($stmt->fetch()) {
        $accounts[] = [
            'user_id' => $user_id,
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'role' => $role,
            'is_active' => (int)$is_active,
            'profile_photo_url' => $profile_photo_url,
            'created_at' => $created_at,
        ];
    }
    $stmt->close();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CM: Accounts</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .avatar { width:34px; height:34px; border-radius:50%; object-fit:cover; border:1px solid #dee2e6; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .status-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .status-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
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
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-people-fill mt-1" viewBox="0 0 16 16">
                <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1z"/>
                <path fill-rule="evenodd" d="M11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m-5.784 6A2.238 2.238 0 0 1 5 12c0-1.355.68-2.75 1.936-3.72C5.873 8.102 4.407 8 3 8 0 8 0 11 0 11s0 1 1 1z"/>
                <path d="M5 8a2 2 0 1 0-4 0 2 2 0 0 0 4 0"/>
            </svg>
            Accounts
        </h3>

        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <form method="GET" action="" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                    <div class="input-group input-group-sm" style="width:260px;">
                        <span class="input-group-text bg-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                        </span>
                        <input type="text" class="form-control" name="search_url" value="<?php echo ($search); ?>" placeholder="Search name or email">
                    </div>

                    <select class="form-select form-select-sm" name="sort" style="width:180px;" onchange="this.form.submit()">
                        <option value="">Sort by</option>
                        <option value="newest" <?php if($sort==='newest'||$sort==='') echo 'selected'; ?>>Newest</option>
                        <option value="nameAZ" <?php if($sort==='nameAZ') echo 'selected'; ?>>Name A–Z</option>
                        <option value="roleAZ" <?php if($sort==='roleAZ') echo 'selected'; ?>>Role</option>
                        <option value="recentActive" <?php if($sort==='recentActive') echo 'selected'; ?>>Recently Active</option>
                    </select>

                    <button class="btn btn-outline-secondary btn-sm" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                        Filters
                    </button>

                    <input type="hidden" name="role" value="<?php echo ($roleFilter); ?>">
                    <input type="hidden" name="status" value="<?php echo ($statusFilter); ?>">
                    <input type="hidden" name="joined_from" value="<?php echo ($joinedFrom); ?>">
                    <input type="hidden" name="joined_to" value="<?php echo ($joinedTo); ?>">
                    <input type="hidden" name="email_contains" value="<?php echo ($emailContains); ?>">
                </form>

                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($accounts); ?> users</small>
                    <a href="/carriemart/admin/accounts/account-form.php" class="btn btn-primary btn-sm">Add Account</a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Avatar</th>
                            <th>Name / Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Join Date</th>
                            <th class="text-center" style="width:180px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($accounts)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No accounts found.</td></tr>
                        <?php else: foreach ($accounts as $acc):
                            $fullName = trim(($acc['first_name'] ?? '') . ' ' . ($acc['last_name'] ?? ''));
                            if ($fullName === '') { $fullName = $acc['username']; }
                            $email = $acc['email'] ?? '';
                            $role = $acc['role'] ?? 'customer';
                            $isActive = (int)$acc['is_active'] === 1;
                            $avatar = $acc['profile_photo_url'] ?: 'https://via.placeholder.com/34x34.png?text=+';
                            $created = $acc['created_at'] ? date('Y-m-d H:i', strtotime($acc['created_at'])) : '';
                            $roleBadgeClasses = $role === 'admin'
                                ? 'badge bg-danger-subtle text-danger-emphasis border border-danger-subtle'
                                : 'badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle';
                        ?>
                            <tr>
                                <td><img src="<?php echo ($avatar); ?>" alt="<?php echo ($fullName); ?>" class="avatar"></td>
                                <td>
                                    <div class="fw-semibold"><?php echo  ($fullName); ?></div>
                                    <div class="text-muted small"><?php echo ($email); ?></div>
                                </td>
                                <td><span class="<?php echo $roleBadgeClasses; ?>"><?php echo ($role); ?></span></td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ($created); ?></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo (int)$acc['user_id']; ?>"
                                       class="btn btn-outline-primary btn-sm"
                                       data-id="<?php echo (int)$acc['user_id']; ?>">View</a>

                                    <a href="account-form.php?id=<?php echo (int)$acc['user_id']; ?>"
                                       class="btn btn-outline-secondary btn-sm"
                                       data-id="<?php echo (int)$acc['user_id']; ?>">Edit</a>

                                    <a href="delete.php?id=<?php echo (int)$acc['user_id']; ?>"
                                       class="btn btn-outline-danger btn-sm"
                                       data-id="<?php echo (int)$acc['user_id']; ?>"
                                       onclick="return confirm('Delete user #<?php echo (int)$acc['user_id']; ?>?');">Delete</a>
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
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET" action="">
                    <input type="hidden" name="search_url" value="<?php echo ($search); ?>">
                    <input type="hidden" name="sort" value="<?php echo ($sort); ?>">

                    <div>
                        <label class="form-label">Role</label>
                        <select class="form-select form-select-sm" name="role">
                            <option value="">Any</option>
                            <option value="admin" <?php if($roleFilter==='admin') echo 'selected'; ?>>Admin</option>
                            <option value="customer" <?php if($roleFilter==='customer') echo 'selected'; ?>>Customer</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">Any</option>
                            <option value="active" <?php if($statusFilter==='active') echo 'selected'; ?>>Active</option>
                            <option value="inactive" <?php if($statusFilter==='inactive') echo 'selected'; ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Joined date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control form-control-sm" name="joined_from" value="<?php echo ($joinedFrom); ?>">
                            <input type="date" class="form-control form-control-sm" name="joined_to" value="<?php echo ($joinedTo); ?>">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Email contains</label>
                        <input type="text" class="form-control form-control-sm" name="email_contains" value="<?php echo ($emailContains); ?>" placeholder="example.com">
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