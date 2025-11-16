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
    </style>
</head>

<body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php');
?>


        <div class="flex-grow-1 p-3"> <!-- other column -->
            <div class="container-fluid">
                <h3 class="mb-4 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-graph-up mt-1" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M0 0h1v15h15v1H0z"/>
                      <path d="M10.933 4.358a.5.5 0 0 1 .709.026l2.5 2.75a.5.5 0 1 1-.736.676L11.5 5.683 8.651 8.95a.5.5 0 0 1-.692.04L5.354 6.879 2.854 9.379a.5.5 0 1 1-.708-.708l2.85-2.85a.5.5 0 0 1 .692-.04l2.605 2.111 2.64-2.534z"/>
                    </svg>
                    Sales
                </h3>

                <style>
                    .amount-cell { font-variant-numeric: tabular-nums; }
                    .table thead th { white-space: nowrap; }
                </style>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Sales Amount</small>
                                <h4 class="mt-2 mb-0 amount-cell" id="metric-total-sales">₱0.00</h4>
                                <small class="text-success" id="metric-total-sales-change">+0%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Orders</small>
                                <h4 class="mt-2 mb-0" id="metric-total-orders">0</h4>
                                <small class="text-muted">paid orders</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Items Sold</small>
                                <h4 class="mt-2 mb-0" id="metric-items-sold">0</h4>
                                <small class="text-muted">sum of quantities</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Refunds / Returns</small>
                                <h4 class="mt-2 mb-0 amount-cell" id="metric-refunds">₱0.00</h4>
                                <small class="text-danger" id="metric-returns-count">0 returns</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Net Revenue</small>
                                <h4 class="mt-2 mb-0 amount-cell" id="metric-net-revenue">₱0.00</h4>
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
                                            <!-- Example rows; replace with server data -->
                                            <tr>
                                                <td>2001</td>
                                                <td>Alpha Smartphone X</td>
                                                <td class="text-end">320</td>
                                                <td class="text-end amount-cell">₱7,999,680.00</td>
                                            </tr>
                                            <tr>
                                                <td>2002</td>
                                                <td>Omega Laptop Pro</td>
                                                <td class="text-end">140</td>
                                                <td class="text-end amount-cell">₱7,630,000.00</td>
                                            </tr>
                                            <tr>
                                                <td>2005</td>
                                                <td>Gamma Smartwatch S</td>
                                                <td class="text-end">480</td>
                                                <td class="text-end amount-cell">₱2,880,000.00</td>
                                            </tr>
                                            <tr>
                                                <td>2010</td>
                                                <td>Delta Headphones Lite</td>
                                                <td class="text-end">520</td>
                                                <td class="text-end amount-cell">₱675,480.00</td>
                                            </tr>
                                            <tr>
                                                <td>2015</td>
                                                <td>Echo Bluetooth Speaker</td>
                                                <td class="text-end">410</td>
                                                <td class="text-end amount-cell">₱533,000.00</td>
                                            </tr>
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
                                            <!-- Example rows; replace with server data -->
                                            <tr>
                                                <td>2099</td>
                                                <td>Zeta VR Headset</td>
                                                <td class="text-end">3</td>
                                                <td class="text-end amount-cell">₱45,000.00</td>
                                            </tr>
                                            <tr>
                                                <td>2098</td>
                                                <td>Theta Smart Home Hub</td>
                                                <td class="text-end">4</td>
                                                <td class="text-end amount-cell">₱12,000.00</td>
                                            </tr>
                                            <tr>
                                                <td>2097</td>
                                                <td>Iota Action Cam</td>
                                                <td class="text-end">5</td>
                                                <td class="text-end amount-cell">₱24,995.00</td>
                                            </tr>
                                            <tr>
                                                <td>2096</td>
                                                <td>Kappa Charging Pad</td>
                                                <td class="text-end">6</td>
                                                <td class="text-end amount-cell">₱5,940.00</td>
                                            </tr>
                                            <tr>
                                                <td>2095</td>
                                                <td>Lambda USB-C Cable</td>
                                                <td class="text-end">8</td>
                                                <td class="text-end amount-cell">₱1,520.00</td>
                                            </tr>
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