<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
/* Back line */
.back-line {
    display: flex; align-items: center; gap: .5rem;
    padding: .5rem .75rem; border-bottom: 1px solid var(--bs-border-color);
    color: var(--bs-body-color); text-decoration: none;
}
.back-line:hover { background-color: rgba(var(--bs-primary-rgb), .06); text-decoration: none; }
.back-line .icon { width: 20px; height: 20px; opacity: .9; }

/* Cart layout */
.cart-list { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.cart-card {
    border: 1px solid transparent;
    transition: border-color .15s ease;
    background: #fff;
}
.cart-card:hover { border-color: rgba(0,0,0,.15);  }

.card-header { background: transparent; }

.cart-row {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    column-gap: 1rem;
    padding-top: .5rem;
}
.cell-check { display: flex; align-items: center; justify-content: center; min-width: 42px; }
.cell-product { min-width: 0; }
.product-content {
    display: grid;
    grid-template-columns: 110px 1fr;
    gap: .75rem;
    align-items: center;
}
.product-img {
    width: 110px;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border-radius: .5rem;
    background: #f8f9fa;
}
.product-meta h6 { margin: 0 0 .125rem 0; font-size: .95rem; }
.brand { color: var(--bs-secondary-color); font-size: .75rem; }
.price { font-weight: 600; margin-top: .25rem; font-size: .85rem; }

.cell-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
    gap: .5rem;
    padding-right: .75rem;
    min-width: 115px;
}
.qty-wrapper { text-align: right; }
.qty-wrapper .label { font-size: .65rem; text-transform: uppercase; letter-spacing: .5px; color: var(--bs-secondary-color); }
.qty { display: inline-flex; align-items: center; gap: .5rem; }
.qty .btn { width: 34px; padding: .125rem 0; }
.qty-value { min-width: 2rem; text-align: center; font-weight: 600; }


.item-tabs { border: none; }
.item-tabs .nav-link {
    border: none;
    border-radius: 0;
    font-size: .9rem;
    color: #dc3545;
}
.item-tabs .nav-link:hover { background: rgba(220,53,69,.1); }
.item-tabs .nav-link:focus { background: rgba(220,53,69,.18); }
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
                <a href="#" class="d-inline-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../assets/me.jfif" alt="mdo" width="32" height="32" class="rounded-circle">
                </a>
                <ul class="dropdown-menu text-small">
                    <li><a class="dropdown-item" href="#">New project...</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Sign out</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<!-- Go Back line -->
<div class="container mb-3">
    <a href="#" class="back-line rounded-2" onclick="history.back(); return false;">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
        </svg>
        <span>Go Back</span>
    </a>
</div>

<div class="container">
    <div class="cart-list">

        <!-- Item 1 -->
        <div class="card cart-card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs justify-content-end item-tabs">
                    <li class="nav-item">
                        <button type="button" class="nav-link remove-item"  aria-label="Remove item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
</svg></button>
                    </li>
                </ul>
            </div>
            <div class="card-body cart-row">
                <div class="cell-check">
                    <input class="form-check-input" type="checkbox" id="chk-1" aria-label="Select item Smartphone X">
                </div>
                <div class="cell-product">
                    <div class="product-content">
                        <img src="https://picsum.photos/seed/phone/800/800" class="product-img" alt="Smartphone X">
                        <div class="product-meta">
                            <h5 class="h5 pb-0 mb-1">Smartphone X</h5>
                            <div class="brand">Brand: Apple</div>
                            <div class="price">₱42,990</div>
                        </div>
                    </div>
                </div>
                <div class="cell-actions">
                    <div class="qty-wrapper">
                        <div class="label pb-2">Quantity</div>
                        <div class="qty">
                            <button type="button" class="btn btn-outline-secondary btn-sm qty-minus" aria-label="Decrease quantity">-</button>
                            <span class="qty-value" data-qty>1</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm qty-plus" aria-label="Increase quantity">+</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

 

    <div class="d-flex justify-content-end align-items-center gap-2 mb-4 mt-4">

        <span class="me-1">Select items for:</span>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="bulkActionBtn">
                Choose action
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkActionBtn">
                <li><a class="dropdown-item" href="#" data-action="delete">Deletion</a></li>
                <li><a class="dropdown-item" href="#" data-action="checkout">Checkout</a></li>
            </ul>
        </div>
        <button type="button" class="btn btn-primary px-5" id="confirmActionBtn">Confirm</button>
    </div>
</div>

<hr>
    <footer class="container py-lg-4 py-md-4 py-3">
        <div class="row">
            <div class="col-12 col-md">
                <img src="../assets/Header-Logo-01.svg" width="50" height="50" class="d-block mb-2"
                    alt="Carriemart logo">
                <small class="d-block mb-3 text-body-secondary">© CarrieMart - 2025 <br>
                    <br> Made by: <br>
                    Kim Rebamba <br>
                    JM Carutcho</small>
            </div>
            <div class="col-6 col-md">
                <!-- empty div for spacing lol */ -->
            </div>
            <div class="col-6 col-md">
                <h5>Shortcuts</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none" href="#">Resource name</a></li>
                    <li><a class="link-secondary text-decoration-none" href="#">Resource</a></li>
                    <li><a class="link-secondary text-decoration-none" href="#">Another resource</a></li>
                    <li><a class="link-secondary text-decoration-none" href="#">Final resource</a></li>
                </ul>
            </div>
            <div class="col-6 col-md">
                <h5>Resources</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none" href="#">Business</a></li>
                    <li><a class="link-secondary text-decoration-none" href="#">Education</a></li>
                    <li><a class="link-secondary text-decoration-none" href="#">Government</a></li>
                    <li><a class="link-secondary text-decoration-none" href="#">Gaming</a></li>
                </ul>
            </div>
            <div class="col-6 col-md">
                <h5>More on</h5>
                <ul class="list-unstyled text-small">
                    <li><a class="link-secondary text-decoration-none" href="https://github.com/KimRebamba">Github
                            :P</a></li>
                </ul>
            </div>
        </div>
    </footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>