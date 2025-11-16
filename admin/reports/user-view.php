<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: User-View</title>
    <?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/links.php');
    ?>
    <link rel="stylesheet" href="/carriemart/includes/admin-panel.css">
</head>

<body>

<?php
include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-panel.php');
?>
        <div class="flex-grow-1 p-3"> <!-- other column -->
            <div class="container-fluid">

                <h3 class="mb-3 d-flex align-items-center gap-2">
                    <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/people.svg" alt="" width="22" height="22" class="mt-1">
                    Users
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
                                <input type="text" class="form-control" placeholder="Search user ID / name / email">
                            </div>
                            <button class="btn btn-outline-secondary btn-sm"
                                    type="button" data-bs-toggle="offcanvas"
                                    data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                    <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                                </svg>
                                Filters
                            </button>
                            <select class="form-select form-select-sm" aria-label="Sort by" style="width:200px;">
                                <option selected>Sort by</option>
                                <option value="ordersDesc">Most orders</option>
                                <option value="ordersAsc">Fewest orders</option>
                                <option value="spentDesc">Highest spent</option>
                                <option value="spentAsc">Lowest spent</option>
                                <option value="newest">Newest accounts</option>
                                <option value="oldest">Oldest accounts</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 3 users</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <style>
                            .table thead th { white-space:nowrap; }
                            .actions-cell .btn { padding:.25rem .55rem; }
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
                                        <th>User ID</th>
                                        <th>Name</th>
                                        <th class="text-end">Orders</th>
                                        <th class="text-end">Total Spent (₱)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example rows; replace with server data -->
                                    <tr>
                                        <td>101</td>
                                        <td>John Doe</td>
                                        <td class="text-end">12</td>
                                        <td class="text-end amount-cell">₱123,456.78</td>
                                    </tr>
                                    <tr>
                                        <td>102</td>
                                        <td>Maria Reyes</td>
                                        <td class="text-end">8</td>
                                        <td class="text-end amount-cell">₱58,240.00</td>
                                    </tr>
                                    <tr>
                                        <td>103</td>
                                        <td>Lee Chan</td>
                                        <td class="text-end">3</td>
                                        <td class="text-end amount-cell">₱7,990.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Offcanvas: Filters (Users) -->
                <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Users</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <form class="vstack gap-3">
                            <div>
                                <label class="form-label">Min orders</label>
                                <input type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div>
                                <label class="form-label">Total spent range (₱)</label>
                                <div class="d-flex gap-2">
                                    <input type="number" step="0.01" class="form-control" placeholder="Min">
                                    <input type="number" step="0.01" class="form-control" placeholder="Max">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Account created</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control">
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Name contains</label>
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