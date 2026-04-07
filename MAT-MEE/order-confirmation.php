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

<style>
/* Confirmation Styles */
.confirm-page .card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.05);
    overflow: hidden;
}
.success-banner {
    background: linear-gradient(135deg, #760000 0%, #022800 100%) !important;
    position: relative;
    overflow: hidden;
    color: white;
}
.success-banner::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle cx="2" cy="2" r="2" fill="rgba(255,255,255,0.04)"/></svg>');
    z-index: 1;
}
.success-banner > div {
    position: relative;
    z-index: 2;
}
.check-icon-wrap {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.5rem;
    animation: scaleIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) backwards;
    animation-delay: 0.2s;
    backdrop-filter: blur(4px);
}
.confirm-page .card-header {
    background-color: #fff !important;
    border-bottom: 2px solid #f1f3f5;
    padding: 1.5rem;
}
.confirm-page .card-header h5 {
    font-weight: 700;
    color: #333;
    font-size: 1.1rem;
    letter-spacing: -0.2px;
}
.info-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #888;
    margin-bottom: 0.4rem;
    font-weight: 600;
}
.info-value {
    font-weight: 600;
    color: #212529;
}
.action-btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 0.9rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}
.btn-primary-custom {
    background-color: #800000;
    border-color: #800000;
    color: #fff;
    box-shadow: 0 4px 12px rgba(128,0,0,0.2);
}
.btn-primary-custom:hover {
    background-color: #990000;
    border-color: #990000;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(128,0,0,0.3);
}
.btn-outline-custom {
    border: 1.5px solid #e9ecef;
    color: #495057;
    background: transparent;
}
.btn-outline-custom:hover {
    background-color: #f8f9fa;
    color: #212529;
    transform: translateY(-2px);
    border-color: #dee2e6;
}
.table > :not(caption) > * > * {
    padding: 1rem 0.5rem;
    border-bottom-color: #f1f3f5;
}
.fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(20px);
}
.stagger-1 { animation-delay: 0.1s; }
.stagger-2 { animation-delay: 0.2s; }
.stagger-3 { animation-delay: 0.3s; }

@keyframes fadeInUp {
    to { opacity: 1; transform: translateY(0); }
}
@keyframes scaleIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<section class="container py-5 confirm-page">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Success Header -->
            <div class="card mb-4 fade-in-up">
                <div class="card-body text-center py-5 success-banner">
                    <div>
                        <div class="check-icon-wrap"><i class="bi bi-check-lg"></i></div>
                        <h1 class="card-title fw-bold">Order Placed Successfully!</h1>
                        <p class="lead mb-0 text-white-50">Thank you for your purchase</p>
                    </div>
                </div>
            </div>

            <!-- Order ID -->
            <div class="card mb-4 fade-in-up stagger-1">
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-md-6 border-end-md border-light">
                            <div class="info-label">Order Number</div>
                            <div class="fs-3 fw-bold" style="color: #800000;">#<?= str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></div>
                        </div>
                        <div class="col-md-6 border-end-md border-light">
                            <div class="info-label">Order Date</div>
                            <div class="fs-5 info-value"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4 fade-in-up stagger-2">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box-seam pe-2"></i> Order Items</h5>
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
            <div class="row mb-4 fade-in-up stagger-3">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-geo-alt pe-2"></i> Shipping Info</h5>
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
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-receipt pe-2"></i> Order Summary</h5>
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
            <div class="card mb-4 fade-in-up stagger-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-card-text pe-2"></i> Additional Notes</h5>
                </div>
                <div class="card-body p-4 text-muted">
                    <?= nl2br(htmlspecialchars($order['notes'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="row gap-3 gap-sm-0 fade-in-up stagger-3 mt-4">
                <div class="col-sm-6">
                    <a href="shop.php" class="action-btn btn-outline-custom w-100">
                        <i class="bi bi-shop"></i> Continue Shopping
                    </a>
                </div>
                <div class="col-sm-6 mt-3 mt-sm-0">
                    <a href="index.php" class="action-btn btn-primary-custom w-100">
                        <i class="bi bi-house"></i> Back to Home
                    </a>
                </div>
            </div>

            <!-- Info Box -->
            <div class="alert mt-5 p-4 fade-in-up stagger-3" style="background: #fdfdfd; border: 1px dashed #ced4da; border-radius: 12px;">
                <p class="mb-3 fw-bold text-dark"><i class="bi bi-stars pe-1" style="color: #fbc02d;"></i> What happens next?</p>
                <ul class="mb-0 text-muted" style="line-height: 1.8;">
                    <li>Our team will verify and process your order</li>
                    <li>You will receive an SMS/Email confirmation</li>
                    <li>Your order will be shipped soon</li>
                    <li>Keep your order ID: <strong class="text-dark">#<?= str_pad($orderId, 5, '0', STR_PAD_LEFT) ?></strong> for tracking</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php 
ob_end_flush(); // Flush output buffer
require_once 'components/page_close.php'; 
?>
