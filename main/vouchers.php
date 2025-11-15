<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Vouchers</title>
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
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            <span>Go Back</span>
        </a>
    </div>

    <div class="container mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0">All Vouchers</h5>
            <small class="text-muted">Showing 8 vouchers</small>
        </div>
    </div>
    
    <!-- Vouchers grid -->
    <div class="container pb-2">
        <div class="product-grid">

            <!-- Voucher: SAVE10 -->
            <div class="product-card">
              
                <h6 class="mt-2 mb-1 fw-bold display-5">SAVE10</h6>
                <p class="small mb-1"><strong>10% OFF</strong></p>
                <p class="text-muted small mb-0">
                    Min purchase: 500<br>
                    Max discount: 100<br>
                    Until: 2025-12-31
                </p>
                <a href="#" class="stretched-link" onclick="copyVoucher('SAVE10'); return false;" aria-label="Copy voucher SAVE10"></a>
            </div>

            <!-- Voucher: YOUS -->
            <div class="product-card">
              
                <h6 class="mt-2 mb-1 fw-bold display-5">YOUS</h6>
                <p class="small mb-1"><strong>10% OFF</strong></p>
                <p class="text-muted small mb-0">
                    Min purchase: 500<br>
                    Max discount: 100<br>
                    Until: 2025-12-31
                </p>
                <a href="#" class="stretched-link" onclick="copyVoucher('YOUS'); return false;" aria-label="Copy voucher SAVE10"></a>
            </div>
        </div>
    </div>

    <!-- Floating toast (use toast.js) -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <button type="button" class="btn btn-primary d-none" id="liveToastBtn">Show live toast</button>
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <img src="../assets/Header-Logo-01.svg" class="rounded me-2" width="16" height="16" alt="CarrieMart">
          <strong class="me-auto">Voucher Copied</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          Copied voucher code.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="toast.js"></script>
    <!-- IMPORTANT: FOR TOAST NOTIFICATION // plus its complicated lol -->
    <script>
      const toastBtn = document.getElementById('liveToastBtn');
      const toastEl = document.getElementById('liveToast');
      const toastBody = toastEl?.querySelector('.toast-body');
      const toastTitle = toastEl?.querySelector('.toast-header .me-auto');
      const toastTime = toastEl?.querySelector('.toast-header small');
        // gets the card body. if clicked, function will run
      async function copyVoucher(code) {
        try { // clipboard API to copy text
          await navigator.clipboard.writeText(code);
        } catch (err) {
          const ta = document.createElement('textarea');
          ta.value = code; //just in case it fails
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta); 
        }
        // changes the toast content
        if (toastBody) toastBody.textContent = `Copied voucher: ${code}`;
        if (toastTitle) toastTitle.textContent = 'Voucher Copied';
        if (toastTime) toastTime.textContent = 'Just now';
        if (toastBtn) toastBtn.click();
      }
    </script>
</body>
</html>