<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Salaries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="mt-1 bi bi-currency-dollar" viewBox="0 0 16 16">
                      <path d="M8.5 13.5c2.5 0 4.5-1.12 4.5-3.25 0-1.6-1.2-2.5-3.38-2.9l-1.24-.24V4.2c1.24.12 2.24.64 2.5 1.55h1.88c-.32-1.84-1.98-3.02-4.38-3.2V1h-1v1.53C4.7 2.7 3 3.96 3 6c0 1.7 1.28 2.66 3.33 3.05l1.17.24v3.07c-1.36-.12-2.48-.7-2.83-1.68H2.77c.35 1.97 2.13 3.24 4.73 3.32V15h1v-1.5M7.5 8.3c-1.3-.26-2.05-.74-2.05-1.7 0-.94.76-1.62 2.05-1.78zm1 .92 1.02.2c1.38.27 2.23.77 2.23 1.83 0 1.03-.9 1.77-2.75 1.92z"/>
                    </svg>
                    Salaries
                </h3>

                <div class="card mb-4 table-card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="input-group input-group-sm" style="width:260px;">
                                <span class="input-group-text bg-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                      <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85ZM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control" placeholder="Search salary ID / emp name">
                            </div>
                            <select class="form-select form-select-sm" aria-label="Sort by" style="width:180px;">
                                <option selected>Sort by</option>
                                <option value="newest">Newest (Pay Date)</option>
                                <option value="oldest">Oldest (Pay Date)</option>
                                <option value="status">Status</option>
                                <option value="rateHigh">Rate: High to Low</option>
                                <option value="rateLow">Rate: Low to High</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 2 salaries</small>
                            <a href="create.php" class="btn btn-primary btn-sm">Add Salary</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <style>
                            .table thead th { white-space:nowrap; }
                            .actions-cell .btn { padding:.25rem .55rem; }
                            .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
                            .status-paid { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
                            .status-pending { background:#fff3cd; color:#664d03; border:1px solid #ffecb5; }
                            .status-cancelled { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
                            @media (max-width: 992px){
                                .table-responsive { font-size:.875rem; }
                                .actions-cell .btn { font-size:.65rem; }
                            }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Salary ID</th>
                                        <th>Emp ID</th>
                                        <th>Name</th>
                                        <th>Pay Date</th>
                                        <th>Period</th>
                                        <th class="text-end">Rate Used</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width:150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example rows; replace with server data (JOIN employees for first_name/last_name) -->
                                    <tr>
                                        <td>5001</td>
                                        <td>1001</td>
                                        <td>Anna Santos</td>
                                        <td>2025-11-15</td>
                                        <td><span class="small text-muted">2025-11-01 to 2025-11-15</span></td>
                                        <td class="text-end">₱18,000.00</td>
                                        <td><span class="status-badge status-paid">paid</span></td>
                                        <td class="text-center actions-cell">
                                            <a href="edit.php?id=5001" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>5002</td>
                                        <td>1002</td>
                                        <td>Juan Dela Cruz</td>
                                        <td>2025-11-15</td>
                                        <td><span class="small text-muted">2025-11-01 to 2025-11-15</span></td>
                                        <td class="text-end">₱16,500.00</td>
                                        <td><span class="status-badge status-pending">pending</span></td>
                                        <td class="text-center actions-cell">
                                            <a href="edit.php?id=5002" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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