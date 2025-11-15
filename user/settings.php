<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Settings</title>
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
    </style>
</head>

<body>
    <div class="container">
        <main class="form-register">
            <div class="py-4 text-center">
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Header-Logo-01.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-7 col-lg-8 mx-auto">
                    <h4 class="mb-3">Profile information</h4>

                    <form class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="firstName" class="form-label">First name</label>
                                <input type="text" class="form-control" id="firstName" placeholder="" required>
                                <div class="invalid-feedback">Valid first name is required.</div>
                            </div>

                            <div class="col-sm-6">
                                <label for="lastName" class="form-label">Last name</label>
                                <input type="text" class="form-control" id="lastName" placeholder="" required>
                                <div class="invalid-feedback">Valid last name is required.</div>
                            </div>

                            <div class="col-12">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text">@</span>
                                    <input type="text" class="form-control" id="username" placeholder="Username" required>
                                    <div class="invalid-feedback">Your username is required.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="inputPassword5" class="form-label">Password</label>
                                <input type="password" id="inputPassword5" class="form-control" aria-describedby="passwordHelpBlock">
                                <div id="passwordHelpBlock" class="form-text">
                                    8–20 characters, letters and numbers, no spaces/special characters/emoji.
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="you@example.com" required>
                                <div class="invalid-feedback">Please enter a valid email.</div>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" placeholder="1234 Main St" required>
                                <div class="invalid-feedback">Please enter your address.</div>
                            </div>

                            <div class="col-12">
                                <label for="phone" class="form-label">Phone number</label>
                                <input type="text" class="form-control" id="phone" placeholder="09##-####-###" required>
                                <div class="invalid-feedback">Please enter your phone number.</div>
                            </div>
                        </div>

                        <!-- Profile picture -->
                        <div class="mb-4 mt-4">
                            <label class="form-label d-block">Profile picture</label>
                            <div class="d-flex align-items-center gap-3">
                                <img id="avatarPreview" class="avatar-lg border" src="/carriemart/assets/person-circle.svg" alt="">
                                <div class="flex-grow-1">
                                    <input class="form-control" type="file" id="formFile" accept="image/*">
                                    <small class="text-body-secondary">JPG, PNG, or GIF. Max 5MB.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row gy-2">
                            <h4 class="mb-2">Account Option</h4>
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="deactivate">
                                    <label class="form-check-label" for="deactivate">Deactivate account</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="clearCart">
                                    <label class="form-check-label" for="clearCart">Clear all products from cart</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
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

    <script>
    // Simple preview for profile picture
    const fileInput = document.getElementById('formFile');
    const avatarPreview = document.getElementById('avatarPreview');
    fileInput?.addEventListener('change', (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        avatarPreview.src = url;
    });
    </script>
</body>

</html>



