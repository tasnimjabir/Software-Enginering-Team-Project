<?php
ob_start(); // Start output buffering to prevent header issues

require_once 'components/config-page.php';
$page_title = 'Checkout';

$successMessage = '';
$errorMessage = '';

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


