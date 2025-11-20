<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$totalSalesAmount = 0.00;
$totalOrdersPaid  = 0;
$totalItemsSold   = 0;
$totalRefunds     = 0.00;
$returnCount      = 0;
$netRevenue       = 0.00;

   
$qsales = "
    SELECT 
        COALESCE(SUM(po.unit_price * po.quantity),0) AS sales_amount,
        COALESCE(SUM(po.quantity),0) AS items_sold,
        COUNT(DISTINCT o.order_id) AS paid_orders
    FROM orders o
    JOIN product_order po ON po.order_id = o.order_id
    WHERE o.payment_status = 'paid'
";
$rs = $conn->query($qsales);
if ($rs && $row = $rs->fetch_assoc()) {
    $totalSalesAmount = (float)$row['sales_amount'];
    $totalItemsSold   = (int)$row['items_sold'];
    $totalOrdersPaid  = (int)$row['paid_orders'];
}
if ($rs) $rs->close();

   
$discountTotal = 0.00;
$qdisc = "
    SELECT o.order_id, o.percent_sale, COALESCE(SUM(po.unit_price * po.quantity),0) AS order_total
    FROM orders o
    JOIN product_order po ON po.order_id = o.order_id
    WHERE o.payment_status = 'paid' AND o.percent_sale > 0
    GROUP BY o.order_id
";
$rd = $conn->query($qdisc);
if ($rd) {
    while ($drow = $rd->fetch_assoc()) {
        $pct = (int)$drow['percent_sale'];
        $orderTotal = (float)$drow['order_total'];
        if ($pct > 0) {
            $discountTotal += ($orderTotal * $pct / 100.0);
        }
    }
    $rd->close();
}

   
$qrefund = "
    SELECT 
        COALESCE(SUM(refund_amount),0) AS refunds_sum,
        COUNT(*) AS returns_cnt
    FROM order_return
    WHERE return_status IN ('approved','processed')
";
$rrf = $conn->query($qrefund);
if ($rrf && $rrow = $rrf->fetch_assoc()) {
    $totalRefunds = (float)$rrow['refunds_sum'];
    $returnCount  = (int)$rrow['returns_cnt'];
}
if ($rrf) $rrf->close();

   
$netRevenue = $totalSalesAmount - $discountTotal - $totalRefunds;

   
$bestItems = [];
$qbest = "
    SELECT 
        p.product_id,
        p.product_name,
        COALESCE(SUM(po.quantity),0) AS qty_sold,
        COALESCE(SUM(po.unit_price * po.quantity),0) AS revenue
    FROM products p
    JOIN product_order po ON po.product_id = p.product_id
    JOIN orders o ON o.order_id = po.order_id
    WHERE o.payment_status = 'paid'
    GROUP BY p.product_id
    ORDER BY qty_sold DESC, revenue DESC
    LIMIT 5
";
$rb = $conn->query($qbest);
if ($rb) {
    while ($b = $rb->fetch_assoc()) {
        $bestItems[] = [
            'product_id' => $b['product_id'],
            'product_name' => $b['product_name'],
            'qty_sold' => (int)$b['qty_sold'],
            'revenue' => (float)$b['revenue']
        ];
    }
    $rb->close();
}

   
$worstItems = [];
$qworst = "
    SELECT 
        p.product_id,
        p.product_name,
        COALESCE(SUM(po.quantity),0) AS qty_sold,
        COALESCE(SUM(po.unit_price * po.quantity),0) AS revenue
    FROM products p
    JOIN product_order po ON po.product_id = p.product_id
    JOIN orders o ON o.order_id = po.order_id
    WHERE o.payment_status = 'paid'
    GROUP BY p.product_id
    HAVING qty_sold > 0
    ORDER BY qty_sold ASC, revenue ASC
    LIMIT 5
";
$rw = $conn->query($qworst);
if ($rw) {
    while ($w = $rw->fetch_assoc()) {
        $worstItems[] = [
            'product_id' => $w['product_id'],
            'product_name' => $w['product_name'],
            'qty_sold' => (int)$w['qty_sold'],
            'revenue' => (float)$w['revenue']
        ];
    }
    $rw->close();
}

   
$fmtAmount = function($v){ return '₱' . number_format((float)$v, 2, '.', ','); };
$fmtQty    = function($v){ return (int)$v; };
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Sales</title>
<?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php');
    ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">

    <style>
    .quick-actions.card { }
    .quick-actions .card-header { padding:.5rem .75rem; }
    .quick-actions .card-body { padding:.75rem .75rem; }
    .quick-actions .btn { padding:.4rem .75rem; }
    .amount-cell { font-variant-numeric: tabular-nums; }
    .table thead th { white-space: nowrap; }
    </style>
</head>

<body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php');
?>


        <div class="flex-grow-1 p-3">    
            <div class="container-fluid">
                <h3 class="mb-4 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-graph-up mt-1" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M0 0h1v15h15v1H0z"/>
                      <path d="M10.933 4.358a.5.5 0 0 1 .709.026l2.5 2.75a.5.5 0 1 1-.736.676L11.5 5.683 8.651 8.95a.5.5 0 0 1-.692.04L5.354 6.879 2.854 9.379a.5.5 0 1 1-.708-.708l2.85-2.85a.5.5 0 0 1 .692-.04l2.605 2.111 2.64-2.534z"/>
                    </svg>
                    Sales
                </h3>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Sales Amount</small>
                                <h4 class="mt-2 mb-0 amount-cell" id="metric-total-sales"><?php echo $fmtAmount($totalSalesAmount); ?></h4>
                                <small class="text-success" id="metric-total-sales-change">+0%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Orders</small>
                                <h4 class="mt-2 mb-0" id="metric-total-orders"><?php echo $totalOrdersPaid; ?></h4>
                                <small class="text-muted">paid orders</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Items Sold</small>
                                <h4 class="mt-2 mb-0" id="metric-items-sold"><?php echo $totalItemsSold; ?></h4>
                                <small class="text-muted">sum of quantities</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Refunds / Returns</small>
                                <h4 class="mt-2 mb-0 amount-cell" id="metric-refunds"><?php echo $fmtAmount($totalRefunds); ?></h4>
                                <small class="text-danger" id="metric-returns-count"><?php echo $returnCount; ?> returns</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Net Revenue</small>
                                <h4 class="mt-2 mb-0 amount-cell" id="metric-net-revenue"><?php echo $fmtAmount($netRevenue); ?></h4>
                                <small class="text-muted">(Sales − Discounts − Refunds)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <strong>Best-selling items</strong>
                                <small class="text-muted">top 5 by qty</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:70px;">Prod ID</th>
                                                <th>Product</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Revenue (₱)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-best-selling">
                                        <?php if (empty($bestItems)): ?>
                                            <tr><td colspan="4" class="text-center text-muted py-3">No sales data.</td></tr>
                                        <?php else: foreach ($bestItems as $it): ?>
                                            <tr>
                                                <td><?php echo $it['product_id']; ?></td>
                                                <td><?php echo $it['product_name']; ?></td>
                                                <td class="text-end"><?php echo $fmtQty($it['qty_sold']); ?></td>
                                                <td class="text-end amount-cell"><?php echo $fmtAmount($it['revenue']); ?></td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted">Includes paid orders only.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <strong>Worst-selling items</strong>
                                <small class="text-muted">bottom 5 by qty</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:70px;">Prod ID</th>
                                                <th>Product</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Revenue (₱)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-worst-selling">
                                        <?php if (empty($worstItems)): ?>
                                            <tr><td colspan="4" class="text-center text-muted py-3">No low-selling items found.</td></tr>
                                        <?php else: foreach ($worstItems as $it): ?>
                                            <tr>
                                                <td><?php echo $it['product_id']; ?></td>
                                                <td><?php echo $it['product_name']; ?></td>
                                                <td class="text-end"><?php echo $fmtQty($it['qty_sold']); ?></td>
                                                <td class="text-end amount-cell"><?php echo $fmtAmount($it['revenue']); ?></td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted">Excludes items with zero sales.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>