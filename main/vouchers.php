<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/config.php');
if (!$conn) { die('DB error'); }

   
$vouchers = [];
$st = $conn->prepare("
    SELECT voucher_code, percent_sale, min_purchase_amount, max_discount_amount, from_date, to_date, is_active
    FROM vouchers
    WHERE is_active = 1
    ORDER BY voucher_code ASC
");
if (!$st) {
    error_log('Failed to prepare vouchers query: ' . $conn->error);
    $vouchers = [];
} else {
    $st->execute();
    $st->bind_result($code, $percent, $minAmt, $maxAmt, $fromDate, $toDate, $active);
    while ($st->fetch()) {
        $vouchers[] = [
            'voucher_code' => $code,
            'percent_sale' => ($percent !== null ? (int)$percent : 0),
            'min_purchase_amount' => (string)$minAmt,
            'max_discount_amount' => ($maxAmt !== null ? (string)$maxAmt : ''),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'is_active' => (int)$active
        ];
    }
    $st->close();
    }
$voucher_count = count($vouchers);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarrieMart: Vouchers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
   
        .back-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem .75rem;
            border-bottom: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
            text-decoration: none;
        }
        .back-line:hover {
            background-color: rgba(var(--bs-primary-rgb), .06);
            text-decoration: none;
        }
        .back-line .icon {
            width: 20px; height: 20px; opacity: .9;
        }

            
        .product-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(2, minmax(0, 1fr)); 
        }
        @media (min-width: 768px) { .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 992px) { .product-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

           
        .product-card {
            position: relative; 
            border: 1px solid transparent;
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            transition: border-color .2s ease, transform .2s ease, background-color .2s ease;
            cursor: pointer;
        }
        .product-card:hover {
            border-color: rgba(0,0,0,.15);
            transform: translateY(-3px);
        }

        .product-card .stretched-link { z-index: 1; }

        .product-img {
            width: 100%;
            height: 200px;
            aspect-ratio: 3 / 2; 
            object-fit: cover;
            border-radius: .9rem;
            display: block;
        }

        .toast {
    box-shadow: none !important;
}
    </style>
</head>
<body>
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/secondary-header.php'); ?>

       
    <div class="container mb-3">
        <a href="#" class="back-line rounded-2"
           onclick="history.back(); return false;">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            <span>Go Back</span>
        </a>
    </div>

   <div class="container mb-3">
        <div class="d-flex align-items-center justify-content-start">
            <small class="text-muted" style="">Showing <?php echo $voucher_count; ?> vouchers</small>
        </div>
    </div>
    
       
    <div class="container pb-2">
        <div class="product-grid">

               
            <?php foreach ($vouchers as $v): ?>
                <div class="product-card">
                    <h6 class="mt-2 mb-1 fw-bold display-5"><?php echo $v['voucher_code']; ?></h6>
                    <p class="small mb-1"><strong><?php echo $v['percent_sale']; ?>% OFF</strong></p>
                    <p class="text-muted small mb-0">
                        Min purchase: <?php echo $v['min_purchase_amount']; ?><br>
                        Max discount: <?php echo ($v['max_discount_amount'] !== '' ? $v['max_discount_amount'] : '—'); ?><br>
                        Until: <?php echo ($v['to_date'] ? $v['to_date'] : '—'); ?>
                    </p>
                    <a href="#" class="stretched-link" onclick="copyVoucher('<?php echo $v['voucher_code']; ?>'); return false;" aria-label="Copy voucher"></a>
                </div>
            <?php endforeach; ?>

            <?php if (empty($vouchers)): ?>
                <div class="text-muted small">No active vouchers.</div>
            <?php endif; ?>
        </div>
    </div>

       
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <button type="button" class="btn btn-primary d-none" id="liveToastBtn">Show live toast</button>
      <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <img src="/carriemart/assets/Logo.svg" class="rounded me-2" width="16" height="16" alt="CarrieMart">
          <strong class="me-auto">Voucher Copied</strong>
          <small>Just now</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          Copied voucher code.
        </div>
      </div>
    </div>

     <?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/carriemart/includes/footer.php');
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="toast.js"></script> -->
    <!-- IMPORTANT: FOR TOAST NOTIFICATION   
    <script>
      const toastBtn = document.getElementById('liveToastBtn');
      const toastEl = document.getElementById('liveToast');
      const toastBody = toastEl?.querySelector('.toast-body');
      const toastTitle = toastEl?.querySelector('.toast-header .me-auto');
      const toastTime = toastEl?.querySelector('.toast-header small');
          
      async function copyVoucher(code) {
        try {   
          await navigator.clipboard.writeText(code);
        } catch (err) {
          const ta = document.createElement('textarea');
          ta.value = code;   
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta); 
        }
          
        if (toastBody) toastBody.textContent = `Copied voucher: ${code}`;
        if (toastTitle) toastTitle.textContent = 'Voucher Copied';
        if (toastTime) toastTime.textContent = 'Just now';
        if (toastBtn) toastBtn.click();
      }
    </script> -->
</body>
</html>