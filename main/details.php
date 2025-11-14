<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    /* Back line */
    .back-line {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem .75rem;
        border-bottom: 1px solid var(--bs-border-color);
        color: var(--bs-body-color);
        text-decoration: none;
    }

    .back-line:hover {
        background-color: rgba(var(--bs-primary-rgb), .06);
        text-decoration: none;
    }

    .back-line .icon {
        width: 20px;
        height: 20px;
        opacity: .9;
    }
    </style>
</head>

<body>
    <header class="p-3 mb-2 border-bottom">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center">
                <a href="#" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                    <img src="../assets/Header-Logo-01.svg" alt="Carriemart logo" width="40" height="40" class="me-2">
                </a>
                <form class="search-form d-flex mb-0 me-2 me-lg-3 flex-grow-1" role="search" style="max-width:540px;">
                    <input type="search" class="form-control w-100" placeholder="Search..." aria-label="Search">
                </form>
                <div class="dropdown text-end avatar-dropdown align-self-center">
                    <a href="#"
                        class="d-inline-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../assets/me.jfif" alt="mdo" width="32" height="32" class="rounded-circle">
                    </a>
                    <ul class="dropdown-menu text-small">
                        <li><a class="dropdown-item" href="#">New project...</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#">Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Go Back line -->
    <div class="container mb-3">
        <a href="#" class="back-line rounded-2" onclick="history.back(); return false;">
            <svg class="icons" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0" />
            </svg>
            <span>Go Back</span>
        </a>
    </div>

    <!-- rating -->
    <div class="d-flex flex-column flex-md-row p-4 gap-4 py-md-5 align-items-center justify-content-center">
        <div class="list-group"> 
            
        <section class="list-group-item list-group-item-action d-flex gap-3 py-3"
                > <img src="https://github.com/twbs.png" alt="" width="32" height="32"
                    class="rounded-circle flex-shrink-0">
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0">List group item heading</h6>
                        <p class="mb-0 opacity-75">Some placeholder content in a paragraph.</p>
                    </div> <small class="opacity-50 text-nowrap">now</small>
                </div>
            </section> 
            
            <a href="#" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true"> <img
                    src="https://github.com/twbs.png" alt="" width="32" height="32"
                    class="rounded-circle flex-shrink-0">
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0">Another title here</h6>
                        <p class="mb-0 opacity-75">Some placeholder content in a paragraph that goes a little longer so
                            it wraps to a new line.</p>
                    </div> <small class="opacity-50 text-nowrap">3d</small>
                </div>
            </a> <a href="#" class="list-group-item list-group-item-action d-flex gap-3 py-3" aria-current="true"> <img
                    src="https://github.com/twbs.png" alt="" width="32" height="32"
                    class="rounded-circle flex-shrink-0">
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0">Third heading</h6>
                        <p class="mb-0 opacity-75">Some placeholder content in a paragraph.</p>
                    </div> <small class="opacity-50 text-nowrap">1w</small>
                </div>
            </a> </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

<!-- $limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM reviews ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

SELECT * FROM reviews
ORDER BY created_at DESC
LIMIT 10 OFFSET 0;   -- first 10
-->
</html>