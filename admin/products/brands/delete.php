<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

// Accept id from GET or POST (index.php links here)
$id_raw = isset($_GET['id']) ? trim($_GET['id']) : (isset($_POST['id']) ? trim($_POST['id']) : '');
$brand_id = ctype_digit($id_raw) ? (int)$id_raw : 0;

if ($brand_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Ensure brand exists and get logo path
$logo_url = null;
$exist = $conn->prepare("SELECT brand_id, logo_url FROM brands WHERE brand_id = ?");
if (!$exist) {
    header('Location: index.php?error=server');
    exit;
}
$exist->bind_param('i', $brand_id);
$exist->execute();
$exist->bind_result($bid, $logo_url);
if (!$exist->fetch()) {
    $exist->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exist->close();

// Deleting a brand will SET NULL brand_id on products (per schema)
$conn->begin_transaction();

try {
    $del = $conn->prepare("DELETE FROM brands WHERE brand_id = ?");
    if (!$del) throw new Exception('server');
    $del->bind_param('i', $brand_id);
    if (!$del->execute()) {
        $del->close();
        throw new Exception('server');
    }
    $del->close();

    $conn->commit();

    // Delete associated logo file if present
    if ($logo_url) {
        $fs = $_SERVER['DOCUMENT_ROOT'] . $logo_url;
        if (is_file($fs)) {
            @unlink($fs);
        }
    }

    header('Location: index.php?status=deleted');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: index.php?error=server');
    exit;
}