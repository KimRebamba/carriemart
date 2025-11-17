<?php
// filepath: c:\xampp_for_carriemart\htdocs\carriemart\admin\employees\delete.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$conn->begin_transaction();

try {
    // Ensure employee exists
    $sel = $conn->prepare("SELECT emp_id FROM employees WHERE emp_id = ?");
    if (!$sel) throw new Exception('prep_select');
    $sel->bind_param('i', $id);
    $sel->execute();
    $sel->bind_result($emp_id);
    if (!$sel->fetch()) {
        $sel->close();
        throw new Exception('not_found');
    }
    $sel->close();

    // (Optional) Count salaries before delete (will cascade)
    $sc = 0;
    $countSal = $conn->prepare("SELECT COUNT(*) FROM salaries WHERE emp_id = ?");
    if ($countSal) {
        $countSal->bind_param('i', $id);
        $countSal->execute();
        $countSal->bind_result($sc);
        $countSal->fetch();
        $countSal->close();
    }

    // Delete employee (salaries cascade automatically)
    $del = $conn->prepare("DELETE FROM employees WHERE emp_id = ?");
    if (!$del) throw new Exception('prep_delete');
    $del->bind_param('i', $id);
    if (!$del->execute() || $del->affected_rows !== 1) {
        $del->close();
        throw new Exception('delete_fail');
    }
    $del->close();

    $conn->commit();

    // Redirect with status and optional cascade info
    header('Location: index.php?status=deleted&id=' . $id . '&salaries_removed=' . $sc);
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