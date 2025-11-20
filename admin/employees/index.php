<?php
   
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$q           = isset($_GET['q']) ? trim($_GET['q']) : '';
$status      = isset($_GET['status']) ? trim($_GET['status']) : '';
$positionId  = isset($_GET['position_id']) ? (int)$_GET['position_id'] : 0;
$gender      = isset($_GET['gender']) ? trim($_GET['gender']) : '';
$hireFrom    = isset($_GET['hire_from']) ? trim($_GET['hire_from']) : '';
$hireTo      = isset($_GET['hire_to']) ? trim($_GET['hire_to']) : '';
$sort        = isset($_GET['sort']) ? trim($_GET['sort']) : '';

   
$positions = [];
$ps = $conn->prepare("SELECT position_id, position_name FROM positions ORDER BY position_name ASC");
if ($ps) {
    $ps->execute();
    $ps->bind_result($pid, $pname);
    while ($ps->fetch()) { $positions[] = ['id'=>$pid, 'name'=>$pname]; }
    $ps->close();
}

   
$sql = "SELECT e.emp_id, e.first_name, e.last_name, e.email, e.phone_number, e.employment_status,
               e.hire_date, e.created_at, e.gender, p.position_name
        FROM employees e
        LEFT JOIN positions p ON e.current_position_id = p.position_id
        WHERE 1";
$types = '';
$n = 0;

   
if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND (CONCAT_WS(' ', e.first_name, e.last_name) LIKE ? OR e.email LIKE ? OR e.phone_number LIKE ? OR p.position_name LIKE ?)";
    $types .= 's'; $n++; ${"p$n"} = $like;
    $types .= 's'; $n++; ${"p$n"} = $like;
    $types .= 's'; $n++; ${"p$n"} = $like;
    $types .= 's'; $n++; ${"p$n"} = $like;
}

   
if ($status === 'active' || $status === 'inactive' || $status === 'terminated' || $status === 'on_leave') {
    $sql .= " AND e.employment_status = ?";
    $types .= 's'; $n++; ${"p$n"} = $status;
}

   
if ($positionId > 0) {
    $sql .= " AND e.current_position_id = ?";
    $types .= 'i'; $n++; ${"p$n"} = $positionId;
}

   
if ($gender === 'male' || $gender === 'female' || $gender === 'other') {
    $sql .= " AND e.gender = ?";
    $types .= 's'; $n++; ${"p$n"} = $gender;
}

   
if ($hireFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hireFrom)) {
    $sql .= " AND e.hire_date >= ?";
    $types .= 's'; $n++; ${"p$n"} = $hireFrom;
}
if ($hireTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hireTo)) {
    $sql .= " AND e.hire_date <= ?";
    $types .= 's'; $n++; ${"p$n"} = $hireTo;
}

   
switch ($sort) {
    case 'hireDate':
        $sql .= " ORDER BY e.hire_date DESC, e.last_name ASC, e.first_name ASC";
        break;
    case 'status':
        $sql .= " ORDER BY e.employment_status ASC, e.last_name ASC, e.first_name ASC";
        break;
    case 'positionAZ':
        $sql .= " ORDER BY p.position_name ASC, e.last_name ASC, e.first_name ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY e.created_at DESC, e.emp_id DESC";
        break;
}

   
$employees = [];
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
            case 9:  $stmt->bind_param($types, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9); break;
            case 10: $stmt->bind_param($types, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $p10); break;
        }
    }
    $stmt->execute();
    $stmt->bind_result($emp_id, $first_name, $last_name, $email, $phone_number, $employment_status, $hire_date, $created_at, $genderRow, $position_name);
    while ($stmt->fetch()) {
        $employees[] = [
            'emp_id' => $emp_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone_number' => $phone_number,
            'employment_status' => $employment_status,
            'hire_date' => $hire_date,
            'created_at' => $created_at,
            'gender' => $genderRow,
            'position_name' => $position_name ?: '',
        ];
    }
    $stmt->close();
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Employee</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .status-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .status-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        .status-terminated { background:#ffe0e3; color:#6f1d22; border:1px solid #ffccd1; }
        .status-onleave { background:#fff3cd; color:#664d03; border:1px solid #ffecb5; }
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
            <img src="/carriemart/assets/briefcase.svg" alt="" width="22" height="22" class="mt-1">
            Employees
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
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search name or email">
                        </div>

                        <select class="form-select form-select-sm" name="sort" style="width:180px;" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="newest"   <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="hireDate" <?php if($sort==='hireDate') echo 'selected'; ?>>Hire Date</option>
                            <option value="status"   <?php if($sort==='status') echo 'selected'; ?>>Status</option>
                            <option value="positionAZ" <?php if($sort==='positionAZ') echo 'selected'; ?>>Position A–Z</option>
                        </select>

                        <button class="btn btn-outline-secondary btn-sm"
                                type="button" data-bs-toggle="offcanvas"
                                data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                            </svg>
                            Filters
                        </button>

                           
                        <input type="hidden" name="status" value="<?php echo $status; ?>">
                        <input type="hidden" name="position_id" value="<?php echo $positionId; ?>">
                        <input type="hidden" name="gender" value="<?php echo $gender; ?>">
                        <input type="hidden" name="hire_from" value="<?php echo $hireFrom; ?>">
                        <input type="hidden" name="hire_to" value="<?php echo $hireTo; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($employees); ?> employees</small>
                    <a href="employee-form.php" class="btn btn-primary btn-sm">Add Employee</a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Emp ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Hire Date</th>
                                <th class="text-center" style="width:180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No employees found.</td>
                            </tr>
                        <?php else: foreach ($employees as $emp):
                            $fullName = trim($emp['first_name'].' '.$emp['last_name']);
                            $pos = $emp['position_name'] !== '' ? $emp['position_name'] : '—';
                            $statusClass = $emp['employment_status']==='active' ? 'status-active'
                                            : ($emp['employment_status']==='inactive' ? 'status-inactive'
                                            : ($emp['employment_status']==='terminated' ? 'status-terminated' : 'status-onleave'));
                        ?>
                            <tr>
                                <td><?php echo $emp['emp_id']; ?></td>
                                <td><?php echo $fullName; ?></td>
                                <td><?php echo $emp['email']; ?></td>
                                <td><?php echo $emp['phone_number']; ?></td>
                                <td><?php echo $pos; ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $emp['employment_status']; ?></span></td>
                                <td><?php echo $emp['hire_date']; ?></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo $emp['emp_id']; ?>" class="btn btn-outline-primary btn-sm" data-id="<?php echo $emp['emp_id']; ?>">View</a>
                                    <a href="employee-form.php?id=<?php echo $emp['emp_id']; ?>" class="btn btn-outline-secondary btn-sm" data-id="<?php echo $emp['emp_id']; ?>">Edit</a>
                                    <a href="delete.php?id=<?php echo $emp['emp_id']; ?>" class="btn btn-outline-danger btn-sm" data-id="<?php echo $emp['emp_id']; ?>" onclick="return confirm('Delete employee #<?php echo $emp['emp_id']; ?>?');">Delete</a>
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
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET" action="">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">

                    <div>
                        <label class="form-label">Employment status</label>
                        <select class="form-select" name="status">
                            <option value="">Any</option>
                            <option value="active"     <?php if($status==='active') echo 'selected'; ?>>Active</option>
                            <option value="inactive"   <?php if($status==='inactive') echo 'selected'; ?>>Inactive</option>
                            <option value="terminated" <?php if($status==='terminated') echo 'selected'; ?>>Terminated</option>
                            <option value="on_leave"   <?php if($status==='on_leave') echo 'selected'; ?>>On leave</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Position</label>
                        <select class="form-select" name="position_id">
                            <option value="0">Any</option>
                            <?php foreach ($positions as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php if($positionId===$p['id']) echo 'selected'; ?>>
                                    <?php echo $p['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="">Any</option>
                            <option value="male" <?php if($gender==='male') echo 'selected'; ?>>Male</option>
                            <option value="female" <?php if($gender==='female') echo 'selected'; ?>>Female</option>
                            <option value="other" <?php if($gender==='other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Hire date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="hire_from" value="<?php echo $hireFrom; ?>">
                            <input type="date" class="form-control" name="hire_to" value="<?php echo $hireTo; ?>">
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