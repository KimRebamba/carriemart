<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CM: Accounts</title>
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
</body>

</html>