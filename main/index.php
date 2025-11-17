<?php
session_start();

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Main</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .about-card {
            position: relative;
            background: url('../assets/about.png') center/cover no-repeat;
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

        .tiles-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: 1fr;          
            grid-auto-rows: 200px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .tile {
            position: relative;
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 1rem;
            overflow: hidden;
            background: #d9d9d9 center/cover no-repeat;
            text-decoration: none;
            transition: transform .25s ease, filter .25s ease;
        }
        .tile::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,.12), rgba(0,0,0,.45));
            opacity: .9;
            transition: opacity .25s ease;
        }
        .tile:hover { transform: scale(1.02); filter: saturate(1.08) brightness(1.05); }
        .tile:hover::before { opacity: .75; }

        .caption {
            position: absolute;
            left: 1.25rem;
            right: 1.25rem;
            bottom: 1.25rem;
            color: #fff;
        }
        .caption .title {
            font-weight: 700;
            font-size: clamp(1.25rem, 0.9rem + 1.5vw, 2.1rem);
            line-height: 1.1;
            margin: 0 0 .15rem 0;
        }
        .caption .desc {
            color: rgba(255,255,255,.9);
            font-weight: 300;
            font-size: 1.05rem;
            margin: 0;
        }

     
        @media (min-width: 576px) {
            .tiles-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-auto-rows: 220px;
            }
            .tile--lg {
                grid-column: 1 / span 2;
            }
        }

        
        @media (min-width: 992px) {
            .tiles-grid {
                grid-template-columns: repeat(3, 1fr);
                grid-template-rows: 300px 220px;
            }
            .tile--lg {
                grid-column: 1 / span 2;
                grid-row: 1;
            }
            .tile--vouchers { grid-column: 3; grid-row: 1; }
            .tile--categories { grid-column: 1; grid-row: 2; }
            .tile--brands { grid-column: 2; grid-row: 2; }
            .tile--cart { grid-column: 3; grid-row: 2; }
        }

       
        @media (min-width: 1200px) {
            .tiles-grid { grid-auto-rows: 240px; }
        }
    </style>
</head>
<body>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/secondary-header.php'); ?>

    
    <!-- products grid -->
    <section class="mt-3 mt-lg-4 pt-0 pb-4">
        <div class="container px-4">
            <div class="tiles-grid">
                <a href="./products.php" class="tile tile--lg" style="background-image:url('../assets/products.jpg');">
                    <div class="caption">
                        <div class="title">Products</div>
                        <div class="desc">Browse and manage product listings.</div>
                    </div>
                </a>
                <a href="./vouchers.php" class="tile tile--vouchers" style="background-image:url('../assets/vouchers.jpg');">
                    <div class="caption">
                        <div class="title">Vouchers</div>
                        <div class="desc">Create and track discount vouchers.</div>
                    </div>
                </a>
                <a href="./categories.php" class="tile tile--categories" style="background-image:url('../assets/categories.jpg');">
                    <div class="caption">
                        <div class="title">Categories</div>
                        <div class="desc">Organize products by category.</div>
                    </div>
                </a>
                <a href="./brands.php" class="tile tile--brands" style="background-image:url('../assets/brands.jpg');">
                    <div class="caption">
                        <div class="title">Brands</div>
                        <div class="desc">Manage brand profiles and assets.</div>
                    </div>
                </a>
                <a href="/carriemart/user/cart/cart.php" class="tile tile--cart" style="background-image:url('../assets/cart.jpg');">
                    <div class="caption">
                        <div class="title">CarrieCart</div>
                        <div class="desc">View and recover active carts.</div>
                    </div>
                </a>
            </div>
        </div>
    </section>
 <?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <!-- The / means start from the web root, lets use this later so that we
     dont have to put the header and footer each time. COOOOOLL -->
    </body>


</html>