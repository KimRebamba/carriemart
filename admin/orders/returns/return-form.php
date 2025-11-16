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
                    <h4 class="mb-3">Edit Return</h4>
                    <form class="needs-validation" method="post" novalidate>
                        <!-- IDs (read-only) -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Return ID</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Order ID</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-4">
                                <label for="cond" class="form-label">Condition</label>
                                <select id="cond" name="cond" class="form-select">
                                    <option value="new">new</option>
                                    <option value="opened" selected>opened</option>
                                    <option value="damaged">damaged</option>
                                    <option value="other">other</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Editable / read-only mix -->
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Reason (read-only)</label>
                                <textarea class="form-control" rows="4" disabled></textarea>
                            </div>
                            <div class="col-md-4">
                                <label for="return_status" class="form-label">Status</label>
                                <select id="return_status" name="return_status" class="form-select">
                                    <option value="requested">requested</option>
                                    <option value="approved" selected>approved</option>
                                    <option value="rejected">rejected</option>
                                    <option value="processed">processed</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="refund_amount" class="form-label">Refund amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" id="refund_amount" name="refund_amount" class="form-control" placeholder="0.00" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Processed at (read-only)</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Created at (read-only)</label>
                                <input type="text" class="form-control" value="" disabled>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <small class="text-muted">Editable: Condition, Status, Refund amount only.</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2"
                                type="submit" style="flex:2 1 0%;">
                                Save changes
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </button>
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2"
                                style="flex:1 1 0%;" onclick="history.back()">
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



