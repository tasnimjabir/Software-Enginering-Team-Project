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
$sql = "SELECT c.id, c.name, c.slug, c.image, c.description,
               (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count,
               (SELECT COUNT(*) FROM sizes s WHERE s.category_id = c.id) as size_count
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
                                <th>Image</th>
                                <th>Category Name</th>
                                <th>Slug</th>
                                <th>Sizes</th>
                                <th>Products Count</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $c): ?>
                                    <tr class="order-row">
                                        <td>
                                            <?php if ($c['image']): ?>
                                                <img src="../upload/categories/<?= htmlspecialchars($c['image']) ?>" 
                                                     style="width:50px; height:50px; object-fit:cover; border-radius:4px;" 
                                                     alt="<?= htmlspecialchars($c['name']) ?>">
                                            <?php else: ?>
                                                <div style="width:50px; height:50px; background:#e0e0e0; border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:24px;">📦</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                                        <td><span class="status shipped"><?= htmlspecialchars($c['slug']) ?></span></td>
                                        <td><?= $c['size_count'] ?> sizes</td>
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
                                    <td colspan="6" style="text-align:center; padding: 20px;">No categories found.</td>
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
        <form id="categoryForm" action="category-save.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="categoryId" value="">

            <div class="modal-body" style="text-align: left; max-height: 70vh; overflow-y: auto;">
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
                
                <div class="form-group">
                    <label class="form-label">Category Image</label>
                    <div style="display:flex; gap:10px; margin-bottom:8px;">
                        <input type="file" name="image" id="fImage" class="form-input" accept="image/*"
                               style="flex:1">
                        <span id="imagePreviewText" style="display:none; padding:8px; background:#f5f5f5; border-radius:4px; font-size:0.9rem;"></span>
                    </div>
                    <img id="imagePreview" style="display:none; width:80px; height:80px; object-fit:cover; border-radius:4px; margin-top:8px;" />
                    <small style="color:#aaa; display:block; margin-top:4px;">Upload a category image (JPG, PNG)</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="fDescription" class="form-input" 
                           placeholder="Enter category description..." style="resize:vertical; min-height:100px;"></textarea>
                    <small style="color:#aaa; display:block; margin-top:4px;">Brief description for this category</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Available Sizes</label>
                    <div id="sizesList" style="border:1px solid #ddd; border-radius:4px; padding:10px; background:#fafafa; margin-bottom:10px;">
                        <!-- Sizes will be dynamically added here -->
                    </div>
                    <button type="button" class="btn btn-ghost" style="font-size:0.9rem;" onclick="addSizeInput()">
                        + Add Size
                    </button>
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
let currentEditingId = 0;

function openModal() {
    currentEditingId = 0;
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('saveBtn').textContent = 'Save Category';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('imagePreviewText').style.display = 'none';
    clearSizesInput();
    document.getElementById('categoryModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function openEditModal(c) {
    currentEditingId = c.id;
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value   = c.id;
    document.getElementById('fName').value        = c.name || '';
    document.getElementById('fSlug').value        = c.slug || '';
    document.getElementById('fDescription').value = c.description || '';
    
    // Show image preview if exists
    if (c.image) {
        document.getElementById('imagePreviewText').textContent = 'Current: ' + c.image;
        document.getElementById('imagePreviewText').style.display = 'inline-block';
        document.getElementById('imagePreview').src = '../upload/categories/' + c.image;
        document.getElementById('imagePreview').style.display = 'block';
    } else {
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('imagePreviewText').style.display = 'none';
    }
    
    document.getElementById('saveBtn').textContent = 'Update Category';
    
    // Load existing sizes
    loadExistingSizes(c.id);
    
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

// Image preview
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('fImage');
    const imagePreview = document.getElementById('imagePreview');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                    document.getElementById('imagePreviewText').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Dynamic Sizes Management
function addSizeInput() {
    const sizesList = document.getElementById('sizesList');
    const sizeId = 'size-' + Date.now();
    
    const sizeDiv = document.createElement('div');
    sizeDiv.id = sizeId;
    sizeDiv.style.cssText = 'display:flex; gap:8px; margin-bottom:8px; align-items:center;';
    sizeDiv.innerHTML = `
        <input type="text" name="sizes[]" class="form-input" placeholder="e.g. XS, S, M, L, XL" style="flex:1; padding:6px 10px; font-size:0.9rem;">
        <button type="button" class="btn" style="padding:6px 10px; background:#ff6b6b; color:white; border:none; border-radius:4px; cursor:pointer; min-width:40px;" onclick="removeSizeInput('${sizeId}')">✕</button>
    `;
    
    sizesList.appendChild(sizeDiv);
}

function removeSizeInput(sizeId) {
    const el = document.getElementById(sizeId);
    if (el) el.remove();
}

function clearSizesInput() {
    const sizesList = document.getElementById('sizesList');
    sizesList.innerHTML = '';
}

function loadExistingSizes(categoryId) {
    clearSizesInput();
    
    if (categoryId <= 0) return; // No sizes for new category
    
    // Fetch existing sizes via AJAX
    fetch('../api/get-sizes.php?category_id=' + categoryId)
        .then(response => {
            if (!response.ok) throw new Error('Network error: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success && Array.isArray(data.sizes) && data.sizes.length > 0) {
                const sizesList = document.getElementById('sizesList');
                data.sizes.forEach(size => {
                    const sizeId = 'size-' + size.id;
                    const sizeDiv = document.createElement('div');
                    sizeDiv.id = sizeId;
                    sizeDiv.dataset.dbId = size.id;
                    sizeDiv.style.cssText = 'display:flex; gap:8px; margin-bottom:8px; align-items:center;';
                    sizeDiv.innerHTML = `
                        <input type="text" name="sizes[]" class="form-input" value="${escapeHtml(size.size_name)}" placeholder="e.g. XS, S, M, L, XL" style="flex:1; padding:6px 10px; font-size:0.9rem;">
                        <input type="hidden" name="size_ids[]" value="${size.id}">
                        <button type="button" class="btn" style="padding:6px 10px; background:#ff6b6b; color:white; border:none; border-radius:4px; cursor:pointer; min-width:40px;" onclick="removeSizeInput('${sizeId}')">✕</button>
                    `;
                    sizesList.appendChild(sizeDiv);
                });
            }
        })
        .catch(err => {
            console.error('Error loading sizes:', err);
            const sizesList = document.getElementById('sizesList');
            sizesList.innerHTML = '<p style="color:#f44336; padding:8px;">Error loading sizes. You can add new ones.</p>';
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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
