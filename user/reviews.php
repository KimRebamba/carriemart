<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Reviews</title>
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

/* Orders layout reused for reviews */
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
/* Actions beside order number */
.order-left { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.order-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
.order-actions .btn-sm { padding:.25rem .5rem; }

.order-grid {
    display: grid;
    grid-template-columns: 1fr; /* no right-side actions column */
    gap: 1rem;
    padding: 1rem;
}
@media (max-width: 576px) { .order-grid { grid-template-columns: 1fr; } }

.info-sections { display: grid; gap: .75rem; }
.section-title {
    font-size: .75rem; letter-spacing: .5px; text-transform: uppercase;
    color: var(--bs-secondary-color); font-weight: 600; margin-bottom: .25rem;
}

.kv {
    display: grid; grid-template-columns: 180px 1fr; gap: .5rem;
    padding: .5rem .75rem; border: 1px solid #e9ecef; border-radius: .375rem; background: #fcfcfd;
}
.kv .k { color: var(--bs-secondary-color); font-size: .85rem; }
.kv .v { font-weight: 500; }

.actions { display: flex; flex-direction: column; gap: .5rem; align-items: stretch; }
.actions .btn { width: 100%; }
    </style>
</head>
<body>
<header class="p-3 mb-2 border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="min-height: 48px;">
            <a href="#" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                <img src="../assets/Header-Logo-01.svg" alt="Carriemart logo" width="40" height="40" class="me-2">
            </a>
            <div class="dropdown text-end avatar-dropdown">
                <a href="#" class="d-inline-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="../assets/me.jfif" alt="" width="32" height="32" class="rounded-circle">
                </a>
                <ul class="dropdown-menu text-small">
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

<!-- Filters toolbar -->
<div class="container mb-3">
    <div class="d-flex align-items-center justify-content-start">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm"
                    type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#filtersOffcanvas" aria-controls="filtersOffcanvas">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" class="me-1" aria-hidden="true">
                    <path d="M1.5 1.5h13a.5.5 0 0 1 .39.812L10 8v5.5a.5.5 0 0 1-.79.407l-2-1.333A.5.5 0 0 1 7 12.167V8L1.11 2.312A.5.5 0 0 1 1.5 1.5z"/>
                </svg>
                Filters
            </button>
            <select class="form-select form-select-sm" aria-label="Sort by" style="width: 180px;">
                <option selected>Sort by</option>
                <option value="recent">Most Recent</option>
                <option value="ratingHigh">Highest Rating</option>
                <option value="ratingLow">Lowest Rating</option>
                <option value="productAZ">Product A–Z</option>
                <option value="productZA">Product Z–A</option>
            </select>
        </div>
        <small class="text-muted" style="margin-left: 1rem;">Showing 2 reviews</small>
    </div>
</div>

<!-- Offcanvas: Filters -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Reviews</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="vstack gap-3">
            <div>
                <label class="form-label">Minimum rating</label>
                <select class="form-select">
                    <option value="">Any</option>
                    <option value="5">5 stars</option>
                    <option value="4">4+ stars</option>
                    <option value="3">3+ stars</option>
                </select>
            </div>
            <div>
                <label class="form-label">Product title</label>
                <input type="text" class="form-control" placeholder="Search product">
            </div>
            <div>
                <label class="form-label">Brand</label>
                <input type="text" class="form-control" placeholder="Search brand">
            </div>
            <div>
                <label class="form-label">Date range</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control">
                    <input type="date" class="form-control">
                </div>
            </div>
            <div>
                <label class="form-label">Has text review</label>
                <select class="form-select">
                    <option value="">Any</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div class="order-list">

        <!-- Review for Order #1001 (has review) -->
        <div class="order-card">
            <div class="order-header">
                <div class="order-left">
                    <div class="order-id">Review • Order #1001</div>
                     <div class="order-actions">
                        <a class="btn btn-primary btn-sm" href="/carriemart/user/review-details.php?mode=edit&product_order_id=1001-1">Edit Review Details</a>
                    </div>
                </div>
                <div class="order-date">Date: 2025-11-14 10:22</div>
            </div>
            <div class="order-grid">
                <div class="info-sections">
                    <div class="section-title">Review details</div>

                    <div class="kv"><div class="k">Order number</div><div class="v">#1001</div></div>
                    <div class="kv"><div class="k">Product title</div><div class="v">Wireless Earbuds Pro</div></div>
                    <div class="kv"><div class="k">Brand</div><div class="v">SoundMax</div></div>
                    <div class="kv"><div class="k">Review title</div><div class="v">Great sound for the price</div></div>
                    <div class="kv"><div class="k">Review description</div>
                        <div class="v">Bass is punchy, battery lasts all day, and pairing is instant. Case feels a bit plasticky.</div>
                    </div>
                    <div class="kv"><div class="k">Rating</div><div class="v">★★★★☆ (4/5)</div></div>
                    <div class="kv"><div class="k">Date</div><div class="v">2025-11-14 10:22</div></div>
                </div>

                <!-- actions moved to header -->
            </div>
        </div>

    </div>
</div>

<hr>
<footer class="container py-lg-4 py-md-4 py-3">
    <div class="row">
        <div class="col-12 col-md">
            <img src="../assets/Header-Logo-01.svg" width="50" height="50" class="d-block mb-2" alt="Carriemart logo">
            <small class="d-block mb-3 text-body-secondary">© CarrieMart - 2025 <br><br> Made by: <br> Kim Rebamba <br> JM Carutcho</small>
        </div>
        <div class="col-6 col-md"><!-- empty --></div>
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
                <li><a class="link-secondary text-decoration-none" href="https://github.com/KimRebamba">Github :P</a></li>
            </ul>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>