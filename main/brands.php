<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Brands</title>
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
            cursor: pointer;
        }
        .product-card:hover {
            border-color: rgba(0,0,0,.15);
            transform: translateY(-3px);
        }

        .product-card .stretched-link { z-index: 1; }

        .product-img {
            width: 100%;
            height: 200px;
            aspect-ratio: 3 / 2; 
            object-fit: cover;
            border-radius: .9rem;
            display: block;
        }
    </style>
</head>
<body>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/secondary-header.php'); ?>

    <!-- Go Back line -->
    <div class="container mb-3">
        <a href="#" class="back-line rounded-2"
           onclick="history.back(); return false;">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            <span>Go Back</span>
        </a>
    </div>

    <div class="container mb-3">
        <div class="d-flex align-items-center justify-content-start">
            <small class="text-muted" style="">Showing 8 brands</small>
        </div>
    </div>
    
    <!-- Brands grid -->
    <div class="container pb-2">
        <div class="product-grid">
            <!-- Brand: Apple -->
            <div class="product-card">
                <img class="product-img" src="https://picsum.photos/seed/apple/600/400" alt="Apple brand image">
                <h6 class="mt-2 mb-1">Apple</h6>
                <a class="small text-decoration-none" href="https://www.apple.com" target="_blank" rel="noopener">https://www.apple.com</a>
                <p class="text-muted small mb-0 mt-1">Consumer electronics, software, and services.</p>
                <a href="./products.php" class="stretched-link" rel="noopener" aria-label="Visit Apple"></a>
            </div>
        
        </div>
    </div>

     <?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>