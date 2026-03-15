<?php	
include 'header.php';
// Fetch analytics data
$totalOrders = $db->fetchOne('SELECT COUNT(*) as count FROM orders')['count'];
$pendingOrders = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "pending"')['count'];
$processingOrders = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "processing"')['count'];
$completedOrders = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "completed" OR status = "delivered"')['count'];
$totalRevenue = $db->fetchOne('SELECT SUM(total) as revenue FROM orders WHERE status != "cancelled"')['revenue'] ?? 0;

// Fetch recent orders
$recentOrders = $db->fetch('SELECT * FROM orders ORDER BY created_at DESC LIMIT 10');
?>


<body>
	<div class="admin-wrapper">

		<div class="layout">
			<?php include 'sidebar.php'; ?>

			<main class="content">
				<!-- Analytics Section -->
				<section class="analytics">
					<div class="analytics-grid">
						<div class="analytics-card">
							<div class="analytics-label">Total Orders</div>
							<div class="analytics-value"><?php echo number_format($totalOrders); ?></div>
							<div class="analytics-icon">📦</div>
						</div>
						<div class="analytics-card">
							<div class="analytics-label">Pending</div>
							<div class="analytics-value" style="color: #ff7043;"><?php echo number_format($pendingOrders); ?></div>
							<div class="analytics-icon">⏱️</div>
						</div>
						<div class="analytics-card">
							<div class="analytics-label">Processing</div>
							<div class="analytics-value" style="color: #ffa726;"><?php echo number_format($processingOrders); ?></div>
							<div class="analytics-icon">⚙️</div>
						</div>
						<div class="analytics-card">
							<div class="analytics-label">Completed</div>
							<div class="analytics-value" style="color: #66bb6a;"><?php echo number_format($completedOrders); ?></div>
							<div class="analytics-icon">✅</div>
						</div>
						<div class="analytics-card">
							<div class="analytics-label">Total Revenue</div>
							<div class="analytics-value" style="color: var(--red-dark);">$<?php echo number_format($totalRevenue, 2); ?></div>
							<div class="analytics-icon">💰</div>
						</div>
					</div>
				</section>

				<!-- Orders Section -->
				<section class="cards">
					<div class="card orders-card">
						<div class="card-title">Recent Orders</div>
						<div class="card-body">
							<table class="orders-table">
								<thead>
									<tr>
										<th>Order ID</th>
										<th>Customer</th>
										<th>Email</th>
										<th>Status</th>
										<th>Total</th>
										<th>Date</th>
									</tr>
								</thead>
								<tbody>
									<?php if (!empty($recentOrders)): ?>
										<?php foreach ($recentOrders as $order): ?>
											<tr class="order-row" data-order-id="<?php echo $order['id']; ?>">
											<td class="order-link" style="cursor: pointer;">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
											<td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
											<td><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></td>
											<td>
											<select class="status-select status-<?php echo $order['status']; ?>" data-order-id="<?php echo $order['id']; ?>" onchange="updateOrderStatus(this)">
													<option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
													<option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
													<option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
													<option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
													<option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
												</select>
											</td>
											<td>$<?php echo number_format($order['total'], 2); ?></td>
											<td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
											<td class="action-icons">
													<a href="#" onclick="deleteOrder(<?php echo $order['id']; ?>); return false;" title="Delete" class="icon-delete">🗑️</a>
												</td>
										</tr>
										<?php endforeach; ?>
									<?php else: ?>
										<tr><td colspan="7" style="text-align: center; padding: 20px; color: #999;">No orders found</td></tr>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</div>
				</section>
			</main>
		</div>
	</div>

	<script>
		function updateOrderStatus(selectElement) {
			const orderId = selectElement.getAttribute('data-order-id');
			const newStatus = selectElement.value;

			fetch('update-status.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'id=' + orderId + '&status=' + newStatus
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					// Update the status class dynamically
					selectElement.className = 'status-select status-' + newStatus;
					// Show brief feedback
					selectElement.style.transform = 'scale(1.05)';
					setTimeout(() => {
						selectElement.style.transform = 'scale(1)';
					}, 300);
				} else {
					alert('Error: ' + data.message);
					location.reload();
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('An error occurred while updating status');
				location.reload();
			});
		}

		function deleteOrder(orderId) {
			if (confirm('Are you sure you want to delete order #' + orderId + '? This action cannot be undone.')) {
				fetch('delete-order.php?id=' + orderId, {
					method: 'POST'
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						alert('Order deleted successfully');
						location.reload();
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

		// Redirect to view-order.php on order row click
		document.querySelectorAll('.order-row').forEach(row => {
			row.addEventListener('click', function(e) {
			if (!e.target.closest('.status-select') && !e.target.closest('.action-icons')) {
				const orderId = this.getAttribute('data-order-id');
				window.location.href = 'view-order.php?id=' + orderId;
			}
			});
		});
	</script>
</body>
</html>
