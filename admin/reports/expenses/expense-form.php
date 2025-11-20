<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if (!$conn) {
    die('Database connection failed.');
}

   
$id_raw   = isset($_GET['id']) ? trim($_GET['id']) : '';
$exp_id   = ctype_digit($id_raw) ? (int)$id_raw : 0;
$isEdit   = $exp_id > 0;

   
$expense = [
    'exp_id'        => '',
    'expense_type'  => 'other',
    'description'   => '',
    'amount'        => '',
    'status'        => 'pending',
    'due_date'      => '',
    'paid_date'     => '',
    'created_at'    => ''
];

   
if ($isEdit) {
    $stmt = $conn->prepare("SELECT exp_id, expense_type, description, amount, status, due_date, paid_date, created_at FROM expenses WHERE exp_id = ?");
    if (!$stmt) {
        header('Location: index.php?error=server');
        exit;
    }
    $stmt->bind_param('i', $exp_id);
    $stmt->execute();
    $stmt->bind_result(
        $expense['exp_id'],
        $expense['expense_type'],
        $expense['description'],
        $expense['amount'],
        $expense['status'],
        $expense['due_date'],
        $expense['paid_date'],
        $expense['created_at']
    );
    if (!$stmt->fetch()) {
        $stmt->close();
        header('Location: index.php?error=not_found');
        exit;
    }
    $stmt->close();
}

   
$formAction = $isEdit ? 'update.php' : 'create.php';

   
$errors = [];
if (isset($_GET['error'])) {
    $codes = explode(',', $_GET['error']);
    foreach ($codes as $c) {
        $c = trim($c);
        if ($c === '') continue;
        if ($c === 'invalid_id')    $errors[] = 'Invalid expense ID.';
        if ($c === 'not_found')     $errors[] = 'Expense not found.';
        if ($c === 'type_invalid')  $errors[] = 'Expense type is invalid.';
        if ($c === 'amount_invalid')$errors[] = 'Amount value is invalid.';
        if ($c === 'amount_required') $errors[] = 'Amount is required.';
        if ($c === 'status_invalid')$errors[] = 'Status value is invalid.';
        if ($c === 'due_invalid')   $errors[] = 'Due date format is invalid.';
        if ($c === 'paid_invalid')  $errors[] = 'Paid date format is invalid.';
        if ($c === 'date_order')    $errors[] = 'Paid date cannot be before due date.';
        if ($c === 'server')        $errors[] = 'Server error. Please try again.';
    }
}

   
$success = false;
$successText = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        $success = true;
        $successText = 'Expense created.';
    } elseif ($_GET['status'] === 'updated') {
        $success = true;
        $successText = 'Expense updated.';
    }
}

   
$sel = function($a, $b){ return (string)$a === (string)$b ? 'selected' : ''; };

   
$expenseTypes = ['inventory_purchase','shipping','maintenance','rent','utilities','other'];
$statuses     = ['pending','paid'];
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
                <img class="d-block mx-auto mb-0" src="/carriemart/assets/Logo.svg" alt="" width="72" height="57">
            </div>

            <div class="row g-5">
                <div class="col-md-8 col-lg-7 mx-auto">
                    <h4 class="mb-3"><?php echo $isEdit ? 'Edit Expense' : 'Add Expense'; ?></h4>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-0" role="alert">
                        <?php foreach ($errors as $e): ?>
                        <div>- <?php echo $e; ?></div>
                        <?php endforeach; ?>
                    </div class="mb-2">
                    <?php elseif ($success): ?>
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-0" role="alert">
                        <span><?php echo $successText; ?></span>
                        <a class="btn btn-success" href="/carriemart/admin/reports/expenses/index.php" role="button">OK</a>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo $formAction; ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Expense ID</label>
                                <input type="text" class="form-control" value="<?php echo $expense['exp_id']; ?>"
                                    disabled>
                                <?php if ($isEdit): ?>
                                <input type="hidden" name="exp_id" value="<?php echo $expense['exp_id']; ?>">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="<?php echo $expense['created_at']; ?>"
                                    disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <?php foreach ($statuses as $st): ?>
                                    <option value="<?php echo $st; ?>"
                                        <?php echo $sel($expense['status'], $st); ?>><?php echo $st; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Expense type</label>
                                <select name="expense_type" class="form-select">
                                    <?php foreach ($expenseTypes as $t): ?>
                                    <option value="<?php echo $t; ?>"
                                        <?php echo $sel($expense['expense_type'], $t); ?>><?php echo $t; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount (₱)</label>
                                <input type="text" name="amount" class="form-control"
                                    value="<?php echo $expense['amount']; ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control"
                                    value="<?php echo $expense['description']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due date</label>
                                <input type="text" name="due_date" class="form-control"
                                    value="<?php echo $expense['due_date']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Paid date</label>
                                <input type="text" name="paid_date" class="form-control"
                                    value="<?php echo $expense['paid_date']; ?>">
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Fields: type, amount, description, status, dates.</small>
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



