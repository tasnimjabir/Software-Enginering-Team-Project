<?php
ob_start(); // Start output buffering to prevent header issues
require_once 'components/config-page.php';

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    ob_end_clean();
    header('Location: index.php');
    exit;
}

$order = $conn->fetchOne('SELECT * FROM orders WHERE id = ?', [$orderId]);
if (!$order) {
    ob_end_clean();
    header('Location: index.php');
    exit;
}

$orderItems = $conn->fetch(
    'SELECT oi.*, p.name, p.main_image FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC',
    [$orderId]
);

$page_title = 'Order Confirmation - #' . $orderId;
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Success Header -->
            <div class="card border-success mb-4">
                <div class="card-body text-center py-5" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">✓</div>
                    <h1 class="card-title">Order Placed Successfully!</h1>
                    <p class="lead mb-0">Thank you for your purchase</p>
                </div>
            </div>

            <!-- Order ID -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div>
                                <small class="text-muted">ORDER ID</small>
                                <p class="fs-3 fw-bold text-primary">#<?= str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div>
                                <small class="text-muted">ORDER DATE</small>
                                <p class="fs-5 fw-semibold"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="upload/products/<?= htmlspecialchars($item['main_image'] ?: 'image/placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="40" height="40" class="rounded" style="object-fit: cover;">
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">৳<?= number_format($item['price_at_purchase'], 0) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end fw-semibold">৳<?= number_format($item['price_at_purchase'] * $item['quantity'], 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Shipping & Order Summary -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Name:</strong><br>
                                <?= htmlspecialchars($order['customer_name']) ?>
                            </p>
                            <p class="mb-2">
                                <strong>Email:</strong><br>
                                <?= htmlspecialchars($order['customer_email']) ?>
                            </p>
                            <p class="mb-2">
                                <strong>Phone:</strong><br>
                                <?= htmlspecialchars($order['phone']) ?>
                            </p>
                            <p class="mb-0">
                                <strong>Address:</strong><br>
                                <?= htmlspecialchars($order['shipping_address']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $subtotal = 0;
                            foreach ($orderItems as $item) {
                                $subtotal += $item['price_at_purchase'] * $item['quantity'];
                            }
                            $shippingCharge = (float)$order['shipping_charge'];
                            $grandTotal = $subtotal + $shippingCharge;
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong>৳<?= number_format($subtotal, 0) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span>Shipping Charge:</span>
                                <strong>৳<?= number_format($shippingCharge, 0) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between fs-5">
                                <span class="fw-bold">Total Amount:</span>
                                <span class="fw-bold text-success">৳<?= number_format($grandTotal, 0) ?></span>
                            </div>
                            <hr>
                            <div class="alert alert-info mb-0">
                                <strong>Status:</strong> <span class="badge bg-warning text-dark">Pending</span><br>
                                <small>Your order is pending and will be processed by our team soon.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($order['notes'])): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Additional Notes</h5>
                </div>
                <div class="card-body">
                    <?= nl2br(htmlspecialchars($order['notes'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="row gap-2">
                <div class="col-sm-6">
                    <a href="shop.php" class="btn btn-outline-primary w-100">
                        <i class="bi bi-shop"></i> Continue Shopping
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="index.php" class="btn btn-primary w-100">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert alert-light border mt-4">
                <p class="mb-2"><strong>What happens next?</strong></p>
                <ul class="mb-0">
                    <li>Our team will verify and process your order</li>
                    <li>You will receive an SMS/Email confirmation</li>
                    <li>Your order will be shipped soon</li>
                    <li>Track your order using Order ID: <strong>#<?= str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></strong></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php 
ob_end_flush(); // Flush output buffer
require_once 'components/page_close.php'; 
?>
