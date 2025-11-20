<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$q            = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort         = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$typeFilter   = isset($_GET['type']) ? trim($_GET['type']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$dueFrom      = isset($_GET['due_from']) ? trim($_GET['due_from']) : '';
$dueTo        = isset($_GET['due_to']) ? trim($_GET['due_to']) : '';
$amountMin    = isset($_GET['amount_min']) ? trim($_GET['amount_min']) : '';
$amountMax    = isset($_GET['amount_max']) ? trim($_GET['amount_max']) : '';
$descContains = isset($_GET['desc']) ? trim($_GET['desc']) : '';

$validDate = function($d){ return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
$isNumber  = function($x){ return $x!=='' && is_numeric($x); };

$sql = "SELECT exp_id, expense_type, description, amount, status, due_date, paid_date, created_at
        FROM expenses
        WHERE 1";
$types = '';
$params = [];

   
if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND (CAST(exp_id AS CHAR) LIKE ? OR expense_type LIKE ? OR description LIKE ?)";
    $types .= 'sss';
    $params[] = $like; $params[] = $like; $params[] = $like;
}

   
if ($typeFilter !== '' && in_array($typeFilter, ['inventory_purchase','shipping','maintenance','rent','utilities','other'], true)) {
    $sql .= " AND expense_type = ?";
    $types .= 's';
    $params[] = $typeFilter;
}

   
if ($statusFilter !== '' && in_array($statusFilter, ['pending','paid'], true)) {
    $sql .= " AND status = ?";
    $types .= 's';
    $params[] = $statusFilter;
}

   
if ($dueFrom !== '' && $validDate($dueFrom)) {
    $sql .= " AND due_date >= ?";
    $types .= 's';
    $params[] = $dueFrom;
}
if ($dueTo !== '' && $validDate($dueTo)) {
    $sql .= " AND due_date <= ?";
    $types .= 's';
    $params[] = $dueTo;
}

   
if ($isNumber($amountMin)) {
    $sql .= " AND amount >= ?";
    $types .= 'd';
    $params[] = (float)$amountMin;
}
if ($isNumber($amountMax)) {
    $sql .= " AND amount <= ?";
    $types .= 'd';
    $params[] = (float)$amountMax;
}

   
if ($descContains !== '') {
    $sql .= " AND description LIKE ?";
    $types .= 's';
    $params[] = '%'.$descContains.'%';
}

   
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY created_at ASC, exp_id ASC";
        break;
    case 'amountHigh':
        $sql .= " ORDER BY amount DESC, created_at DESC";
        break;
    case 'amountLow':
        $sql .= " ORDER BY amount ASC, created_at ASC";
        break;
    case 'dueSoon':
          
        $sql .= " ORDER BY (due_date IS NULL) ASC, due_date ASC, created_at DESC";
        break;
    case 'statusPaid':
          
        $sql .= " ORDER BY (status='paid') DESC, created_at DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY created_at DESC, exp_id DESC";
        break;
}

$expenses = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    if ($types !== '') $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->bind_result($exp_id, $expense_type, $description, $amount, $status, $due_date, $paid_date, $created_at);
    while ($stmt->fetch()) {
        $expenses[] = [
            'exp_id' => $exp_id,
            'expense_type' => $expense_type,
            'description' => $description,
            'amount' => (float)$amount,
            'status' => $status,
            'due_date' => $due_date,
            'paid_date' => $paid_date,
            'created_at' => $created_at
        ];
    }
    $stmt->close();
}

$totalCount = count($expenses);
$totalAmountDisplayed = 0.0;
foreach ($expenses as $e) $totalAmountDisplayed += $e['amount'];

function expBadgeClass($status) {
    if ($status === 'paid') return 'exp-paid';
    return 'exp-pending';
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Expenses</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        .exp-pending { background:#fff3cd; color:#664d03; border:1px solid #ffe69c; }
        .exp-paid { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .amount-cell { font-variant-numeric: tabular-nums; }
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
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/receipt.svg" alt="" width="22" height="22" class="mt-1">
            Expenses
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
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search expense ID / type / description">
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
                            <option value="amountHigh" <?php if($sort==='amountHigh') echo 'selected'; ?>>Amount: High→Low</option>
                            <option value="amountLow" <?php if($sort==='amountLow') echo 'selected'; ?>>Amount: Low→High</option>
                            <option value="dueSoon" <?php if($sort==='dueSoon') echo 'selected'; ?>>Due soon</option>
                            <option value="statusPaid" <?php if($sort==='statusPaid') echo 'selected'; ?>>Paid first</option>
                        </select>
                           
                        <input type="hidden" name="type" value="<?php echo $typeFilter; ?>">
                        <input type="hidden" name="status" value="<?php echo $statusFilter; ?>">
                        <input type="hidden" name="due_from" value="<?php echo $dueFrom; ?>">
                        <input type="hidden" name="due_to" value="<?php echo $dueTo; ?>">
                        <input type="hidden" name="amount_min" value="<?php echo $amountMin; ?>">
                        <input type="hidden" name="amount_max" value="<?php echo $amountMax; ?>">
                        <input type="hidden" name="desc" value="<?php echo $descContains; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo $totalCount; ?> expenses (Total ₱<?php echo number_format($totalAmountDisplayed,2,'.',','); ?>)</small>
                    <a href="expense-form.php" class="btn btn-primary btn-sm">Add Expense</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Exp ID</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount (₱)</th>
                                <th>Status</th>
                                <th>Due</th>
                                <th>Paid</th>
                                <th class="text-center" style="width:160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($expenses)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No expenses found.</td></tr>
                        <?php else: foreach ($expenses as $ex):
                            $amountFmt = number_format($ex['amount'], 2, '.', ',');
                            $badgeCls  = expBadgeClass($ex['status']);
                            $dueDisp   = $ex['due_date'] ? $ex['due_date'] : '—';
                            $paidDisp  = $ex['paid_date'] ? $ex['paid_date'] : '—';
                            $descShort = $ex['description'] !== '' && $ex['description'] !== null ? $ex['description'] : '—';
                        ?>
                            <tr>
                                <td><?php echo $ex['exp_id']; ?></td>
                                <td><?php echo $ex['expense_type']; ?></td>
                                <td class="text-truncate" style="max-width:220px;"><?php echo $descShort; ?></td>
                                <td class="text-end amount-cell">₱<?php echo $amountFmt; ?></td>
                                <td><span class="status-badge <?php echo $badgeCls; ?>"><?php echo $ex['status']; ?></span></td>
                                <td><span class="small text-muted"><?php echo $dueDisp; ?></span></td>
                                <td><span class="small text-muted"><?php echo $paidDisp; ?></span></td>
                                <td class="text-center actions-cell">
                                    <a href="expense-form.php?id=<?php echo $ex['exp_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $ex['exp_id']; ?>" class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Delete expense #<?php echo $ex['exp_id']; ?>?');">Delete</a>
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
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Expenses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">
                    <div>
                        <label class="form-label">Expense type</label>
                        <select class="form-select" name="type">
                            <option value="">Any</option>
                            <?php
                            $typesList = ['inventory_purchase','shipping','maintenance','rent','utilities','other'];
                            foreach ($typesList as $t) {
                                echo '<option value="'.$t.'" '.($typeFilter===$t?'selected':'').'>'.$t.'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Any</option>
                            <option value="pending" <?php if($statusFilter==='pending') echo 'selected'; ?>>pending</option>
                            <option value="paid" <?php if($statusFilter==='paid') echo 'selected'; ?>>paid</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Due date range</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" name="due_from" value="<?php echo $dueFrom; ?>">
                            <input type="date" class="form-control" name="due_to" value="<?php echo $dueTo; ?>">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Amount range (₱)</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" name="amount_min" value="<?php echo $amountMin; ?>" placeholder="Min">
                            <input type="text" class="form-control" name="amount_max" value="<?php echo $amountMax; ?>" placeholder="Max">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Description contains</label>
                        <input type="text" class="form-control" name="desc" value="<?php echo $descContains; ?>" placeholder="Keyword">
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
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>