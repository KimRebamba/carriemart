<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Register</title>
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
    </style>
</head>

<body>

    <!-- form -->
    <div class="container ">
        <main class="form-register">
            <div class="py-4 text-center"> <img class="d-block mx-auto mb-0" src="./assets/Header-Logo-01.svg" alt=""
                    width="72" height="57">
            </div>
            <div class="row g-5">
                <div class="col-md-7 col-lg-8 mx-auto">
                    <h4 class="mb-3">Account Information</h4>
                    <form class="needs-validation" novalidate="">
                        <div class="row g-3">
                            <div class="col-sm-6"> <label for="firstName" class="form-label">First name</label> <input
                                    type="text" class="form-control" id="firstName" placeholder="" value="" required="">
                                <div class="invalid-feedback">
                                    Valid first name is required.
                                </div>
                            </div>
                            <div class="col-sm-6"> <label for="lastName" class="form-label">Last name</label> <input
                                    type="text" class="form-control" id="lastName" placeholder="" value="" required="">
                                <div class="invalid-feedback">
                                    Valid last name is required.
                                </div>
                            </div>
                            <div class="col-12"> <label for="username" class="form-label">Username</label>
                                <div class="input-group has-validation"> <span class="input-group-text">@</span> <input
                                        type="text" class="form-control" id="username" placeholder="Username"
                                        required="">
                                    <div class="invalid-feedback">
                                        Your username is required.
                                    </div>
                                </div>
                            </div>

                            <label for="inputPassword5" class="form-label">Password</label>
                            <input type="password" id="inputPassword5" class="form-control mt-0 mb-0"
                                aria-describedby="passwordHelpBlock">
                            <div id="passwordHelpBlock" class="form-text">
                                Your password should be 8-20 characters long, contain letters and numbers, and must not
                                contain spaces, special characters, or emoji. (For better security, but whatever)
                            </div>

                            <div class="col-12 mt-3"> <label for="email" class="form-label">Email</label> <input type="email"
                                    class="form-control" id="email" placeholder="you@example.com">
                                <div class="invalid-feedback">
                                    Please enter a valid email address for shipping updates.
                                </div>
                            </div>
                            <div class="col-12"> <label for="address" class="form-label">Address</label> <input
                                    type="text" class="form-control" id="address" placeholder="1234 Main St"
                                    required="">
                                <div class="invalid-feedback">
                                    Please enter your shipping address.
                                </div>
                            </div>

                            <div class="col-12"> <label for="phone" class="form-label">Phone Number</label> <input
                                    type="text" class="form-control" id="address" placeholder="09##-####-###"
                                    required="">
                                <div class="invalid-feedback">
                                    Please enter your shipping address.
                                </div>
                            </div>

                            <div class="mb-0">
                                <label for="formFile" class="form-label">Upload a profile picture</label>
                                <input class="form-control" type="file" id="formFile">
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-lg flex-grow-1 d-flex align-items-center justify-content-center gap-2 btn-icon"
                                        type="submit">
                                        Submit
                                         <img src="./assets/person-circle.svg" alt="" aria-hidden="true">
                                    </button>
                                    <button type="button"
                                        class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
                                        onclick="history.back()">
                                        Go back
                                        <img src="./assets/caret-right-square.svg" alt="" aria-hidden="true">
                                    </button>
                                </div>
                            </div>

                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
</body>

</html>