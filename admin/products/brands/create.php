<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: brand-form.php');
    exit;
}

$errors = [];

// Gather inputs
$brand_name   = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
$website      = isset($_POST['website']) ? trim($_POST['website']) : '';
$description  = isset($_POST['description']) ? trim($_POST['description']) : '';
$is_active_raw= isset($_POST['is_active']) ? trim($_POST['is_active']) : '1';

// Validate required name
if ($brand_name === '') {
    $errors[] = 'name_required';
}

// Validate status
if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = ($is_active_raw === '1') ? 1 : 0;

// Validate website (optional, allow without scheme; only length check)
if ($website !== '' && strlen($website) > 255) {
    $errors[] = 'website_invalid';
}

// Validate and stage logo upload (optional)
$logo_url = null;
$uploadedFsPath = null;
$maxSize    = 5 * 1024 * 1024; // 5MB
$allowedMime= ['image/jpeg','image/png','image/gif'];

if (isset($_FILES['logo_file']) && is_array($_FILES['logo_file'])) {
    $err = $_FILES['logo_file']['error'];
    if ($err !== UPLOAD_ERR_NO_FILE) {
        if ($err !== UPLOAD_ERR_OK) {
            $errors[] = 'server';
        } else {
            $size = (int)$_FILES['logo_file']['size'];
            $tmp  = $_FILES['logo_file']['tmp_name'];
            if ($size > $maxSize) {
                $errors[] = 'logo_size';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmp);
                if (!in_array($mime, $allowedMime, true)) {
                    $errors[] = 'logo_type';
                }
            }
        }
    }
}

// Duplicate check by brand_name
$dup = $conn->prepare("SELECT brand_id FROM brands WHERE brand_name = ? LIMIT 1");
if ($dup) {
    $dup->bind_param('s', $brand_name);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

// Stop if validation failed
if (!empty($errors)) {
    header('Location: brand-form.php?error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

// If a logo was provided and validated, move it now (before DB as we need the path)
if (isset($_FILES['logo_file']) && is_array($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/carriemart/uploads/brand_logos';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            header('Location: brand-form.php?error=server');
            exit;
        }
    }
    $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
    if ($ext === '') {
        // derive from MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['logo_file']['tmp_name']);
        if ($mime === 'image/jpeg') $ext = 'jpg';
        elseif ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/gif') $ext = 'gif';
        else $ext = 'img';
    }
    $filename = 'brand_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destFs   = $uploadDir . '/' . $filename;
    $destUrl  = '/carriemart/uploads/brand_logos/' . $filename;

    if (!move_uploaded_file($_FILES['logo_file']['tmp_name'], $destFs)) {
        header('Location: brand-form.php?error=server');
        exit;
    }
    $uploadedFsPath = $destFs;
    $logo_url = $destUrl;
}

// Insert brand
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO brands (brand_name, logo_url, website, description, is_active) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception('server');

    $logoParam = ($logo_url !== null ? $logo_url : null);
    $websiteParam = ($website !== '' ? $website : null);
    $descParam = ($description !== '' ? $description : null);

    $stmt->bind_param('ssssi', $brand_name, $logoParam, $websiteParam, $descParam, $is_active);
    if (!$stmt->execute()) {
        // Handle unique constraint fallback
        if ($conn->errno === 1062) {
            $stmt->close();
            if ($uploadedFsPath && is_file($uploadedFsPath)) @unlink($uploadedFsPath);
            $conn->rollback();
            header('Location: brand-form.php?error=duplicate');
            exit;
        }
        throw new Exception('server');
    }
    $newId = $stmt->insert_id;
    $stmt->close();

    $conn->commit();
    header('Location: brand-form.php?id=' . $newId . '&status=created');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if ($uploadedFsPath && is_file($uploadedFsPath)) {
        @unlink($uploadedFsPath);
    }
    header('Location: brand-form.php?error=server');
    exit;
}