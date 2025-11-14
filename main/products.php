<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Products</title>
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
            width: 20px; height: 20px; opacity: .9;
        }

        /* Grid */
        .product-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(2, minmax(0, 1fr)); 
        }
        @media (min-width: 768px) { .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 992px) { .product-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        /* Card */
        .product-card {
            position: relative; 
            border: 1px solid transparent;
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            transition: border-color .2s ease, transform .2s ease, background-color .2s ease;
            cursor: pointer; /* click!! */
        }
        .product-card:hover {
            border-color: rgba(0,0,0,.15);
            transform: translateY(-3px);
        }

        .brand-link,
        .brand-link:hover,
        .brand-link:focus,
        .brand-link:active {
            text-decoration: none;
            color: inherit;
        }

        .brand-link:hover{
            text-decoration: underline;
        }
        /* Prevent pointer not clicking to specific details - annoying */
        .product-card button,
        .product-card a.brand-link { position: relative; z-index: 2; }
        .product-card .stretched-link { z-index: 1; } /* IMPORTANT - BOOTSTRAP HIDDEN LINK */

        .product-img {
            width: 100%;
            height: 200px;
            aspect-ratio: 3 / 2; 
            object-fit: cover;
            border-radius: .9rem;
            display: block;
        }
        .price {
            font-size: 1.2rem;
            font-weight: 700;
        }
        .rating { display:inline-flex; align-items:center; }
        .rating .star { width:14px; height:14px; }
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
        <a href="#" class="back-line rounded-2"
           onclick="history.back(); return false;">
            <svg class="icons" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
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
                    <option value="popular">Most Popular</option>
                    <option value="rating">Highest Rated</option>
                    <option value="priceLow">Price: Low to High</option>
                    <option value="priceHigh">Price: High to Low</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
            <small class="text-muted" style="margin-left: 1rem;">Showing 25 products</small>
        </div>
    </div>

    <!-- Offcanvas: Filters -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel" data-bs-scroll="true">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filters</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form class="vstack gap-3">
                <div>
                    <label class="form-label">Category</label>
                    <select class="form-select">
                        <option>All</option>
                        <option>Clothing</option>
                        <option>Electronics</option>
                        <option>Home</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Brand</label>
                    <input type="text" class="form-control" placeholder="Search brand">
                </div>
                <div>
                    <label class="form-label">Price range</label>
                    <div class="d-flex gap-2">
                        <input type="number" class="form-control" placeholder="Min">
                        <input type="number" class="form-control" placeholder="Max">
                    </div>
                </div>
                <div>
                    <label class="form-label">Minimum rating</label>
                    <select class="form-select">
                        <option>Any</option>
                        <option>4.5+</option>
                        <option>4.0+</option>
                        <option>3.5+</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="button" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Product grid -->
    <div class="container pb-5">
        <div class="product-grid">
            <!-- Card 1 -->
            <div class="product-card">
                <img class="product-img" src="../assets/pro1.webp" alt="Wireless Headphones">
                <h6 class="mt-2 mb-1">Wireless Headphones</h6>
                <div class="d-flex align-items-center justify-content-between mb-2 small">
                    <span class="text-muted">Brand:
                        <a href="brand.php?name=AudioMax" class="brand-link">Roland</a>
                    </span>
                    <span class="rating fw-semibold d-inline-flex align-items-center gap-1">
                        <span>4.8</span>
                        <svg class="star" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                            <path d="M3.612 15.443 4.6 9.97.825 6.765l5.059-.736L8 1.5l2.116 4.529 5.059.736L11.4 9.97l.988 5.473L8 12.897l-4.388 2.546z"/>
                        </svg>
                    </span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="price">$129.99</span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm flex-grow-1">Buy Now</button>
                    <button class="btn btn-outline-secondary btn-sm flex-grow-1">Add to Cart</button>
                </div>
                <a href="./details.php" class="stretched-link" aria-label="View Wireless Headphones"></a>
            </div>

        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>
</html>