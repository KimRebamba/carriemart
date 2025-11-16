<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Orders</title>
    <?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php');
?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
    <style>
    </style>
</head>

<body>
<?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php');
?>

        <div class="flex-grow-1 p-3"> <!-- other column -->
            <div class="container-fluid">

                <h3 class="mb-3 d-flex align-items-center gap-2">
                    <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/cart3.svg" alt="" width="22" height="22" class="mt-1">
                    Orders
                </h3>

                <div class="card mb-4 table-card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="width:300px;">
                                <span class="input-group-text bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control" placeholder="Search order ID / voucher / user">
                            </div>
                            <button class="btn btn-outline-secondary btn-sm"
                                    type="button" data-bs-toggle="offcanvas"
                                    data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                    <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                                </svg>
                                Filters
                            </button>
                            <select class="form-select form-select-sm" aria-label="Sort by" style="width:180px;">
                                <option selected>Sort by</option>
                                <option value="newest">Newest</option>
                                <option value="oldest">Oldest</option>
                                <option value="paymentStatus">Payment Status</option>
                                <option value="orderStatus">Order Status</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 2 orders</small>
                            <a href="create.php" class="btn btn-primary btn-sm">Add Order</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
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
                                        <th class="text-end">Delivery Fee</th>
                                        <th class="text-center" style="width:160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example rows; replace with server data -->
                                    <tr>
                                        <td>7001</td>
                                        <td>15</td>
                                        <td>WELCOME10</td>
                                        <td><span class="status-badge pay-paid">paid</span></td>
                                        <td><span class="status-badge ord-completed">completed</span></td>
                                        <td>Credit Card</td>
                                        <td>2025-11-15 10:24</td>
                                        <td class="text-end">₱120.00</td>
                                        <td class="text-center actions-cell ">
                                            <a href="view.php?id=7001" class="btn btn-outline-primary btn-sm my-1">View</a>
                                            <a href="edit.php?id=7001" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm mb-1">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>7002</td>
                                        <td>27</td>
                                        <td>—</td>
                                        <td><span class="status-badge pay-pending">pending</span></td>
                                        <td><span class="status-badge ord-processing">processing</span></td>
                                        <td>COD</td>
                                        <td>2025-11-16 08:05</td>
                                        <td class="text-end">₱95.00</td>
                                        <td class="text-center actions-cell">
                                             <a href="view.php?id=7001" class="btn btn-outline-primary btn-sm my-1">View</a>
                                            <a href="edit.php?id=7001" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm mb-1">Delete</button>
                                        </td>
                                    </tr>
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
                        <form class="vstack gap-3">
                            <div>
                                <label class="form-label">Payment status</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option value="pending">pending</option>
                                    <option value="paid">paid</option>
                                    <option value="refunded">refunded</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Order status</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option value="pending">pending</option>
                                    <option value="processing">processing</option>
                                    <option value="shipped">shipped</option>
                                    <option value="completed">completed</option>
                                    <option value="cancelled">cancelled</option>
                                    <option value="requested_refund">requested_refund</option>
                                    <option value="returned">returned</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Payment option</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option>Credit Card</option>
                                    <option>COD</option>
                                    <option>Bank Transfer</option>
                                    <option>e-Wallet</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Date ordered range</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control">
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary btn-sm">Apply Filters</button>
                            </div>
                        </form>
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