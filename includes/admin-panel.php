<!-- side panel -->
    <div class="d-flex"> <!-- DO NOT REMOVE - for flex items -->
        <div class="flex-shrink-0 p-3 border" style="width:280px; min-height:100vh;"> <!-- side panel-->
            <a href="/" class="d-flex align-items-start pb-3 mb-3 link-body-emphasis text-decoration-none border-bottom gap-2">
                <img src="/carriemart/assets/Logo.svg" alt="logo icon" height="40">
                <div class="d-flex flex-column lh-1">
                    <small class="fw-normal">CarrieMart</small>
                    <span class="fs-5 fw-semibold mt-1">Admin Panel</span>
                </div>
            </a>
            <ul class="list-unstyled ps-0">

                <!-- Home (link button) -->
                <li class="mb-1">
                    <a class="btn btn-toggle d-flex w-100 align-items-center rounded border-0"
                       href="/carriemart/admin/index.php">
                        <img class="icon" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/house-door.svg" alt="">
                        <span>Home</span>
                    </a>
                </li>

                <!-- Accounts (link button) -->
                <li class="mb-1">
                    <a class="btn btn-toggle d-flex w-100 align-items-center rounded border-0"
                       href="/carriemart/admin/accounts/index.php">
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
                            <li><a href="/carriemart/admin/employees/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">View & Edit Information</a></li>
                            <li><a href="/carriemart/admin/employees/positions/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Positions</a></li>
                            <li><a href="/carriemart/admin/employees/salaries/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Salaries</a></li>
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
                            <li><a href="/carriemart/admin/orders/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">View & Edit Information</a></li>
                            <li><a href="/carriemart/admin/orders/vouchers/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Vouchers</a></li>
                            <li><a href="/carriemart/admin/orders/reviews/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Reviews</a></li>
                            <li><a href="/carriemart/admin/orders/returns/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Returns</a></li>
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
                            <li><a href="/carriemart/admin/products/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">View & Edit Information</a></li>
                            <li><a href="/carriemart/admin/products/brands/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Brands</a></li>
                            <li><a href="/carriemart/admin/products/categories/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Categories</a></li>
                            <li><a href="/carriemart/admin/products/suppliers/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Suppliers</a></li>
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
                            <li><a href="/carriemart/admin/reports/expenses/index.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Expenses</a></li>
                            <li><a href="/carriemart/admin/reports/sales.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sales</a></li>
                            <li><a href="/carriemart/admin/reports/user-view.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sort by Users</a></li>
                            <li><a href="/carriemart/admin/reports/item-view.php" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sort by Items</a></li>
                        </ul>
                    </div>
                </li>

                <li class="border-top my-3"></li>

                <li class="mb-1">
                    <a href="/carriemart/index.php" class="btn btn-toggle d-flex w-100 align-items-center rounded border-0" aria-expanded="false">
                        <small class="fw-normal">Log out</small>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="currentColor" class="caret bi bi-caret-right-fill ms-2" viewBox="0 0 16 16">
                          <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>

    <script src="sidebars.js"></script>