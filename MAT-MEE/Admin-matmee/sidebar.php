<!-- Sidebar Component -->
<aside class="sidebar">
	<nav>
		<ul>
			<?php
			$currentPage = basename($_SERVER['PHP_SELF']);
			$pages = [
				'index.php' => 'Dashboard',
				// 'customers.php' => 'Customers',
				'products.php' => 'Products',
				'categories.php' => 'Categories',
				// 'orders.php' => 'Orders',
				'messages.php' => 'Contact Messages',
				// 'reports.php' => 'Reports',
				'carousel.php' => 'Carousel',
				'info.php' => 'Info',
				'settings.php' => 'Settings'
			];
			?>
			<?php foreach ($pages as $page => $name): ?>
				<li class="<?php echo ($currentPage === $page || ($page === 'orders.php' && $currentPage === 'view-order.php')) ? 'active' : ''; ?>">
					<a href="<?php echo $page; ?>"><?php echo $name; ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</aside>
