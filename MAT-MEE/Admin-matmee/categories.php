<?php
include 'header.php';

// ── Flash messages from redirects ────────────────────────────────────────────
$flash = '';
if (isset($_GET['msg'])) {
    $msgs = [
        'created' => ['success', '✓ Category created successfully!'],
        'updated' => ['success', '✓ Category updated successfully!'],
        'deleted' => ['success', '✓ Category deleted successfully!'],
        'error'   => ['error',   '✗ An error occurred. Please try again.'],
        'in_use'  => ['error',   '✗ Cannot delete category because it has products.'],
    ];
    $m = $_GET['msg'];
    if (isset($msgs[$m])) {
        [$type, $text] = $msgs[$m];
        $flash = "<div class='alert $type'>$text</div>";
    }
}

// ── Fetch categories with product count ──────────────────────────────────────
$sql = "SELECT c.id, c.name, c.slug, 
               (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
        FROM categories c
        ORDER BY c.name ASC";
$categories = $db->fetch($sql);

$totalCategories = count($categories);
$totalProducts = 0;
foreach ($categories as $c) {
    $totalProducts += $c['product_count'];
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
                    <h1 class="page-title">Categories</h1>
                    <p class="page-sub">Manage product categories</p>
                </div>
                <button class="btn btn-primary btn-icon" onclick="openModal()">
                    <span>＋</span> Add Category
                </button>
            </div>

            <!-- Analytics Strip -->
            <div class="analytics-grid" style="margin-bottom:24px;">
                <div class="analytics-card">
                    <div class="analytics-label">Total Categories</div>
                    <div class="analytics-value"><?= number_format($totalCategories) ?></div>
                    <div class="analytics-icon">🗂️</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-label">Total Classified Products</div>
                    <div class="analytics-value" style="color:#66bb6a;"><?= number_format($totalProducts) ?></div>
                    <div class="analytics-icon">📦</div>
                </div>
            </div>

            <?= $flash ?>

            <!-- Categories Grid / Table -->
            <div class="card">
                <div class="card-body">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Slug</th>
                                <th>Products Count</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $c): ?>
                                    <tr class="order-row">
                                        <td>#<?= $c['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                                        <td><span class="status shipped"><?= htmlspecialchars($c['slug']) ?></span></td>
                                        <td><?= $c['product_count'] ?> products</td>
                                        <td style="text-align: right;">
                                            <div class="action-icons" style="opacity:1; visibility:visible; justify-content: flex-end;">
                                                <a href="#" class="icon-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($c)) ?>); return false;" title="Edit">✏️</a>
                                                <a href="#" class="icon-delete" onclick="confirmDelete(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['name'])) ?>', <?= $c['product_count'] ?>); return false;" title="Delete">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 20px;">No categories found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- ── Modal Overlay ─────────────────────────────────────────────────── -->
<div id="categoryModal" class="modal-overlay" onclick="closeModalOnOverlay(event)">
    <div class="modal-box modal-box-sm">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Category</h2>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="categoryForm" action="category-save.php" method="POST">
            <input type="hidden" name="id" id="categoryId" value="">

            <div class="modal-body" style="text-align: left;">
                <div class="form-group">
                    <label class="form-label">Category Name <span class="req">*</span></label>
                    <input type="text" name="name" id="fName" class="form-input" required
                           placeholder="e.g. T-Shirt" oninput="autoSlug(this.value)">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" id="fSlug" class="form-input"
                           placeholder="auto-generated">
                    <small style="color:#aaa; display:block; margin-top:4px;">URL-friendly name. Leave blank to auto-generate.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveBtn">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────────────────────── -->
<div id="deleteModal" class="modal-overlay modal-sm" onclick="closeDeleteOnOverlay(event)">
    <div class="modal-box modal-box-sm">
        <div class="delete-icon-big">🗑️</div>
        <h3 class="delete-title">Delete Category?</h3>
        <p class="delete-sub" id="deleteCategoryName"></p>
        <p class="delete-warn" id="deleteCategoryWarn">This will permanently remove the category.</p>
        <div class="delete-actions" id="deleteActions">
            <button class="btn btn-ghost" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
            <form id="deleteForm" action="category-delete.php" method="POST" style="display:inline;">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" class="btn btn-danger" id="deleteConfirmBtn">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('saveBtn').textContent = 'Save Category';
    document.getElementById('categoryModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function openEditModal(c) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value   = c.id;
    document.getElementById('fName').value        = c.name || '';
    document.getElementById('fSlug').value        = c.slug || '';
    document.getElementById('saveBtn').textContent = 'Update Category';
    document.getElementById('categoryModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeModalOnOverlay(e) {
    if (e.target === document.getElementById('categoryModal')) closeModal();
}

function autoSlug(val) {
    if (document.getElementById('categoryId').value) return; 
    document.getElementById('fSlug').value = val.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
}

function confirmDelete(id, name, count) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteCategoryName').textContent = name;
    
    if (count > 0) {
        document.getElementById('deleteCategoryWarn').textContent = 'WARNING: This category contains ' + count + ' products. Cannot delete.';
        document.getElementById('deleteCategoryWarn').style.color = '#f44336';
        document.getElementById('deleteConfirmBtn').style.display = 'none';
    } else {
        document.getElementById('deleteCategoryWarn').textContent = 'This will permanently remove the category and cannot be undone.';
        document.getElementById('deleteCategoryWarn').style.color = '#aaa';
        document.getElementById('deleteConfirmBtn').style.display = 'inline-block';
    }
    
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
        closeModal();
        document.getElementById('deleteModal').classList.remove('open');
        document.body.style.overflow = '';
    }
});

const flash = document.querySelector('.alert');
if (flash) setTimeout(() => { flash.style.opacity = '0'; flash.style.transform = 'translateY(-8px)'; setTimeout(() => flash.remove(), 400); }, 4000);
</script>
</body>
</html>
