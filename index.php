<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carriemart: Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>

    .hero-row {
        align-items: stretch;
    }

    .hero-carousel {
        width: 100%;
        max-width: 670px;
        aspect-ratio: 670 / 427;
        /* because its items won't stay fixed size for some reason */
        border-radius: 1rem;
        overflow: hidden;

    }

    @media (min-width: 1400px) {

        /* for return xxl */
        .hero-carousel {
            width: 640px;
        }
    }


    .hero-carousel .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* fills 640×427 - idk its the size of that pexel pic */
    }

    /*to center search and avatar*/
    .search-form {
        flex: 0 1 420px;
    }

    .avatar-dropdown {
        flex-shrink: 0;
    }

    /* make search and avatar share line on <lg */
    @media (max-width: 991.98px) {
        .search-form {
            flex-basis: 70%;
            max-width: 70%;
        }
    }

    @media (min-width: 992px) {
        .search-form {
            flex-basis: 420px;
        }
    }

    .feature-icon-small {
        width: 3rem;
        height: 3rem;
        padding: .25rem;
    }

    .feature-icon-small img {
        display: block;
        width: 70%;
        height: 70%;
        object-fit: contain;
        filter: brightness(0) invert(1);
        /* turn black SVGs white */
    }

    /* marketing */
    .marketing-card {
        position: relative;
        background: url('./assets/sample5.jpg') center/cover no-repeat;
        border-radius: 1rem;
        overflow: hidden;
        display: flex;
        align-items: center;
        min-height: 30vh;
    }

    .marketing-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .60);
    }

    .marketing-card .content {
        position: relative;
        /* above overlay */
        color: #fff;
        width: 100%;
        padding: 1.5rem;
    }

    @media (min-width: 576px) {
        .marketing-card {
            min-height: 45vh;
        }

        .marketing-card .content {
            padding: 2rem;
        }
    }

    @media (min-width: 768px) {
        .marketing-card {
            min-height: 45vh;
        }

        .marketing-card .content {
            padding: 2.5rem;
        }
    }

    @media (min-width: 992px) {
        .marketing-card {
            min-height: 45vh;
        }

        .marketing-card .content {
            padding: 3rem;
        }
    }



    /* center marketing content vertically on ≥ sm */
    @media (min-width: 576px) {
        .marketing-section {
            display: flex;
            align-items: center;
        }

        .marketing-section .content {
            padding-top: 0;
            padding-bottom: 0;
        }
    }

    /* two promo cards */
    .promo-cards-section .promo-card {
        position: relative;
        background: center/cover no-repeat;
        border-radius: 1rem;
        overflow: hidden;
        display: flex;
        align-items: flex-end;

        min-height: 260px;
    }

    .promo-cards-section .promo-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .60);
    }

    .promo-cards-section .promo-card .content {
        position: relative;
        color: #fff;
        width: 100%;
        padding: 1rem 1.25rem;
    }

    @media (min-width: 576px) {
        .promo-cards-section .promo-card {
            min-height: 320px;
        }
    }

    @media (min-width: 768px) {
        .promo-cards-section .promo-card {
            min-height: 380px;
        }
    }

    @media (min-width: 992px) {
        .promo-cards-section .promo-card {
            min-height: 420px;
        }
    }

    .about-card {
        position: relative;
        background: url('./assets/about.png') center/cover no-repeat;
        border-radius: 1rem;
        overflow: hidden;
        display: flex;
        align-items: center;
        min-height: 320px;
    }

    .about-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .45);
    }

    .about-card .content {
        position: relative;
        color: #fff;
        width: 100%;
        padding: 1.5rem;
    }

    @media (min-width: 576px) {
        .about-card {
            min-height: 380px;
        }

        .about-card .content {
            padding: 2rem;
        }
    }

    @media (min-width: 768px) {
        .about-card {
            min-height: 420px;
        }

        .about-card .content {
            padding: 2.5rem;
        }
    }

    @media (min-width: 992px) {
        .about-card {
            min-height: 460px;
        }

        .about-card .content {
            padding: 3rem;
        }
    }

    /* partnerships logos */
    .brand-logo {
        max-height: 120px;
        width: auto;
        display: block;
        filter: grayscale(100%);
        opacity: .85;
        transition: filter .2s ease, opacity .2s ease, transform .2s ease;
    }

    .brand-logo:hover,
    .brand-logo:focus-visible {
        filter: none;
        opacity: 1;
        transform: scale(1.02);
    }

    
    </style>
</head>

<body>
    <header class="p-3 mb-3 border-bottom">
        <div class="container">

            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start"> <a
                    href="#" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                    <img src="./assets/Header-Logo-01.svg" alt="Carriemart logo" width="40" height="40" class="me-2">
                </a>
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="#" class="nav-link px-2 link-secondary">Home</a></li>
                    <li><a href="#" class="nav-link px-2 link-body-emphasis">Categories</a></li>
                    <li><a href="#" class="nav-link px-2 link-body-emphasis">Products</a></li>
                    <li><a href="#" class="nav-link px-2 link-body-emphasis">Vouchers</a></li>
                </ul>

                <form class="search-form d-flex mb-0 me-2 me-lg-3" role="search">
                    <input type="search" class="form-control w-100" placeholder="Search..." aria-label="Search">
                </form>

                <!-- profile pic user dropdown -->
                <div class="dropdown text-end avatar-dropdown align-self-center">
                    <a href="#"
                        class="d-inline-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="./assets/me.jfif" alt="mdo" width="32" height="32" class="rounded-circle">
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

    <!-- hero -->
    <div class="container col-xxl-8 px-4 pt-3 pb-0 pt-lg-4 pb-lg-0">
        <div class="row hero-row g-4 g-lg-5">
            <div class="col-lg-6">
                <h1 class="display-5 fw-bold text-body-emphasis lh-1 mb-3">The Best Music Store in Taguig</h1>
                <p class="lead">Listening to Clair De Lune as you gaze at the beautiful moon outside your window?
                    Add to the mood with your own musical instrument. Whether it be a piano, guitar, or just your voice!
                    Make your own vibe here, in CarrieMart. :)</p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a href="./login.php"><button type="button" class="btn btn-primary btn-lg px-4 me-md-2">Sign in</button></a>
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4">Look around :P</button>
                </div>
            </div>

            <!-- carousel column second -->
            <div class="col-12 col-lg-6 d-flex">
                <div class="hero-carousel mx-auto mx-lg-0">
                    <div id="carouselExampleIndicators" class="carousel slide">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                                class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                aria-label="Slide 3"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="./assets/keyboard.jpg" alt="...">
                            </div>
                            <div class="carousel-item">
                                <img src="./assets/piano.jpg" alt="...">
                            </div>
                            <div class="carousel-item">
                                <img src="./assets/mic.jpg" alt="...">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- two-up promo cards -->
    <section class="promo-cards-section text-center mt-3 mt-lg-5">
        <div class="container col-xxl-8 px-4">
            <div class="row g-4">
                <div class="col-12 col-md-6 d-flex">
                    <div class="promo-card w-100" style="background-image:url('./assets/sample2.jpg');">
                        <div class="content">
                            <h2 class="display-0 fw-bold mb-1">What you see, what you'll get</h2>
                            <p class="lead mb-0 text-white-90">All products are 100% verified & tested.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 d-flex">
                    <div class="promo-card w-100" style="background-image:url('./assets/sample3.jpg');">
                        <div class="content">
                            <h2 class="display-0 fw-bold mb-1">Honesty? That's DMajor here!</h2>
                            <p class="lead mb-0 text-white-90">Don't be afraid to tell us what's good or bad.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- marketing -->
    <section class="marketing-section text-center mt-3 mt-lg-5">
        <div class="container col-xxl-8 px-4">
            <div class="marketing-card">
                <div class="content">
                    <h1 class="display-5 fw-bold mb-1">Play "Master Of Puppets" by Metallica..</h1>
                    <p class="lead mb-0 text-white-90">or any song.. with our <em>*state-of-the-art*</em> instruments in
                        store.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- partnerships -->
    <section class="mt-4 mt-lg-4 pt-0 mb-0">
        <div class="container col-xxl-8 px-4">
            <h2 class="pb-3 border-bottom display-0 mb-1" style="text-align:center;">Trusted by musicians and major brands across the Philippines</h2>
            <div class="row justify-content-center text-center g-4 border-bottom">
                <div class="col-6 col-sm-4 col-md-3 pt-3 pb-4 d-flex justify-content-center">
                    <img src="./assets/me.jfif" alt="Partner 1" class="brand-logo">
                </div>
                <div class="col-6 col-sm-4 col-md-3 pt-3 pb-4 d-flex justify-content-center">
                    <img src="./assets/me.jfif" alt="Partner 1" class="brand-logo">
                </div>
                <!-- add more logos, db-->
            </div>
        </div>
    </section>

    <!-- features -->
    <div class="container px-4 pt-2 pb-1 pt-lg-2 pb-lg-0">
        <div class="row row-cols-1 row-cols-md-2 align-items-md-center g-4 g-lg-5 pt-4">

            <div class="col d-flex flex-column align-items-start gap-2">
                <h2 class="fw-semibold text-body-emphasis">Register now for more features! No tracking. Shop safely, as you
                    should. <em>- our web dev guy</em></h2>
                <p class="text-body-secondary">You probably won't be able to checkout items when you don't have an
                    account but hey that's security, baby!</p> <a href="#" class="btn btn-primary btn-lg">Create an
                    account</a>
            </div>
            <div class="col">
                <div class="row row-cols-1 row-cols-sm-2 g-4">
                    <div class="col d-flex flex-column gap-2">
                        <div
                            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
                            <img src="./assets/bug.svg" alt="Admin Panel icon">
                        </div>
                        <h4 class="fw-semibold mb-0 text-body-emphasis">Admin Panel</h4>
                        <p class="text-body-secondary">CRUD transactions, website info, and more.</p>
                    </div>

                    <div class="col d-flex flex-column gap-2">
                        <div
                            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
                            <img src="./assets/bag-fill.svg" alt="Cart System icon">
                        </div>
                        <h4 class="fw-semibold mb-0 text-body-emphasis">Cart System</h4>
                        <p class="text-body-secondary">Register, add-to-cart, and checkout your items today.</p>
                    </div>

                    <div class="col d-flex flex-column gap-2">
                        <div
                            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
                            <img src="./assets/briefcase.svg" alt="Employee Panel icon">
                        </div>
                        <h4 class="fw-semibold mb-0 text-body-emphasis">Employee Panel</h4>
                        <p class="text-body-secondary">If you're an employee here, hello fellow carriemite!</p>
                    </div>

                    <div class="col d-flex flex-column gap-2">
                        <div
                            class="feature-icon-small d-inline-flex align-items-center justify-content-center text-bg-primary bg-gradient fs-4 rounded-3">
                            <img src="./assets/box-seam.svg" alt="Refunds icon">
                        </div>
                        <h4 class="fw-semibold mb-0 text-body-emphasis">Refunds!!!</h4>
                        <p class="text-body-secondary">In case something goes wrong, return products before shipping.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- about post -->
    <section class="about-section mt-3 mt-lg-4 pt-0 pb-4">
        <div class="container col-xxl-8 px-4">
            <div class="about-card">
                <div class="content">
                    <h2 class="display-6 fw-bold mb-2">Online 24/7, 1-5 pm Daily!</h2>
                    <p class="lead mb-3 text-white-90">Address: 123 Mabini Street, Barangay San Isidro, Taguig City,
                        Metro Manila, 1100 <br>
                        Email: support@carriemart.ph <br>
                        Phone: +63 917 654 3210</p>
                    <p class="lead mb-0">
                        <a href="#" class="link-light fw-bold text-decoration-none">
                            Back-to-top
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor"
                                class="bi bi-chevron-right ms-1" viewBox="0 0 16 16" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M6.146 3.646a.5.5 0 0 1 .708 0L10.354 7.146a.5.5 0 0 1 0 .708L6.854 11.646a.5.5 0 1 1-.708-.708L9.293 8 6.146 4.354a.5.5 0 0 1 0-.708z" />
                            </svg>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </section>


    <!--footer-->
    <hr>
    <footer class="container py-5">
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
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>