<?php
session_start();
require_once '../components/connection.php';

$db = DatabaseConnection::getInstance();

// Get order ID from URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($orderId === 0) {
    die('Invalid order ID');
}

// Fetch order details
$order = $db->fetchOne('SELECT * FROM orders WHERE id = ?', [$orderId]);

if (!$order) {
    die('Order not found');
}

// Fetch order items
$orderItems = $db->fetch('SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?', [$orderId]);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($newStatus, $validStatuses)) {
        $result = $db->execute('UPDATE orders SET status = ? WHERE id = ?', [$newStatus, $orderId]);
        if ($result) {
            $order['status'] = $newStatus;
            $successMessage = 'Order status updated successfully!';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>View Order #<?php echo $orderId; ?> - Admin Dashboard</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="admin-wrapper">
		<?php include 'header.php'; ?>

		<div class="layout">
			<?php include 'sidebar.php'; ?>

			<main class="content">
				<section class="view-order">
					<div class="back-button">
						<a href="index.php">← Back to Dashboard</a>
					</div>

					<?php if (isset($successMessage)): ?>
						<div class="alert success">✓ <?php echo htmlspecialchars($successMessage); ?></div>
					<?php endif; ?>

					<div class="card">
						<div class="card-title">Order #<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></div>
						<div class="card-body">
							<div class="order-details-grid">
								<div class="order-section">
									<h3>Order Information</h3>
									<table class="info-table">
										<tr>
											<td class="label">Status:</td>
											<td>
												<form method="POST" id="statusForm" style="display: flex; gap: 10px; align-items: center;">
													<select name="status" id="statusSelect" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; cursor: pointer; font-weight: 600;">
														<option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
														<option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
														<option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
														<option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
														<option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
													</select>
												</form>
											</td>
										</tr>
										<tr>
											<td class="label">Created:</td>
											<td><?php echo date('M d, Y \a\t H:i A', strtotime($order['created_at'])); ?></td>
										</tr>
										<tr>
											<td class="label">Total Amount:</td>
											<td><strong>$<?php echo number_format($order['total'], 2); ?></strong></td>
										</tr>
										<tr>
											<td class="label">Shipping:</td>
											<td>$<?php echo number_format($order['shipping_charge'], 2); ?></td>
										</tr>
									</table>
								</div>

								<div class="order-section">
									<h3>Customer Information</h3>
									<table class="info-table">
										<tr>
											<td class="label">Name:</td>
											<td><?php echo htmlspecialchars($order['customer_name']); ?></td>
										</tr>
										<tr>
											<td class="label">Email:</td>
											<td><?php echo htmlspecialchars($order['customer_email']); ?></td>
										</tr>
										<tr>
											<td class="label">Phone:</td>
											<td><?php echo htmlspecialchars($order['phone']); ?></td>
										</tr>
									</table>
								</div>
							</div>

							<div class="order-section" style="margin-top: 24px;">
								<h3>Shipping Address</h3>
								<div class="address-box">
									<?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
								</div>
							</div>

							<?php if (!empty($order['notes'])): ?>
								<div class="order-section" style="margin-top: 24px;">
									<h3>Notes</h3>
									<div class="notes-box">
										<?php echo nl2br(htmlspecialchars($order['notes'])); ?>
									</div>
								</div>
							<?php endif; ?>

							<div class="order-section" style="margin-top: 24px;">
								<h3>Order Items</h3>
								<table class="orders-table">
									<thead>
										<tr>
											<th>Product</th>
											<th>Quantity</th>
											<th>Price</th>
											<th>Total</th>
										</tr>
									</thead>
									<tbody>
										<?php if (!empty($orderItems)): ?>
											<?php foreach ($orderItems as $item): ?>
												<tr>
													<td><?php echo htmlspecialchars($item['name']); ?></td>
													<td><?php echo $item['quantity']; ?></td>
													<td>$<?php echo number_format($item['price_at_purchase'], 2); ?></td>
													<td>$<?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></td>
												</tr>
											<?php endforeach; ?>
										<?php else: ?>
											<tr><td colspan="4" style="text-align: center; padding: 20px; color: #999;">No items found</td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>

							<div class="order-actions" style="margin-top: 24px; display: flex; gap: 12px;">
							<button onclick="deleteOrder(<?php echo $order['id']; ?>)" class="btn btn-danger">Delete Order</button>
							</div>
						</div>
					</div>
				</section>
			</main>
		</div>
	</div>

	<script>
		// Auto-submit status form on change
		document.getElementById('statusSelect')?.addEventListener('change', function() {
			document.getElementById('statusForm').submit();
		});

		function deleteOrder(orderId) {
			if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
				fetch('delete-order.php?id=' + orderId, {
					method: 'POST'
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						alert('Order deleted successfully');
						window.location.href = 'index.php';
					} else {
						alert('Error: ' + data.message);
					}
				})
				.catch(error => {
					console.error('Error:', error);
					alert('An error occurred while deleting the order');
				});
			}
		}
	</script>
</body>
</html>
