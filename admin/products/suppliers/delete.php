<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$id_raw = isset($_GET['id']) ? trim($_GET['id']) : (isset($_POST['id']) ? trim($_POST['id']) : '');
$supplier_id = ctype_digit($id_raw) ? (int)$id_raw : 0;

if ($supplier_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

   
$exist = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
if (!$exist) {
    header('Location: index.php?error=server');
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

   
$conn->begin_transaction();
try {
    $del = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
    if (!$del) throw new Exception('server');
    $del->bind_param('i', $supplier_id);
    if (!$del->execute()) {
        $del->close();
        throw new Exception('server');
    }
    $del->close();

    $conn->commit();
    header('Location: index.php?status=deleted');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: index.php?error=server');
    exit;
}