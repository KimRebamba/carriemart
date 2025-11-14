<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

</head>

<body>

    <!-- SIDE PANEL -->
    <div class="flex-shrink-0 p-3" style="width: 280px; min-height: 100vh;"> <a href="/"
            class="d-flex align-items-center pb-3 mb-3 link-body-emphasis text-decoration-none border-bottom gap-2">
             <img src="../assets/Header-Logo-01.svg" alt="logo icon" height="40px"> <span class="fs-5 fw-semibold"> Admin Panel</span> </a>
        <ul class="list-unstyled ps-0">
            <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0"
                    data-bs-toggle="collapse" data-bs-target="#home-collapse" aria-expanded="true">
                    <img class="me-2" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/house-door.svg" alt="" width="16" height="16">
                    Home <img src="../assets/caret-down.svg" alt="" width="12" height="12" style="margin-left: .5rem;">
                </button>
                <div class="collapse show" id="home-collapse" style="">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Overview</a></li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Updates</a></li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Reports</a></li>
                    </ul>
                </div>
            </li>
            <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed"
                    data-bs-toggle="collapse" data-bs-target="#dashboard-collapse" aria-expanded="false">
                    <img class="me-2" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/speedometer2.svg" alt="" width="16" height="16">
                    Dashboard
                </button>
                <div class="collapse" id="dashboard-collapse">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Overview</a></li>
                        <li><a href="#" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Weekly</a>
                        </li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Monthly</a></li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Annually</a></li>
                    </ul>
                </div>
            </li>
            <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed"
                    data-bs-toggle="collapse" data-bs-target="#orders-collapse" aria-expanded="false">
                    <img class="me-2" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/cart3.svg" alt="" width="16" height="16">
                    Orders
                </button>
                <div class="collapse" id="orders-collapse">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="#" class="link-body-emphasis d-inline-flex text-decoration-none rounded">New</a>
                        </li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Processed</a></li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Shipped</a></li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Returned</a></li>
                    </ul>
                </div>
            </li>
            <li class="border-top my-3"></li>
            <li class="mb-1">
                <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed"
                    data-bs-toggle="collapse" data-bs-target="#account-collapse" aria-expanded="false">
                    <img class="me-2" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/person-circle.svg" alt="" width="16" height="16">
                    Account
                </button>
                <div class="collapse" id="account-collapse">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="#" class="link-body-emphasis d-inline-flex text-decoration-none rounded">New...</a>
                        </li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Profile</a></li>
                        <li><a href="#"
                                class="link-body-emphasis d-inline-flex text-decoration-none rounded">Settings</a></li>
                        <li><a href="#" class="link-body-emphasis d-inline-flex text-decoration-none rounded">Sign
                                out</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    
    <script src="sidebars.js"></script>
</body>

</html>