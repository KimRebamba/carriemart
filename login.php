<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrieart: Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
    .form-signin {
        background-color: #ffffff;
        border-radius: 1rem;
        max-width: 430px;
        padding: 1rem;
    }

    .btn-icon img {
        width: 1.125rem;
        height: 1.125rem;
         filter: brightness(0) invert(1);
    }

    .btn-icon-inverted img {
        width: 1.125rem;
        height: 1.125rem;
        filter: invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%);
        opacity: .95;
    }
    </style>
</head>

<!-- sign in form -->

<body class="d-flex align-items-center py-4 bg-body-tertiary">
    <main class="form-signin w-100 m-auto">
        <form>
            <img class="d-block mx-auto mb-4" src="./assets/Header-Logo-01.svg" alt="Carriemart" width="72" height="57">

            <div class="row g-2 align-items-center mb-3">
                <label for="inputEmail3" class="col-12 col-sm-3 col-form-label text-sm-start">Email</label>
                <div class="col-12 col-sm-9">
                    <input type="email" class="form-control mt-1 mt-sm-0" id="inputEmail3" placeholder="you@example.com">
                </div>
            </div>

            <div class="row g-2 align-items-center mb-3">
                <label for="inputPassword3" class="col-12 col-sm-3 col-form-label text-sm-start">Password</label>
                <div class="col-12 col-sm-9">
                    <input type="password" class="form-control mt-1 mt-sm-0" id="inputPassword3" placeholder="••••••••">
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-12 offset-sm-3 col-sm-9">
                    <div class="form-check form-switch pt-1">
                        <input class="form-check-input" type="checkbox" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mb-2">
                <button type="submit" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 btn-icon">
                    Sign in
                    <img src="./assets/person-circle.svg" alt="" aria-hidden="true">
                </button>
            </div>

            <div class="row g-2">
                <div class="col-8">
                    <button type="button" class="btn btn-primary w-100 d-inline-flex align-items-center justify-content-center gap-2 btn-icon">
                        Create an account
                        <img src="./assets/plus-circle.svg" alt="" aria-hidden="true">
                    </button>
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted " onclick="history.back()">
                        Go back
                        <img src="./assets/caret-right-square.svg" alt="" aria-hidden="true">
                    </button>
                </div>
            </div>
        </form>
    </main>


    <script src="/docs/5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" class="astro-vvvwv3sm">
    </script>
</body>


</html>