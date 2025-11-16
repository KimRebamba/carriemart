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
                    <h4 class="mb-3">Edit Order</h4>

                    <form class="needs-validation" method="post" novalidate>
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
                                <label for="voucher_code" class="form-label">Voucher code</label>
                                <select id="voucher_code" name="voucher_code" class="form-select">
                                    <option value="" selected>None</option>
                                </select>
                            </div>
                        </div>

                        <!-- Status & payment -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="payment_status" class="form-label">Payment status</label>
                                <select id="payment_status" name="payment_status" class="form-select" required>
                                    <option value="pending" selected>pending</option>
                                    <option value="paid">paid</option>
                                    <option value="refunded">refunded</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="order_status" class="form-label">Order status</label>
                                <select id="order_status" name="order_status" class="form-select" required>
                                    <option value="pending" selected>pending</option>
                                    <option value="processing">processing</option>
                                    <option value="shipped">shipped</option>
                                    <option value="completed">completed</option>
                                    <option value="cancelled">cancelled</option>
                                    <option value="requested_refund">requested_refund</option>
                                    <option value="returned">returned</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment option & fee -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="payment_option" class="form-label">Payment option</label>
                                <select id="payment_option" name="payment_option" class="form-select">
                                    <option value="" selected>—</option>
                                    <option>Credit Card</option>
                                    <option>COD</option>
                                    <option>Bank Transfer</option>
                                    <option>e-Wallet</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="percent_sale" class="form-label">% Sale</label>
                                <input type="number" min="0" max="100" id="percent_sale" name="percent_sale" class="form-control" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label for="delivery_fee" class="form-label">Delivery fee</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" id="delivery_fee" name="delivery_fee" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <!-- Delivery details -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="delivery_recipient" class="form-label">Recipient name</label>
                                <input type="text" id="delivery_recipient" name="delivery_recipient" class="form-control" placeholder="Full name">
                            </div>
                            <div class="col-md-6">
                                <label for="delivery_phone" class="form-label">Recipient phone</label>
                                <input type="text" id="delivery_phone" name="delivery_phone" class="form-control" placeholder="09##-###-####">
                            </div>
                            <div class="col-12">
                                <label for="delivery_address" class="form-label">Delivery address</label>
                                <input type="text" id="delivery_address" name="delivery_address" class="form-control" placeholder="Address">
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
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                type="submit" style="flex: 2 1 0%;">
                                Save changes
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </button>
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



