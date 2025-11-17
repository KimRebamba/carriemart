<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$found = false;
$voucher = [];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT voucher_id, voucher_code, percent_sale, min_purchase_amount,
                                   max_discount_amount, from_date, to_date, is_active, created_at
                            FROM vouchers WHERE voucher_id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($voucher_id, $voucher_code, $percent_sale, $min_purchase_amount,
                           $max_discount_amount, $from_date, $to_date, $is_active, $created_at);
        if ($stmt->fetch()) {
            $found = true;
            $voucher = [
                'voucher_id' => $voucher_id,
                'voucher_code' => $voucher_code,
                'percent_sale' => $percent_sale,
                'min_purchase_amount' => $min_purchase_amount,
                'max_discount_amount' => $max_discount_amount,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'is_active' => $is_active,
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
$pageTitle = $isEdit ? 'Edit Voucher' : 'Add Voucher';

$errors = [];
if (isset($_GET['error'])) {
    foreach (explode(',', $_GET['error']) as $e) {
        $e = trim($e);
        if ($e === 'code_required')        $errors[] = 'Voucher code is required.';
        if ($e === 'code_invalid')         $errors[] = 'Voucher code is invalid.';
        if ($e === 'code_duplicate')       $errors[] = 'Voucher code already exists.';
        if ($e === 'percent_invalid')      $errors[] = 'Percent sale must be a whole number 0–100.';
        if ($e === 'min_purchase_invalid') $errors[] = 'Min purchase amount invalid.';
        if ($e === 'max_discount_invalid') $errors[] = 'Max discount amount invalid.';
        if ($e === 'date_range_invalid')   $errors[] = 'Date range invalid (check ordering).';
        if ($e === 'status_invalid')       $errors[] = 'Status value invalid.';
        if ($e === 'server')               $errors[] = 'Server error. Try again.';
    }
}

$successMsg = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') $successMsg = 'Voucher created.';
    if ($_GET['status'] === 'updated') $successMsg = 'Voucher updated.';
}

$val = function($key, $fallback='') use ($voucher, $isEdit) {
    if ($isEdit && array_key_exists($key, $voucher)) return $voucher[$key];
    return isset($_POST[$key]) ? $_POST[$key] : $fallback;
};
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Voucher Form</title>
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
                            <div class="col-6">
                                <label class="form-label">Voucher ID</label>
                                <input type="text" class="form-control" value="<?php echo $val('voucher_id'); ?>"
                                    disabled>
                                <input type="hidden" name="voucher_id" value="<?php echo $val('voucher_id'); ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Created at</label>
                                <input type="text" class="form-control" value="<?php echo $val('created_at'); ?>"
                                    disabled>
                            </div>
                        </div>
                        <hr class="my-4">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Voucher code</label>
                                <input type="text" name="voucher_code" class="form-control" maxlength="20"
                                    value="<?php echo $val('voucher_code'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">% Sale</label>
                                <input type="text" name="percent_sale" class="form-control"
                                    value="<?php echo $val('percent_sale'); ?>" placeholder="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <?php $statusVal = (string)$val('is_active','1'); ?>
                                <select name="is_active" class="form-select">
                                    <option value="1" <?php echo ($statusVal==='1'?'selected':''); ?>>active</option>
                                    <option value="0" <?php echo ($statusVal==='0'?'selected':''); ?>>inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Min purchase amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" name="min_purchase_amount" class="form-control"
                                        value="<?php echo $val('min_purchase_amount'); ?>" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max discount amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" name="max_discount_amount" class="form-control"
                                        value="<?php echo $val('max_discount_amount'); ?>" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">From date</label>
                                <input type="date" name="from_date" class="form-control"
                                    value="<?php echo $val('from_date'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To date</label>
                                <input type="date" name="to_date" class="form-control"
                                    value="<?php echo $val('to_date'); ?>">
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



