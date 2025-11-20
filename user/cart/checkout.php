<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');
if (!$conn) { die('DB error'); }

   
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $params = [];
    if (isset($_GET['error']) && $_GET['error'] !== '') $params['error'] = $_GET['error'];
    if (isset($_GET['status']) && $_GET['status'] !== '') $params['status'] = $_GET['status'];
    $qs = !empty($params) ? ('?' . http_build_query($params)) : '';
    header('Location: /carriemart/user/cart/checkout-form.php' . $qs);
    exit;
}
if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    header('Location: /carriemart/main/products.php?error=login_required');
    exit;
}

$userId = (int)$_SESSION['user_id'];

   
$itemsRaw = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
$delivery_recipient = isset($_POST['delivery_recipient']) ? trim($_POST['delivery_recipient']) : '';
$delivery_address   = isset($_POST['delivery_address']) ? trim($_POST['delivery_address']) : '';
$delivery_phone     = isset($_POST['delivery_phone']) ? trim($_POST['delivery_phone']) : '';
$payment_option_raw = isset($_POST['payment_option']) ? trim($_POST['payment_option']) : 'COD';
   
$payment_option_map = [
    'cash_on_delivery' => 'COD',
    'credit_card' => 'Credit Card',
    'gcash' => 'e-Wallet'
];
$payment_option = isset($payment_option_map[$payment_option_raw]) ? $payment_option_map[$payment_option_raw] : $payment_option_raw;
   
if (!in_array($payment_option, ['COD', 'Credit Card', 'Bank Transfer', 'e-Wallet'])) {
    $payment_option = 'COD';
}
$voucher_code_raw   = isset($_POST['voucher']) ? trim($_POST['voucher']) : '';

   
$items = [];
foreach ($itemsRaw as $pid => $qty) {
    if (ctype_digit((string)$pid)) {
        $q = (int)$qty;
        if ($q > 0) $items[(int)$pid] = $q;
    }
}
if (empty($items)) {
    header('Location: /carriemart/user/cart/checkout-form.php?error=no_items');
    exit;
}

   
$cartId = null;
$sc = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
if (!$sc) {
    error_log('Failed to prepare cart query: ' . $conn->error);
    header('Location: /carriemart/user/cart/cart.php?error=server');
    exit;
}
$sc->bind_param('i', $userId);
$sc->execute();
$sc->bind_result($cartId);
if (!$sc->fetch()) $cartId = null;
$sc->close();
if ($cartId === null) {
    header('Location: /carriemart/user/cart/cart.php?error=no_cart');
    exit;
}

   
$pids = array_keys($items);
$placeholders = implode(',', array_fill(0, count($pids), '?'));
$types = str_repeat('i', count($pids));

$sql = "
SELECT p.product_id, p.product_name, p.retail_price, p.stock_level, p.is_active
FROM products p
WHERE p.product_id IN ($placeholders)
FOR UPDATE
";

$conn->begin_transaction();

try {
      
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare product query: ' . $conn->error);
    }
    $bind = [$types];
    foreach ($pids as $id) { $bind[] = $id; }
    $refs = [];
    foreach ($bind as $k => $v) { $refs[$k] = &$bind[$k]; }
    call_user_func_array([$stmt, 'bind_param'], $refs);

    $stmt->execute();
    $stmt->bind_result($pid, $pname, $price, $stock, $is_active);

    $snap = [];
    while ($stmt->fetch()) {
        $snap[$pid] = [
            'name' => $pname,
            'price' => (float)$price,
            'stock' => (int)$stock,
            'active' => (int)$is_active
        ];
    }
    $stmt->close();

      
    $errors = [];
    $subtotal = 0.00;
    foreach ($items as $pid => $qty) {
        if (!isset($snap[$pid])) { $errors[] = 'not_found_'.$pid; continue; }
        if ($snap[$pid]['active'] !== 1) { $errors[] = 'inactive_'.$pid; continue; }
        if ($snap[$pid]['stock'] < $qty) { $errors[] = 'stock_'.$pid; continue; }
        $subtotal += $snap[$pid]['price'] * $qty;
    }
    if (!empty($errors)) {
        $conn->rollback();
        $ids = implode(',', $pids);
        header('Location: /carriemart/user/cart/checkout-form.php?ids='.$ids.'&error='.implode(',', $errors));
        exit;
    }

      
    $voucher_code = null;
    $percent_sale = 0;
    $discount = 0.00;
    $delivery_fee = 50.00;

    if ($voucher_code_raw !== '') {
        $v = $conn->prepare("
            SELECT voucher_code, percent_sale, min_purchase_amount, max_discount_amount, from_date, to_date, is_active
            FROM vouchers
            WHERE voucher_code = ?
            LIMIT 1
        ");
        if ($v) {
            $v->bind_param('s', $voucher_code_raw);
            $v->execute();
            $v->bind_result($vc, $vperc, $vmin, $vmax, $vfrom, $vto, $vactive);
            if ($v->fetch()) {
                $validDate = true;
                $today = date('Y-m-d');
                if (!is_null($vfrom) && $vfrom !== '' && $today < $vfrom) $validDate = false;
                if (!is_null($vto) && $vto !== '' && $today > $vto) $validDate = false;
                $meetsMin = ($subtotal >= (float)$vmin);

                if ((int)$vactive === 1 && $validDate && $meetsMin && (int)$vperc > 0) {
                    $voucher_code = $vc;
                    $percent_sale = (int)$vperc;
                    $rawDisc = $subtotal * ($percent_sale / 100.0);
                    if (!is_null($vmax) && (float)$vmax > 0) {
                        $discount = min($rawDisc, (float)$vmax);
                    } else {
                        $discount = $rawDisc;
                    }
                }
            }
            $v->close();
        }
    }

      
    $currentTimestamp = date('Y-m-d H:i:s');

      
    $ord = $conn->prepare("
        INSERT INTO orders
            (user_id, voucher_code, date_ordered, payment_status, order_status, payment_option,
             delivery_recipient, delivery_address, delivery_phone, percent_sale, delivery_fee, created_at)
        VALUES (?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$ord) { throw new Exception('prep_order'); }

    $ord->bind_param(
        'issssssids',
        $userId,
        $voucher_code,
        $currentTimestamp,
        $payment_option,
        $delivery_recipient,
        $delivery_address,
        $delivery_phone,
        $percent_sale,
        $delivery_fee,
        $currentTimestamp
    );

    if (!$ord->execute()) { $ord->close(); throw new Exception('exec_order'); }
    $orderId = $ord->insert_id;
    $ord->close();

      
    $line = $conn->prepare("INSERT INTO product_order (order_id, product_id, quantity, unit_price, created_at) VALUES (?, ?, ?, ?, ?)");
    if (!$line) { throw new Exception('prep_line'); }

    $updStock = $conn->prepare("UPDATE products SET stock_level = stock_level - ? WHERE product_id = ?");
    if (!$updStock) { $line->close(); throw new Exception('prep_stock'); }

    foreach ($items as $pid => $qty) {
        $unit = $snap[$pid]['price'];
        $line->bind_param('iiids', $orderId, $pid, $qty, $unit, $currentTimestamp);
        if (!$line->execute()) { $line->close(); $updStock->close(); throw new Exception('exec_line'); }

        $updStock->bind_param('ii', $qty, $pid);
        if (!$updStock->execute()) { $line->close(); $updStock->close(); throw new Exception('exec_stock'); }
    }
    $line->close();
    $updStock->close();

      
    $ph = implode(',', array_fill(0, count($pids), '?'));
    $del = $conn->prepare("DELETE cp FROM cart_product cp JOIN cart c ON c.cart_id = cp.cart_id WHERE c.user_id = ? AND cp.product_id IN ($ph)");
    if ($del) {
        $types = 'i' . str_repeat('i', count($pids));
        $bind = [$types, $userId];
        foreach ($pids as $id) { $bind[] = $id; }
        $refs = [];
        foreach ($bind as $k => $v) { $refs[$k] = &$bind[$k]; }
        call_user_func_array([$del, 'bind_param'], $refs);
        $del->execute();
        $del->close();
    }

    $conn->commit();

      
    $date_ordered = '';
    $payment_status = 'pending';
    $order_status = 'pending';
    $db_voucher_code = $voucher_code ? $voucher_code : '';
    $db_percent_sale = (int)$percent_sale;
    $db_delivery_fee = (float)$delivery_fee;

    $oi = $conn->prepare("SELECT date_ordered, payment_status, order_status, voucher_code, percent_sale, delivery_fee FROM orders WHERE order_id = ? LIMIT 1");
    if ($oi) {
        $oi->bind_param('i', $orderId);
        $oi->execute();
        $oi->bind_result($date_ordered, $payment_status, $order_status, $db_voucher_code, $db_percent_sale, $db_delivery_fee);
        $oi->fetch();
        $oi->close();
    }

      
    $userEmail = null; $fullName = '';
    $acc = $conn->prepare("SELECT email, first_name, last_name FROM accounts WHERE user_id = ? LIMIT 1");
    if ($acc) {
        $acc->bind_param('i', $userId);
        $acc->execute();
        $acc->bind_result($email, $fname, $lname);
        if ($acc->fetch()) { $userEmail = $email; $fullName = trim(($fname ? $fname : '') . ' ' . ($lname ? $lname : '')); }
        $acc->close();
    }

      
    $subtotal = 0.0;
    foreach ($items as $pid => $qty) {
        $subtotal = $subtotal + ($snap[$pid]['price'] * $qty);
    }
    $final_discount = $discount > 0 ? $discount : 0.0;
    $final_delivery = $db_delivery_fee;
    $grand_total = $subtotal - $final_discount + $final_delivery;

      
    $itemsText = "";
    $itemsHtml = "";
    foreach ($items as $pid => $qty) {
        $name = $snap[$pid]['name'];
        $unit = $snap[$pid]['price'];
        $line = $unit * $qty;
        $itemsText = $itemsText . "- " . $name . " | qty: " . $qty . " | unit: " . $unit . " | final: " . $line . "\n";
        $itemsHtml = $itemsHtml . "<li>" . $name . " | qty: " . $qty . " | unit: " . $unit . " | final: " . $line . "</li>";
    }

    $subject  = "CarrieMart Order #" . $orderId;

      
    $textBody = ""
        . "Order ID: #" . $orderId . "\n"
        . "Date Ordered: " . $date_ordered . "\n"
        . "Payment Status: " . $payment_status . "\n"
        . "Order Status: " . $order_status . "\n"
        . "Payment Option: " . $payment_option . "\n"
        . "Voucher Code: " . ($db_voucher_code ? $db_voucher_code : "none") . "\n"
        . "Percent Sale: " . $db_percent_sale . "\n"
        . "Delivery Fee: " . $final_delivery . "\n"
        . "Delivery Recipient: " . $delivery_recipient . "\n"
        . "Delivery Address: " . $delivery_address . "\n"
        . "Delivery Phone: " . $delivery_phone . "\n\n"
        . "Items:\n"
        . $itemsText . "\n"
        . "Subtotal: " . $subtotal . "\n"
        . "Discount: " . $final_discount . "\n"
        . "Total: " . $grand_total . "\n";

      
    $htmlBody = ""
        . "<h4>Order #" . $orderId . "</h4>"
        . "<p>Date Ordered: " . $date_ordered . "</p>"
        . "<p>Payment Status: " . $payment_status . "<br>"
        . "Order Status: " . $order_status . "<br>"
        . "Payment Option: " . $payment_option . "<br>"
        . "Voucher Code: " . ($db_voucher_code ? $db_voucher_code : "none") . "<br>"
        . "Percent Sale: " . $db_percent_sale . "<br>"
        . "Delivery Fee: " . $final_delivery . "</p>"
        . "<p><strong>Deliver To</strong><br>"
        . $delivery_recipient . "<br>"
        . $delivery_address . "<br>"
        . "Phone: " . $delivery_phone . "</p>"
        . "<p><strong>Items</strong></p>"
        . "<ul>" . $itemsHtml . "</ul>"
        . "<p>Subtotal: " . $subtotal . "<br>"
        . "Discount: " . $final_discount . "<br>"
        . "<strong>Total: " . $grand_total . "</strong></p>";

      
    if ($userEmail) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/Exception.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/PHPMailer.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/SMTP.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'da9dbc54de8e7e';
            $mail->Password   = 'd1cfc747ddfe4b';
            $mail->Port       = 2525;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom('no-reply@carriemart.com', 'Carriemart');
            $mail->addAddress($userEmail, $fullName ? $fullName : 'Customer');

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('Mail error: ' . $mail->ErrorInfo);
        }
    }

      
    header('Location: /carriemart/user/cart/cart.php?status=order_placed&order_id=' . $orderId);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $ids = implode(',', $pids);
    header('Location: /carriemart/user/cart/checkout-form.php?ids='.$ids.'&error=server');
    exit;
}
?>