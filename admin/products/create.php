<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: product-form.php');
    exit;
}

$errors = [];

// Gather inputs
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

// Basic validations
if ($product_name === '') {
    $errors[] = 'name_required';
}

// Money cleaners
$moneyClean = function($v) {
    $v = str_replace(['₱', ',', ' '], '', $v);
    return $v;
};

// Retail price
$retail_price_clean = $moneyClean($retail_price_raw);
if ($retail_price_clean === '' || !is_numeric($retail_price_clean) || (float)$retail_price_clean < 0) {
    $errors[] = 'price_invalid';
}
$retail_price = (float)$retail_price_clean;

// Cost price
$cost_price_clean = $moneyClean($cost_price_raw);
if ($cost_price_clean === '' || !is_numeric($cost_price_clean) || (float)$cost_price_clean < 0) {
    $errors[] = 'cost_invalid';
}
$cost_price = (float)$cost_price_clean;

// Stock level
if ($stock_level_raw === '' || (!ctype_digit($stock_level_raw) && !(strlen($stock_level_raw) && $stock_level_raw[0] === '0'))) {
    $errors[] = 'stock_invalid';
}
$stock_level = (int)$stock_level_raw;
if ($stock_level < 0) $errors[] = 'stock_invalid';

// Condition
$allowedConditions = ['new','used','refurbished'];
if (!in_array($product_condition, $allowedConditions, true)) {
    $errors[] = 'condition_invalid';
}

// Warranty
if ($warranty_raw === '' || !ctype_digit($warranty_raw)) {
    $errors[] = 'warranty_invalid';
}
$warranty_months = (int)$warranty_raw;

// Status
if (!in_array($is_active_raw, ['0','1'], true)) {
    $errors[] = 'status_invalid';
}
$is_active = ($is_active_raw === '1') ? 1 : 0;

// Foreign keys: brand/category/supplier
$brand_id = null;
if ($brand_id_raw !== '') {
    if (!ctype_digit($brand_id_raw)) {
        $errors[] = 'brand_invalid';
    } else {
        $val = (int)$brand_id_raw;
        $chk = $conn->prepare("SELECT brand_id FROM brands WHERE brand_id = ?");
        if ($chk) {
            $chk->bind_param('i', $val);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) $errors[] = 'brand_invalid';
            $chk->close();
        } else {
            $errors[] = 'server';
        }
        $brand_id = $val;
    }
}

$category_id = null;
if ($category_id_raw !== '') {
    if (!ctype_digit($category_id_raw)) {
        $errors[] = 'category_invalid';
    } else {
        $val = (int)$category_id_raw;
        $chk = $conn->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        if ($chk) {
            $chk->bind_param('i', $val);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) $errors[] = 'category_invalid';
            $chk->close();
        } else {
            $errors[] = 'server';
        }
        $category_id = $val;
    }
}

$supplier_id = null;
if ($supplier_id_raw !== '') {
    if (!ctype_digit($supplier_id_raw)) {
        $errors[] = 'supplier_invalid';
    } else {
        $val = (int)$supplier_id_raw;
        $chk = $conn->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
        if ($chk) {
            $chk->bind_param('i', $val);
            $chk->execute();
            $chk->store_result();
            if ($chk->num_rows === 0) $errors[] = 'supplier_invalid';
            $chk->close();
        } else {
            $errors[] = 'server';
        }
        $supplier_id = $val;
    }
}

// Duplicate check
$dup = $conn->prepare("SELECT product_id FROM products WHERE product_name = ? AND COALESCE(model,'') = COALESCE(?, '') LIMIT 1");
if ($dup) {
    $dup->bind_param('ss', $product_name, $model);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) $errors[] = 'duplicate';
    $dup->close();
} else {
    $errors[] = 'server';
}

// Photos validation
$maxFiles = 10;
$maxSize  = 5 * 1024 * 1024;
$allowedMime = ['image/jpeg','image/png','image/gif'];
$photos = [];
if (isset($_FILES['photos_new']) && is_array($_FILES['photos_new']['name'])) {
    $names = $_FILES['photos_new']['name'];
    $tmpns = $_FILES['photos_new']['tmp_name'];
    $sizes = $_FILES['photos_new']['size'];
    $errs  = $_FILES['photos_new']['error'];

    for ($i = 0; $i < count($names); $i++) {
        if ($errs[$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($errs[$i] !== UPLOAD_ERR_OK) { $errors[] = 'server'; continue; }
        $photos[] = [
            'name' => $names[$i],
            'tmp'  => $tmpns[$i],
            'size' => $sizes[$i]
        ];
    }

    if (count($photos) > $maxFiles) $errors[] = 'photos_count';

    if (!empty($photos)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        foreach ($photos as $p) {
            if ($p['size'] > $maxSize) { $errors[] = 'photos_size'; break; }
            $mime = $finfo->file($p['tmp']);
            if (!in_array($mime, $allowedMime, true)) { $errors[] = 'photos_type'; break; }
        }
    }
}

// Stop on errors
if (!empty($errors)) {
    header('Location: product-form.php?error=' . implode(',', array_values(array_unique($errors))));
    exit;
}

// Normalize nullable strings
$modelParam       = ($model !== '' ? $model : null);
$descriptionParam = ($description !== '' ? $description : null);
$specsParam       = ($specifications !== '' ? $specifications : null);

$conn->begin_transaction();

try {
    // Insert product
    $stmt = $conn->prepare("INSERT INTO products
        (product_name, brand_id, model, category_id, retail_price, cost_price, supplier_id, description, specifications, product_condition, warranty_months, is_active, stock_level)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) throw new Exception('server');

    $types = 'sisiddisssiii';
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
        $stock_level
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('server');
    }
    $newId = $stmt->insert_id;
    $stmt->close();

    // Insert automatic expense record for initial inventory purchase
    if ($stock_level > 0 && $cost_price > 0) {
        $expType    = 'inventory_purchase';
        $expDesc    = 'Initial stock for product #' . $newId . ': ' . $product_name;
        $expAmount  = $cost_price * $stock_level;
        $expStatus  = 'paid';
        $today      = date('Y-m-d');
        $expStmt = $conn->prepare("INSERT INTO expenses (expense_type, description, amount, status, due_date, paid_date) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$expStmt) throw new Exception('server');
        $expStmt->bind_param('ssdsss', $expType, $expDesc, $expAmount, $expStatus, $today, $today);
        if (!$expStmt->execute()) {
            $expStmt->close();
            throw new Exception('server');
        }
        $expStmt->close();
    }

    // Handle photo uploads
    $uploadedPaths = [];
    if (!empty($photos)) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/carriemart/uploads/product_photos';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0775, true)) {
                throw new Exception('server');
            }
        }

        $ins = $conn->prepare("INSERT INTO product_photos (product_id, photo_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
        if (!$ins) throw new Exception('server');

        $sort = 0;
        foreach ($photos as $idx => $p) {
            $ext = pathinfo($p['name'], PATHINFO_EXTENSION);
            $ext = strtolower($ext);
            if ($ext === '') {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($p['tmp']);
                if ($mime === 'image/jpeg') $ext = 'jpg';
                elseif ($mime === 'image/png') $ext = 'png';
                elseif ($mime === 'image/gif') $ext = 'gif';
                else $ext = 'img';
            }
            $filename = 'p_' . $newId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destFs   = $uploadDir . '/' . $filename;
            $destUrl  = '/carriemart/uploads/product_photos/' . $filename;

            if (!move_uploaded_file($p['tmp'], $destFs)) {
                $ins->close();
                throw new Exception('server');
            }
            $uploadedPaths[] = $destFs;

            $is_primary = ($idx === 0) ? 1 : 0;
            $sort++;

            $ins->bind_param('isii', $newId, $destUrl, $is_primary, $sort);
            if (!$ins->execute()) {
                $ins->close();
                throw new Exception('server');
            }
        }
        $ins->close();
    }

    $conn->commit();
    header('Location: product-form.php?id=' . $newId . '&status=created');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    if (!empty($uploadedPaths)) {
        foreach ($uploadedPaths as $p) {
            if (is_file($p)) @unlink($p);
        }
    }
    header('Location: product-form.php?error=server');
    exit;
}