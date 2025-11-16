<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Vouchers</title>
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
                    <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/ticket-perforated.svg" alt="" width="22" height="22" class="mt-1">
                    Vouchers
                </h3>

                <div class="card mb-4 table-card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="width:320px;">
                                <span class="input-group-text bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control" placeholder="Search voucher code">
                            </div>
                            <select class="form-select form-select-sm" aria-label="Sort by" style="width:180px;">
                                <option selected>Sort by</option>
                                <option value="newest">Newest</option>
                                <option value="oldest">Oldest</option>
                                <option value="percentHigh">Percent: High→Low</option>
                                <option value="percentLow">Percent: Low→High</option>
                                <option value="active">Active first</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 3 vouchers</small>
                            <a href="create.php" class="btn btn-primary btn-sm">Add Voucher</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <style>
                            .table thead th { white-space:nowrap; }
                            .actions-cell .btn { padding:.25rem .55rem; }
                            .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
                            .v-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
                            .v-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
                            @media (max-width: 992px){
                                .table-responsive { font-size:.85rem; }
                                .actions-cell .btn { font-size:.65rem; }
                            }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Code</th>
                                        <th class="text-end">% Sale</th>
                                        <th class="text-end">Min Purchase</th>
                                        <th class="text-end">Max Discount</th>
                                        <th>Date Range</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-center" style="width:160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example rows; replace with server data -->
                                    <tr>
                                        <td>101</td>
                                        <td>WELCOME10</td>
                                        <td class="text-end">10%</td>
                                        <td class="text-end">₱1,000.00</td>
                                        <td class="text-end">₱300.00</td>
                                        <td><span class="small text-muted">2025-11-01 to 2025-12-31</span></td>
                                        <td><span class="status-badge v-active">active</span></td>
                                        <td><span class="small text-muted">2025-11-01</span></td>
                                        <td class="text-center actions-cell">
                                            <a href="edit.php?id=101" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm mb-1">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>102</td>
                                        <td>FREESHIP</td>
                                        <td class="text-end">0%</td>
                                        <td class="text-end">₱0.00</td>
                                        <td class="text-end">₱150.00</td>
                                        <td><span class="small text-muted">2025-11-10 to 2025-12-10</span></td>
                                        <td><span class="status-badge v-active">active</span></td>
                                        <td><span class="small text-muted">2025-11-10</span></td>
                                        <td class="text-center actions-cell">
                                            <a href="edit.php?id=102" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm mb-1">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>103</td>
                                        <td>FLASH25</td>
                                        <td class="text-end">25%</td>
                                        <td class="text-end">₱2,500.00</td>
                                        <td class="text-end">₱500.00</td>
                                        <td><span class="small text-muted">2025-10-01 to 2025-10-15</span></td>
                                        <td><span class="status-badge v-inactive">inactive</span></td>
                                        <td><span class="small text-muted">2025-10-01</span></td>
                                        <td class="text-center actions-cell">
                                            <a href="edit.php?id=103" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm mb-1">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Offcanvas: Filters (Vouchers) -->
                <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Vouchers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <form class="vstack gap-3">
                            <div>
                                <label class="form-label">Active status</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option value="active">active</option>
                                    <option value="inactive">inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">% Sale minimum</label>
                                <input type="number" class="form-control" placeholder="e.g. 10">
                            </div>
                            <div>
                                <label class="form-label">Date range (from_date / to_date)</label>
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