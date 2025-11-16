<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Expenses</title>
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
                    <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/receipt.svg" alt="" width="22" height="22" class="mt-1">
                    Expenses
                </h3>

                <div class="card mb-4 table-card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="width:340px;">
                                <span class="input-group-text bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control" placeholder="Search expense ID / type / description">
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
                                <option value="amountHigh">Amount: High→Low</option>
                                <option value="amountLow">Amount: Low→High</option>
                                <option value="dueSoon">Due soon</option>
                                <option value="statusPaid">Paid first</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 3 expenses</small>
                            <a href="create.php" class="btn btn-primary btn-sm">Add Expense</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
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
                                    <!-- Example rows; replace with server data -->
                                    <tr>
                                        <td>7001</td>
                                        <td>inventory_purchase</td>
                                        <td class="text-truncate" style="max-width:220px;">Batch of mobile accessories</td>
                                        <td class="text-end amount-cell">₱45,000.00</td>
                                        <td><span class="status-badge exp-paid">paid</span></td>
                                        <td><span class="small text-muted">2025-11-05</span></td>
                                        <td><span class="small text-muted">2025-11-06</span></td>
                                        <td class="text-center actions-cell">
                                            <a href="edit.php?id=7001" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </td>
                                    </tr>
                                   
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Offcanvas: Filters (Expenses) -->
                <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Expenses</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <form class="vstack gap-3">
                            <div>
                                <label class="form-label">Expense type</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option value="inventory_purchase">inventory_purchase</option>
                                    <option value="shipping">shipping</option>
                                    <option value="maintenance">maintenance</option>
                                    <option value="rent">rent</option>
                                    <option value="utilities">utilities</option>
                                    <option value="other">other</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option value="pending">pending</option>
                                    <option value="paid">paid</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Due date range</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control">
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Amount range (₱)</label>
                                <div class="d-flex gap-2">
                                    <input type="number" step="0.01" class="form-control" placeholder="Min">
                                    <input type="number" step="0.01" class="form-control" placeholder="Max">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Description contains</label>
                                <input type="text" class="form-control" placeholder="Keyword">
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