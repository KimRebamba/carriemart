<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$position = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$found = false;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT position_id, position_name, monthly_rate, created_at FROM positions WHERE position_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($position_id, $position_name, $monthly_rate, $created_at);
        if ($stmt->fetch()) {
            $found = true;
            $position = [
                'position_id' => $position_id,
                'position_name' => $position_name,
                'monthly_rate' => $monthly_rate,
                'created_at' => $created_at
            ];
        }
        $stmt->close();
    }
    if (!$found) {
        header('Location: index.php?error=not_found');
        exit;
    }
}

$isEdit = ($id > 0 && $found);
$formAction = $isEdit ? 'update.php' : 'create.php';
$pageTitle = $isEdit ? 'Edit Position' : 'Add Position';

// Collect error codes from redirects (comma-separated supported)
$errors = [];
if (isset($_GET['error'])) {
    $codes = explode(',', $_GET['error']);
    foreach ($codes as $e) {
        $e = trim($e);
        if ($e === 'name_required')   $errors[] = 'Position name is required.';
        if ($e === 'rate_required')   $errors[] = 'Monthly rate is required.';
        if ($e === 'rate_invalid')    $errors[] = 'Monthly rate must be a number greater than or equal to 0.';
        if ($e === 'duplicate')       $errors[] = 'Position name already exists.';
        if ($e === 'server')          $errors[] = 'Server error. Please try again.';
    }
}

$successMsg = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') $successMsg = 'Position created.';
    if ($_GET['status'] === 'updated') $successMsg = 'Position updated.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Edit Position</title>
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
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Logo.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-8 col-lg-7 mx-auto">
                    <h4 class="mb-3"><?php echo $pageTitle; ?></h4>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-3" role="alert">
                        <?php foreach ($errors as $err): ?>
                        <div>- <?php echo $err; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php elseif ($successMsg !== ''): ?>
                    <div class="alert alert-success mb-3" role="alert">
                        <?php echo $successMsg; ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo $formAction; ?>">
                        <?php if ($isEdit): ?>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Position ID</label>
                                <input type="text" class="form-control" value="<?php echo $position['position_id']; ?>"
                                    disabled>
                                <input type="hidden" name="position_id" value="<?php echo $position['position_id']; ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date created</label>
                                <input type="text" class="form-control" value="<?php echo $position['created_at']; ?>"
                                    disabled>
                            </div>
                        </div>
                        <hr class="my-4">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Position name</label>
                                <input type="text" class="form-control" name="position_name"
                                    value="<?php echo $position['position_name'] ?? ($_POST['position_name'] ?? ''); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Monthly rate</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" name="monthly_rate" placeholder="0.00"
                                        value="<?php echo isset($position['monthly_rate']) ? $position['monthly_rate'] : ($_POST['monthly_rate'] ?? ''); ?>">
                                </div>
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



