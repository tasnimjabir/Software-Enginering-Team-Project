<?php
include 'header.php';

// ── Flash messages from redirects ────────────────────────────────────────────
$flash = '';
if (isset($_GET['msg'])) {
    $msgs = [
        'deleted' => ['success', '✓ Message deleted successfully!'],
        'read'    => ['success', '✓ Message marked as read!'],
        'error'   => ['error',   '✗ An error occurred. Please try again.']
    ];
    $m = $_GET['msg'];
    if (isset($msgs[$m])) {
        [$type, $text] = $msgs[$m];
        $flash = "<div class='alert $type'>$text</div>";
    }
}

// ── Fetch messages ──────────────────────────────────────
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$messages = $db->fetch($sql);

$totalMessages = count($messages);
$unreadCount = 0;
foreach ($messages as $m) {
    if (!$m['is_read']) $unreadCount++;
}

?>
<body>
<div class="admin-wrapper">
    <div class="layout">
        <?php include 'sidebar.php'; ?>

        <main class="content">

            <!-- Header Row -->
            <div class="products-header">
                <div>
                    <h1 class="page-title">Contact Messages</h1>
                    <p class="page-sub">View and manage messages from the contact page</p>
                </div>
            </div>

            <!-- Analytics Strip -->
            <div class="analytics-grid" style="margin-bottom:24px;">
                <div class="analytics-card">
                    <div class="analytics-label">Total Messages</div>
                    <div class="analytics-value"><?= number_format($totalMessages) ?></div>
                    <div class="analytics-icon" style="color:#ba68c8;"><i class="bi bi-envelope"></i></div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-label">Unread Messages</div>
                    <div class="analytics-value" style="color:#f44336;"><?= number_format($unreadCount) ?></div>
                    <div class="analytics-icon" style="color:#f44336;"><i class="bi bi-envelope-exclamation"></i></div>
                </div>
            </div>

            <?= $flash ?>

            <!-- Messages Grid / Table -->
            <div class="card">
                <div class="card-body">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email/Phone</th>
                                <th>Subject</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $m): ?>
                                    <tr class="order-row" style="<?= !$m['is_read'] ? 'background:#393939;' : '' ?>">
                                        <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                                        <td>
                                            <?= htmlspecialchars($m['email']) ?><br>
                                            <small><?= htmlspecialchars($m['phone']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($m['subject']) ?></td>
                                        <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($m['message']) ?>">
                                            <?= htmlspecialchars($m['message']) ?>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($m['created_at'])) ?></td>
                                        <td>
                                            <?php if ($m['is_read']): ?>
                                                <span class="status delivered">Read</span>
                                            <?php else: ?>
                                                <span class="status pending">Unread</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                                <a href="#" class="btn btn-sm btn-ghost" onclick="viewMessage(<?= htmlspecialchars(json_encode($m)) ?>); return false;" title="View Message" style="padding: 4px 8px; font-size: 0.8rem; margin-right: 5px; color:#1976d2;">View</a>
                                                <?php if (!$m['is_read']): ?>
                                                    <a href="message-action.php?action=read&id=<?= $m['id'] ?>" class="btn btn-sm btn-ghost" title="Mark as Read" style="padding: 4px 8px; font-size: 0.8rem; margin-right: 5px;">Mark Read</a>
                                                <?php endif; ?>
                                                <a href="#" class="icon-delete" onclick="confirmDelete(<?= $m['id'] ?>); return false;" title="Delete"><span style="font-size:1.2rem; color:#f44336;"><i class="bi bi-trash"></i></span></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding: 20px;">No messages found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- ── View Message Modal ──────────────────────────────────────────── -->
<div id="viewModal" class="modal-overlay" onclick="closeViewOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title">Message Details</h2>
            <button class="modal-close" onclick="closeViewModal()">✕</button>
        </div>
        <div class="modal-body" style="text-align: left;">
            <div style="margin-bottom: 15px;">
                <strong>From:</strong> <span id="viewName"></span>
            </div>
            <div style="margin-bottom: 15px;">
                <strong>Email:</strong> <span id="viewEmail"></span>
            </div>
            <div style="margin-bottom: 15px;">
                <strong>Phone:</strong> <span id="viewPhone"></span>
            </div>
            <div style="margin-bottom: 15px;">
                <strong>Subject:</strong> <span id="viewSubject"></span>
            </div>
            <div style="margin-bottom: 15px;">
                <strong>Date:</strong> <span id="viewDate"></span>
            </div>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
            <div style="margin-bottom: 15px;">
                <strong>Message:</strong>
                <p id="viewMessageContent" style="margin-top: 10px; white-space: pre-wrap; background: #f9f9f9; padding: 15px; border-radius: 8px; font-size: 0.95rem; line-height: 1.5; color: #333; border: 1px solid #efefef;"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────────────────────── -->
<div id="deleteModal" class="modal-overlay modal-sm" onclick="closeDeleteOnOverlay(event)">
    <div class="modal-box modal-box-sm">
        <div class="delete-icon-big" style="font-size:3rem; color:#f44336; margin-bottom:1rem;"><i class="bi bi-trash"></i></div>
        <h3 class="delete-title">Delete Message?</h3>
        <p class="delete-warn" id="deleteCategoryWarn">This will permanently remove the message.</p>
        <div class="delete-actions" id="deleteActions">
            <button class="btn btn-ghost" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
            <form id="deleteForm" action="message-action.php" method="POST" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" class="btn btn-danger" id="deleteConfirmBtn">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function viewMessage(msg) {
    document.getElementById('viewName').textContent = msg.name;
    document.getElementById('viewEmail').textContent = msg.email;
    document.getElementById('viewPhone').textContent = msg.phone;
    document.getElementById('viewSubject').textContent = msg.subject;
    document.getElementById('viewMessageContent').textContent = msg.message;
    document.getElementById('viewDate').textContent = new Date(msg.created_at).toLocaleString();
    
    document.getElementById('viewModal').classList.add('open');
    document.body.style.overflow = 'hidden';

    // Mark as read automatically via AJAX if it's currently unread
    if (msg.is_read == 0) {
        fetch('message-action.php?action=read_ajax&id=' + msg.id)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Ideally we'd remove the 'unread' styling and 'Mark Read' button dynamically, 
                // but a simple approach is just letting the page refresh next time.
                // We'll quickly just change the row styling if it existed.
                // Optionally reload after a bit or let admin see changes on next load.
            }
        });
    }
}

function closeViewModal() {
    document.getElementById('viewModal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeViewOnOverlay(e) {
    if (e.target === document.getElementById('viewModal')) {
        closeViewModal();
    }
}

function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeDeleteOnOverlay(e) {
    if (e.target === document.getElementById('deleteModal')) {
        document.getElementById('deleteModal').classList.remove('open');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('deleteModal').classList.remove('open');
        closeViewModal();
        document.body.style.overflow = '';
    }
});

const flash = document.querySelector('.alert');
if (flash) setTimeout(() => { flash.style.opacity = '0'; flash.style.transform = 'translateY(-8px)'; setTimeout(() => flash.remove(), 400); }, 4000);
</script>
</body>
</html>
