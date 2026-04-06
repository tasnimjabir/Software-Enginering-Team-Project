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

<section class="container py-5">
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
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">📍 Shipping & Contact Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" novalidate>
                                <div class="mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control" required value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>" placeholder="Enter your full name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="customer_email" class="form-control" required value="<?= htmlspecialchars($_POST['customer_email'] ?? '') ?>" placeholder="your@email.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="01700000000">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Shipping Address <span class="text-danger">*</span></label>
                                    <textarea name="shipping_address" class="form-control" rows="4" required placeholder="House no., Road, Area, City"><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Additional Notes (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="e.g., Call before delivery, special instructions, etc."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="bi bi-check-circle"></i> Place Order
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">📦 Order Summary</h5>
                        </div>
                        <div class="card-body">
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

                            <div class="alert alert-light mt-3 mb-0">
                                <small><i class="bi bi-info-circle"></i> Cash on Delivery (COD) Available</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'components/page_close.php'; ?>


