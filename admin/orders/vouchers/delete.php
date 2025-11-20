<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$conn->begin_transaction();

try {
      
    $sel = $conn->prepare("SELECT voucher_code FROM vouchers WHERE voucher_id = ?");
    if (!$sel) throw new Exception('server');
    $sel->bind_param('i', $id);
    $sel->execute();
    $sel->bind_result($voucher_code);
    if (!$sel->fetch()) {
        $sel->close();
        throw new Exception('not_found');
    }
    $sel->close();

      
    $ordersAffected = 0;
    $cnt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE voucher_code = ?");
    if ($cnt) {
        $cnt->bind_param('s', $voucher_code);
        $cnt->execute();
        $cnt->bind_result($ordersAffected);
        $cnt->fetch();
        $cnt->close();
    }

      
    $del = $conn->prepare("DELETE FROM vouchers WHERE voucher_id = ?");
    if (!$del) throw new Exception('server');
    $del->bind_param('i', $id);
    if (!$del->execute() || $del->affected_rows !== 1) {
        $del->close();
        throw new Exception('delete_fail');
    }
    $del->close();

    $conn->commit();
    header('Location: index.php?status=deleted&id=' . $id . '&orders=' . $ordersAffected);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $code = $e->getMessage();
    if ($code === 'not_found') {
        header('Location: index.php?error=not_found');
    } else {
        header('Location: index.php?error=server');
    }
    exit;
}