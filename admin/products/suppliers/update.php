<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errors = [];

// Supplier ID
$id_raw = isset($_POST['supplier_id']) ? trim($_POST['supplier_id']) : '';
$supplier_id = ctype_digit($id_raw) ? (int)$id_raw : 0;
if ($supplier_id <= 0) {
    header('Location: supplier-form.php?id='.$supplier_id.'&error=invalid_id');
    exit;
}

// Ensure supplier exists + get current data
$exist = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
if (!$exist) {
    header('Location: supplier-form.php?id='.$supplier_id.'&error=server');
    exit;
}
$exist->bind_param('i', $supplier_id);
$exist->execute();
$exist->store_result();
if ($exist->num_rows === 0) {
    $exist->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exist->close();

// Inputs
$supplier_name  = isset($_POST['supplier_name']) ? trim($_POST['supplier_name']) : '';
$contact_person = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : '';
$contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
$email          = isset($_POST['email']) ? trim($_POST['email']) : '';
$address        = isset($_POST['address']) ? trim($_POST['address']) : '';
$is_active_raw  = isset($_POST['is_active']) ? trim($_POST['is_active']) : '1';

// Validation
if ($supplier_name === '') $errors[] = 'name_required';
if (!in_array($is_active_raw, ['0','1'], true)) $errors[] = 'status_invalid';
$is_active = ($is_active_raw === '1') ? 1 : 0;

if ($email !== '') {
    if (strlen($email) > 100 || strpos($email, '@') === false) {
        $errors[] = 'email_invalid';
    }
}

// Duplicate name excluding self
$dup = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = ? AND supplier_id <> ? LIMIT 1");
if ($dup) {
    $dup->bind_param('si', $supplier_name, $supplier_id);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

// Redirect on errors
if (!empty($errors)) {
    header('Location: supplier-form.php?id='.$supplier_id.'&error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

// Update
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, contact_person = ?, contact_number = ?, email = ?, address = ?, is_active = ? WHERE supplier_id = ?");
    if (!$stmt) throw new Exception('server');

    $cp   = ($contact_person !== '' ? $contact_person : null);
    $cnum = ($contact_number !== '' ? $contact_number : null);
    $eml  = ($email !== '' ? $email : null);
    $addr = ($address !== '' ? $address : null);

    $stmt->bind_param('ssssssi', $supplier_name, $cp, $cnum, $eml, $addr, $is_active, $supplier_id);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('server');
    }
    $stmt->close();

    $conn->commit();
    header('Location: supplier-form.php?id='.$supplier_id.'&status=updated');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: supplier-form.php?id='.$supplier_id.'&error=server');
    exit;
}
?>