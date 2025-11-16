<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
?>

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
                    <h4 class="mb-3">Edit Expense</h4>

                    <form class="needs-validation" method="post" novalidate>
                        <!-- Expense IDs & timestamps (read-only) -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Expense ID</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option value="pending" selected>pending</option>
                                    <option value="paid">paid</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Core expense fields -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="expense_type" class="form-label">Expense type</label>
                                <select id="expense_type" name="expense_type" class="form-select" required>
                                    <option value="inventory_purchase">inventory_purchase</option>
                                    <option value="shipping">shipping</option>
                                    <option value="maintenance">maintenance</option>
                                    <option value="rent">rent</option>
                                    <option value="utilities">utilities</option>
                                    <option value="other" selected>other</option>
                                </select>
                                <div class="invalid-feedback">Required.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount (₱)</label>
                                <input type="number" id="amount" name="amount" step="0.01" min="0" class="form-control" required>
                                <div class="invalid-feedback">Enter a valid amount.</div>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" id="description" name="description" class="form-control" maxlength="255" placeholder="Optional">
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due date</label>
                                <input type="date" id="due_date" name="due_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="paid_date" class="form-label">Paid date</label>
                                <input type="date" id="paid_date" name="paid_date" class="form-control">
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Essential fields only: type, amount, description, dates, status.</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                type="submit" style="flex: 2 1 0%;">
                                Save Expense
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



