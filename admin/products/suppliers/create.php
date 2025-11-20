<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: supplier-form.php');
    exit;
}

$errors = [];

   
$supplier_name  = isset($_POST['supplier_name']) ? trim($_POST['supplier_name']) : '';
$contact_person = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : '';
$contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
$email          = isset($_POST['email']) ? trim($_POST['email']) : '';
$address        = isset($_POST['address']) ? trim($_POST['address']) : '';
$is_active_raw  = isset($_POST['is_active']) ? trim($_POST['is_active']) : '1';

   
if ($supplier_name === '') $errors[] = 'name_required';
if (!in_array($is_active_raw, ['0','1'], true)) $errors[] = 'status_invalid';
$is_active = $is_active_raw === '1' ? 1 : 0;

if ($email !== '') {
    if (strlen($email) > 100 || strpos($email, '@') === false) {
        $errors[] = 'email_invalid';
    }
}

   
$dup = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = ? LIMIT 1");
if ($dup) {
    $dup->bind_param('s', $supplier_name);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

   
if (!empty($errors)) {
    header('Location: supplier-form.php?error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

   
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, contact_number, email, address, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception('server');

    $cp   = ($contact_person !== '' ? $contact_person : null);
    $cnum = ($contact_number !== '' ? $contact_number : null);
    $eml  = ($email !== '' ? $email : null);
    $addr = ($address !== '' ? $address : null);

    $stmt->bind_param('sssssi', $supplier_name, $cp, $cnum, $eml, $addr, $is_active);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('server');
    }
    $newId = $stmt->insert_id;
    $stmt->close();

    $conn->commit();
    header('Location: supplier-form.php?id=' . $newId . '&status=created');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    header('Location: supplier-form.php?error=server');
    exit;
}