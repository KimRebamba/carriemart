<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .container {
    max-width: 960px;
}


    .btn-icon-inverted img {
        width: 1.125rem;
        height: 1.125rem;
        filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
        opacity: .95;
    }
    </style>
</head>

<body>
        <div class="container">
            <main>
                <div class="py-4 text-center"> <img class="d-block mx-auto mb-0"
                        src="/carriemart/assets/Header-Logo-01.svg" alt="" width="72" height="57">
                </div>

                <div class="row g-5">
                    <div class="col-md-5 col-lg-4 order-md-last my-5">
                        <h4 class="d-flex justify-content-between align-items-center mb-3"> <span
                                class="text-primary">Your cart</span> <span
                                class="badge bg-primary rounded-pill">3</span> </h4>
                        <ul class="list-group mb-3">
                            <li class="list-group-item d-flex justify-content-between lh-sm">
                                <div>
                                    <h6 class="my-0">Product name</h6> <small class="text-body-secondary">Brief
                                        description</small>
                                </div> <span class="text-body-secondary">$12</span>
                            </li>
                            
                            <li class="list-group-item d-flex justify-content-between bg-body-tertiary">
                                <div class="text-success">
                                    <h6 class="my-0">Promo code</h6> <small>EXAMPLECODE</small>
                                </div> <span class="text-success">−$5</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between bg-body-tertiary">
                                <div class="text-danger">
                                    <h6 class="my-0">Delivery Fee</h6>
                                </div> <span class="text-danger">$5</span>
                            </li>

                            <li class="list-group-item d-flex justify-content-between"> <span>Total (USD)</span>
                                <strong>$20</strong> </li>
                        </ul>
                        <form class="card p-2">
                            <div class="input-group"> <input type="text" class="form-control" placeholder="Promo code">
                                <button type="submit" class="btn btn-secondary">Redeem</button> </div>
                        </form>
                    </div>

                    <div class="col-md-7 col-lg-8 my-5 ">
                        <h4 class="mb-3">Order Information</h4>
                        <form class="needs-validation" novalidate="">
                            
                            <div class="row g-3">
                                <div class="col-12"> <label for="name" class="form-label">Delivery Recipient</label> <input
                                        type="text" class="form-control" id="name" placeholder="e.g. Berto"
                                        required="">
                                    <div class="invalid-feedback">
                                        Please enter the recipient's name.
                                    </div>
                                </div>

                                <div class="col-12"> <label for="address" class="form-label">Delivery Address</label> <input
                                        type="text" class="form-control" id="address" placeholder="e.g. 1234 Main St"
                                        required="">
                                    <div class="invalid-feedback">
                                        Please enter your shipping address.
                                    </div>
                                </div>

                                <div class="col-12"> <label for="phone" class="form-label">Contact Number</label> <input
                                        type="text" class="form-control" id="phone" placeholder="e.g. 09##-###-####"
                                        required="">
                                    <div class="invalid-feedback">
                                        Please enter your contact number.
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <h4 class="mb-3">Payment Option</h4>
                            <div class="my-3">
                                <div class="form-check"> <input id="credit" name="paymentMethod" type="radio"
                                        class="form-check-input" checked="" required=""> <label class="form-check-label"
                                        for="credit">Cash on Delivery</label> </div>
                                <div class="form-check"> <input id="debit" name="paymentMethod" type="radio"
                                        class="form-check-input" required=""> <label class="form-check-label"
                                        for="debit">Credit card</label> </div>
                                <div class="form-check"> <input id="paypal" name="paymentMethod" type="radio"
                                        class="form-check-input" required=""> <label class="form-check-label"
                                        for="paypal">Gcash</label> </div>
                            </div>
                            <hr class="my-4"> 
                            <div class="d-flex w-100 gap-2">
                                <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center btn-icon-inverted" type="submit" style="flex: 2 1 0%;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bag-check me-2" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
  <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
</svg>
                                    Continue to checkout
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted" style="flex: 1 1 0%;" onclick="history.back()">
                                    Go back
                                    <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
            
            
        </div>

        <script src="/docs/5.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" class="astro-vvvwv3sm">
        </script>
        <script src="checkout.js" class="astro-vvvwv3sm"></script>
    </body>
</body>

</html>