<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$salary = [];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$found = false;

// Load employees for dropdown
$employees = [];
$es = $conn->prepare("SELECT emp_id, first_name, last_name FROM employees ORDER BY first_name, last_name");
if ($es) {
    $es->execute();
    $es->bind_result($e_id, $e_fn, $e_ln);
    while ($es->fetch()) {
        $employees[] = ['emp_id'=>$e_id,'name'=>trim($e_fn.' '.$e_ln)];
    }
    $es->close();
}

// If editing existing salary
if ($id > 0) {
    $stmt = $conn->prepare("SELECT salary_id, emp_id, pay_date, from_date, to_date, rate_used, status, created_at
                            FROM salaries WHERE salary_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($salary_id, $emp_id, $pay_date, $from_date, $to_date, $rate_used, $status, $created_at);
        if ($stmt->fetch()) {
            $found = true;
            $salary = [
                'salary_id' => $salary_id,
                'emp_id' => $emp_id,
                'pay_date' => $pay_date,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'rate_used' => $rate_used,
                'status' => $status,
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
$pageTitle = $isEdit ? 'Edit Salary' : 'Add Salary';

// Error codes -> messages (like register.php style)
$errors = [];
if (isset($_GET['error'])) {
    $codes = explode(',', $_GET['error']);
    foreach ($codes as $e) {
        $e = trim($e);
        if ($e === 'emp_required')      $errors[] = 'Employee is required.';
        if ($e === 'emp_not_found')     $errors[] = 'Selected employee not found.';
        if ($e === 'pay_date_required') $errors[] = 'Pay date is required.';
        if ($e === 'rate_required')     $errors[] = 'Rate is required.';
        if ($e === 'rate_invalid')      $errors[] = 'Rate must be a number >= 0.';
        if ($e === 'status_invalid')    $errors[] = 'Status value invalid.';
        if ($e === 'period_invalid')    $errors[] = 'Period dates invalid or out of order.';
        if ($e === 'server')            $errors[] = 'Server error. Please try again.';
    }
}

$successMsg = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') $successMsg = 'Salary record created.';
    if ($_GET['status'] === 'updated') $successMsg = 'Salary record updated.';
}

// Preserve posted values if redirected with errors (for create mode)
$getVal = function($key, $fallback='') use ($salary, $isEdit) {
    if ($isEdit && isset($salary[$key])) return $salary[$key];
    return isset($_POST[$key]) ? $_POST[$key] : $fallback;
};

$val_emp_id    = $getVal('emp_id');
$val_pay_date  = $getVal('pay_date');
$val_from_date = $getVal('from_date');
$val_to_date   = $getVal('to_date');
$val_rate_used = $getVal('rate_used');
$val_status    = $getVal('status', 'pending');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: <?php echo $pageTitle; ?></title>
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
                        <?php foreach ($errors as $er): ?>
                        <div>- <?php echo $er; ?></div>
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
                            <div class="col-sm-6">
                                <label class="form-label">Salary ID</label>
                                <input type="text" class="form-control" value="<?php echo $salary['salary_id']; ?>"
                                    disabled>
                                <input type="hidden" name="salary_id" value="<?php echo $salary['salary_id']; ?>">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="<?php echo $salary['created_at']; ?>"
                                    disabled>
                            </div>
                        </div>
                        <hr class="my-4">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee</label>
                                <select name="emp_id" class="form-select">
                                    <option value="">Emp ID – First Last</option>
                                    <?php foreach ($employees as $e): ?>
                                    <option value="<?php echo $e['emp_id']; ?>"
                                        <?php echo ((string)$val_emp_id === (string)$e['emp_id'] ? 'selected':''); ?>>
                                        <?php echo $e['emp_id'].' – '.$e['name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Pay date</label>
                                <input type="date" class="form-control" name="pay_date" value="<?php echo $val_pay_date; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rate used (Monthly)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" name="rate_used" placeholder="0.00"
                                        value="<?php echo $val_rate_used; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">From date</label>
                                <input type="date" class="form-control" name="from_date" value="<?php echo $val_from_date; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To date</label>
                                <input type="date" class="form-control" name="to_date" value="<?php echo $val_to_date; ?>">
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" <?php echo ($val_status==='pending'?'selected':''); ?>>pending
                                    </option>
                                    <option value="paid" <?php echo ($val_status==='paid'?'selected':''); ?>>paid</option>
                                    <option value="cancelled" <?php echo ($val_status==='cancelled'?'selected':''); ?>>cancelled
                                    </option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2 mb-3">
                            <button class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 btn-icon"
                                type="submit" style="flex:2 1 0%;">
                                Save changes
                                <img src="/carriemart/assets/person-check-fill.svg" alt="" aria-hidden="true">
                            </button>
                            <button type="button"
                                class="btn btn-outline-secondary btn-lg d-inline-flex align-items-center justify-content-center gap-2 btn-icon-inverted"
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



