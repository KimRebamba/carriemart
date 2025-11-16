<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>CarrieMart: Returns</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<style>
.back-line {display:flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border-bottom:1px solid var(--bs-border-color);color:var(--bs-body-color);text-decoration:none;}
.back-line:hover {background-color:rgba(var(--bs-primary-rgb), .06);text-decoration:none;}
.back-line .icon {width:20px;height:20px;opacity:.9;}

.order-list {display:grid;grid-template-columns:1fr;gap:1rem;}
.order-card {background:#fff;border-radius:.5rem;border:1px solid transparent;transition:border-color .15s ease;}
.order-card:hover {border-color:rgba(0,0,0,.2);}
.order-header {display:flex;justify-content:space-between;align-items:center;padding:.75rem 1rem;border-bottom:1px solid rgba(0,0,0,.06);flex-wrap:wrap;gap:.5rem;}
.order-left {display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.order-actions {display:flex;gap:.5rem;flex-wrap:wrap;}
.order-actions .btn-sm {padding:.25rem .5rem;}
.order-id {font-weight:600;}
.order-date {color:var(--bs-secondary-color);font-size:.875rem;}

.order-grid {display:grid;grid-template-columns:1fr;gap:1rem;padding:1rem;}
.info-sections {display:grid;gap:.75rem;}
.section-title {font-size:.75rem;letter-spacing:.5px;text-transform:uppercase;color:var(--bs-secondary-color);font-weight:600;margin-bottom:.25rem;}
.kv {display:grid;grid-template-columns:180px 1fr;gap:.5rem;padding:.5rem .75rem;border:1px solid #e9ecef;border-radius:.375rem;background:#fcfcfd;}
.kv .k {color:var(--bs-secondary-color);font-size:.85rem;}
.kv .v {font-weight:500;}

.status-badge {font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;padding:.25rem .45rem;border-radius:.35rem;display:inline-block;}
.status-requested {background:#fff3cd;color:#856404;border:1px solid #ffe8a1;}
.status-approved {background:#d1e7dd;color:#0f5132;border:1px solid #badbcc;}
.status-rejected {background:#f8d7da;color:#842029;border:1px solid #f5c2c7;}
.status-processed {background:#cfe2ff;color:#084298;border:1px solid #b6d4fe;}

.product-list {display:grid;gap:.5rem;}
.product-row {
  display:grid;
  grid-template-columns: 2fr 1.1fr 90px 1.4fr 140px 130px 110px; 
  column-gap:.75rem; row-gap:.25rem;
  padding:.5rem .75rem; background:#f8f9fa; border:1px solid #e9ecef; border-radius:.375rem; align-items:center;
}
.product-row .qty {text-align:center; white-space:nowrap;}
.product-row .amount {text-align:right; font-weight:600;}
.product-row .title,.product-row .label,.product-row .reason {min-width:0; overflow:hidden; text-overflow:ellipsis;}

.order-items { display:grid; gap:.5rem; }
.order-item {
  display:grid;
  grid-template-columns: 2fr 1fr 80px; 
  column-gap:.75rem; row-gap:.25rem;
  padding:.5rem .75rem; background:#f8f9fa; border:1px solid #e9ecef; border-radius:.375rem; align-items:center;
}
.order-item .title, .order-item .label { min-width:0; overflow:hidden; text-overflow:ellipsis; }
.order-item .qty { text-align:right; white-space:nowrap; }

.order-items-header{
  display:grid;
  grid-template-columns: 2fr 1fr 80px; 
  column-gap:.75rem;
  padding:.25rem .75rem;
  color: var(--bs-secondary-color);
  font-size:.75rem; text-transform:uppercase; letter-spacing:.5px; font-weight:600;
}

@media (max-width:576px){
  .order-item{ grid-template-columns:1fr; }
  .order-item .qty{ text-align:left; }
  .order-items-header{ display:none; }
  .order-item [data-label]::before{
    content: attr(data-label) ": ";
    display:block;
    color: var(--bs-secondary-color);
    font-size:.8em;
    margin-bottom:2px;
    font-weight:600;
    letter-spacing:.3px;
  }
}

@media (max-width: 768px){
  .product-row {grid-template-columns: 1.8fr 1fr 70px 1.2fr 130px 120px 100px; column-gap:.5rem;}
}
@media (max-width: 576px){
  .product-row {grid-template-columns: 1fr; }
  .product-row .amount {text-align:left;}
}
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

<div class="container mb-3">
    <a href="#" class="back-line rounded-2" onclick="history.back();return false;">
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
                <option value="amountHigh">Amount: High to Low</option>
                <option value="amountLow">Amount: Low to High</option>
                <option value="status">Status</option>
                <option value="orderAZ">Order # A–Z</option>
            </select>
        </div>
        <small class="text-muted" style="margin-left: 1rem;">Showing 2 returns</small>
    </div>
</div>

<!-- Offcanvas: Filters -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filter Returns</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form class="vstack gap-3">
            <div>
                <label class="form-label">Return status</label>
                <select class="form-select">
                    <option value="">Any</option>
                    <option value="requested">Requested</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="processed">Processed</option>
                </select>
            </div>
            <div>
                <label class="form-label">Order number</label>
                <input type="text" class="form-control" placeholder="#1001">
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
                <label class="form-label">Min return amount</label>
                <input type="number" class="form-control" step="0.01" placeholder="0.00">
            </div>
            <div class="d-grid">
                <button type="button" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div class="order-list">
        <div class="order-card">
            <div class="order-header">
                <div class="order-left">
                    <div class="order-id">Return • Order #1001</div>
                    <div class="order-actions">
                        <a class="btn btn-primary btn-sm" href="/carriemart/user/return-details.php?mode=edit&product_return_id=2001">Edit Return Details</a>
                        <a class="btn btn-danger btn-sm" href="/carriemart/user/return-details.php?mode=edit&product_return_id=2001">Cancel Return</a>
                    </div>
                </div>
                <div class="order-date">Date: 2025-11-15 09:40</div>
            </div>
            <div class="order-grid">
                <div class="info-sections">
                    <div class="order-items-header py-0">Return details</div>

                    <div class="kv"><div class="k">Order number</div><div class="v">#1001</div></div>

                    <div>    
                    
                        <div class="order-items-header mb-1">
                            <div>Product</div>
                            <div>Brand</div>
                            <div class="text-end">Qty</div>
                        </div>
                        <div class="order-items">
                            <div class="order-item">
                                <div class="title" data-label="Product">Wireless Earbuds Pro</div>
                                <div class="label" data-label="Brand">SoundMax</div>
                                <div class="qty" data-label="Qty returned">x1</div>
                            </div>
                            <div class="order-item">
                                <div class="title" data-label="Product">USB-C Fast Charger 30W</div>
                                <div class="label" data-label="Brand">Voltix</div>
                                <div class="qty" data-label="Qty returned">x1</div>
                            </div>
                        </div>
                    </div>

                    <div class="kv"><div class="k">Reason</div><div class="v">Items arrived with defects; wrong model for charger</div></div>
                    <div class="kv">
                        <div class="k">Condition</div>
                        <div class="v">
                            <select class="form-select form-select-sm" disabled>
                                <option>new</option>
                                <option>opened</option>
                                <option selected>damaged</option>
                                <option>other</option>
                            </select>
                        </div>
                    </div>
                    <div class="kv">
                        <div class="k">Return status</div>
                        <div class="v"><span class="status-badge status-approved">Approved</span></div>
                    </div>
                    <div class="kv"><div class="k">Return amount</div><div class="v">₱4,394.00</div></div>

                    <div class="kv"><div class="k">Date</div><div class="v">2025-11-15 09:40</div></div>
                </div>
            </div>
        </div>

    </div>
</div>

<hr>
<footer class="container py-lg-4 py-md-4 py-3">
    <div class="row">
        <div class="col-12 col-md">
            <img src="../assets/Header-Logo-01.svg" width="50" height="50" class="d-block mb-2" alt="">
            <small class="d-block mb-3 text-body-secondary">© CarrieMart - 2025<br><br>Made by:<br>Kim Rebamba<br>JM Carutcho</small>
        </div>
        <div class="col-6 col-md"></div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>