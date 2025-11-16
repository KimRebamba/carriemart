<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// Accept id from GET or POST
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

//Optional: prevent deleting the currently logged-in user (uncomment if desired)
if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id) {
    header('Location: index.php?error=cannot_delete_self');
    exit;
}

$photoUrl = '';

$conn->begin_transaction();

try {
    // Fetch profile photo path first (so we can delete the file after DB commit)
    $sel = $conn->prepare("SELECT profile_photo_url FROM accounts WHERE user_id = ?");
    if (!$sel) { throw new Exception('Prepare select failed'); }
    $sel->bind_param('i', $id);
    $sel->execute();
    $sel->bind_result($photoUrl);
    if (!$sel->fetch()) {
        $sel->close();
        throw new Exception('Not found');
    }
    $sel->close();

    // Rely on FK rules from schema.sql:
    // - cart has ON DELETE CASCADE -> cart_product also cascades
    // - orders.user_id, product_review.user_id have ON DELETE SET NULL
    $del = $conn->prepare("DELETE FROM accounts WHERE user_id = ?");
    if (!$del) { throw new Exception('Prepare delete failed'); }
    $del->bind_param('i', $id);
    if (!$del->execute() || $del->affected_rows !== 1) {
        $del->close();
        throw new Exception('Delete failed');
    }
    $del->close();

    $conn->commit();

    // After successful commit, try to remove profile photo file (best-effort)
    if (!empty($photoUrl) && strpos($photoUrl, '/carriemart/uploads/profile_photos/') === 0) {
        $fsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . $photoUrl;
        if (is_file($fsPath)) {
            if($photoUrl !== '/carriemart/uploads/profile_photos/default.jpg'){
 @unlink($fsPath);
            }
        }
    }

    header('Location: index.php?status=deleted&id=' . $id);
    exit;

} catch (Throwable $e) {
    $conn->rollback();
    header('Location: index.php?error=server');
    exit;
}


