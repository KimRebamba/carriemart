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
      
    $sel = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ?");
    if (!$sel) throw new Exception('server');
    $sel->bind_param('i', $id);
    $sel->execute();
    $sel->bind_result($oid);
    if (!$sel->fetch()) {
        $sel->close();
        throw new Exception('not_found');
    }
    $sel->close();

      
    $lineCount = 0;
    $cnt = $conn->prepare("SELECT COUNT(*) FROM product_order WHERE order_id = ?");
    if ($cnt) {
        $cnt->bind_param('i', $id);
        $cnt->execute();
        $cnt->bind_result($lineCount);
        $cnt->fetch();
        $cnt->close();
    }

      
    $del = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    if (!$del) throw new Exception('server');
    $del->bind_param('i', $id);
    if (!$del->execute() || $del->affected_rows !== 1) {
        $del->close();
        throw new Exception('delete_fail');
    }
    $del->close();

    $conn->commit();
    header('Location: index.php?status=deleted&id=' . $id . '&lines=' . $lineCount);
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
?>