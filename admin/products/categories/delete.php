<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$id_raw = isset($_GET['id']) ? trim($_GET['id']) : (isset($_POST['id']) ? trim($_POST['id']) : '');
$category_id = ctype_digit($id_raw) ? (int)$id_raw : 0;

if ($category_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

   
$photo_url = null;
$exist = $conn->prepare("SELECT category_id, photo_url FROM categories WHERE category_id = ?");
if (!$exist) {
    header('Location: index.php?error=server');
    exit;
}
$exist->bind_param('i', $category_id);
$exist->execute();
$exist->bind_result($cid, $photo_url);
if (!$exist->fetch()) {
    $exist->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exist->close();

   

   
$conn->begin_transaction();

try {
    $del = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    if (!$del) throw new Exception('server');
    $del->bind_param('i', $category_id);
    if (!$del->execute()) {
        $del->close();
        throw new Exception('server');
    }
    $del->close();

    $conn->commit();

      
    if ($photo_url) {
        $fs = $_SERVER['DOCUMENT_ROOT'] . $photo_url;
        if (is_file($fs)) @unlink($fs);
    }

    header('Location: index.php?status=deleted');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: index.php?error=server');
    exit;
}
?>