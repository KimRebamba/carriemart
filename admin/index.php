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

    .btn-toggle {
        padding: .25rem .5rem;
        font-weight: 600;
        color: var(--bs-emphasis-color);
        background-color: transparent;
    }

    /* IIMPORTANT - FROM CSS.SIDEBAR.JS!!! ANNOYING AF! */
    .btn-toggle .caret {
        width: 12px;
        height: 12px;
        transition: transform .35s ease;
        transform-origin: center;
        opacity: .8;
    }

    .btn-toggle[aria-expanded="true"] .caret {
        transform: rotate(90deg);
    }

    .btn-toggle .icon {
        width: 16px;
        height: 16px;
        opacity: .9;
        margin-right: .5rem;
    }

    .btn-toggle-nav a {
        padding: .1875rem .5rem;
        margin-top: .125rem;
        margin-left: 1.25rem;
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

                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0"
                         aria-expanded="true">
                        <img class="icon" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/house-door.svg"
                            alt="">
                        <span>Home</span>
                    </button>
                </li>

                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0"
                        aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon bi bi-person-circle" viewBox="0 0 16 16">
  <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
  <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
</svg>
                        <span>Accounts</span>
                    </button>
                    
                </li>

                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#employees-collapse" aria-expanded="false">
                        <img class="icon" src="../assets/briefcase.svg"
                            alt="">
                        <span>Employees</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
                    </button>
                    <div class="collapse" id="employees-collapse">
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
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#orders-collapse" aria-expanded="false">
                        <img class="icon" src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/cart3.svg" alt="">
                        <span>Orders</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
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


                <li class="mb-1">
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#product-collapse" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon bi bi-database-add" viewBox="0 0 16 16">
  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0"/>
  <path d="M12.096 6.223A5 5 0 0 0 13 5.698V7c0 .289-.213.654-.753 1.007a4.5 4.5 0 0 1 1.753.25V4c0-1.007-.875-1.755-1.904-2.223C11.022 1.289 9.573 1 8 1s-3.022.289-4.096.777C2.875 2.245 2 2.993 2 4v9c0 1.007.875 1.755 1.904 2.223C4.978 15.71 6.427 16 8 16c.536 0 1.058-.034 1.555-.097a4.5 4.5 0 0 1-.813-.927Q8.378 15 8 15c-1.464 0-2.766-.27-3.682-.687C3.356 13.875 3 13.373 3 13v-1.302c.271.202.58.378.904.525C4.978 12.71 6.427 13 8 13h.027a4.6 4.6 0 0 1 0-1H8c-1.464 0-2.766-.27-3.682-.687C3.356 10.875 3 10.373 3 10V8.698c.271.202.58.378.904.525C4.978 9.71 6.427 10 8 10q.393 0 .774-.024a4.5 4.5 0 0 1 1.102-1.132C9.298 8.944 8.666 9 8 9c-1.464 0-2.766-.27-3.682-.687C3.356 7.875 3 7.373 3 7V5.698c.271.202.58.378.904.525C4.978 6.711 6.427 7 8 7s3.022-.289 4.096-.777M3 4c0-.374.356-.875 1.318-1.313C5.234 2.271 6.536 2 8 2s2.766.27 3.682.687C12.644 3.125 13 3.627 13 4c0 .374-.356.875-1.318 1.313C10.766 5.729 9.464 6 8 6s-2.766-.27-3.682-.687C3.356 4.875 3 4.373 3 4"/>
</svg>
                        <span>Products</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
                    </button>
                    <div class="collapse" id="product-collapse">
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
                    <button class="btn btn-toggle d-flex w-100 align-items-center rounded border-0 collapsed"
                        data-bs-toggle="collapse" data-bs-target="#report-collapse" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="icon bi bi-table" viewBox="0 0 16 16">
  <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z"/>
</svg>
                        <span>Reports</span>
                        <img class="caret ms-auto"
                            src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/caret-right.svg" alt="">
                    </button>
                    <div class="collapse" id="report-collapse"> <!-- always change id -->
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

                <li class="border-top my-3"></li>

                <li class="mb-1">
                    
                  
                       <a href="../index.php" class="btn btn-toggle d-flex w-100 align-items-center rounded border-0"  aria-expanded="false">
                        <small class="fw-normal">Log out</small>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="caret bi bi-caret-right-fill ms-2" viewBox="0 0 16 16">
  <path d="m12.14 8.753-5.482 4.796c-.646.566-1.658.106-1.658-.753V3.204a1 1 0 0 1 1.659-.753l5.48 4.796a1 1 0 0 1 0 1.506z"/>
</svg> </a> 
                 
                       
                </li>
            </ul>
        </div>


        <div class="flex-grow-1 p-3"> <!-- other column -->

            <table class="table table-striped table-hover table-bordered">
  <thead class="">
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>John</td>
      <td>Doe</td>
      <td>@social</td>
    </tr>
  </tbody>
</table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    <script src="sidebars.js"></script>
</body>

</html>