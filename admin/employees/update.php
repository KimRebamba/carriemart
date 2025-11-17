<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/admin-auth.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$emp_id             = isset($_POST['emp_id']) ? (int)$_POST['emp_id'] : 0;
$first_name         = trim($_POST['first_name'] ?? '');
$last_name          = trim($_POST['last_name'] ?? '');
$email              = trim($_POST['email'] ?? '');
$phone_number       = trim($_POST['phone_number'] ?? '');
$address            = trim($_POST['address'] ?? '');
$birth_date         = trim($_POST['birth_date'] ?? '');
$gender             = trim($_POST['gender'] ?? '');
$employment_status  = trim($_POST['employment_status'] ?? 'active');
$hire_date          = trim($_POST['hire_date'] ?? '');
$current_position_id = isset($_POST['current_position_id']) && $_POST['current_position_id'] !== '' ? (int)$_POST['current_position_id'] : 0;

if ($emp_id <= 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Ensure employee exists
$exists = $conn->prepare("SELECT emp_id FROM employees WHERE emp_id = ?");
if (!$exists) { header('Location: employee-form.php?id=' . $emp_id . '&error=server'); exit; }
$exists->bind_param('i', $emp_id);
$exists->execute();
$exists->store_result();
if ($exists->num_rows === 0) {
    $exists->close();
    header('Location: index.php?error=not_found');
    exit;
}
$exists->close();

// Validate inputs (mirror employee-form error codes)
$errors = [];
if ($first_name === '') $errors[] = 'first_name_required';
if ($last_name === '')  $errors[] = 'last_name_required';
if ($address === '')    $errors[] = 'address_required';

// Email required + format
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'invalid_email';

// Phone required + PH mobile pattern 09XXXXXXXXX (11 digits)
if ($phone_number === '') {
    $errors[] = 'phone_required';
} else {
    $digitsPhone = preg_replace('/\D/', '', $phone_number);
    if (!preg_match('/^09\d{9}$/', $digitsPhone)) $errors[] = 'invalid_phone';
}

// Employment status enum
$allowedStatus = ['active','inactive','terminated','on_leave'];
if (!in_array($employment_status, $allowedStatus, true)) $errors[] = 'employment_status_bad';

// Hire date required (Y-m-d)
if ($hire_date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hire_date)) {
    $errors[] = 'hire_date_required';
}

// Optional birth date validate if present
if ($birth_date !== '') {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth_date)) {
        $errors[] = 'birth_date_invalid';
    } else {
        if ($birth_date > date('Y-m-d')) $errors[] = 'birth_date_invalid';
    }
}

// Gender enum or blank -> store NULL if blank
$allowedGender = ['male','female','other',''];
if (!in_array($gender, $allowedGender, true)) $gender = '';

// Validate position (optional). If invalid, set to 0 -> becomes NULL
if ($current_position_id > 0) {
    $pchk = $conn->prepare("SELECT position_id FROM positions WHERE position_id = ?");
    if ($pchk) {
        $pchk->bind_param('i', $current_position_id);
        $pchk->execute();
        $pchk->store_result();
        if ($pchk->num_rows === 0) {
            $current_position_id = 0;
        }
        $pchk->close();
    } else {
        $errors[] = 'server';
    }
}

// Duplicate email check excluding this employee
if (!in_array('invalid_email', $errors, true)) {
    $dup = $conn->prepare("SELECT emp_id FROM employees WHERE email = ? AND emp_id <> ? LIMIT 1");
    if ($dup) {
        $dup->bind_param('si', $email, $emp_id);
        $dup->execute();
        $dup->store_result();
        if ($dup->num_rows > 0) $errors[] = 'duplicate_email';
        $dup->close();
    } else {
        $errors[] = 'server';
    }
}

if (!empty($errors)) {
    header('Location: employee-form.php?id=' . $emp_id . '&error=' . implode(',', $errors));
    exit;
}

// Update (use NULLIF to store NULLs)
$sql = "UPDATE employees
        SET first_name = ?,
            last_name = ?,
            email = ?,
            phone_number = ?,
            address = ?,
            birth_date = NULLIF(?, ''),
            gender = NULLIF(?, ''),
            employment_status = ?,
            hire_date = NULLIF(?, ''),
            current_position_id = NULLIF(?, 0)
        WHERE emp_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Location: employee-form.php?id=' . $emp_id . '&error=server');
    exit;
}

$stmt->bind_param(
    'sssssssssii',
    $first_name,
    $last_name,
    $email,
    $phone_number,
    $address,
    $birth_date,
    $gender,
    $employment_status,
    $hire_date,
    $current_position_id,
    $emp_id
);

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: employee-form.php?id=' . $emp_id . '&error=server');
    exit;
}
$stmt->close();

header('Location: index.php?id=' . $emp_id . '&status=updated');
exit;