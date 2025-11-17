<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: category-form.php');
    exit;
}

$errors = [];

$category_name   = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
$description     = isset($_POST['description']) ? trim($_POST['description']) : '';
$is_active_raw   = isset($_POST['is_active']) ? trim($_POST['is_active']) : '1';

if ($category_name === '') {
    $errors[] = 'name_required';
}

if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = $is_active_raw === '1' ? 1 : 0;

$maxSize = 5 * 1024 * 1024;
$allowedMime = ['image/jpeg','image/png','image/gif'];
$photo_url = null;
$uploadedFsPath = null;

if (isset($_FILES['photo_file']) && is_array($_FILES['photo_file'])) {
    $err = $_FILES['photo_file']['error'];
    if ($err !== UPLOAD_ERR_NO_FILE) {
        if ($err !== UPLOAD_ERR_OK) {
            $errors[] = 'server';
        } else {
            $size = (int)$_FILES['photo_file']['size'];
            $tmp  = $_FILES['photo_file']['tmp_name'];
            if ($size > $maxSize) {
                $errors[] = 'photo_size';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmp);
                if (!in_array($mime, $allowedMime, true)) {
                    $errors[] = 'photo_type';
                }
            }
        }
    }
}

$dup = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ? LIMIT 1");
if ($dup) {
    $dup->bind_param('s', $category_name);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

if (!empty($errors)) {
    header('Location: category-form.php?error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

if (isset($_FILES['photo_file']) && is_array($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/carriemart/uploads/category_photos';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            header('Location: category-form.php?error=server');
            exit;
        }
    }
    $ext = strtolower(pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION));
    if ($ext === '') {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['photo_file']['tmp_name']);
        if ($mime === 'image/jpeg') $ext = 'jpg';
        elseif ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/gif') $ext = 'gif';
        else $ext = 'img';
    }
    $filename = 'category_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destFs   = $uploadDir . '/' . $filename;
    $destUrl  = '/carriemart/uploads/category_photos/' . $filename;
    if (!move_uploaded_file($_FILES['photo_file']['tmp_name'], $destFs)) {
        header('Location: category-form.php?error=server');
        exit;
    }
    $uploadedFsPath = $destFs;
    $photo_url = $destUrl;
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO categories (category_name, description, photo_url, is_active) VALUES (?, ?, ?, ?)");
    if (!$stmt) throw new Exception('server');

    $descParam = ($description !== '' ? $description : null);
    $photoParam = ($photo_url !== null ? $photo_url : null);

    $stmt->bind_param('sssi', $category_name, $descParam, $photoParam, $is_active);
    if (!$stmt->execute()) {
        if ($conn->errno === 1062) {
            $stmt->close();
            $conn->rollback();
            if ($uploadedFsPath && is_file($uploadedFsPath)) @unlink($uploadedFsPath);
            header('Location: category-form.php?error=duplicate');
            exit;
        }
        throw new Exception('server');
    }
    $newId = $stmt->insert_id;
    $stmt->close();

    $conn->commit();
    header('Location: index.php?id=' . $newId . '&status=created');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if ($uploadedFsPath && is_file($uploadedFsPath)) @unlink($uploadedFsPath);
    header('Location: category-form.php?error=server');
    exit;
}