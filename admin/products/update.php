<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errors = [];

   
$product_id_raw    = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
$product_id        = ctype_digit($product_id_raw) ? (int)$product_id_raw : 0;

if ($product_id <= 0) {
    header('Location: product-form.php?id='.$product_id.'&error=invalid_id');
    exit;
}

   
$exist = $conn->prepare("SELECT product_id FROM products WHERE product_id = ?");
if (!$exist) {
    header('Location: product-form.php?id='.$product_id.'&error=server');
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

   
$product_name       = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
$model              = isset($_POST['model']) ? trim($_POST['model']) : '';
$brand_id_raw       = isset($_POST['brand_id']) ? trim($_POST['brand_id']) : '';
$category_id_raw    = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
$supplier_id_raw    = isset($_POST['supplier_id']) ? trim($_POST['supplier_id']) : '';
$retail_price_raw   = isset($_POST['retail_price']) ? trim($_POST['retail_price']) : '';
$cost_price_raw     = isset($_POST['cost_price']) ? trim($_POST['cost_price']) : '';
$stock_level_raw    = isset($_POST['stock_level']) ? trim($_POST['stock_level']) : '';
$product_condition  = isset($_POST['product_condition']) ? trim($_POST['product_condition']) : 'new';
$warranty_raw       = isset($_POST['warranty_months']) ? trim($_POST['warranty_months']) : '12';
$is_active_raw      = isset($_POST['is_active']) ? trim($_POST['is_active']) : '1';
$description        = isset($_POST['description']) ? trim($_POST['description']) : '';
$specifications     = isset($_POST['specifications']) ? trim($_POST['specifications']) : '';

   
if ($product_name === '') {
    $errors[] = 'name_required';
}

   
$moneyClean = function($v) {
    return str_replace(['₱', ',', ' '], '', $v);
};

$retail_price_clean = $moneyClean($retail_price_raw);
if ($retail_price_clean === '' || !is_numeric($retail_price_clean) || (float)$retail_price_clean < 0) {
    $errors[] = 'price_invalid';
}
$retail_price = (float)$retail_price_clean;

$cost_price_clean = $moneyClean($cost_price_raw);
if ($cost_price_clean === '' || !is_numeric($cost_price_clean) || (float)$cost_price_clean < 0) {
    $errors[] = 'cost_invalid';
}
$cost_price = (float)$cost_price_clean;

   
if ($stock_level_raw === '' || (!ctype_digit($stock_level_raw) && !(strlen($stock_level_raw) && $stock_level_raw[0] === '0'))) {
    $errors[] = 'stock_invalid';
}
$stock_level = (int)$stock_level_raw;
if ($stock_level < 0) $errors[] = 'stock_invalid';

   
$allowedConditions = ['new','used','refurbished'];
if (!in_array($product_condition, $allowedConditions, true)) {
    $errors[] = 'condition_invalid';
}

   
if ($warranty_raw === '' || !ctype_digit($warranty_raw)) {
    $errors[] = 'warranty_invalid';
}
$warranty_months = (int)$warranty_raw;

   
if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = ($is_active_raw === '1') ? 1 : 0;

   
$brand_id = null;
if ($brand_id_raw !== '') {
    if (!ctype_digit($brand_id_raw)) {
        $errors[] = 'brand_invalid';
    } else {
        $bid = (int)$brand_id_raw;
        $chk = $conn->prepare("SELECT brand_id FROM brands WHERE brand_id = ?");
        if ($chk) {
            $chk->bind_param('i', $bid);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) $errors[] = 'brand_invalid';
            $chk->close();
            $brand_id = $bid;
        } else {
            $errors[] = 'server';
        }
    }
}

$category_id = null;
if ($category_id_raw !== '') {
    if (!ctype_digit($category_id_raw)) {
        $errors[] = 'category_invalid';
    } else {
        $cid = (int)$category_id_raw;
        $chk = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        if ($chk) {
            $chk->bind_param('i', $cid);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) $errors[] = 'category_invalid';
            $chk->close();
            $category_id = $cid;
        } else {
            $errors[] = 'server';
        }
    }
}

$supplier_id = null;
if ($supplier_id_raw !== '') {
    if (!ctype_digit($supplier_id_raw)) {
        $errors[] = 'supplier_invalid';
    } else {
        $sid = (int)$supplier_id_raw;
        $chk = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
        if ($chk) {
            $chk->bind_param('i', $sid);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) $errors[] = 'supplier_invalid';
            $chk->close();
            $supplier_id = $sid;
        } else {
            $errors[] = 'server';
        }
    }
}

   
$dup = $conn->prepare("SELECT product_id FROM products WHERE product_name = ? AND COALESCE(model,'') = COALESCE(?, '') AND product_id <> ? LIMIT 1");
if ($dup) {
    $dup->bind_param('ssi', $product_name, $model, $product_id);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

   
$maxFiles = 10;
$maxSize  = 5 * 1024 * 1024;   
$allowedMime = ['image/jpeg','image/png','image/gif'];
$newPhotos = [];

if (isset($_FILES['photos_new']) && is_array($_FILES['photos_new']['name'])) {
    $names = $_FILES['photos_new']['name'];
    $tmpns = $_FILES['photos_new']['tmp_name'];
    $sizes = $_FILES['photos_new']['size'];
    $errs  = $_FILES['photos_new']['error'];

    for ($i = 0; $i < count($names); $i++) {
        if ($errs[$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($errs[$i] !== UPLOAD_ERR_OK) { $errors[] = 'server'; continue; }
        $newPhotos[] = [
            'name' => $names[$i],
            'tmp'  => $tmpns[$i],
            'size' => $sizes[$i]
        ];
    }

    if (count($newPhotos) > $maxFiles) $errors[] = 'photos_count';

    if (!empty($newPhotos)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        foreach ($newPhotos as $p) {
            if ($p['size'] > $maxSize) { $errors[] = 'photos_size'; break; }
            $mime = $finfo->file($p['tmp']);
            if (!in_array($mime, $allowedMime, true)) { $errors[] = 'photos_type'; break; }
        }
    }
}

if (!empty($errors)) {
    header('Location: product-form.php?id='.$product_id.'&error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

   
$modelParam         = ($model !== '' ? $model : null);
$descriptionParam   = ($description !== '' ? $description : null);
$specsParam         = ($specifications !== '' ? $specifications : null);

   
$primary_photo_id_raw = isset($_POST['primary_photo_id']) ? trim($_POST['primary_photo_id']) : '';
$primary_photo_id = ctype_digit($primary_photo_id_raw) ? (int)$primary_photo_id_raw : 0;

$photos_existing = isset($_POST['photos_existing']) && is_array($_POST['photos_existing']) ? $_POST['photos_existing'] : [];

$uploadedFsPaths = [];

$conn->begin_transaction();

try {
      
    $sql = "UPDATE products SET
                product_name = ?,
                brand_id = ?,
                model = ?,
                category_id = ?,
                retail_price = ?,
                cost_price = ?,
                supplier_id = ?,
                description = ?,
                specifications = ?,
                product_condition = ?,
                warranty_months = ?,
                is_active = ?,
                stock_level = ?
            WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('server');

      
    $types = 'sisiddisssiiii';
    $stmt->bind_param(
        $types,
        $product_name,
        $brand_id,
        $modelParam,
        $category_id,
        $retail_price,
        $cost_price,
        $supplier_id,
        $descriptionParam,
        $specsParam,
        $product_condition,
        $warranty_months,
        $is_active,
        $stock_level,
        $product_id
    );
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('server');
    }
    $stmt->close();

      
    if (!empty($photos_existing)) {
        foreach ($photos_existing as $ppidStr => $data) {
            if (!ctype_digit((string)$ppidStr)) continue;
            $ppid = (int)$ppidStr;

              
            $selP = $conn->prepare("SELECT photo_url FROM product_photos WHERE product_photo_id = ? AND product_id = ?");
            if (!$selP) throw new Exception('server');
            $selP->bind_param('ii', $ppid, $product_id);
            $selP->execute();
            $selP->bind_result($photo_url);
            if ($selP->fetch()) {
                $selP->close();
                $remove = isset($data['remove']) && $data['remove'] === '1';
                $sort_order_raw = isset($data['sort_order']) ? trim($data['sort_order']) : '0';
                $sort_order = ctype_digit($sort_order_raw) ? (int)$sort_order_raw : 0;

                if ($remove) {
                      
                    $del = $conn->prepare("DELETE FROM product_photos WHERE product_photo_id = ? AND product_id = ?");
                    if (!$del) throw new Exception('server');
                    $del->bind_param('ii', $ppid, $product_id);
                    if (!$del->execute()) {
                        $del->close();
                        throw new Exception('server');
                    }
                    $del->close();

                      
                    if ($photo_url) {
                        $fs = $_SERVER['DOCUMENT_ROOT'] . $photo_url;
                        if (is_file($fs)) {
                            @unlink($fs);
                        }
                    }

                    if ($ppid === $primary_photo_id) {
                        $primary_photo_id = 0;
                    }
                } else {
                      
                    $up = $conn->prepare("UPDATE product_photos SET sort_order = ? WHERE product_photo_id = ? AND product_id = ?");
                    if (!$up) throw new Exception('server');
                    $up->bind_param('iii', $sort_order, $ppid, $product_id);
                    if (!$up->execute()) {
                        $up->close();
                        throw new Exception('server');
                    }
                    $up->close();
                }
            } else {
                $selP->close();
            }
        }
    }

      
    if ($primary_photo_id > 0) {
        $chkPrim = $conn->prepare("SELECT product_photo_id FROM product_photos WHERE product_photo_id = ? AND product_id = ?");
        if (!$chkPrim) throw new Exception('server');
        $chkPrim->bind_param('ii', $primary_photo_id, $product_id);
        $chkPrim->execute();
        $chkPrim->store_result();
        if ($chkPrim->num_rows === 0) {
            $primary_photo_id = 0;
        }
        $chkPrim->close();
    }

      
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/carriemart/uploads/product_photos';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            throw new Exception('server');
        }
    }

      
    $maxSort = 0;
    $qMax = $conn->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM product_photos WHERE product_id = ?");
    if (!$qMax) throw new Exception('server');
    $qMax->bind_param('i', $product_id);
    $qMax->execute();
    $qMax->bind_result($maxSort);
    $qMax->fetch();
    $qMax->close();

    if (!empty($newPhotos)) {
        $ins = $conn->prepare("INSERT INTO product_photos (product_id, photo_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
        if (!$ins) throw new Exception('server');

        $idx = 0;
        foreach ($newPhotos as $p) {
            $ext = strtolower(pathinfo($p['name'], PATHINFO_EXTENSION));
            if ($ext === '') {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($p['tmp']);
                if ($mime === 'image/jpeg') $ext = 'jpg';
                elseif ($mime === 'image/png') $ext = 'png';
                elseif ($mime === 'image/gif') $ext = 'gif';
                else $ext = 'img';
            }
            $filename = 'p_' . $product_id . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destFs  = $uploadDir . '/' . $filename;
            $destUrl = '/carriemart/uploads/product_photos/' . $filename;

            if (!move_uploaded_file($p['tmp'], $destFs)) {
                $ins->close();
                throw new Exception('server');
            }
            $uploadedFsPaths[] = $destFs;

            $maxSort++;

              
            $hasAny = 0;
            $qAny = $conn->prepare("SELECT 1 FROM product_photos WHERE product_id = ? LIMIT 1");
            if (!$qAny) {
                $ins->close();
                throw new Exception('server');
            }
            $qAny->bind_param('i', $product_id);
            $qAny->execute();
            $qAny->store_result();
            $hasAny = $qAny->num_rows;
            $qAny->close();

            $is_primary_insert = ($primary_photo_id === 0 && $hasAny === 0 && $idx === 0) ? 1 : 0;

            $ins->bind_param('isii', $product_id, $destUrl, $is_primary_insert, $maxSort);
            if (!$ins->execute()) {
                $ins->close();
                throw new Exception('server');
            }

            if ($is_primary_insert === 1 && $primary_photo_id === 0) {
                $primary_photo_id = $ins->insert_id;
            }

            $idx++;
        }
        $ins->close();
    }

      
    $clear = $conn->prepare("UPDATE product_photos SET is_primary = 0 WHERE product_id = ?");
    if (!$clear) throw new Exception('server');
    $clear->bind_param('i', $product_id);
    if (!$clear->execute()) {
        $clear->close();
        throw new Exception('server');
    }
    $clear->close();

    if ($primary_photo_id > 0) {
        $setP = $conn->prepare("UPDATE product_photos SET is_primary = 1 WHERE product_photo_id = ? AND product_id = ?");
        if (!$setP) throw new Exception('server');
        $setP->bind_param('ii', $primary_photo_id, $product_id);
        $setP->execute();
        $setP->close();
    }

    $checkPrim = $conn->prepare("SELECT product_photo_id FROM product_photos WHERE product_id = ? AND is_primary = 1 LIMIT 1");
    if (!$checkPrim) throw new Exception('server');
    $checkPrim->bind_param('i', $product_id);
    $checkPrim->execute();
    $checkPrim->store_result();
    if ($checkPrim->num_rows === 0) {
        $checkPrim->close();
        $pick = $conn->prepare("SELECT product_photo_id FROM product_photos WHERE product_id = ? ORDER BY sort_order ASC, product_photo_id ASC LIMIT 1");
        if (!$pick) throw new Exception('server');
        $pick->bind_param('i', $product_id);
        $pick->execute();
        $pick->bind_result($first_photo_id);
        if ($pick->fetch()) {
            $pick->close();
            $setAny = $conn->prepare("UPDATE product_photos SET is_primary = 1 WHERE product_photo_id = ?");
            if (!$setAny) throw new Exception('server');
            $setAny->bind_param('i', $first_photo_id);
            $setAny->execute();
            $setAny->close();
        } else {
            $pick->close();
        }
    } else {
        $checkPrim->close();
    }

    $conn->commit();
    header('Location: product-form.php?id='.$product_id.'&status=updated');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if (!empty($uploadedFsPaths)) {
        foreach ($uploadedFsPaths as $p) {
            if (is_file($p)) @unlink($p);
        }
    }
    header('Location: product-form.php?id='.$product_id.'&error=server');
    exit;
}