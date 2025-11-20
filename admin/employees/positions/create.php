<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: position-form.php');
    exit;
}

$position_name = trim($_POST['position_name'] ?? '');
$monthly_rate_raw = trim($_POST['monthly_rate'] ?? '');

$errors = [];

   
if ($position_name === '') {
    $errors[] = 'name_required';
}

   
if ($monthly_rate_raw === '') {
    $errors[] = 'rate_required';
}
$rate_clean = str_replace([',', '₱', ' '], '', $monthly_rate_raw);
if ($monthly_rate_raw !== '' && (!is_numeric($rate_clean) || (float)$rate_clean < 0)) {
    $errors[] = 'rate_invalid';
}
$monthly_rate = ($monthly_rate_raw === '' ? 0.00 : (float)$rate_clean);

   
if (!in_array('name_required', $errors, true)) {
    $dup = $conn->prepare("SELECT position_id FROM positions WHERE position_name = ? LIMIT 1");
    if ($dup) {
        $dup->bind_param('s', $position_name);
        $dup->execute();
        $dup->store_result();
        if ($dup->num_rows > 0) {
            $errors[] = 'duplicate';
        }
        $dup->close();
    } else {
        $errors[] = 'server';
    }
}

if (!empty($errors)) {
    header('Location: position-form.php?error=' . implode(',', $errors));
    exit;
}

   
$stmt = $conn->prepare("INSERT INTO positions (position_name, monthly_rate) VALUES (?, ?)");
if (!$stmt) {
    header('Location: position-form.php?error=server');
    exit;
}
$stmt->bind_param('sd', $position_name, $monthly_rate);
if (!$stmt->execute()) {
    $stmt->close();
    header('Location: position-form.php?error=server');
    exit;
}
$newId = $stmt->insert_id;
$stmt->close();

header('Location: index.php?id=' . $newId . '&status=created');
exit;