<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// Accept ID from GET or POST
$id_raw = isset($_GET['id']) ? trim($_GET['id']) : (isset($_POST['id']) ? trim($_POST['id']) : '');
$exp_id = ctype_digit($id_raw) ? (int)$id_raw : 0;

if ($exp_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Ensure expense exists
$exist = $conn->prepare("SELECT exp_id FROM expenses WHERE exp_id = ?");
if (!$exist) {
    header('Location: index.php?error=server');
    exit;
}
$exist->bind_param('i', $exp_id);
$exist->execute();
$exist->store_result();
if ($exist->num_rows === 0) {
    $exist->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exist->close();

// Transaction (simple)
$conn->begin_transaction();
try {
    $del = $conn->prepare("DELETE FROM expenses WHERE exp_id = ?");
    if (!$del) throw new Exception('prep');
    $del->bind_param('i', $exp_id);
    if (!$del->execute()) {
        $del->close();
        throw new Exception('exec');
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