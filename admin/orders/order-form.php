<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$order = [];
$stmt = $conn->prepare("SELECT o.order_id, o.user_id, o.voucher_code, o.payment_status, o.order_status,
                               o.payment_option, o.delivery_recipient, o.delivery_address, o.delivery_phone,
                               o.percent_sale, o.delivery_fee, o.date_ordered, o.completed_at,
                               a.username, a.first_name, a.last_name
                        FROM orders o
                        LEFT JOIN accounts a ON o.user_id = a.user_id
                        WHERE o.order_id = ?");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($order_id, $user_id, $voucher_code, $payment_status, $order_status,
                       $payment_option, $delivery_recipient, $delivery_address, $delivery_phone,
                       $percent_sale, $delivery_fee, $date_ordered, $completed_at,
                       $username, $first_name, $last_name);
    if ($stmt->fetch()) {
        $order = [
            'order_id' => $order_id,
            'user_id' => $user_id,
            'voucher_code' => $voucher_code,
            'payment_status' => $payment_status,
            'order_status' => $order_status,
            'payment_option' => $payment_option,
            'delivery_recipient' => $delivery_recipient,
            'delivery_address' => $delivery_address,
            'delivery_phone' => $delivery_phone,
            'percent_sale' => $percent_sale,
            'delivery_fee' => $delivery_fee,
            'date_ordered' => $date_ordered,
            'completed_at' => $completed_at,
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name
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
        if ($e === 'payment_status_invalid') $errors[] = 'Payment status invalid. Refunded can only be set when order status is "requested_refund" or "returned".';
        if ($e === 'order_status_invalid')   $errors[] = 'Order status invalid.';
        if ($e === 'percent_sale_invalid')   $errors[] = 'Percent sale invalid (0–100).';
        if ($e === 'delivery_fee_invalid')   $errors[] = 'Delivery fee invalid.';
        if ($e === 'recipient_required')     $errors[] = 'Recipient name required.';
        if ($e === 'address_required')       $errors[] = 'Delivery address required.';
        if ($e === 'phone_required')         $errors[] = 'Delivery phone required.';
        if ($e === 'phone_invalid')          $errors[] = 'Delivery phone format invalid.';
        if ($e === 'server')                 $errors[] = 'Server error. Try again.';
    }
}

$successMsg = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'updated') $successMsg = 'Order updated.';
}

$fullName = trim(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CM: Edit Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-register { background:#fff; border-radius:1rem; padding:1rem; }
        .btn-icon img { width:1.125rem; height:1.125rem; filter:brightness(0) invert(1); }
        .btn-icon-inverted img { width:1.125rem; height:1.125rem; filter:invert(43%) sepia(6%) saturate(179%) hue-rotate(169deg) brightness(92%) contrast(88%); }
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
                <h4 class="mb-3">Edit Order</h4>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-3">
                        <?php foreach ($errors as $er): ?><div>- <?php echo $er; ?></div><?php endforeach; ?>
                    </div>
                <?php elseif ($successMsg !== ''): ?>
                    <div class="alert alert-success mb-3"><?php echo $successMsg; ?></div>
                <?php endif; ?>

                <form method="post" action="update.php">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Order ID</label>
                            <input type="text" class="form-control" value="<?php echo $order['order_id']; ?>" disabled>
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date ordered</label>
                            <input type="text" class="form-control" value="<?php echo $order['date_ordered']; ?>" disabled>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">User ID</label>
                            <input type="text" class="form-control" value="<?php echo $order['user_id']; ?>" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" value="<?php echo $order['username']; ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voucher code</label>
                            <input type="text" class="form-control" value="<?php echo ($order['voucher_code']!=='' && $order['voucher_code']!==null)?$order['voucher_code']:'—'; ?>" disabled>
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Payment status</label>
                            <select name="payment_status" id="payment_status" class="form-select">
                                <option value="pending"  <?php echo ($order['payment_status']==='pending'?'selected':''); ?>>pending</option>
                                <option value="paid"     <?php echo ($order['payment_status']==='paid'?'selected':''); ?>>paid</option>
                                <option value="refunded" <?php echo ($order['payment_status']==='refunded'?'selected':''); ?>>refunded</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Order status</label>
                            <select name="order_status" id="order_status" class="form-select">
                                <option value="pending"          <?php echo ($order['order_status']==='pending'?'selected':''); ?>>pending</option>
                                <option value="processing"       <?php echo ($order['order_status']==='processing'?'selected':''); ?>>processing</option>
                                <option value="shipped"          <?php echo ($order['order_status']==='shipped'?'selected':''); ?>>shipped</option>
                                <option value="completed"        <?php echo ($order['order_status']==='completed'?'selected':''); ?>>completed</option>
                                <option value="cancelled"        <?php echo ($order['order_status']==='cancelled'?'selected':''); ?>>cancelled</option>
                                <option value="requested_refund" <?php echo ($order['order_status']==='requested_refund'?'selected':''); ?>>requested_refund</option>
                                <option value="returned"         <?php echo ($order['order_status']==='returned'?'selected':''); ?>>returned</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Payment option</label>
                            <select name="payment_option" class="form-select">
                                <option value="" <?php echo ($order['payment_option']===''||$order['payment_option']===null?'selected':''); ?>>—</option>
                                <option value="Credit Card" <?php echo ($order['payment_option']==='Credit Card'?'selected':''); ?>>Credit Card</option>
                                <option value="COD" <?php echo ($order['payment_option']==='COD'?'selected':''); ?>>COD</option>
                                <option value="Bank Transfer" <?php echo ($order['payment_option']==='Bank Transfer'?'selected':''); ?>>Bank Transfer</option>
                                <option value="e-Wallet" <?php echo ($order['payment_option']==='e-Wallet'?'selected':''); ?>>e-Wallet</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">% Sale</label>
                            <input type="text" class="form-control" name="percent_sale" value="<?php echo $order['percent_sale']; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Delivery fee</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control" name="delivery_fee" value="<?php echo $order['delivery_fee']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Recipient name</label>
                            <input type="text" class="form-control" name="delivery_recipient" value="<?php echo $order['delivery_recipient']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Recipient phone</label>
                            <input type="text" class="form-control" name="delivery_phone" value="<?php echo $order['delivery_phone']; ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Delivery address</label>
                            <input type="text" class="form-control" name="delivery_address" value="<?php echo $order['delivery_address']; ?>">
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Completed at</label>
                            <input type="text" class="form-control" value="<?php echo $order['completed_at']; ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Customer name</label>
                            <input type="text" class="form-control" value="<?php echo $fullName; ?>" disabled>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



