<?php
   
   

$method = $_SERVER['REQUEST_METHOD'];

   
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
    header('Location: ' . $target . $query, true, 303);   
} else {
    header('Location: ' . $target . $query, true, 302);
}
exit;