<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/user-auth.php');


if (!$conn) { die('DB error'); }

   
$userId = 0;
if (isset($_SESSION['user_id']) && ctype_digit((string)$_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
} else {
    $id_raw = isset($_GET['id']) ? trim($_GET['id']) : '';
    $userId = ctype_digit($id_raw) ? (int)$id_raw : 0;
}
if ($userId <= 0) {
    header('Location: /carriemart/main/products.php?error=no_user');
    exit;
}

   
$cartId = null;
$st = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$st->bind_param('i', $userId);
$st->execute();
$st->bind_result($cartId);
if (!$st->fetch()) $cartId = null;
$st->close();

if ($cartId === null) {
    $ins = $conn->prepare("INSERT INTO cart (user_id) VALUES (?)");
    $ins->bind_param('i', $userId);
    if ($ins->execute()) {
        $cartId = $ins->insert_id;
    }
    $ins->close();
    if ($cartId === null) { die('Unable to initialize cart'); }
}

   
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

      
    if ($action === 'bump') {
        $pid_raw = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
        $op = isset($_POST['op']) ? trim($_POST['op']) : '';
        $pid = ctype_digit($pid_raw) ? (int)$pid_raw : 0;

        if ($pid > 0 && ($op === 'inc' || $op === 'dec')) {
              
            $q = $conn->prepare("
                SELECT cp.quantity, p.stock_level
                FROM cart_product cp
                JOIN products p ON p.product_id = cp.product_id
                WHERE cp.cart_id = ? AND cp.product_id = ?
            ");
            $q->bind_param('ii', $cartId, $pid);
            $q->execute();
            $q->bind_result($curQty, $stockLevel);
            if ($q->fetch()) {
                $q->close();
                $newQty = $curQty;
                if ($op === 'inc') $newQty = $curQty + 1;
                if ($op === 'dec') $newQty = $curQty - 1;
                if ($newQty < 1) $newQty = 1;
                if ($stockLevel > 0 && $newQty > $stockLevel) $newQty = (int)$stockLevel;

                $u = $conn->prepare("UPDATE cart_product SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                $u->bind_param('iii', $newQty, $cartId, $pid);
                $u->execute();
                $u->close();
            } else {
                $q->close();
            }
        }
        header('Location: cart.php?status=qty_updated');
        exit;
    }

      
    if ($action === 'set_qty') {
        $pid_raw = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
        $qty_raw = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
        $pid = ctype_digit($pid_raw) ? (int)$pid_raw : 0;
        $qty = ctype_digit($qty_raw) ? (int)$qty_raw : 0;

        if ($pid > 0) {
            if ($qty < 1) $qty = 1;

            $q = $conn->prepare("SELECT stock_level FROM products WHERE product_id = ?");
            $q->bind_param('i', $pid);
            $q->execute();
            $q->bind_result($stockLevel);
            if ($q->fetch()) {
                if ($stockLevel > 0 && $qty > $stockLevel) $qty = (int)$stockLevel;
            }
            $q->close();

            $u = $conn->prepare("UPDATE cart_product SET quantity = ? WHERE cart_id = ? AND product_id = ?");
            $u->bind_param('iii', $qty, $cartId, $pid);
            $u->execute();
            $u->close();
        }
        header('Location: cart.php?status=qty_set');
        exit;
    }

      
    if ($action === 'remove') {
        $pid_raw = isset($_POST['product_id']) ? trim($_POST['product_id']) : '';
        $pid = ctype_digit($pid_raw) ? (int)$pid_raw : 0;
        if ($pid > 0) {
            $d = $conn->prepare("DELETE FROM cart_product WHERE cart_id = ? AND product_id = ?");
            $d->bind_param('ii', $cartId, $pid);
            $d->execute();
            $d->close();
        }
        header('Location: cart.php?status=removed');
        exit;
    }

      
    if ($action === 'bulk') {
        $bulk_action = isset($_POST['bulk_action']) ? trim($_POST['bulk_action']) : '';
        $sel = isset($_POST['sel']) && is_array($_POST['sel']) ? $_POST['sel'] : [];
        $ids = [];
        foreach ($sel as $s) { if (ctype_digit((string)$s)) $ids[] = (int)$s; }

        if (!empty($ids)) {
            if ($bulk_action === 'delete') {
                  
                  
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $types = str_repeat('i', count($ids) + 1);
                $sql = "DELETE FROM cart_product WHERE cart_id = ? AND product_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $bind = array_merge([$types, $cartId], $ids);
                  
                $refs = [];
                foreach ($bind as $k => $v) { $refs[$k] = &$bind[$k]; }
                call_user_func_array([$stmt, 'bind_param'], $refs);
                $stmt->execute();
                $stmt->close();
                header('Location: cart.php?status=deleted');
                exit;
            } elseif ($bulk_action === 'checkout') {
                  
                $qs = http_build_query(['ids' => $ids]);
                header('Location: /carriemart/user/cart/checkout-form.php?' . $qs);
                exit;
            }
        }
        header('Location: cart.php?error=none_selected');
        exit;
    }
}

   
$items = [];
$total = 0.0;

$sql = "
SELECT 
    cp.product_id,
    cp.quantity,
    p.product_name,
    p.retail_price,
    p.stock_level,
    b.brand_name,
    COALESCE(ph.photo_url, '/carriemart/assets/default-product.png') AS photo_url
FROM cart_product cp
JOIN cart c ON c.cart_id = cp.cart_id
JOIN products p ON p.product_id = cp.product_id
LEFT JOIN brands b ON b.brand_id = p.brand_id
LEFT JOIN (
    SELECT product_id, MAX(is_primary) AS is_primary, MIN(photo_url) AS photo_url
    FROM product_photos
    WHERE is_primary = 1
    GROUP BY product_id
) ph ON ph.product_id = p.product_id
WHERE c.user_id = ?
ORDER BY cp.cart_product_id DESC";
$st = $conn->prepare($sql);
if (!$st) {
    error_log('Failed to prepare cart items query: ' . $conn->error);
    $items = [];
} else {
    $st->bind_param('i', $userId);
    $st->execute();
    $st->bind_result($pid, $qty, $pname, $price, $stock, $brand, $photo);
    while ($st->fetch()) {
        $line = (float)$price * (int)$qty;
        $total += $line;
        $items[] = [
            'product_id' => $pid,
            'quantity' => (int)$qty,
            'product_name' => $pname,
            'retail_price' => (float)$price,
            'stock_level' => (int)$stock,
            'brand_name' => $brand,
            'photo_url' => $photo ?: '/carriemart/assets/default-product.png'
        ];
    }
    $st->close();
    }

   
function peso($v){ return '₱' . number_format((float)$v, 2, '.', ','); }

   
function cm_map_error($code) {
    if (strpos($code, 'stock_') === 0) return 'Insufficient stock for product #' . substr($code, 6) . '.';
    if (strpos($code, 'not_found_') === 0) return 'Product not found #' . substr($code, 10) . '.';
    if (strpos($code, 'inactive_') === 0) return 'Product inactive #' . substr($code, 9) . '.';
    switch ($code) {
        case 'server': return 'A server error occurred.';
        case 'no_items': return 'No items selected.';
        case 'no_cart': return 'No cart found.';
        case 'login_required': return 'Please log in to continue.';
        case 'invalid_id': return 'Invalid ID.';
        case 'out_of_stock': return 'Item is out of stock.';
        case 'none_selected': return 'No items were selected.';
        default: return $code;
    }
}
function cm_map_status($code, $orderId = null) {
    switch ($code) {
        case 'qty_updated': return 'Quantity updated.';
        case 'qty_set': return 'Quantity set.';
        case 'removed': return 'Item removed from cart.';
        case 'deleted': return 'Selected items removed from cart.';
        case 'order_placed': return 'Order placed successfully.' . ($orderId ? ' Order ID: #' . $orderId : '');
        default: return '';
    }
}
$cm_errors = [];
$cm_success = [];
if (isset($_GET['error']) && $_GET['error'] !== '') {
    $codes = explode(',', trim($_GET['error']));
    foreach ($codes as $c) {
        $m = cm_map_error(trim($c));
        if ($m !== '') $cm_errors[] = $m;
    }
}
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $codes = explode(',', trim($_GET['status']));
    foreach ($codes as $c) {
        $m = cm_map_status(trim($c), isset($_GET['order_id']) ? $_GET['order_id'] : null);
        if ($m !== '') $cm_success[] = $m;
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CarrieMart: Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
   
.back-line { display:flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border-bottom:1px solid var(--bs-border-color); color:var(--bs-body-color); text-decoration:none; }
.back-line:hover { background-color: rgba(var(--bs-primary-rgb), .06); text-decoration: none; }
.back-line .icon { width: 20px; height: 20px; opacity: .9; }

   
.cart-list { display: grid; grid-template-columns: 1fr; gap: 1rem; }
.cart-card { border: 1px solid transparent; transition: border-color .15s ease; background: #fff; }
.cart-card:hover { border-color: rgba(0,0,0,.15); }
.card-header { background: transparent; }
.cart-row { display:grid; grid-template-columns: auto 1fr auto; align-items:center; column-gap:1rem; padding-top:.5rem; }
.cell-check { display:flex; align-items:center; justify-content:center; min-width:42px; }
.cell-product { min-width:0; }
.product-content { display:grid; grid-template-columns: 110px 1fr; gap:.75rem; align-items:center; }
.product-img { width:110px; aspect-ratio:1 / 1; object-fit:cover; border-radius:.5rem; background:#f8f9fa; }
.product-meta h6 { margin:0 0 .125rem 0; font-size:.95rem; }
.brand { color: var(--bs-secondary-color); font-size:.75rem; }
.price { font-weight:600; margin-top:.25rem; font-size:.85rem; }
.cell-actions { display:flex; flex-direction:column; align-items:flex-end; justify-content:center; gap:.5rem; padding-right:.75rem; min-width:115px; }
.qty-wrapper { text-align:right; }
.qty-wrapper .label { font-size:.65rem; text-transform:uppercase; letter-spacing:.5px; color: var(--bs-secondary-color); }
.qty { display:inline-flex; align-items:center; gap:.35rem; }
.qty .btn { width:34px; padding:.125rem 0; }
.qty-value { min-width:2.25rem; text-align:center; font-weight:600; }
.item-tabs { border:none; }
.item-tabs .nav-link { border:none; border-radius:0; font-size:.9rem; color:#dc3545; }
.item-tabs .nav-link:hover { background: rgba(220,53,69,.1); }
.item-tabs .nav-link:focus { background: rgba(220,53,69,.18); }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/third-header.php'); ?>

   
<div class="container mb-3">
    <a href="#" class="back-line rounded-2" onclick="history.back(); return false;">
        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
        </svg>
        <span>Go Back</span>
    </a>
</div>

<div class="container">
    <?php if (!empty($cm_errors)): ?>
        <div class="alert alert-danger mb-2" role="alert">
            <?php foreach ($cm_errors as $em): ?>
                <div>- <?php echo $em; ?></div>
            <?php endforeach; ?>
        </div>
    <?php elseif (!empty($cm_success)): ?>
        <div class="alert alert-success mb-2" role="alert">
            <?php foreach ($cm_success as $sm): ?>
                <div><?php echo $sm; ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" id="bulkForm" action="">
        <input type="hidden" name="action" value="bulk">
        <input type="hidden" name="bulk_action" id="bulkActionField" value="">
    </form>

    <div class="cart-list">
        <?php if (empty($items)): ?>
            <div class="alert alert-info">Your cart is empty.</div>
        <?php else: foreach ($items as $row): 
            $lineTotal = $row['retail_price'] * $row['quantity'];
        ?>
        <div class="card cart-card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs justify-content-end item-tabs">
                    <li class="nav-item">
                        <form method="post" class="m-0">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                            <button type="submit" class="nav-link remove-item" aria-label="Remove item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                                  <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                                </svg>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
            <div class="card-body cart-row">
                <div class="cell-check">
                    <input class="form-check-input" type="checkbox" name="sel[]" form="bulkForm" value="<?php echo $row['product_id']; ?>">
                </div>
                <div class="cell-product">
                    <div class="product-content">
                        <img src="<?php echo $row['photo_url']; ?>" class="product-img" alt="<?php echo $row['product_name']; ?>">
                        <div class="product-meta">
                            <h5 class="h5 pb-0 mb-1"><?php echo $row['product_name']; ?></h5>
                            <div class="brand">Brand: <?php echo $row['brand_name'] ? $row['brand_name'] : '—'; ?></div>
                            <div class="price"><?php echo peso($row['retail_price']); ?> • Line: <?php echo peso($lineTotal); ?></div>
                            <div class="small text-muted">Stock: <?php echo (int)$row['stock_level']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="cell-actions">
                    <div class="qty-wrapper">
                        <div class="label pb-2">Quantity</div>
                        <div class="qty">
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="bump">
                                <input type="hidden" name="op" value="dec">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" class="btn btn-outline-secondary btn-sm qty-minus" aria-label="Decrease quantity">-</button>
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="set_qty">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="text" name="quantity" value="<?php echo $row['quantity']; ?>" class="form-control form-control-sm d-inline-block" style="width:48px; text-align:center;">
                            </form>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="action" value="bump">
                                <input type="hidden" name="op" value="inc">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" class="btn btn-outline-secondary btn-sm qty-plus" aria-label="Increase quantity">+</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <?php if (!empty($items)): ?>
    <div class="d-flex justify-content-between align-items-center gap-2 mb-4 mt-4">
        <div class="h5 m-0">Subtotal: <?php echo peso($total); ?></div>

        <div class="d-flex align-items-center gap-2">
            <span class="me-1">Select items for:</span>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="bulkActionBtn">
                    Choose action
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkActionBtn">
                    <li><a class="dropdown-item" href="/carriemart/user/cart/delete.php" data-action="delete">Deletion</a></li>
                    <li><a class="dropdown-item" href="/carriemart/user/cart/checkout-form.php" data-action="checkout">Checkout</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-primary px-5" id="confirmActionBtn">Confirm</button>
        </div>
    </div>
    <?php endif; ?>
</div>
 <?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    var bulkAction = '';
    var menu = document.querySelectorAll('.dropdown-menu [data-action]');
    menu.forEach(function(el){
        el.addEventListener('click', function(e){
            e.preventDefault();
            bulkAction = this.getAttribute('data-action');
            document.getElementById('bulkActionBtn').innerText = this.innerText;
        });
    });
    var confirmBtn = document.getElementById('confirmActionBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function(){
            if (!bulkAction) {
                alert('Please select an action first.');
                return;
            }
            var checkboxes = document.querySelectorAll('input[name="sel[]"]:checked');
            if (checkboxes.length === 0) {
                alert('Please select at least one item.');
                return;
            }
            
              
            var form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
              
            checkboxes.forEach(function(cb){
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'sel[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            
              
            if (bulkAction === 'delete') {
                form.action = '/carriemart/user/cart/delete.php';
            } else if (bulkAction === 'checkout') {
                form.action = '/carriemart/user/cart/checkout-form.php';
            } else {
                return;
            }
            
            document.body.appendChild(form);
            form.submit();
        });
    }
})();
</script>
</body>
</html>