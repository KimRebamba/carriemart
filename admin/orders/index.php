<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// Inputs (GET)
$q              = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort           = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$paymentStatus  = isset($_GET['payment_status']) ? trim($_GET['payment_status']) : '';
$orderStatus    = isset($_GET['order_status']) ? trim($_GET['order_status']) : '';
$paymentOption  = isset($_GET['payment_option']) ? trim($_GET['payment_option']) : '';
$dateFrom       = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo         = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// Build query
$sql = "SELECT o.order_id, o.user_id, o.voucher_code, o.payment_status, o.order_status,
               o.payment_option, o.date_ordered, o.delivery_fee, o.percent_sale,
               a.username, a.first_name, a.last_name, a.email,
               COALESCE(SUM(po.quantity * po.unit_price), 0) AS subtotal
        FROM orders o
        LEFT JOIN accounts a ON o.user_id = a.user_id
        LEFT JOIN product_order po ON po.order_id = o.order_id
        WHERE 1";
$types = '';
$params = [];

// Search (order ID, user ID, voucher, username, email, full name)
if ($q !== '') {
    $like = '%'.$q.'%';
    $sql .= " AND (CAST(o.order_id AS CHAR) LIKE ?
                  OR CAST(o.user_id AS CHAR) LIKE ?
                  OR o.voucher_code LIKE ?
                  OR a.username LIKE ?
                  OR a.email LIKE ?
                  OR CONCAT_WS(' ', a.first_name, a.last_name) LIKE ?)";

    $types .= 'ssssss';
    array_push($params, $like, $like, $like, $like, $like, $like);
}

// Filters
if (in_array($paymentStatus, ['pending','paid','refunded'], true)) {
    $sql .= " AND o.payment_status = ?";
    $types .= 's';
    $params[] = $paymentStatus;
}
if (in_array($orderStatus, ['pending','processing','shipped','completed','cancelled','requested_refund','returned'], true)) {
    $sql .= " AND o.order_status = ?";
    $types .= 's';
    $params[] = $orderStatus;
}
if ($paymentOption !== '') {
    $sql .= " AND o.payment_option = ?";
    $types .= 's';
    $params[] = $paymentOption;
}
// Date range on date_ordered (YYYY-MM-DD)
if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $sql .= " AND DATE(o.date_ordered) >= ?";
    $types .= 's';
    $params[] = $dateFrom;
}
if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $sql .= " AND DATE(o.date_ordered) <= ?";
    $types .= 's';
    $params[] = $dateTo;
}

// Add GROUP BY after WHERE clause
$sql .= " GROUP BY o.order_id";

// Sorting
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY o.date_ordered ASC, o.order_id ASC";
        break;
    case 'paymentStatus':
        $sql .= " ORDER BY o.payment_status ASC, o.date_ordered DESC";
        break;
    case 'orderStatus':
        $sql .= " ORDER BY o.order_status ASC, o.date_ordered DESC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY o.date_ordered DESC, o.order_id DESC";
        break;
}

// Fetch
$orders = [];
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('Failed to prepare orders query: ' . $conn->error);
    $orders = [];
} else {
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $stmt->bind_result($order_id, $user_id, $voucher_code, $payment_status, $order_status, $payment_option, $date_ordered, $delivery_fee, $percent_sale, $username, $first_name, $last_name, $email, $subtotal);
    while ($stmt->fetch()) {
        $discount = ((int)$percent_sale > 0) ? ($subtotal * ((int)$percent_sale / 100.0)) : 0.0;
        $total = $subtotal - $discount + (float)$delivery_fee;
        $orders[] = [
            'order_id' => $order_id,
            'user_id' => $user_id,
            'voucher_code' => $voucher_code,
            'payment_status' => $payment_status,
            'order_status' => $order_status,
            'payment_option' => $payment_option,
            'date_ordered' => $date_ordered,
            'delivery_fee' => $delivery_fee,
            'percent_sale' => (int)$percent_sale,
            'subtotal' => (float)$subtotal,
            'discount' => (float)$discount,
            'total' => (float)$total,
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        ];
    }
    $stmt->close();
    }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Orders</title>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php'); ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
        .table thead th { white-space:nowrap; }
        .actions-cell .btn { padding:.25rem .55rem; }
        .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
        /* Payment */
        .pay-pending { background:#fff3cd; color:#664d03; border:1px solid #ffecb5; }
        .pay-paid { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .pay-refunded { background:#cfe2ff; color:#084298; border:1px solid #b6d4fe; }
        /* Order */
        .ord-pending { background:#f8f9fa; color:#495057; border:1px solid #dee2e6; }
        .ord-processing { background:#e2e3ff; color:#343a40; border:1px solid #d1d5ff; }
        .ord-shipped { background:#d7ecff; color:#084298; border:1px solid #c2e0ff; }
        .ord-completed { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
        .ord-cancelled { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
        .ord-returned { background:#ffe0e3; color:#6f1d22; border:1px solid #ffccd1; }
        .ord-requested_refund { background:#ffe8cc; color:#664d03; border:1px solid #ffd8a8; }
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
            <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/cart3.svg" alt="" width="22" height="22" class="mt-1">
            Orders
        </h3>

        <div class="card mb-4 table-card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                        <div class="input-group input-group-sm" style="width:300px;">
                            <span class="input-group-text bg-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                </svg>
                            </span>
                            <input type="text" class="form-control" name="q" value="<?php echo $q; ?>" placeholder="Search order ID / voucher / user">
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
                            <option value="newest"  <?php if($sort===''||$sort==='newest') echo 'selected'; ?>>Newest</option>
                            <option value="oldest"  <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
                            <option value="paymentStatus" <?php if($sort==='paymentStatus') echo 'selected'; ?>>Payment Status</option>
                            <option value="orderStatus"   <?php if($sort==='orderStatus') echo 'selected'; ?>>Order Status</option>
                        </select>

                        <!-- preserve filters on sort/search -->
                        <input type="hidden" name="payment_status" value="<?php echo $paymentStatus; ?>">
                        <input type="hidden" name="order_status" value="<?php echo $orderStatus; ?>">
                        <input type="hidden" name="payment_option" value="<?php echo $paymentOption; ?>">
                        <input type="hidden" name="date_from" value="<?php echo $dateFrom; ?>">
                        <input type="hidden" name="date_to" value="<?php echo $dateTo; ?>">
                    </form>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <small class="text-muted">Showing <?php echo count($orders); ?> orders</small>
                    
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>User ID</th>
                                <th>Voucher</th>
                                <th>Payment</th>
                                <th>Order Status</th>
                                <th>Payment Option</th>
                                <th>Date Ordered</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Delivery Fee</th>
                                <th class="text-end">Total</th>
                                <th class="text-center" style="width:160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No orders found.</td>
                            </tr>
                        <?php else: foreach ($orders as $o):
                            $payClass = 'pay-' . $o['payment_status'];
                            $ordClass = 'ord-' . str_replace(' ', '_', $o['order_status']);
                            $voucher = ($o['voucher_code'] !== null && $o['voucher_code'] !== '') ? $o['voucher_code'] : '—';
                            $opt = ($o['payment_option'] !== null && $o['payment_option'] !== '') ? $o['payment_option'] : '—';
                            $dt = $o['date_ordered'] ? date('Y-m-d H:i', strtotime($o['date_ordered'])) : '';
                        ?>
                            <tr>
                                <td><?php echo $o['order_id']; ?></td>
                                <td><?php echo $o['user_id'] !== null ? $o['user_id'] : '—'; ?></td>
                                <td><?php echo $voucher; ?></td>
                                <td><span class="status-badge <?php echo $payClass; ?>"><?php echo $o['payment_status']; ?></span></td>
                                <td><span class="status-badge <?php echo $ordClass; ?>"><?php echo $o['order_status']; ?></span></td>
                                <td><?php echo $opt; ?></td>
                                <td><?php echo $dt; ?></td>
                                <td class="text-end">₱<?php echo number_format((float)$o['subtotal'], 2); ?></td>
                                <td class="text-end">₱<?php echo number_format((float)$o['delivery_fee'], 2); ?></td>
                                <td class="text-end"><strong>₱<?php echo number_format((float)$o['total'], 2); ?></strong></td>
                                <td class="text-center actions-cell">
                                    <a href="view.php?id=<?php echo $o['order_id']; ?>" class="btn btn-outline-primary btn-sm my-1">View</a>
                                    <a href="order-form.php?id=<?php echo $o['order_id']; ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $o['order_id']; ?>" class="btn btn-outline-danger btn-sm mb-1" onclick="return confirm('Delete order #<?php echo $o['order_id']; ?>?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Offcanvas: Filters (Orders) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="vstack gap-3" method="GET" action="">
                    <input type="hidden" name="q" value="<?php echo $q; ?>">
                    <input type="hidden" name="sort" value="<?php echo $sort; ?>">

                    <div>
                        <label class="form-label">Payment status</label>
                        <select class="form-select" name="payment_status">
                            <option value="">Any</option>
                            <option value="pending"  <?php if($paymentStatus==='pending') echo 'selected'; ?>>pending</option>
                            <option value="paid"     <?php if($paymentStatus==='paid') echo 'selected'; ?>>paid</option>
                            <option value="refunded" <?php if($paymentStatus==='refunded') echo 'selected'; ?>>refunded</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Order status</label>
                        <select class="form-select" name="order_status">
                            <option value="">Any</option>
                            <option value="pending"           <?php if($orderStatus==='pending') echo 'selected'; ?>>pending</option>
                            <option value="processing"        <?php if($orderStatus==='processing') echo 'selected'; ?>>processing</option>
                            <option value="shipped"           <?php if($orderStatus==='shipped') echo 'selected'; ?>>shipped</option>
                            <option value="completed"         <?php if($orderStatus==='completed') echo 'selected'; ?>>completed</option>
                            <option value="cancelled"         <?php if($orderStatus==='cancelled') echo 'selected'; ?>>cancelled</option>
                            <option value="requested_refund"  <?php if($orderStatus==='requested_refund') echo 'selected'; ?>>requested_refund</option>
                            <option value="returned"          <?php if($orderStatus==='returned') echo 'selected'; ?>>returned</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Payment option</label>
                        <select class="form-select" name="payment_option">
                            <option value="">Any</option>
                            <option value="Credit Card" <?php if($paymentOption==='Credit Card') echo 'selected'; ?>>Credit Card</option>
                            <option value="COD"         <?php if($paymentOption==='COD') echo 'selected'; ?>>COD</option>
                            <option value="Bank Transfer" <?php if($paymentOption==='Bank Transfer') echo 'selected'; ?>>Bank Transfer</option>
                            <option value="e-Wallet"    <?php if($paymentOption==='e-Wallet') echo 'selected'; ?>>e-Wallet</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Date ordered range</label>
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