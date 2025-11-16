<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Item-View</title>
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
                    <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/box-seam.svg" alt="" width="22" height="22" class="mt-1">
                    Items
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
                                <input type="text" class="form-control" placeholder="Search product ID / name">
                            </div>
                            <button class="btn btn-outline-secondary btn-sm"
                                    type="button" data-bs-toggle="offcanvas"
                                    data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                                    <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                                </svg>
                                Filters
                            </button>
                            <select class="form-select form-select-sm" aria-label="Sort by" style="width:220px;">
                                <option selected>Sort by</option>
                                <option value="qtyDesc">Most sold (qty)</option>
                                <option value="qtyAsc">Least sold (qty)</option>
                                <option value="revDesc">Highest revenue</option>
                                <option value="revAsc">Lowest revenue</option>
                                <option value="retDesc">Most returns</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 5 items</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <style>
                            .table thead th { white-space:nowrap; }
                            .amount-cell { font-variant-numeric: tabular-nums; }
                            @media (max-width: 992px){
                                .table-responsive { font-size:.85rem; }
                            }
                        </style>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product ID</th>
                                        <th>Product name</th>
                                        <th class="text-end">Total qty sold</th>
                                        <th class="text-end">Total revenue (₱)</th>
                                        <th class="text-end">Return count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Example rows; replace with server data (JOIN products, product_order; LEFT JOIN orders/order_return) -->
                                    <tr>
                                        <td>2001</td>
                                        <td>Alpha Smartphone X</td>
                                        <td class="text-end">320</td>
                                        <td class="text-end amount-cell">₱7,999,680.00</td>
                                        <td class="text-end">5</td>
                                    </tr>
                                    <tr>
                                        <td>2002</td>
                                        <td>Omega Laptop Pro</td>
                                        <td class="text-end">140</td>
                                        <td class="text-end amount-cell">₱7,630,000.00</td>
                                        <td class="text-end">2</td>
                                    </tr>
                                    <tr>
                                        <td>2005</td>
                                        <td>Gamma Smartwatch S</td>
                                        <td class="text-end">480</td>
                                        <td class="text-end amount-cell">₱2,880,000.00</td>
                                        <td class="text-end">1</td>
                                    </tr>
                                    <tr>
                                        <td>2010</td>
                                        <td>Delta Headphones Lite</td>
                                        <td class="text-end">520</td>
                                        <td class="text-end amount-cell">₱675,480.00</td>
                                        <td class="text-end">0</td>
                                    </tr>
                                    <tr>
                                        <td>2015</td>
                                        <td>Echo Bluetooth Speaker</td>
                                        <td class="text-end">410</td>
                                        <td class="text-end amount-cell">₱533,000.00</td>
                                        <td class="text-end">3</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Offcanvas: Filters (Items) -->
                <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Items</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <form class="vstack gap-3">
                            <div>
                                <label class="form-label">Category</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <!-- populate from categories -->
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Brand</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <!-- populate from brands -->
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Ordered date range</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control">
                                    <input type="date" class="form-control">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Min qty sold</label>
                                <input type="number" min="0" class="form-control" placeholder="0">
                            </div>
                            <div>
                                <label class="form-label">Min revenue (₱)</label>
                                <input type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
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