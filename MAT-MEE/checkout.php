<?php
ob_start(); // Start output buffering to prevent header issues

require_once 'components/config-page.php';
$page_title = 'Checkout';

$successMessage = '';
$errorMessage = '';

require 'Email/PHPMailer-master/src/PHPMailer.php';
require 'Email/PHPMailer-master/src/SMTP.php';
require 'Email/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verify cart exists
if (empty($cartId)) {
    ob_end_clean();
    header('Location: cart.php');
    exit;
}

$cartItems = $conn->fetch(
    'SELECT ci.id AS cart_item_id, ci.quantity, p.id AS product_id, p.name, p.slug, p.main_image, p.price, p.discount_price, ci.size_id, s.size_name FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     LEFT JOIN sizes s ON s.id = ci.size_id
     WHERE ci.cart_id = ?
     ORDER BY ci.id DESC',
    [$cartId]
);

if (empty($cartItems)) {
    ob_end_clean();
    header('Location: cart.php');
    exit;
}

$shippingCharge = 80.00;
$orderTotal = 0;
foreach ($cartItems as $item) {
    $unitPrice = (!empty($item['discount_price']) && (float)$item['discount_price'] < (float)$item['price']) ? (float)$item['discount_price'] : (float)$item['price'];
    $orderTotal += $unitPrice * $item['quantity'];
}

$grandTotal = $orderTotal + $shippingCharge;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($customerName)) {
        $errorMessage = 'Please enter your name.';
    } elseif (empty($customerEmail)) {
        $errorMessage = 'Please enter your email address.';
    } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif (empty($phone)) {
        $errorMessage = 'Please enter your phone number.';
    } elseif (empty($address)) {
        $errorMessage = 'Please enter your shipping address.';
    } elseif (empty($cartItems)) {
        $errorMessage = 'Your cart is empty. Add items before placing an order.';
    } else {
        // Insert order
        $orderInserted = $conn->execute(
            'INSERT INTO orders (user_id, session_id, total, shipping_charge, status, customer_name, customer_email, shipping_address, phone, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [null, session_id(), (float)$grandTotal, (float)$shippingCharge, 'pending', $customerName, $customerEmail, $address, $phone, $notes]
        );

        if ($orderInserted === false || $orderInserted <= 0) {
            $errorMessage = 'Error creating order: ' . $conn->getError();
        } else {
            $orderId = $conn->lastId();
            
            if ($orderId <= 0) {
                $errorMessage = 'Error retrieving order ID. Please try again.';
            } else {
                $allItemsInserted = true;
                
                foreach ($cartItems as $item) {
                    $unitPrice = (!empty($item['discount_price']) && (float)$item['discount_price'] < (float)$item['price']) ? (float)$item['discount_price'] : (float)$item['price'];
                    
                    $itemInserted = $conn->execute(
                        'INSERT INTO order_items (order_id, product_id, size_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?, ?)',
                        [$orderId, $item['product_id'], $item['size_id'] ?? null, $item['quantity'], (float)$unitPrice]
                    );
                    
                    if ($itemInserted === false || $itemInserted <= 0) {
                        $allItemsInserted = false;
                        break;
                    }
                }
                
                if (!$allItemsInserted) {
                    $errorMessage = 'Error saving order items: ' . $conn->getError();
                } else {
                    // Clear cart items
                    $cartCleared = $conn->execute('DELETE FROM cart_items WHERE cart_id = ?', [$cartId]);
                    
                    if ($cartCleared === false) {
                        // Order successful even if cart clear fails
                    }
                    
                    // Redirect to confirmation - clear buffer first
                    ob_end_clean();


                    $mail = new PHPMailer(true);

                    // ── Build HTML email body ────────────────────────────────────────
                    $orderIdPadded   = str_pad($orderId, 5, '0', STR_PAD_LEFT);
                    $orderDate       = date('d F Y, h:i A');
                    $mailOrderTotal  = 0;
                    $itemRowsHtml    = '';
                    foreach ($cartItems as $item) {
                        $unitP = (!empty($item['discount_price']) && (float)$item['discount_price'] < (float)$item['price'])
                            ? (float)$item['discount_price'] : (float)$item['price'];
                        $subP  = $unitP * $item['quantity'];
                        $mailOrderTotal += $subP;
                        $sizeLabel = !empty($item['size_name']) ? ' <span style="color:#999;font-size:12px;">(' . htmlspecialchars($item['size_name']) . ')</span>' : '';
                        $itemRowsHtml .= '
                        <tr>
                          <td style="padding:14px 12px;border-bottom:1px solid #f0f0f0;">
                            <span style="font-weight:600;color:#1a1a1a;">' . htmlspecialchars($item['name']) . '</span>' . $sizeLabel . '<br>
                            <span style="color:#888;font-size:12px;">Qty: ' . (int)$item['quantity'] . '</span>
                          </td>
                          <td style="padding:14px 12px;border-bottom:1px solid #f0f0f0;text-align:right;font-weight:600;color:#1a1a1a;white-space:nowrap;">
                            &#2547;' . number_format($unitP, 0) . '
                          </td>
                          <td style="padding:14px 12px;border-bottom:1px solid #f0f0f0;text-align:right;font-weight:700;color:#800000;white-space:nowrap;">
                            &#2547;' . number_format($subP, 0) . '
                          </td>
                        </tr>';
                    }
                    $mailGrandTotal = $mailOrderTotal + $shippingCharge;

                    $notesRow = !empty($notes)
                        ? '<tr><td colspan="3" style="padding:10px 12px;background:#fffbe6;border-radius:6px;font-size:13px;color:#555;">
                            <strong>📝 Note:</strong> ' . nl2br(htmlspecialchars($notes)) . '</td></tr>' : '';

                    $emailBody = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Confirmation – Mat-Mee</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f7;font-family:\'Segoe UI\',Arial,sans-serif;">

  <!-- Wrapper -->
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f7;padding:40px 0;">
    <tr>
      <td align="center">
        <table width="620" cellpadding="0" cellspacing="0" style="max-width:620px;width:100%;">

          <!-- ── Header ── -->
          <tr>
            <td style="background:linear-gradient(135deg,#800000 0%,#3a0000 100%);border-radius:16px 16px 0 0;padding:40px 30px;text-align:center;">
              <div style="display:inline-block;background:rgba(255,255,255,0.12);border-radius:50%;width:72px;height:72px;line-height:72px;font-size:34px;margin-bottom:18px;">🛍️</div><br>
              <h1 style="margin:0 0 6px;color:#ffffff;font-size:26px;font-weight:800;letter-spacing:-0.5px;">Order Confirmed!</h1>
              <p style="margin:0;color:rgba(255,255,255,0.75);font-size:15px;">Thank you for shopping with <strong style="color:#fff;">Mat-Mee</strong></p>
            </td>
          </tr>

          <!-- ── White Card ── -->
          <tr>
            <td style="background:#ffffff;border-radius:0 0 16px 16px;padding:36px 30px 30px;box-shadow:0 8px 40px rgba(0,0,0,0.08);">

              <!-- Greeting -->
              <p style="margin:0 0 24px;font-size:16px;color:#333;">Hi <strong style="color:#800000;">' . htmlspecialchars($customerName) . '</strong>,</p>
              <p style="margin:0 0 28px;font-size:14px;color:#555;line-height:1.7;">
                Great news! We\'ve received your order and it\'s now being processed. Below is a summary of your purchase for your records.
              </p>

              <!-- Order Meta -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                <tr>
                  <td width="50%" style="background:#fdf5f5;border-radius:10px;padding:16px 18px;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;font-weight:700;margin-bottom:4px;">Order Number</div>
                    <div style="font-size:22px;font-weight:800;color:#800000;">#' . $orderIdPadded . '</div>
                  </td>
                  <td width="4%"></td>
                  <td width="46%" style="background:#f7faff;border-radius:10px;padding:16px 18px;">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#999;font-weight:700;margin-bottom:4px;">Order Date</div>
                    <div style="font-size:14px;font-weight:600;color:#333;">' . $orderDate . '</div>
                  </td>
                </tr>
              </table>

              <!-- Items Table -->
              <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:10px;">Items Ordered</div>
              <table width="100%" cellpadding="0" cellspacing="0" style="border-radius:10px;overflow:hidden;border:1px solid #f0f0f0;margin-bottom:24px;">
                <thead>
                  <tr style="background:#f8f8f8;">
                    <th style="padding:12px;text-align:left;font-size:12px;color:#666;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Product</th>
                    <th style="padding:12px;text-align:right;font-size:12px;color:#666;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Unit Price</th>
                    <th style="padding:12px;text-align:right;font-size:12px;color:#666;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">Subtotal</th>
                  </tr>
                </thead>
                <tbody>' . $itemRowsHtml . $notesRow . '</tbody>
              </table>

              <!-- Totals -->
              <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                <tr>
                  <td style="text-align:right;padding:6px 0;font-size:14px;color:#555;">Subtotal:</td>
                  <td style="text-align:right;padding:6px 0;font-size:14px;color:#333;font-weight:600;width:120px;">&#2547;' . number_format($mailOrderTotal, 0) . '</td>
                </tr>
                <tr>
                  <td style="text-align:right;padding:6px 0;font-size:14px;color:#555;">Shipping Charge:</td>
                  <td style="text-align:right;padding:6px 0;font-size:14px;color:#333;font-weight:600;">&#2547;' . number_format($shippingCharge, 0) . '</td>
                </tr>
                <tr>
                  <td colspan="2"><hr style="border:none;border-top:2px dashed #eee;margin:8px 0;"></td>
                </tr>
                <tr>
                  <td style="text-align:right;padding:6px 0;font-size:17px;font-weight:800;color:#800000;">Total Amount:</td>
                  <td style="text-align:right;padding:6px 0;font-size:17px;font-weight:800;color:#800000;">&#2547;' . number_format($mailGrandTotal, 0) . '</td>
                </tr>
              </table>

              <!-- Shipping Info -->
              <div style="background:#f9f9f9;border-left:4px solid #800000;border-radius:0 10px 10px 0;padding:18px 20px;margin-bottom:28px;">
                <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#666;margin-bottom:10px;">Delivery Address</div>
                <p style="margin:0 0 4px;font-size:14px;color:#333;"><strong>' . htmlspecialchars($customerName) . '</strong></p>
                <p style="margin:0 0 4px;font-size:13px;color:#555;">' . nl2br(htmlspecialchars($address)) . '</p>
                <p style="margin:0;font-size:13px;color:#555;">📞 ' . htmlspecialchars($phone) . '</p>
              </div>

              <!-- Payment Method -->
              <div style="background:#fff8f8;border:1px solid #f5d0d0;border-radius:10px;padding:16px 20px;margin-bottom:28px;display:flex;align-items:center;gap:12px;">
                <div>
                  <div style="font-weight:700;color:#333;font-size:14px;">Cash on Delivery (COD)</div>
                  <div style="font-size:12px;color:#888;margin-top:2px;">Pay when your order arrives at your doorstep.</div>
                </div>
              </div>

              <!-- What\'s Next Steps -->
              <div style="margin-bottom:28px;">
                <div style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#888;margin-bottom:14px;">⏭️ What Happens Next?</div>
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="padding:0 0 12px;">
                      <div style="display:inline-block;background:#800000;color:#fff;border-radius:50%;width:26px;height:26px;line-height:26px;text-align:center;font-size:12px;font-weight:700;margin-right:10px;">1</div>
                      <span style="font-size:14px;color:#444;">Our team will verify &amp; confirm your order</span>
                    </td>
                  </tr>
                  <tr>
                    <td style="padding:0 0 12px;">
                      <div style="display:inline-block;background:#800000;color:#fff;border-radius:50%;width:26px;height:26px;line-height:26px;text-align:center;font-size:12px;font-weight:700;margin-right:10px;">2</div>
                      <span style="font-size:14px;color:#444;">Your order will be packed and dispatched</span>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div style="display:inline-block;background:#800000;color:#fff;border-radius:50%;width:26px;height:26px;line-height:26px;text-align:center;font-size:12px;font-weight:700;margin-right:10px;">3</div>
                      <span style="font-size:14px;color:#444;">Delivery to your address – pay on arrival!</span>
                    </td>
                  </tr>
                </table>
              </div>

            </td>
          </tr>

          <!-- ── Footer ── -->
          <tr>
            <td style="padding:24px 0 0;text-align:center;">
              <p style="margin:0 0 6px;font-size:13px;color:#aaa;">© ' . date('Y') . ' Mat-Mee. All rights reserved.</p>
              <p style="margin:0;font-size:12px;color:#bbb;">This email was sent to <a href="mailto:' . htmlspecialchars($customerEmail) . '" style="color:#800000;text-decoration:none;">' . htmlspecialchars($customerEmail) . '</a></p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>';
require_once 'env.php'; // Load email credentials
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $gmail;
                        $mail->Password   = $password;
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom($gmail, 'Mat-Mee');
                        $mail->addAddress($customerEmail, $customerName);

                        $mail->isHTML(true);
                        $mail->Subject = 'Order Confirmed #' . $orderIdPadded . ' – Mat-Mee';
                        $mail->Body    = $emailBody;
                        $mail->AltBody = 'Hi ' . $customerName . ', your order #' . $orderIdPadded . ' has been placed successfully! Grand total: BDT ' . number_format($mailGrandTotal, 0) . '. Thank you for shopping with Mat-Mee.';

                        $mail->send();
                    } catch (Exception $e) {
                        // Email failure is non-critical – order already saved
                        error_log('Mailer Error: ' . $mail->ErrorInfo);
                    }
                    header('Location: order-confirmation.php?id=' . $orderId);
                    
                    exit;
                }
            }
        }
    }
}

// Flush output buffer after all checks pass
ob_end_flush();
?>

<style>
/* Checkout Styles */
.checkout-page .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.04);
}
.checkout-page .card-header {
    background-color: transparent !important;
    border-bottom: 2px solid #f1f3f5;
    padding: 1.5rem;
    color: #333 !important;
}
.checkout-page .card-header h5 {
    font-weight: 700;
    font-size: 1.1rem;
    letter-spacing: -0.2px;
}
.form-control-modern {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
    background-color: #fafbfc;
}
.form-control-modern:focus {
    background-color: #fff;
    border-color: #800000;
    box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.08);
    outline: none;
}
.form-label-modern {
    font-weight: 600;
    font-size: 0.9rem;
    color: #495057;
    margin-bottom: 0.5rem;
}
.order-summary-item {
    transition: background-color 0.2s ease;
}
.order-summary-item:hover {
    background-color: #f8f9fa;
}
.btn-place-order {
    background-color: #800000;
    border-color: #800000;
    border-radius: 8px;
    font-weight: 600;
    padding: 1rem;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
    color: #fff;
}
.btn-place-order:hover {
    background-color: #990000;
    border-color: #990000;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(128, 0, 0, 0.3);
    color: #fff;
}
.checkout-summary-box {
    background: #fdfdfd;
}
.stagger-1 { animation-delay: 0.1s; }
.stagger-2 { animation-delay: 0.2s; }
.fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(20px);
}
@keyframes fadeInUp {
    to { opacity: 1; transform: translateY(0); }
}
</style>

<section class="container py-5 checkout-page">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="mb-4">Checkout</h1>

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($errorMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-7 fade-in-up stagger-1">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-geo-alt pe-2 text-danger"></i> Shipping & Contact Information</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" novalidate>
                                <div class="mb-3">
                                    <label class="form-label-modern">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control form-control-modern" required value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>" placeholder="Enter your full name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-modern">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="customer_email" class="form-control form-control-modern" required value="<?= htmlspecialchars($_POST['customer_email'] ?? '') ?>" placeholder="your@email.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-modern">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control form-control-modern" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="01700000000">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-modern">Shipping Address <span class="text-danger">*</span></label>
                                    <textarea name="shipping_address" class="form-control form-control-modern" rows="4" required placeholder="House no., Road, Area, City"><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label-modern">Additional Notes (Optional)</label>
                                    <textarea name="notes" class="form-control form-control-modern" rows="3" placeholder="e.g., Call before delivery, special instructions, etc."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn-place-order d-block w-100">
                                    <i class="bi bi-check2-circle me-1"></i> Place Order
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 fade-in-up stagger-2">
                    <div class="card checkout-summary-box sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-box-seam pe-2 text-primary"></i> Order Summary</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="fs-6 fw-semibold mb-2">Items in Order:</div>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($cartItems as $item):
                                        $unitPrice = (!empty($item['discount_price']) && (float)$item['discount_price'] < (float)$item['price']) ? (float)$item['discount_price'] : (float)$item['price'];
                                        $subtotal = $unitPrice * $item['quantity'];
                                    ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-start py-2 px-0">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold" style="font-size: 0.95rem;"><?= htmlspecialchars($item['name']) ?></div>
                                                <small class="text-muted">
                                                    Qty: <?= $item['quantity'] ?>
                                                    <?= !empty($item['size_name']) ? ' • Size: ' . htmlspecialchars($item['size_name']) : '' ?>
                                                </small>
                                            </div>
                                            <span class="fw-semibold text-end" style="min-width: 80px;">৳<?= number_format($subtotal, 0) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>৳<?= number_format($orderTotal, 0) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span>Shipping Charge:</span>
                                <strong class="text-muted">৳<?= number_format($shippingCharge, 0) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between fs-5">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold text-success">৳<?= number_format($grandTotal, 0) ?></span>
                            </div>

                            <div class="alert mt-4 py-3" style="background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; display:flex; align-items:center; gap:10px;">
                                <i class="bi bi-wallet2 fs-4 text-secondary"></i>
                                <div>
                                    <p class="mb-0 fw-semibold text-dark">Cash on Delivery (COD)</p>
                                    <small class="text-muted">Pay when you receive your order.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'components/page_close.php'; ?>


