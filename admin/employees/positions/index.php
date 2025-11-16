<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Positions</title>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="mt-1 bi bi-briefcase">
                      <path d="M6.5 0A1.5 1.5 0 0 0 5 1.5V3H1.5A1.5 1.5 0 0 0 0 4.5V6h16V4.5A1.5 1.5 0 0 0 14.5 3H11V1.5A1.5 1.5 0 0 0 9.5 0zM10 3H6V1.5a.5.5 0 0 1 .5-.5h3A.5.5 0 0 1 10 1.5z"/>
                      <path d="M0 7.5V13a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7.5H0m6 2a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3A.5.5 0 0 1 6 9.5"/>
                    </svg>
                    Positions
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
                                <input type="text" class="form-control" placeholder="Search position name">
                            </div>
                            <select class="form-select form-select-sm" aria-label="Sort by" style="width:180px;">
                                <option selected>Sort by</option>
                                <option value="newest">Newest</option>
                                <option value="nameAZ">Name A–Z</option>
                                <option value="rateHigh">Rate: High to Low</option>
                                <option value="rateLow">Rate: Low to High</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 2 positions</small>
                            <a href="create.php" class="btn btn-primary btn-sm">Add Position</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <style>
                            .table thead th { white-space:nowrap; }
                            .actions-cell .btn { padding:.25rem .55rem; }
                            @media (max-width: 992px){
                                .table-responsive { font-size:.875rem; }
                                .actions-cell .btn { font-size:.65rem; }
                            }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Position</th>
                                        <th class="text-end">Monthly Rate</th>
                                        <th>Created</th>
                                        <th class="text-center" style="width:180px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                 
                                    <tr>
                                        <td>1</td>
                                        <td>Sales Associate</td>
                                        <td class="text-end">₱18,000.00</td>
                                        <td>2025-10-01 09:30</td>
                                        <td class="text-center actions-cell">    
                                            <a href="edit.php?id=1" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Warehouse Clerk</td>
                                        <td class="text-end">₱16,500.00</td>
                                        <td>2025-09-12 14:05</td>
                                        <td class="text-center actions-cell">
                                            <a href="edit.php?id=2" class="btn btn-outline-secondary btn-sm">Edit</a>
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