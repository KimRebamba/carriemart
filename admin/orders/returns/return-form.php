<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$return = [];
$stmt = $conn->prepare("SELECT order_return_id, order_id, reason, cond, return_status, refund_amount, processed_at, created_at
                        FROM order_return WHERE order_return_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($order_return_id, $order_id, $reason, $cond, $return_status, $refund_amount, $processed_at, $created_at);
    if ($stmt->fetch()) {
        $return = [
            'order_return_id' => $order_return_id,
            'order_id' => $order_id,
            'reason' => $reason,
            'cond' => $cond,
            'return_status' => $return_status,
            'refund_amount' => $refund_amount,
            'processed_at' => $processed_at,
            'created_at' => $created_at
        ];
    } else {
        $stmt->close();
        header('Location: index.php?error=not_found');
        exit;
    }
    $stmt->close();
} else {
    header('Location: index.php?error=server');
    exit;
}

$errors = [];
if (isset($_GET['error'])) {
    foreach (explode(',', $_GET['error']) as $e) {
        $e = trim($e);
        if ($e === 'invalid_id')     $errors[] = 'Invalid return ID.';
        if ($e === 'not_found')      $errors[] = 'Return record not found.';
        if ($e === 'cond_invalid')   $errors[] = 'Condition value invalid.';
        if ($e === 'status_invalid') $errors[] = 'Status value invalid.';
        if ($e === 'refund_invalid') $errors[] = 'Refund amount invalid.';
        if ($e === 'processed_lock') $errors[] = 'Processed return cannot be changed.';
        if ($e === 'server')         $errors[] = 'Server error. Try again.';
    }
}

$success = (isset($_GET['status']) && $_GET['status'] === 'updated');

$editableCond = ['new','opened','damaged','other'];
$editableStatus = ['requested','approved','rejected','processed'];

$condVal = $return['cond'];
$statusVal = $return['return_status'];
$refundVal = (string)$return['refund_amount'];
$processedDisplay = $return['processed_at'] ? $return['processed_at'] : '';
$createdDisplay = $return['created_at'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CarrieMart: Edit Return</title>
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
                    <h4 class="mb-3">Edit Return</h4>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-3" role="alert">
                        <?php foreach ($errors as $er): ?><div>- <?php echo $er; ?></div><?php endforeach; ?>
                    </div>
                    <?php elseif ($success): ?>
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-3" role="alert">
                        <span>Return updated.</span>
                        <a class="btn btn-success btn-sm" href="index.php">OK</a>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="update.php">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Return ID</label>
                                <input type="text" class="form-control" value="<?php echo $return['order_return_id']; ?>"
                                    disabled>
                                <input type="hidden" name="order_return_id" value="<?php echo $return['order_return_id']; ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Order ID</label>
                                <input type="text" class="form-control" value="<?php echo $return['order_id']; ?>" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Condition</label>
                                <select name="cond" class="form-select">
                                    <?php foreach ($editableCond as $c): ?>
                                    <option value="<?php echo $c; ?>" <?php echo ($condVal===$c?'selected':''); ?>><?php echo $c; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Reason (read-only)</label>
                                <textarea class="form-control" rows="4" disabled><?php echo ($return['reason']!==null?$return['reason']:''); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="return_status" class="form-select">
                                    <?php foreach ($editableStatus as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo ($statusVal===$s?'selected':''); ?>><?php echo $s; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Refund amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" name="refund_amount" class="form-control" value="<?php echo $refundVal; ?>"
                                        placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Processed at (read-only)</label>
                                <input type="text" class="form-control" value="<?php echo $processedDisplay; ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Created at (read-only)</label>
                                <input type="text" class="form-control" value="<?php echo $createdDisplay; ?>" disabled>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <small class="text-muted">Editable: Condition, Status, Refund amount.</small>
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



