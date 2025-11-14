<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>

    .back-line {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem .75rem;
        border-bottom: 1px solid var(--bs-border-color);
        color: var(--bs-body-color);
        text-decoration: none;
    }
    .back-line:hover { background-color: rgba(var(--bs-primary-rgb), .06); text-decoration: none; }
    .back-line .icon { width: 20px; height: 20px; opacity: .9; }

    .section { padding-block: 1rem; }
    @media (min-width: 992px){ .section { padding-block:1.25rem; } }

    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .5rem;
    }
    .gallery-grid .hero {
        grid-column: 1 / span 3;
        aspect-ratio: 16 / 9;
        border-radius: 1rem;
        overflow: hidden;
    }
    .gallery-grid .thumb {
        aspect-ratio: 4 / 3;
        border-radius: .75rem;
        overflow: hidden;
    }
    .gallery-grid img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

  
    .equal-row { align-items: stretch; }
    .equal-col { display: flex; }
    .equal-fill { height:100%; width:100%; }
    .carousel-inner, .carousel-item { height:100%; }
    .carousel-item img { height:100%; object-fit:cover; }

    .star { width:18px; height:18px; color:#ffb400; }
    .link-plain, .link-plain:hover, .link-plain:focus { color:inherit; text-decoration:none; }

    .qty-wrap { max-width:220px; }
    .cta-wrap { margin-top:1.25rem; }
    .cta-wrap .btn-primary { width:100%; padding:.75rem 1rem; font-size:1.05rem; }

  
    .section-divider {
        margin: 2rem auto;
        max-width: var(--cm-container, 1140px);
        width: 100%;
    }
    .section-divider hr {
        margin: 0;
    }
    @media (min-width:1200px){
        .section-divider { --cm-container: 1140px; }
    }

    .section1-row { --bs-gutter-x:3rem; --bs-gutter-y:1.5rem; } 

   
    .info-dropdowns .dropdown { position: relative; }
    .info-dropdowns .dropdown-toggle {
        width: 100%;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
    }
    .info-dropdowns .dropdown-menu {
        width: 100%;
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: .25rem;
        border-radius: .75rem;
        border: 1px solid black;
    }
    .info-dropdowns .dropdown-menu .menu-text {
        font-size: .875rem;
        line-height: 1.3;
        white-space: normal;
    }
    .info-dropdowns .dropdown-menu .heading {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        opacity: .6;
        margin-bottom: .25rem;
    }
    .info-dropdowns .dropdown + .dropdown { margin-top: .5rem; }
    @media (min-width: 992px){
        .info-dropdowns { min-height: 100%; width: auto;}
    }

    @media (min-width: 992px) {
        .section-2-row { align-items: flex-start; }  
        .carousel-lock { height: 420px; width: 533px;}/*very dumb way to align width*/ 
    }
    @media (max-width: 991.98px) {
        .carousel-lock { height: 420px; width: auto; 
        }
    }
    @media (min-width: 1000px) {
        .carousel-lock { height: 420px; width: 443px; 
        }
    }

    @media (min-width: 1200px) {
        .carousel-lock { height: 420px; width: 533px; 
        }
    }
    .carousel-lock .carousel-inner,
    .carousel-lock .carousel-item { height: 100%; width: auto;}
    .carousel-lock .carousel-item img { height: 100%; object-fit: cover; }


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
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            <span>Go Back</span>
        </a>
    </div>

    <!-- SECTION 1 -->
    <section class="container section">
        <div class="row section1-row align-items-start">
            <div class="col-12 col-lg-6">
                <div class="gallery-grid">
                    <figure class="hero m-0">
                        <img src="https://picsum.photos/seed/headphones-main/960/540" alt="Main photo">
                    </figure>
                    <figure class="thumb m-0">
                        <img src="https://picsum.photos/seed/headphones-side/400/300" alt="Side view">
                    </figure>
                    <figure class="thumb m-0">
                        <img src="https://picsum.photos/seed/headphones-close/400/300" alt="Close-up">
                    </figure>
                    <figure class="thumb m-0">
                        <img src="https://picsum.photos/seed/headphones-accessories/400/300" alt="Accessories">
                    </figure>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <h1 class="h3 fw-bold mb-2">Wireless Headphones</h1>
                <div class="mb-2 small">
                    <span class="text-muted">Brand:
                        <a href="./brand.php?name=AudioMax" class="link-plain">AudioMax</a>
                    </span>
                    <span class="ms-3 d-inline-flex align-items-center fw-semibold">
                        4.8
                        <svg class="star ms-1" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                            <path d="M3.612 15.443 4.6 9.97.825 6.765l5.059-.736L8 1.5l2.116 4.529 5.059.736L11.4 9.97l.988 5.473L8 12.897l-4.388 2.546z"/>
                        </svg>
                    </span>
                </div>
                <div class="mb-2 d-flex align-items-center">
    <span class="badge text-bg-success fw-normal">Stock: 120</span>
</div>
                <div class="h4 mb-3">$129.99</div>

                <div class="qty-wrap mb-2">
                    <label class="form-label small mb-1">Quantity</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button" onclick="const i=this.nextElementSibling;i.stepDown();">-</button>
                        <input type="number" class="form-control text-center" value="1" min="1">
                        <button class="btn btn-outline-secondary" type="button" onclick="const i=this.previousElementSibling;i.stepUp();">+</button>
                    </div>
                </div>

                <div class="cta-wrap">
                    <button class="btn btn-primary">Add to Cart</button>
                </div>
            </div>
        </div>
    </section>

  
    <div class="section-divider"><hr></div>

    <!-- SECTION 2 -->
    <section class="container section">
        <div class="row g-4 section-2-row">
            <div class="col-12 col-lg-6">
                <div id="prodCarousel" class="carousel slide carousel-lock" data-bs-ride="false" data-bs-interval="false">
                    <div class="carousel-inner rounded-3 overflow-hidden">
                        <div class="carousel-item active">
                            <img src="https://picsum.photos/seed/headphones-main/960/540" class="d-block w-100" alt="Slide 1">
                        </div>
                        <div class="carousel-item">
                            <img src="https://picsum.photos/seed/headphones-side/960/540" class="d-block w-100" alt="Slide 2">
                        </div>
                        <div class="carousel-item">
                            <img src="https://picsum.photos/seed/headphones-close/960/540" class="d-block w-100" alt="Slide 3">
                        </div>
                        <div class="carousel-item">
                            <img src="https://picsum.photos/seed/headphones-accessories/960/540" class="d-block w-100" alt="Slide 4">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#prodCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#prodCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="accordion accordion-flush w-100" id="productFlushAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingCat">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseCat" aria-expanded="false" aria-controls="flush-collapseCat">
                                Categories
                            </button>
                        </h2>
                        <div id="flush-collapseCat" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingCat">
                            <div class="accordion-body">Headphones, Audio, Wireless</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingBrand">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseBrand" aria-expanded="false" aria-controls="flush-collapseBrand">
                                Brand
                            </button>
                        </h2>
                        <div id="flush-collapseBrand" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingBrand">
                            <div class="accordion-body">AudioMax</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingModel">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseModel" aria-expanded="false" aria-controls="flush-collapseModel">
                                Model
                            </button>
                        </h2>
                        <div id="flush-collapseModel" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingModel">
                            <div class="accordion-body">AX-500 Pro</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingDesc">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseDesc" aria-expanded="false" aria-controls="flush-collapseDesc">
                                Description
                            </button>
                        </h2>
                        <div id="flush-collapseDesc" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingDesc">
                            <div class="accordion-body">Comfortable over-ear wireless headphones with ANC and 40h battery.</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingSpec">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseSpec" aria-expanded="false" aria-controls="flush-collapseSpec">
                                Specifications
                            </button>
                        </h2>
                        <div id="flush-collapseSpec" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingSpec">
                            <div class="accordion-body">Bluetooth 5.3, USB‑C, 20–20kHz, 40mm drivers, AAC/SBC.</div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingSupp">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseSupp" aria-expanded="false" aria-controls="flush-collapseSupp">
                                Supplier
                            </button>
                        </h2>
                        <div id="flush-collapseSupp" class="accordion-collapse collapse"
                             data-bs-parent="#productFlushAccordion" aria-labelledby="flush-headingSupp">
                            <div class="accordion-body">CarrieMart Fulfillment</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="section-divider"><hr></div>

    <!-- SECTION 3 -->
    <section class="container section rating-reviews">
        <div class="row g-4">
           
            <div class="col-12 col-lg-4">
                <h3 class="h5 fw-bold mb-2">Rating</h3>
                <div class="d-flex align-items-center">
                    <span class="display-6 fw-bold">4.8</span>
                    <svg class="star ms-2" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                        <path d="M3.612 15.443 4.6 9.97.825 6.765l5.059-.736L8 1.5l2.116 4.529 5.059.736L11.4 9.97l.988 5.473L8 12.897l-4.388 2.546z"/>
                    </svg>
                </div>
                <div class="text-muted">1,245 reviews</div>

                <div class="d-flex align-items-center gap-2 mt-3">
                    
                <select class="form-select form-select-sm" aria-label="Sort by" style="width: 180px;">
                    <option selected>Sort by</option>
                    <option value="popular">Most Popular</option>
                    <option value="rating">Highest Rated</option>
                    <option value="priceLow">Price: Low to High</option>
                    <option value="priceHigh">Price: High to Low</option>
                    <option value="newest">Newest</option>
                </select>
            </div>
            </div>

            <!-- List + pagination -->
            <div class="col-12 col-lg-8">
                <div class="list-group mb-3">
                    <div class="list-group-item d-flex gap-3 py-3">
                        <img src="https://picsum.photos/seed/u1/64/64" class="rounded-circle flex-shrink-0" width="32" height="32" alt="">
                        <div class="d-flex w-100 justify-content-between">
                            <div>
                                <h6 class="mb-1">Great sound and battery</h6>
                                <p class="mb-0 text-muted">Comfortable to wear, ANC is decent for the price.</p>
                            </div>
                            <small class="text-nowrap text-muted">2d</small>
                        </div>
                    </div>
                    <div class="list-group-item d-flex gap-3 py-3">
                        <img src="https://picsum.photos/seed/u2/64/64" class="rounded-circle flex-shrink-0" width="32" height="32" alt="">
                        <div class="d-flex w-100 justify-content-between">
                            <div>
                                <h6 class="mb-1">Solid build</h6>
                                <p class="mb-0 text-muted">Pairs quickly and holds charge well.</p>
                            </div>
                            <small class="text-nowrap text-muted">1w</small>
                        </div>
                    </div>
                </div>

                <nav class="d-flex justify-content-end" aria-label="Reviews pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled"><span class="page-link">Previous</span></li>                        
                        <li class="page-item"><a class="page-link" href="#rating-reviews">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </section>

    
    <div class="section-divider"><hr></div>
    <footer class="container py-lg-5 py-md-4 py-3">
        <div class="row">
            <div class="col-12 col-md">
                <img src="./assets/Header-Logo-01.svg" width="50" height="50" class="d-block mb-2"
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


<!-- $limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM reviews ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

SELECT * FROM reviews
ORDER BY created_at DESC
LIMIT 10 OFFSET 0;   -- first 10
-->
</html>