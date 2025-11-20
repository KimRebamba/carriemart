<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$position_id      = isset($_POST['position_id']) ? (int)$_POST['position_id'] : 0;
$position_name    = trim($_POST['position_name'] ?? '');
$monthly_rate_raw = trim($_POST['monthly_rate'] ?? '');

if ($position_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

   
$chk = $conn->prepare("SELECT position_id FROM positions WHERE position_id = ?");
if (!$chk) {
    header('Location: position-form.php?id='.$position_id.'&error=server');
    exit;
}
$chk->bind_param('i', $position_id);
$chk->execute();
$chk->store_result();
if ($chk->num_rows === 0) {
    $chk->close();
    header('Location: index.php?error=not_found');
    exit;
}
$chk->close;

$errors = [];

   
if ($position_name === '') $errors[] = 'name_required';

   
if ($monthly_rate_raw === '') $errors[] = 'rate_required';
$rate_clean = str_replace([',','₱',' '], '', $monthly_rate_raw);
if ($monthly_rate_raw !== '' && (!is_numeric($rate_clean) || (float)$rate_clean < 0)) {
    $errors[] = 'rate_invalid';
}
$monthly_rate = ($monthly_rate_raw === '' ? 0.00 : (float)$rate_clean);

   
if (!in_array('name_required', $errors, true)) {
    $dup = $conn->prepare("SELECT position_id FROM positions WHERE position_name = ? AND position_id <> ? LIMIT 1");
    if ($dup) {
        $dup->bind_param('si', $position_name, $position_id);
        $dup->execute();
        $dup->store_result();
        if ($dup->num_rows > 0) $errors[] = 'duplicate';
        $dup->close();
    } else {
        $errors[] = 'server';
    }
}

if (!empty($errors)) {
    header('Location: position-form.php?id='.$position_id.'&error='.implode(',', $errors));
    exit;
}

   
$stmt = $conn->prepare("UPDATE positions SET position_name = ?, monthly_rate = ? WHERE position_id = ?");
if (!$stmt) {
    header('Location: position-form.php?id='.$position_id.'&error=server');
    exit;
}
$stmt->bind_param('sdi', $position_name, $monthly_rate, $position_id);
if (!$stmt->execute()) {
    $stmt->close();
    header('Location: position-form.php?id='.$position_id.'&error=server');
    exit;
}
$stmt->close();

header('Location: index.php?id='.$position_id.'&status=updated');
exit;