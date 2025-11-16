<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
    .btn-toggle[aria-expanded="true"] {
        color: rgba(var(--bs-emphasis-color-rgb), .85);
    }
    .btn-toggle { padding:.25rem .5rem; font-weight:600; color:var(--bs-emphasis-color); background-color:transparent; }
    /* IIMPORTANT - FROM CSS.SIDEBAR.JS!!! ANNOYING AF! */
    .btn-toggle .caret { width:12px; height:12px; transition: transform .35s ease; transform-origin:center; opacity:.8; }
    .btn-toggle[aria-expanded="true"] .caret { transform: rotate(90deg); }
    .btn-toggle .icon { width:16px; height:16px; opacity:.9; margin-right:.5rem; }
    .btn-toggle-nav a { padding:.1875rem .5rem; margin-top:.125rem; margin-left:1.25rem; }

    .table-card{ border-radius:.5rem; overflow:hidden; }
    .table-card .card-header{ border-bottom:1px solid var(--bs-border-color); }
    .table-card .table{ margin-bottom:0; border:0; }
    .table-card .table > :not(caption) > * > *{ border-bottom:1px solid var(--bs-border-color); }
    .table-card .table tbody tr:last-child td{ border-bottom:0; }


    .card-header a:not(.btn),
    .card-header a:not(.btn):hover,
    .card-header a:not(.btn):focus,
    .card-header a:not(.btn):active {
      color: inherit;
      text-decoration: none;
    }
  
    .flex-shrink-0 > a.link-body-emphasis,
    .flex-shrink-0 > a.link-body-emphasis:hover,
    .flex-shrink-0 > a.link-body-emphasis:focus,
    .flex-shrink-0 > a.link-body-emphasis:active {
      color: inherit;
      text-decoration: none;
    }
    </style>
</head>

<body>
    <div class="d-flex"> <!-- DO NOT REMOVE - for flex items -->
        <div class="flex-shrink-0 p-3 border" style="width:280px; min-height:100vh;"> <!-- side panel-->
            <a href="/" class="d-flex align-items-start pb-3 mb-3 link-body-emphasis text-decoration-none border-bottom gap-2">
                <img src="../assets/Header-Logo-01.svg" alt="logo icon" height="40">
                <div class="d-flex flex-column lh-1">
                    <small class="fw-normal">CarrieMart</small>
                    <span class="fs-5 fw-semibold mt-1">Admin Panel</span>
                </div>
            </a>
            <ul class="list-unstyled ps-0">

                <!-- Home (link button) -->
                <li class="mb-1">
                    <a class="btn btn-toggle d-flex w-100 align-items-center rounded border-0"
                       href="../index.php">
                        <img class="icon" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/house-door.svg" alt="">
                        <span>Home</span>
                    </a>
                </li>

                <!-- Accounts (link button) -->
                <li class="mb-1">
                    <a class="btn btn-toggle d-flex w-100 align-items-center rounded border-0"
                       href="index.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="currentColor" class="icon bi bi-person-circle" viewBox="0 0 16 16">
                          <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                          <path fill-rule="evenodd"
                                d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                        </svg>
                        <span>Accounts</span>
                    </a>
                </li>

                <!-- Employees (dropdown) -->
                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#employees-collapse" aria-expanded="false">
                        <img class="icon" src="../assets/briefcase.svg" alt="">
                        <span>Employees</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
                    </button>
                    <div class="collapse" id="employees-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="../employees/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">View & Edit Information</a></li>
                            <li><a href="../employees/positions.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Positions</a></li>
                            <li><a href="../employees/salaries.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Salaries</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Orders (dropdown) -->
                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#orders-collapse" aria-expanded="false">
                        <img class="icon" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/cart3.svg" alt="">
                        <span>Orders</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
                    </button>
                    <div class="collapse" id="orders-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="../orders/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">View & Edit Information</a></li>
                            <li><a href="../orders/vouchers.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Vouchers</a></li>
                            <li><a href="../orders/reviews.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Reviews</a></li>
                            <li><a href="../orders/returns.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Returns</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Products (dropdown) -->
                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#product-collapse" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="currentColor" class="icon bi bi-database-add" viewBox="0 0 16 16">
                          <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0"/>
                          <path d="M12.096 6.223A5 5 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.5 4.5 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.5 4.5 0 0 1-.813-.927Q8.378 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.6 4.6 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10q.393 0 .774-.024a4.5 4.5 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777M3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4"/>
                        </svg>
                        <span>Products</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
                    </button>
                    <div class="collapse" id="product-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="../products/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">View & Edit Information</a></li>
                            <li><a href="../products/brands.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Brands</a></li>
                            <li><a href="../products/categories.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Categories</a></li>
                            <li><a href="../products/suppliers.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Suppliers</a></li>
                        </ul>
                    </div>
                </li>

                <!-- Reports (dropdown) -->
                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#report-collapse" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="currentColor" class="icon bi bi-table" viewBox="0 0 16 16">
                          <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z"/>
                        </svg>
                        <span>Reports</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
                    </button>
                    <div class="collapse" id="report-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="../reports/expenses.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Expenses</a></li>
                            <li><a href="../reports/sales.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sales</a></li>
                            <li><a href="../reports/users.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sort by Users</a></li>
                            <li><a href="../reports/items.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sort by Items</a></li>
                        </ul>
                    </div>
                </li>

                <li class="border-top my-3"></li>

                <li class="mb-1">
                    <a href="../index.php" class="btn btn-toggle d-flex w-100 align-items-center rounded border-0" aria-expanded="false">
                        <small class="fw-normal">Log out</small>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="currentColor" class="caret bi bi-caret-right-fill ms-2" viewBox="0 0 16 16">
                          <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>


        <div class="flex-grow-1 p-3"> <!-- other column -->
            <div class="container-fluid">

                <h3 class="mb-3 d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-people-fill mt-1" viewBox="0 0 16 16">
                      <path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1z"/>
                      <path fill-rule="evenodd" d="M11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m-5.784 6A2.238 2.238 0 0 1 5 12c0-1.355.68-2.75 1.936-3.72C5.873 8.102 4.407 8 3 8 0 8 0 11 0 11s0 1 1 1z"/>
                      <path d="M5 8a2 2 0 1 0-4 0 2 2 0 0 0 4 0"/>
                    </svg>
                    Accounts
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
                                <input type="text" class="form-control" placeholder="Search name or email">
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
                                <option value="nameAZ">Name A–Z</option>
                                <option value="roleAZ">Role A–Z</option>
                                <option value="recentActive">Recently Active</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <small class="text-muted">Showing 2 users</small>
                            <a href="create.php" class="btn btn-primary btn-sm">Add Account</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <style>
                            .avatar { width:34px; height:34px; border-radius:50%; object-fit:cover; border:1px solid #dee2e6; }
                            .status-badge { font-size:.65rem; letter-spacing:.5px; font-weight:600; padding:.35rem .55rem; border-radius:.35rem; text-transform:uppercase; }
                            .status-active { background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; }
                            .status-inactive { background:#f8d7da; color:#842029; border:1px solid #f5c2c7; }
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
                                        <th>Avatar</th>
                                        <th>Name / Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Join Date</th>
                                        <th class="text-center" style="width:180px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><img src="/uploads/mark.jpg" alt="Mark" class="avatar"></td>
                                        <td>
                                            <div class="fw-semibold">Mark Otto</div>
                                            <div class="text-muted small">mark@example.com</div>
                                        </td>
                                        <td><span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle ">customer</span></td>
                                        <td><span class="status-badge status-active">Active</span></td>
                                        <td>2025-11-16 12:00</td>
                                        <td class="text-center actions-cell">
                                            <a href="view.php?id=1" class="btn btn-outline-primary btn-sm">View</a>
                                            <a href="edit.php?id=1" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><img src="/uploads/jane.png" alt="Jane" class="avatar"></td>
                                        <td>
                                            <div class="fw-semibold">Jane Doe</div>
                                            <div class="text-muted small">jane@example.com</div>
                                        </td>
                                        <td><span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">admin</span></td>
                                        <td><span class="status-badge status-inactive">Inactive</span></td>
                                        <td>2025-11-10 08:22</td>
                                        <td class="text-center actions-cell">
                                            <a href="view.php?id=2" class="btn btn-outline-primary btn-sm">View</a>
                                            <a href="edit.php?id=2" class="btn btn-outline-secondary btn-sm">Edit</a>
                                            <button class="btn btn-outline-danger btn-sm">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Offcanvas: Filters -->
                <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Users</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <form class="vstack gap-3">
                            <div>
                                <label class="form-label">Role</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option value="admin">Admin</option>
                                    <option value="employee">Employee</option>
                                    <option value="customer">Customer</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select class="form-select">
                                    <option value="">Any</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Joined date range</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control" placeholder="From">
                                    <input type="date" class="form-control" placeholder="To">
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Email contains</label>
                                <input type="text" class="form-control" placeholder="example.com">
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

    <script src="sidebars.js"></script>
</body>

</html>