<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errors = [];

// Inputs
$brand_id_raw     = isset($_POST['brand_id']) ? trim($_POST['brand_id']) : '';
$brand_id         = ctype_digit($brand_id_raw) ? (int)$brand_id_raw : 0;

if ($brand_id <= 0) {
    header('Location: brand-form.php?id='.$brand_id.'&error=invalid_id');
    exit;
}

// Ensure brand exists
$exist = $conn->prepare("SELECT brand_id, logo_url FROM brands WHERE brand_id = ?");
if (!$exist) {
    header('Location: brand-form.php?id='.$brand_id.'&error=server');
    exit;
}
$exist->bind_param('i', $brand_id);
$exist->execute();
$exist->bind_result($chk_id, $db_logo_url);
if (!$exist->fetch()) {
    $exist->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exist->close();

// Gather fields
$brand_name        = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
$website           = isset($_POST['website']) ? trim($_POST['website']) : '';
$description       = isset($_POST['description']) ? trim($_POST['description']) : '';
$is_active_raw     = isset($_POST['is_active']) ? trim($_POST['is_active']) : '1';
$current_logo_form = isset($_POST['logo_url_current']) ? trim($_POST['logo_url_current']) : '';

// Validate name
if ($brand_name === '') {
    $errors[] = 'name_required';
}

// Validate status
if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = ($is_active_raw === '1') ? 1 : 0;

// Validate website (optional)
if ($website !== '' && strlen($website) > 255) {
    $errors[] = 'website_invalid';
}

// Validate logo file (optional)
$maxSize     = 5 * 1024 * 1024; // 5MB
$allowedMime = ['image/jpeg','image/png','image/gif'];
$newLogoOk   = false;
$newLogoTmp  = null;
$newLogoName = null;
$logo_error  = null;

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
                } else {
                    $newLogoOk   = true;
                    $newLogoTmp  = $tmp;
                    $newLogoName = $_FILES['logo_file']['name'];
                }
            }
        }
    }
}

// Duplicate name (exclude self)
$dup = $conn->prepare("SELECT brand_id FROM brands WHERE brand_name = ? AND brand_id <> ? LIMIT 1");
if ($dup) {
    $dup->bind_param('si', $brand_name, $brand_id);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

// Short-circuit on errors
if (!empty($errors)) {
    header('Location: brand-form.php?id='.$brand_id.'&error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

// If new logo uploaded, move it now
$newLogoFs   = null;
$newLogoUrl  = null;
if ($newLogoOk) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/carriemart/uploads/brand_logos';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            header('Location: brand-form.php?id='.$brand_id.'&error=server');
            exit;
        }
    }
    $ext = strtolower(pathinfo($newLogoName, PATHINFO_EXTENSION));
    if ($ext === '') {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($newLogoTmp);
        if ($mime === 'image/jpeg') $ext = 'jpg';
        elseif ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/gif') $ext = 'gif';
        else $ext = 'img';
    }
    $filename  = 'brand_' . $brand_id . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destFs    = $uploadDir . '/' . $filename;
    $destUrl   = '/carriemart/uploads/brand_logos/' . $filename;

    if (!move_uploaded_file($newLogoTmp, $destFs)) {
        header('Location: brand-form.php?id='.$brand_id.'&error=server');
        exit;
    }
    $newLogoFs  = $destFs;
    $newLogoUrl = $destUrl;
}

// Choose final logo_url (prefer new upload, else current form value, else DB value, else NULL)
$finalLogoUrl = $newLogoUrl !== null ? $newLogoUrl : ($current_logo_form !== '' ? $current_logo_form : ($db_logo_url !== null ? $db_logo_url : null));
$websiteParam = ($website !== '' ? $website : null);
$descParam    = ($description !== '' ? $description : null);

// Update
$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE brands SET brand_name = ?, logo_url = ?, website = ?, description = ?, is_active = ? WHERE brand_id = ?");
    if (!$stmt) throw new Exception('server');

    // s s s s i i
    $stmt->bind_param('ssssii', $brand_name, $finalLogoUrl, $websiteParam, $descParam, $is_active, $brand_id);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('server');
    }
    $stmt->close();

    $conn->commit();

    // If we uploaded new logo and there was an old one different from final, delete old file
    $oldLogo = $db_logo_url;
    if ($newLogoFs && $oldLogo && $oldLogo !== $finalLogoUrl) {
        $oldFs = $_SERVER['DOCUMENT_ROOT'] . $oldLogo;
        if (is_file($oldFs)) {
            @unlink($oldFs);
        }
    }

    header('Location: brand-form.php?id='.$brand_id.'&status=updated');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if ($newLogoFs && is_file($newLogoFs)) {
        @unlink($newLogoFs);
    }
    header('Location: brand-form.php?id='.$brand_id.'&error=server');
    exit;
}