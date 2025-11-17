<?php
// Redirect search requests to products.php, carrying the keyword and optional filters.
// No DB work needed.

$method = $_SERVER['REQUEST_METHOD'];

// Accept common parameter names for keyword
$q = '';
if ($method === 'POST') {
    if (isset($_POST['q'])) $q = trim($_POST['q']);
    elseif (isset($_POST['keyword'])) $q = trim($_POST['keyword']);
    elseif (isset($_POST['s'])) $q = trim($_POST['s']);
} else {
    if (isset($_GET['q'])) $q = trim($_GET['q']);
    elseif (isset($_GET['keyword'])) $q = trim($_GET['keyword']);
    elseif (isset($_GET['s'])) $q = trim($_GET['s']);
}

// Optional filters passthrough (if present in the request)
$params = [];
if ($q !== '') $params['q'] = $q;

$optional = ['category','brand','min_price','max_price','min_rating','sort'];
foreach ($optional as $key) {
    if ($method === 'POST' && isset($_POST[$key]) && trim($_POST[$key]) !== '') {
        $params[$key] = trim($_POST[$key]);
    } elseif ($method !== 'POST' && isset($_GET[$key]) && trim($_GET[$key]) !== '') {
        $params[$key] = trim($_GET[$key]);
    }
}

$target = '/carriemart/main/products.php';
$query  = !empty($params) ? ('?' . http_build_query($params)) : '';

if ($method === 'POST') {
    header('Location: ' . $target . $query, true, 303); // See Other to avoid form resubmission
} else {
    header('Location: ' . $target . $query, true, 302);
}
exit;