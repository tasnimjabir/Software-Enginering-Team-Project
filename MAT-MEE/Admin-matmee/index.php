<?php	
include 'header.php';

// ── Core analytics ────────────────────────────────────────────────────────────
$totalOrders      = $db->fetchOne('SELECT COUNT(*) as count FROM orders')['count'] ?? 0;
$pendingOrders    = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "pending"')['count'] ?? 0;
$processingOrders = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "processing"')['count'] ?? 0;
$shippedOrders    = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "shipped"')['count'] ?? 0;
$completedOrders  = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "completed" OR status = "delivered"')['count'] ?? 0;
$cancelledOrders  = $db->fetchOne('SELECT COUNT(*) as count FROM orders WHERE status = "cancelled"')['count'] ?? 0;
$totalRevenue     = $db->fetchOne('SELECT SUM(total) as revenue FROM orders WHERE status != "cancelled"')['revenue'] ?? 0;
$totalProducts    = $db->fetchOne('SELECT COUNT(*) as count FROM products')['count'] ?? 0;
$totalCategories  = $db->fetchOne('SELECT COUNT(*) as count FROM categories')['count'] ?? 0;

// ── Sales over the last 30 days (Line chart) ──────────────────────────────────
$salesRows = $db->fetch(
    'SELECT DATE(created_at) as day, SUM(total) as revenue
     FROM orders
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
       AND status != "cancelled"
     GROUP BY day
     ORDER BY day ASC'
);
// Build a full 30-day range so gaps show as 0
$salesMap = [];
foreach ($salesRows as $r) { $salesMap[$r['day']] = (float)$r['revenue']; }
$salesLabels = $salesData = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $salesLabels[] = date('M d', strtotime($d));
    $salesData[]   = $salesMap[$d] ?? 0;
}

// ── Top 8 selling products (Bar chart) ────────────────────────────────────────
$topProducts = $db->fetch(
    'SELECT p.name, SUM(oi.quantity) as total_sold
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     GROUP BY oi.product_id
     ORDER BY total_sold DESC
     LIMIT 8'
);
$topLabels = array_column($topProducts, 'name');
$topData   = array_column($topProducts, 'total_sold');

// ── Views over the last 30 days (Line chart – using orders as proxy) ──────────
$viewRows = $db->fetch(
    'SELECT DATE(created_at) as day, COUNT(*) as cnt
     FROM orders
     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
     GROUP BY day
     ORDER BY day ASC'
);
$viewMap = [];
foreach ($viewRows as $r) { $viewMap[$r['day']] = (int)$r['cnt']; }
$viewLabels = $viewData = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $viewLabels[] = date('M d', strtotime($d));
    $viewData[]   = $viewMap[$d] ?? 0;
}

// ── Recent orders ─────────────────────────────────────────────────────────────
$recentOrders = $db->fetch('SELECT * FROM orders ORDER BY created_at DESC LIMIT 10');
?>

<body>
<div class="admin-wrapper">
    <div class="layout">
        <?php include 'sidebar.php'; ?>

        <main class="content">

            <!-- ══ PAGE TITLE ══════════════════════════════════════════════ -->
            <div class="products-header" style="margin-bottom:28px;">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-sub">Welcome back — here's what's happening today</p>
                </div>
            </div>

            <!-- ══ TWO COLUMNS: analytics list + order-status pie ══════════ -->
            <div style="display:grid; grid-template-columns:320px 1fr; gap:20px; margin-bottom:20px;">

                <!-- Analytics list card -->
                <div class="card" style="border-radius:14px; box-shadow:0 4px 18px rgba(0,0,0,.06); overflow:hidden;">
                    <div style="padding:18px 20px 10px; font-weight:700; font-size:.95rem; letter-spacing:.3px; border-bottom:1px solid #f1f1f1;">
                        <i class="bi bi-bar-chart-line me-2" style="color:#c62828;"></i>Overview
                    </div>
                    <div style="padding:4px 0;">

                        <?php
                        $metrics = [
                            ['Total Orders',    $totalOrders,                  'bi-bag-check',        '#5c6bc0'],
                            ['Total Revenue',   '$'.number_format($totalRevenue,2), 'bi-currency-dollar', '#c62828'],
                            ['Total Products',  $totalProducts,                'bi-box-seam',         '#1976d2'],
                            ['Categories',      $totalCategories,              'bi-folder2-open',     '#ba68c8'],
                            ['Pending',         $pendingOrders,                'bi-clock',            '#ff7043'],
                            ['Processing',      $processingOrders,             'bi-gear',             '#ffa726'],
                            ['Shipped',         $shippedOrders,                'bi-truck',            '#29b6f6'],
                            ['Completed',       $completedOrders,              'bi-check-circle',     '#66bb6a'],
                            ['Cancelled',       $cancelledOrders,              'bi-x-circle',         '#ef5350'],
                        ];
                        foreach ($metrics as $m):
                        ?>
                        <div style="display:flex; align-items:center; justify-content:space-between;
                                    padding:10px 20px; border-bottom:1px solid #f7f7f7;
                                    transition:background .15s;" 
                             onmouseover="this.style.background='#000000'" 
                             onmouseout="this.style.background='transparent'">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="width:30px; height:30px; border-radius:8px;
                                             background:<?= $m[3] ?>18;
                                             display:flex; align-items:center; justify-content:center;">
                                    <i class="bi <?= $m[2] ?>" style="color:<?= $m[3] ?>; font-size:.9rem;"></i>
                                </span>
                                <span style="font-size:.87rem; color:#999; font-weight:500;"><?= $m[0] ?></span>
                            </div>
                            <span style="font-weight:700; font-size:.95rem;"><?= $m[1] ?></span>
                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- Order Status Pie Chart -->
                <div class="card" style="border-radius:14px; box-shadow:0 4px 18px rgba(0,0,0,.06); padding:22px;">
                    <div style="font-weight:700; font-size:.95rem; margin-bottom:16px;">
                        <i class="bi bi-pie-chart me-2" style="color:#c62828;"></i>Order Status Distribution
                    </div>
                    <div style="height:270px;">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ══ SALES OVER TIME (Line) ═══════════════════════════════════ -->
            <div class="card" style="border-radius:14px; box-shadow:0 4px 18px rgba(0,0,0,.06); padding:22px; margin-bottom:20px;">
                <div style="font-weight:700; font-size:.95rem; margin-bottom:16px;">
                    <i class="bi bi-graph-up-arrow me-2" style="color:#c62828;"></i>Sales Revenue — Last 30 Days
                </div>
                <div style="height:260px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- ══ TWO CHARTS ROW: Top Products + Orders/Views Over Time ═══ -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

                <!-- Top Selling Products Bar -->
                <div class="card" style="border-radius:14px; box-shadow:0 4px 18px rgba(0,0,0,.06); padding:22px;">
                    <div style="font-weight:700; font-size:.95rem; margin-bottom:16px;">
                        <i class="bi bi-trophy me-2" style="color:#fbc02d;"></i>Top Selling Products
                    </div>
                    <div style="height:260px;">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>

                <!-- Orders Over Time Line -->
                <div class="card" style="border-radius:14px; box-shadow:0 4px 18px rgba(0,0,0,.06); padding:22px;">
                    <div style="font-weight:700; font-size:.95rem; margin-bottom:16px;">
                        <i class="bi bi-activity me-2" style="color:#1976d2;"></i>Orders Over Time — Last 30 Days
                    </div>
                    <div style="height:260px;">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ══ RECENT ORDERS TABLE ══════════════════════════════════════ -->
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
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr class="order-row" data-order-id="<?php echo $order['id']; ?>">
                                        <td class="order-link" style="cursor:pointer;">#<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_email'] ?? 'N/A'); ?></td>
                                        <td>
                                            <select class="status-select status-<?php echo $order['status']; ?>" data-order-id="<?php echo $order['id']; ?>" onchange="updateOrderStatus(this)">
                                                <option value="pending"    <?php echo $order['status']==='pending'    ? 'selected':''; ?>>Pending</option>
                                                <option value="processing" <?php echo $order['status']==='processing' ? 'selected':''; ?>>Processing</option>
                                                <option value="shipped"    <?php echo $order['status']==='shipped'    ? 'selected':''; ?>>Shipped</option>
                                                <option value="delivered"  <?php echo $order['status']==='delivered'  ? 'selected':''; ?>>Delivered</option>
                                                <option value="cancelled"  <?php echo $order['status']==='cancelled'  ? 'selected':''; ?>>Cancelled</option>
                                            </select>
                                        </td>
                                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td class="action-icons">
                                            <a href="#" onclick="deleteOrder(<?php echo $order['id']; ?>); return false;" title="Delete" class="icon-delete" style="color:#f44336; font-size:1.2rem;"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" style="text-align:center; padding:20px; color:#999;">No orders found</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>
</div>

<!-- ══ CHART.JS ═══════════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* ── Shared defaults ─────────────────────────────────────────────────── */
Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
Chart.defaults.color = '#888';

/* ── 1. Order Status Pie ─────────────────────────────────────────────── */
new Chart(document.getElementById('orderStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending','Processing','Shipped','Completed','Cancelled'],
        datasets: [{
            data: [
                <?= (int)$pendingOrders ?>,
                <?= (int)$processingOrders ?>,
                <?= (int)$shippedOrders ?>,
                <?= (int)$completedOrders ?>,
                <?= (int)$cancelledOrders ?>
            ],
            backgroundColor: ['#ff7043','#ffa726','#29b6f6','#66bb6a','#ef5350'],
            borderWidth: 0,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, boxWidth: 12, boxHeight: 12 } }
        },
        cutout: '62%'
    }
});

/* ── 2. Sales Over Time Line ─────────────────────────────────────────── */
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($salesLabels) ?>,
        datasets: [{
            label: 'Revenue ($)',
            data: <?= json_encode($salesData) ?>,
            borderColor: '#c62828',
            backgroundColor: 'rgba(198,40,40,0.08)',
            borderWidth: 2.5,
            pointRadius: 3,
            pointBackgroundColor: '#c62828',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: '#f4f4f4' }, ticks: { maxTicksLimit: 8 } },
            y: { grid: { color: '#f4f4f4' }, beginAtZero: true,
                 ticks: { callback: v => '$' + v } }
        }
    }
});

/* ── 3. Top Selling Products Bar ─────────────────────────────────────── */
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($topLabels) ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?= json_encode($topData) ?>,
            backgroundColor: [
                '#c62828','#e53935','#ef5350','#e57373',
                '#ffa726','#ffb74d','#66bb6a','#4db6ac'
            ],
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: {
                maxRotation: 30, minRotation: 15,
                callback: function(val, i) {
                    const lbl = this.getLabelForValue(val);
                    return lbl.length > 12 ? lbl.slice(0,12)+'…' : lbl;
                }
            }},
            y: { grid: { color: '#f4f4f4' }, beginAtZero: true,
                 ticks: { stepSize: 1 } }
        }
    }
});

/* ── 4. Orders Over Time Line ────────────────────────────────────────── */
new Chart(document.getElementById('viewsChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($viewLabels) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode($viewData) ?>,
            borderColor: '#dd5c11',
            backgroundColor: 'rgba(25,118,210,0.07)',
            borderWidth: 2.5,
            pointRadius: 3,
            pointBackgroundColor: '#1976d2',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: '#f4f4f4' }, ticks: { maxTicksLimit: 8 } },
            y: { grid: { color: '#f4f4f4' }, beginAtZero: true,
                 ticks: { stepSize: 1 } }
        }
    }
});

/* ── Order status update ─────────────────────────────────────────────── */
function updateOrderStatus(selectElement) {
    const orderId = selectElement.getAttribute('data-order-id');
    const newStatus = selectElement.value;
    fetch('update-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + orderId + '&status=' + newStatus
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            selectElement.className = 'status-select status-' + newStatus;
            selectElement.style.transform = 'scale(1.05)';
            setTimeout(() => selectElement.style.transform = 'scale(1)', 300);
        } else {
            alert('Error: ' + data.message);
            location.reload();
        }
    })
    .catch(() => { alert('An error occurred'); location.reload(); });
}

/* ── Delete order ────────────────────────────────────────────────────── */
function deleteOrder(orderId) {
    if (confirm('Delete order #' + orderId + '? This cannot be undone.')) {
        fetch('delete-order.php?id=' + orderId, { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) { alert('Deleted'); location.reload(); }
            else alert('Error: ' + data.message);
        })
        .catch(() => alert('An error occurred'));
    }
}

/* ── Row click → view order ──────────────────────────────────────────── */
document.querySelectorAll('.order-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (!e.target.closest('.status-select') && !e.target.closest('.action-icons')) {
            window.location.href = 'view-order.php?id=' + this.getAttribute('data-order-id');
        }
    });
});
</script>
</body>
</html>
