<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errors = [];

// Inputs
$category_id_raw   = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
$category_id       = ctype_digit($category_id_raw) ? (int)$category_id_raw : 0;

if ($category_id <= 0) {
    header('Location: category-form.php?id='.$category_id.'&error=invalid_id');
    exit;
}

// Ensure category exists + get current photo
$db_photo_url = null;
$exist = $conn->prepare("SELECT category_id, photo_url FROM categories WHERE category_id = ?");
if (!$exist) {
    header('Location: category-form.php?id='.$category_id.'&error=server');
    exit;
}
$exist->bind_param('i', $category_id);
$exist->execute();
$exist->bind_result($cid_chk, $db_photo_url);
if (!$exist->fetch()) {
    $exist->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exist->close();

$category_name      = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
$description        = isset($_POST['description']) ? trim($_POST['description']) : '';
$is_active_raw      = isset($_POST['is_active']) ? trim($_POST['is_active']) : '1';
$current_photo_form = isset($_POST['photo_url_current']) ? trim($_POST['photo_url_current']) : '';

// Validate name
if ($category_name === '') {
    $errors[] = 'name_required';
}

// Validate status
if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = $is_active_raw === '1' ? 1 : 0;

// File validation
$maxSize     = 5 * 1024 * 1024;
$allowedMime = ['image/jpeg','image/png','image/gif'];
$newPhotoOk  = false;
$newPhotoTmp = null;
$newPhotoName= null;

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
                } else {
                    $newPhotoOk  = true;
                    $newPhotoTmp = $tmp;
                    $newPhotoName= $_FILES['photo_file']['name'];
                }
            }
        }
    }
}

// Duplicate name (exclude self)
$dup = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ? AND category_id <> ? LIMIT 1");
if ($dup) {
    $dup->bind_param('si', $category_name, $category_id);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

// Short-circuit on errors
if (!empty($errors)) {
    header('Location: category-form.php?id='.$category_id.'&error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

// Move new photo if provided
$newPhotoFs  = null;
$newPhotoUrl = null;
if ($newPhotoOk) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/carriemart/uploads/category_photos';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            header('Location: category-form.php?id='.$category_id.'&error=server');
            exit;
        }
    }
    $ext = strtolower(pathinfo($newPhotoName, PATHINFO_EXTENSION));
    if ($ext === '') {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($newPhotoTmp);
        if ($mime === 'image/jpeg') $ext = 'jpg';
        elseif ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/gif') $ext = 'gif';
        else $ext = 'img';
    }
    $filename = 'category_' . $category_id . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destFs   = $uploadDir . '/' . $filename;
    $destUrl  = '/carriemart/uploads/category_photos/' . $filename;
    if (!move_uploaded_file($newPhotoTmp, $destFs)) {
        header('Location: category-form.php?id='.$category_id.'&error=server');
        exit;
    }
    $newPhotoFs  = $destFs;
    $newPhotoUrl = $destUrl;
}

// Decide final photo_url
$finalPhotoUrl = $newPhotoUrl !== null ? $newPhotoUrl : ($current_photo_form !== '' ? $current_photo_form : ($db_photo_url !== null ? $db_photo_url : null));
$descParam     = ($description !== '' ? $description : null);

// Update record
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE categories SET category_name = ?, description = ?, photo_url = ?, is_active = ? WHERE category_id = ?");
    if (!$stmt) throw new Exception('server');

    $stmt->bind_param('sssii', $category_name, $descParam, $finalPhotoUrl, $is_active, $category_id);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('server');
    }
    $stmt->close();

    $conn->commit();

    // Delete old photo file if replaced
    if ($newPhotoFs && $db_photo_url && $db_photo_url !== $finalPhotoUrl) {
        $oldFs = $_SERVER['DOCUMENT_ROOT'] . $db_photo_url;
        if (is_file($oldFs)) @unlink($oldFs);
    }

    header('Location: index.php?id='.$category_id.'&status=updated');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if ($newPhotoFs && is_file($newPhotoFs)) @unlink($newPhotoFs);
    header('Location: category-form.php?id='.$category_id.'&error=server');
    exit;
}
