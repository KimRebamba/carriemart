

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-speedometer mt-1" viewBox="0 0 16 16">
  <path d="M8 2a.5.5 0 0 1 .5.5V4a.5.5 0 0 1-1 0V2.5A.5.5 0 0 1 8 2M3.732 3.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707M2 8a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 8m9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5m.754-4.246a.39.39 0 0 0-.527-.02L7.547 7.31A.91.91 0 1 0 8.85 8.569l3.434-4.297a.39.39 0 0 0-.029-.518z"/>
  <path fill-rule="evenodd" d="M6.664 15.889A8 8 0 1 1 9.336.11a8 8 0 0 1-2.672 15.78zm-4.665-4.283A11.95 11.95 0 0 1 8 10c2.186 0 4.236.585 6.001 1.606a7 7 0 1 0-12.002 0"/>
</svg>
                    Dashboard
                </h3>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card  h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Sales Today</small>
                                <h4 class="mt-2 mb-0" id="metric-sales">₱0.00</h4>
                                <small class="text-success" id="metric-sales-change">+0%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Orders Today</small>
                                <h4 class="mt-2 mb-0" id="metric-orders">0</h4>
                                <small class="text-primary" id="metric-orders-change">+0%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Expenses Today</small>
                                <h4 class="mt-2 mb-0" id="metric-expenses">₱0.00</h4>
                                <small class="text-danger" id="metric-expenses-change">0%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Net Profit Today</small>
                                <h4 class="mt-2 mb-0" id="metric-profit">₱0.00</h4>
                                <small class="text-muted" id="metric-profit-note">(Sales – Expenses)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Total Users</small>
                                <h4 class="mt-2 mb-0" id="metric-users">0</h4>
                                <small class="text-muted">Active + Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Products In Store</small>
                                <h4 class="mt-2 mb-0" id="metric-products">0</h4>
                                <small class="text-muted">SKU Count</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Low Stock Alerts</small>
                                <h4 class="mt-2 mb-0" id="metric-lowstock">0</h4>
                                <small class="text-danger">Need restock</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card  h-100">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-semibold">Pending Returns</small>
                                <h4 class="mt-2 mb-0" id="metric-returns">0</h4>
                                <small class="text-warning">Awaiting action</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shortcuts -->
                <div class="card mb-4 quick-actions">
                    <div class="card-header d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-lightning-charge">
                            <path d="M11.3 1H6.7L1 9h4.6L4.7 15 13 7H8.4l2.9-6z"/>
                        </svg>
                        <strong>Quick Actions</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm">Add Account</a>
                            <a href="#" class="btn btn-outline-primary btn-sm">Add Employee</a>
                            <a href="#" class="btn btn-outline-primary btn-sm">Add Expense</a>
                            <a href="#" class="btn btn-outline-primary btn-sm">Add Product</a>
                        </div>
                    </div>
                </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    <script src="sidebars.js"></script>
</body>

</html>