<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

   
$id_raw = isset($_GET['id']) ? trim($_GET['id']) : (isset($_POST['id']) ? trim($_POST['id']) : '');
$product_id = ctype_digit($id_raw) ? (int)$id_raw : 0;

if ($product_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

   
$exist = $conn->prepare("SELECT product_id FROM products WHERE product_id = ?");
if (!$exist) {
    header('Location: index.php?error=server');
    exit;
}
$exist->bind_param('i', $product_id);
$exist->execute();
$exist->store_result();
if ($exist->num_rows === 0) {
    $exist->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exist->close();

   
$inUse = $conn->prepare("SELECT 1 FROM product_order WHERE product_id = ? LIMIT 1");
if (!$inUse) {
    header('Location: index.php?error=server');
    exit;
}
$inUse->bind_param('i', $product_id);
$inUse->execute();
$inUse->store_result();
if ($inUse->num_rows > 0) {
    $inUse->close();
    header('Location: index.php?error=in_use');
    exit;
}
$inUse->close();

   
$photoFiles = [];
$ps = $conn->prepare("SELECT photo_url FROM product_photos WHERE product_id = ?");
if ($ps) {
    $ps->bind_param('i', $product_id);
    $ps->execute();
    $ps->bind_result($photo_url);
    while ($ps->fetch()) {
        if ($photo_url) {
            $fsPath = $_SERVER['DOCUMENT_ROOT'] . $photo_url;
            $photoFiles[] = $fsPath;
        }
    }
    $ps->close();
}

$conn->begin_transaction();

try {
      
    $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    if (!$del) {
        throw new Exception('server');
    }
    $del->bind_param('i', $product_id);
    if (!$del->execute()) {
        $del->close();
        throw new Exception('server');
    }
    $del->close();

    $conn->commit();

      
    if (!empty($photoFiles)) {
        foreach ($photoFiles as $p) {
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }

    header('Location: index.php?status=deleted');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: index.php?error=server');
    exit;
}