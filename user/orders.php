<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Orders</title>
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

/* Orders layout */
.order-list { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.order-card { background: #fff; border-radius: .5rem; border:1px solid transparent; transition:border-color .15s ease; }
.order-card:hover { border-color: rgba(0,0,0,.2); }

.order-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: .75rem 1rem; border-bottom: 1px solid rgba(0,0,0,.06);
    flex-wrap: wrap; gap: .5rem;
}
.order-id { font-weight: 600; }
.order-date { color: var(--bs-secondary-color); font-size: .875rem; }
/* Header actions (beside Order #...) */
.order-left { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.order-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.order-actions .btn-sm { padding:.25rem .5rem; }
/* Grid: remove right actions column */
.order-grid {
     display: grid;
    grid-template-columns: 1fr;
     gap: 1rem;
     padding: 1rem;
}
@media (max-width: 576px) {
    .order-grid { grid-template-columns: 1fr; }
}

.info-sections { display: grid; gap: .75rem; }
.section-title {
    font-size: .75rem; letter-spacing: .5px; text-transform: uppercase;
    color: var(--bs-secondary-color); font-weight: 600; margin-bottom: .25rem;
}

.product-list { display: grid; gap: .5rem; }
.product-row {
    display: grid;
    grid-template-columns: 2fr 1.2fr 110px 80px 220px; /* Title | Brand | Price | Qty | Actions */
    column-gap: .75rem;
    row-gap: .25rem;
    padding: .5rem .75rem; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: .375rem;
    align-items: center;
}
.product-row .label { color: var(--bs-secondary-color); font-size: .85rem; }
.product-row .price { font-weight: 600; text-align: right; }
.product-row .qty { text-align: right; white-space: nowrap; }
.product-row .title, .product-row .label { min-width: 0; overflow: hidden; text-overflow: ellipsis; }

/* Per-line actions */
.product-actions { display: flex; flex-direction: column; align-items: stretch; gap: .5rem; width: 100%; }
.product-actions .btn { width: 100%; }

@media (max-width: 768px) {
  .product-row { grid-template-columns: 1.7fr 1.2fr 90px 60px 200px; column-gap: .5rem; }
}
@media (max-width: 576px) {
  .product-row { grid-template-columns: 1fr; }
  .product-actions { margin-top: .25rem; }
  .product-row .price, .product-row .qty { text-align: left; }
}

.kv {
    display: grid; grid-template-columns: 180px 1fr; gap: .5rem;
    padding: .5rem .75rem; border: 1px solid #e9ecef; border-radius: .375rem; background: #fcfcfd;
}
.kv .k { color: var(--bs-secondary-color); font-size: .85rem; }
.kv .v { font-weight: 500; }

.summary {
    display: grid; gap: 0;
}
.summary .line {
    display: grid; grid-template-columns: 1fr auto; gap: .75rem;
    padding: .5rem .75rem; border: 1px solid #e9ecef; border-radius: .375rem; background: #fcfcfd;
}
.summary .muted { color: var(--bs-secondary-color); font-size: .875rem; }

.actions {
    display: flex; flex-direction: column; gap: .5rem; align-items: stretch; justify-content: flex-start;
}
.actions .btn { width: 100%; }

.split-two { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
@media (max-width: 576px) { .split-two { grid-template-columns: 1fr; } }
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
    <div class="order-list">

        <!-- Order #1001 -->
        <div class="order-card">
            <div class="order-header">
                <div class="order-left">
                    <div class="order-id">Order #1001</div>
                    <div class="order-actions">
                        <button class="btn btn-primary btn-sm">Edit Delivery Details</button>
                        <button class="btn btn-outline-danger btn-sm">Cancel Order</button>
                    </div>
                </div>
                <div class="order-date">Date Ordered: 2025-11-12 14:32</div>
            </div>
            <div class="order-grid">
                <div class="info-sections">
                    <div>
                        <div class="section-title">Products</div>
                        <div class="product-list">
                            <div class="product-row">
                                <div class="title">Wireless Earbuds Pro</div>
                                <div class="label">SoundMax</div>
                                <div class="price">₱3,495</div>
                                <div class="qty">Qty: 1</div>
                                <div class="product-actions">
                                    <a class="btn btn-outline-secondary btn-sm w-100" href="/carriemart/user/review-details.php?mode=edit&product_order_id=1001-1">Edit Review</a>
                                    <a class="btn btn-outline-danger btn-sm w-100" href="#" onclick="/* TODO: link to return/refund flow */ return false;">Return/Refund</a>
                                </div>
                            </div>
                            <div class="product-row">
                                <div class="title">USB-C Fast Charger 30W</div>
                                <div class="label">Voltix</div>
                                <div class="price">₱899</div>
                                <div class="qty">Qty: 2</div>
                                <div class="product-actions">
                                    <a class="btn btn-outline-secondary btn-sm w-100" href="/carriemart/user/review-details.php?mode=write&product_order_id=1001-2">Add Review</a>
                                    <a class="btn btn-outline-danger btn-sm w-100" href="#" onclick="/* TODO: link to return/refund flow */ return false;">Return/Refund</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="section-title">Delivery</div>
                        <div class="kv"><div class="k">Recipient</div><div class="v">Jane Doe</div></div>
                        <div class="kv"><div class="k">Address</div><div class="v">221B Baker St, Quezon City</div></div>
                        <div class="kv"><div class="k">Phone</div><div class="v">0917-123-4567</div></div>
                    </div>

                    <div class="split-two">
                        <div>
                            <div class="section-title">Payment</div>
                            <div class="kv"><div class="k">Payment Option</div><div class="v">GCash</div></div>
                            <div class="kv"><div class="k">Payment Status</div><div class="v text-success">Paid</div></div>
                            <div class="kv"><div class="k">Order Status</div><div class="v">Shipped</div></div>
                        </div>
                        <div>
                            <div class="section-title">Summary</div>
                            <div class="summary">
                                <div class="line"><span>Delivery Fee</span><span>₱85</span></div>
                                <div class="line"><span>Discount Amount <span class="muted">(USED VOUCHER: SAVE500)</span></span><span>-₱500</span></div>
                                <div class="line"><span>SubTotal</span><span>₱4,793</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order #1002 -->
        <div class="order-card">
            <div class="order-header">
                <div class="order-left">
                    <div class="order-id">Order #1002</div>
                    <div class="order-actions">
                        <button class="btn btn-primary btn-sm">Edit Delivery Details</button>
                        <button class="btn btn-outline-danger btn-sm">Cancel Order</button>
                    </div>
                </div>
                <div class="order-date">Date Ordered: 2025-11-13 09:15</div>
            </div>
            <div class="order-grid">
                <div class="info-sections">
                    <div>
                        <div class="section-title">Products</div>
                        <div class="product-list">
                            <div class="product-row">
                                <div class="title">Mechanical Keyboard 87-Key</div>
                                <div class="label">KeyForge</div>
                                <div class="price">₱4,250</div>
                                <div class="qty">Qty: 1</div>
                                <div class="product-actions">
                                    <a class="btn btn-outline-secondary btn-sm w-100" href="/carriemart/user/review-details.php?mode=write&product_order_id=1002-1">Add Review</a>
                                    <a class="btn btn-outline-danger btn-sm w-100" href="#" onclick="/* TODO: link to return/refund flow */ return false;">Return/Refund</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="section-title">Delivery</div>
                        <div class="kv"><div class="k">Recipient</div><div class="v">John Smith</div></div>
                        <div class="kv"><div class="k">Address</div><div class="v">742 Evergreen Terrace, Manila</div></div>
                        <div class="kv"><div class="k">Phone</div><div class="v">0908-765-4321</div></div>
                    </div>

                    <div class="split-two">
                        <div>
                            <div class="section-title">Payment</div>
                            <div class="kv"><div class="k">Payment Option</div><div class="v">Cash on Delivery</div></div>
                            <div class="kv"><div class="k">Payment Status</div><div class="v text-warning">Pending</div></div>
                            <div class="kv"><div class="k">Order Status</div><div class="v">Processing</div></div>
                        </div>
                        <div>
                            <div class="section-title">Summary</div>
                            <div class="summary">
                                <div class="line"><span>Delivery Fee</span><span>₱120</span></div>
                                <div class="line"><span>Discount Amount <span class="muted">(USED VOUCHER: None)</span></span><span>₱0</span></div>
                                <div class="line"><span>SubTotal</span><span>₱4,250</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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