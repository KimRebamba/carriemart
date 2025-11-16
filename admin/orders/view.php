<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    .form-register {
        background-color: #ffffff;
        border-radius: 1rem;
        padding: 1rem;
    }

    .btn-icon-inverted img {
        width: 1.125rem;
        height: 1.125rem;
        filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
        opacity: .95;
    }

    .btn-icon img {
        width: 1.125rem;
        height: 1.125rem;
        filter: brightness(0) invert(1);
    }

    .avatar-lg {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        background: #f1f3f5;
    }

    .label-small {
        font-size: .8rem;
        color: var(--bs-secondary-color);
    }
    </style>
</head>

<body>
    <div class="container">
        <main class="form-register">
            <div class="py-4 text-center">
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Header-Logo-01.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-8 col-lg-7 mx-auto">
                    <h4 class="mb-3">View Order</h4>

                    <form class="needs-validation" novalidate>
                        <!-- Order IDs & timestamps (read-only display) -->
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Order ID</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date ordered</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Account (read-only) & voucher -->
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">User ID</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Voucher code</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <!-- Status & payment -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Payment status</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Order status</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <!-- Payment option & fee -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Payment option</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-3">
                                <label for="percent_sale" class="form-label">% Sale</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-3">
                                <label for="delivery_fee" class="form-label">Delivery fee</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <!-- Delivery details -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Recipient name</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Recipient phone</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Delivery address</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <!-- Completion (read-only) -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Completed at</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 mb-3">
                            <a class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                               style="flex: 2 1 0%;" href="order-form.php">
                                Edit Order
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </a>
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                style="flex: 1 1 0%;" onclick="history.back()">
                                Go back
                                <img src="/carriemart/assets/caret-right-square.svg" alt="" aria-hidden="true">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>



