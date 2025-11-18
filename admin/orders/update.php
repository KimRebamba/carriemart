<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/Exception.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/PHPMailer/src/SMTP.php');

function get_order_email_payload(mysqli $conn, int $orderId): ?array {
    $sql = "SELECT order_id, user_id, date_ordered, payment_status, order_status, voucher_code,
                   percent_sale, delivery_fee, delivery_recipient, delivery_address, delivery_phone,
                   username, email, product_order_id, product_id, product_name, quantity, unit_price, line_total
            FROM order_transaction_details
            WHERE order_id = ?
            ORDER BY product_order_id ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Order email payload prepare failed: ' . $conn->error);
        return null;
    }
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $stmt->bind_result(
        $order_id_row,
        $user_id,
        $date_ordered,
        $payment_status,
        $order_status,
        $voucher_code,
        $percent_sale,
        $delivery_fee,
        $delivery_recipient,
        $delivery_address,
        $delivery_phone,
        $username,
        $email,
        $product_order_id,
        $product_id,
        $product_name,
        $quantity,
        $unit_price,
        $line_total
    );
    $lines = [];
    $subtotal = 0.0;
    $orderMeta = null;
    while ($stmt->fetch()) {
        if ($orderMeta === null) {
            $orderMeta = [
                'order_id' => $order_id_row,
                'user_id' => $user_id,
                'date_ordered' => $date_ordered,
                'payment_status' => $payment_status,
                'order_status' => $order_status,
                'voucher_code' => $voucher_code,
                'percent_sale' => (int)$percent_sale,
                'delivery_fee' => (float)$delivery_fee,
                'delivery_recipient' => $delivery_recipient,
                'delivery_address' => $delivery_address,
                'delivery_phone' => $delivery_phone,
                'username' => $username,
                'email' => $email
            ];
        }
        $lines[] = [
            'product_order_id' => $product_order_id,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'quantity' => (int)$quantity,
            'unit_price' => (float)$unit_price,
            'line_total' => (float)$line_total
        ];
        $subtotal += (float)$line_total;
    }
    $stmt->close();
    if ($orderMeta === null || empty($lines)) {
        return null;
    }
    $discountAmount = $orderMeta['percent_sale'] > 0
        ? $subtotal * ($orderMeta['percent_sale'] / 100)
        : 0.0;
    $grandTotal = $subtotal - $discountAmount + $orderMeta['delivery_fee'];
    $orderMeta['lines'] = $lines;
    $orderMeta['subtotal'] = $subtotal;
    $orderMeta['discount_amount'] = $discountAmount;
    $orderMeta['grand_total'] = $grandTotal;
    return $orderMeta;
}

function send_order_update_email(mysqli $conn, int $orderId): void {
    $orderData = get_order_email_payload($conn, $orderId);
    if (!$orderData || empty($orderData['email'])) {
        return;
    }
    $customerName = $orderData['delivery_recipient']
        ?: ($orderData['username'] ?: 'Customer');
    $lineRows = '';
    foreach ($orderData['lines'] as $line) {
        $lineRows .= sprintf(
            '<tr><td>%s</td><td style="text-align:right;">%d</td><td style="text-align:right;">₱%s</td><td style="text-align:right;">₱%s</td></tr>',
            htmlspecialchars($line['product_name'], ENT_QUOTES, 'UTF-8'),
            $line['quantity'],
            number_format($line['unit_price'], 2),
            number_format($line['line_total'], 2)
        );
    }
    $body = sprintf(
        '<p>Hi %s,</p>
         <p>Your order <strong>#%d</strong> has been updated to <strong>%s</strong>.</p>
         <table width="100%%" cellpadding="6" cellspacing="0" border="1" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:14px;">
            <thead>
                <tr>
                    <th align="left">Item</th>
                    <th align="right">Qty</th>
                    <th align="right">Unit Price</th>
                    <th align="right">Line Total</th>
                </tr>
            </thead>
            <tbody>%s</tbody>
         </table>
         <p>Subtotal: ₱%s<br>
            Discount (%d%%): ₱%s<br>
            Delivery Fee: ₱%s<br>
            <strong>Grand Total: ₱%s</strong>
         </p>
         <p>Thank you for shopping with CarrieMart.</p>',
        htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8'),
        $orderData['order_id'],
        htmlspecialchars($orderData['order_status'], ENT_QUOTES, 'UTF-8'),
        $lineRows,
        number_format($orderData['subtotal'], 2),
        $orderData['percent_sale'],
        number_format($orderData['discount_amount'], 2),
        number_format($orderData['delivery_fee'], 2),
        number_format($orderData['grand_total'], 2)
    );
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = MAILTRAP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAILTRAP_USERNAME;
        $mail->Password = MAILTRAP_PASSWORD;
        $mail->Port = MAILTRAP_PORT;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->setFrom('no-reply@carriemart.com', 'CarrieMart');
        $mail->addAddress($orderData['email'], $customerName);
        $mail->isHTML(true);
        $mail->Subject = sprintf('Update for Order #%d', $orderData['order_id']);
        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log('Order update email failed: ' . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$order_id        = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$payment_status  = trim($_POST['payment_status'] ?? '');
$order_status    = trim($_POST['order_status'] ?? '');
$payment_option  = trim($_POST['payment_option'] ?? '');
$percent_sale_raw= trim($_POST['percent_sale'] ?? '');
$delivery_fee_raw= trim($_POST['delivery_fee'] ?? '');
$delivery_recipient = trim($_POST['delivery_recipient'] ?? '');
$delivery_address   = trim($_POST['delivery_address'] ?? '');
$delivery_phone     = trim($_POST['delivery_phone'] ?? '');
$posted_voucher     = trim($_POST['voucher_code'] ?? ''); // read-only on form

if ($order_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$errors = [];

// Load existing order
$sel = $conn->prepare("SELECT voucher_code, completed_at FROM orders WHERE order_id = ?");
if (!$sel) {
    header('Location: order-form.php?id='.$order_id.'&error=server');
    exit;
}
$sel->bind_param('i', $order_id);
$sel->execute();
$sel->bind_result($existing_voucher, $existing_completed_at);
if (!$sel->fetch()) {
    $sel->close();
    header('Location: index.php?error=not_found');
    exit;
}
$sel->close();

// Validate payment_status enum
$allowedPay = ['pending','paid','refunded'];
if (!in_array($payment_status, $allowedPay, true)) {
    $errors[] = 'payment_status_invalid';
}

// Validate order_status enum
$allowedOrder = ['pending','processing','shipped','completed','cancelled','requested_refund','returned'];
if (!in_array($order_status, $allowedOrder, true)) {
    $errors[] = 'order_status_invalid';
}

// Voucher read-only check
if ($posted_voucher !== '' && $posted_voucher !== $existing_voucher) {
    $errors[] = 'voucher_invalid';
}

// Percent sale
$percent_sale_raw = ($percent_sale_raw === '' ? '0' : $percent_sale_raw);
if (!ctype_digit($percent_sale_raw)) {
    $errors[] = 'percent_sale_invalid';
} else {
    $percent_sale = (int)$percent_sale_raw;
    if ($percent_sale < 0 || $percent_sale > 100) $errors[] = 'percent_sale_invalid';
}
if (!isset($percent_sale)) $percent_sale = 0;

// Delivery fee
$fee_clean = str_replace(['₱',',',' '], '', $delivery_fee_raw);
if ($fee_clean === '') $fee_clean = '0';
if (!is_numeric($fee_clean) || (float)$fee_clean < 0) {
    $errors[] = 'delivery_fee_invalid';
}
$delivery_fee = (float)$fee_clean;

// Recipient
if ($delivery_recipient === '') $errors[] = 'recipient_required';

// Address
if ($delivery_address === '') $errors[] = 'address_required';

// Phone
if ($delivery_phone === '') {
    $errors[] = 'phone_required';
} else {
    $digits = preg_replace('/\D/','',$delivery_phone);
    if (!preg_match('/^09\d{9}$/', $digits)) $errors[] = 'phone_invalid';
    $delivery_phone = $delivery_phone; // keep original formatting
}

if (!empty($errors)) {
    header('Location: order-form.php?id='.$order_id.'&error='.implode(',', $errors));
    exit;
}

// completed_at logic
$completed_at_new = ($order_status === 'completed')
    ? ($existing_completed_at ? $existing_completed_at : date('Y-m-d H:i:s'))
    : null;

// Update
$sql = "UPDATE orders
        SET payment_status = ?,
            order_status = ?,
            payment_option = NULLIF(?, ''),
            delivery_recipient = ?,
            delivery_address = ?,
            delivery_phone = ?,
            percent_sale = ?,
            delivery_fee = ?,
            completed_at = ?
        WHERE order_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: order-form.php?id='.$order_id.'&error=server');
    exit;
}

// Fixed bind_param - remove the confusing conditional logic
$stmt->bind_param(
    'ssssssidsi',        // Type definition: 10 parameters
    $payment_status,     // s - string
    $order_status,       // s - string
    $payment_option,     // s - string
    $delivery_recipient, // s - string
    $delivery_address,   // s - string
    $delivery_phone,     // s - string
    $percent_sale,       // i - integer
    $delivery_fee,       // d - double
    $completed_at_new,   // s - string (nullable)
    $order_id            // i - integer
);

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: order-form.php?id='.$order_id.'&error=server');
    exit;
}
$stmt->close();

send_order_update_email($conn, $order_id);

header('Location: order-form.php?id='.$order_id.'&status=updated');
exit;