<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Edit Salary</title>
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
                    <h4 class="mb-3">Edit Salary</h4>

                    <form class="needs-validation" method="post" novalidate>
                        <!-- IDs & timestamps (read-only display) -->
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Salary ID</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Employee reference -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emp_id" class="form-label">Employee</label>
                                <select id="emp_id" name="emp_id" class="form-select" required>
                                    <option value="" selected>Emp ID – First Last</option>
                                </select>
                                <div class="invalid-feedback">Employee is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Name</label>
                                <input type="text" class="form-control" value="" placeholder="First Last" disabled>
                            </div>
                        </div>

                        <!-- Pay details -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="pay_date" class="form-label">Pay date</label>
                                <input type="date" id="pay_date" name="pay_date" class="form-control" required>
                                <div class="invalid-feedback">Pay date is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="rate_used" class="form-label">Rate used (Monthly)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" id="rate_used" name="rate_used" class="form-control" placeholder="0.00" required>
                                </div>
                                <div class="invalid-feedback">Rate is required.</div>
                            </div>
                        </div>

                        <!-- Period -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="from_date" class="form-label">From date</label>
                                <input type="date" id="from_date" name="from_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="to_date" class="form-label">To date</label>
                                <input type="date" id="to_date" name="to_date" class="form-control">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select" required>
                                    <option value="pending" selected>pending</option>
                                    <option value="paid">paid</option>
                                    <option value="cancelled">cancelled</option>
                                </select>
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



