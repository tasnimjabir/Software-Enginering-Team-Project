<?php
include 'header.php';

// ── Fetch categories for dropdown ────────────────────────────────────────────
$categories = $db->fetch('SELECT id, name FROM categories ORDER BY name ASC');

// ── Flash messages from redirects ────────────────────────────────────────────
$flash = '';
if (isset($_GET['msg'])) {
    $msgs = [
        'created' => ['success', '✓ Product created successfully!'],
        'updated' => ['success', '✓ Product updated successfully!'],
        'deleted' => ['success', '✓ Product deleted successfully!'],
        'error'   => ['error',   '✗ An error occurred. Please try again.'],
    ];
    $m = $_GET['msg'];
    if (isset($msgs[$m])) {
        [$type, $text] = $msgs[$m];
        $flash = "<div class='alert $type'>$text</div>";
    }
}

// ── Search / filter ──────────────────────────────────────────────────────────
$search   = trim($_GET['search']   ?? '');
$catFilter = intval($_GET['cat']   ?? 0);
$sort     = in_array($_GET['sort'] ?? '', ['name','price_asc','price_desc','newest']) ? $_GET['sort'] : 'newest';

$where  = [];
$params = [];

if ($search !== '') {
    $where[]  = '(p.name LIKE ? OR p.slug LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($catFilter > 0) {
    $where[]  = 'p.category_id = ?';
    $params[] = $catFilter;
}

$sortMap = [
    'name'       => 'p.name ASC',
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'newest'     => 'p.id DESC',
];
$orderBy = $sortMap[$sort];

$sql = "SELECT p.id, p.name, p.slug, p.price, p.main_image,
               c.name AS category, p.category_id, p.description, p.discount_price
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id"
     . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY $orderBy";

$products = $db->fetch($sql, $params);

// Fetch product gallery images
$productIds = array_column($products, 'id');
$imagesByProduct = [];
if (!empty($productIds)) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $images = $db->fetch("SELECT product_id, image FROM product_images WHERE product_id IN ($placeholders)", $productIds);
    foreach ($images as $img) {
        $imagesByProduct[$img['product_id']][] = $img['image'];
    }
}
foreach ($products as &$p) {
    $p['gallery'] = $imagesByProduct[$p['id']] ?? [];
}
unset($p);

// ── Analytics ────────────────────────────────────────────────────────────────
$totalProducts  = $db->fetchOne('SELECT COUNT(*) AS c FROM products')['c'] ?? 0;
$totalCategories = count($categories);
$avgPrice       = $db->fetchOne('SELECT AVG(price) AS a FROM products')['a'] ?? 0;
$discountedItems= $db->fetchOne('SELECT COUNT(*) AS c FROM products WHERE discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price')['c'] ?? 0;
?>
<body>
<div class="admin-wrapper">
    <div class="layout">
        <?php include 'sidebar.php'; ?>

        <main class="content">

            <!-- Header Row -->
            <div class="products-header">
                <div>
                    <h1 class="page-title">Products</h1>
                    <p class="page-sub">Manage your store catalogue</p>
                </div>
                <button class="btn btn-primary btn-icon" onclick="openModal()">
                    <span>＋</span> Add Product
                </button>
            </div>

            <!-- Analytics Strip -->
            <div class="analytics-grid" style="margin-bottom:24px;">
                <div class="analytics-card">
                    <div class="analytics-label">Total Products</div>
                    <div class="analytics-value"><?= number_format($totalProducts) ?></div>
                    <div class="analytics-icon">📦</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-label">Categories</div>
                    <div class="analytics-value"><?= $totalCategories ?></div>
                    <div class="analytics-icon">🗂️</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-label">Avg Price</div>
                    <div class="analytics-value" style="color:#66bb6a;">$<?= number_format($avgPrice, 2) ?></div>
                    <div class="analytics-icon">💲</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-label">On Sale</div>
                    <div class="analytics-value" style="color:#ff7043;"><?= $discountedItems ?></div>
                    <div class="analytics-icon">🏷️</div>
                </div>
            </div>

            <?= $flash ?>

            <!-- Filter Bar -->
            <form method="GET" class="filter-bar" id="filterForm">
                <div class="search-wrap">
                    <span class="search-icon">🔍</span>
                    <input type="text" name="search" placeholder="Search products…"
                           value="<?= htmlspecialchars($search) ?>" class="search-input"
                           onchange="this.form.submit()">
                </div>
                <select name="cat" class="filter-select" onchange="this.form.submit()">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $catFilter == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="newest"     <?= $sort==='newest'     ? 'selected':'' ?>>Newest First</option>
                    <option value="name"       <?= $sort==='name'       ? 'selected':'' ?>>Name A–Z</option>
                    <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected':'' ?>>Price ↑</option>
                    <option value="price_desc" <?= $sort==='price_desc' ? 'selected':'' ?>>Price ↓</option>
                </select>
                <?php if ($search || $catFilter): ?>
                    <a href="products.php" class="btn-clear">✕ Clear</a>
                <?php endif; ?>
            </form>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <?php
                            $img = !empty($p['main_image'])
                                ? '../' . ltrim($p['main_image'], '/')
                                : 'https://via.placeholder.com/300x200/2a2a2a/888?text=No+Image';
                        ?>
                        <div class="product-card" id="pcard-<?= $p['id'] ?>">
                            <div class="product-img-wrap">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                                     class="product-img" onerror="this.src='https://via.placeholder.com/300x200/2a2a2a/888?text=No+Image'">
                                <div class="product-overlay">
                                    <button class="overlay-btn" title="Edit"
                                        onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)">✏️ Edit</button>
                                    <button class="overlay-btn danger" title="Delete"
                                        onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')">🗑️ Delete</button>
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-category"><?= htmlspecialchars($p['category'] ?? 'Uncategorized') ?></div>
                                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="product-footer">
                                    <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                                    <?php if (!empty($p['discount_price']) && $p['discount_price'] < $p['price']): ?>
                                        <span class="stock-badge low">Sale</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div class="empty-text">No products found</div>
                        <button class="btn btn-primary" onclick="openModal()" style="margin-top:14px;">Add your first product</button>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>

<!-- ── Modal Overlay ─────────────────────────────────────────────────── -->
<div id="productModal" class="modal-overlay" onclick="closeModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Product</h2>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <form id="productForm" action="product-save.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="productId" value="">

            <div class="modal-body">
                <div class="form-grid">
                    <!-- LEFT -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="req">*</span></label>
                            <input type="text" name="name" id="fName" class="form-input" required
                                   placeholder="e.g. T-shirt" oninput="autoSlug(this.value)">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="fSlug" class="form-input"
                                   placeholder="auto-generated">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" id="fCategory" class="form-input">
                                <option value="">— Select Category —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label class="form-label">Price ($) <span class="req">*</span></label>
                                <input type="number" name="price" id="fPrice" class="form-input" required
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Discount Price ($)</label>
                                <input type="number" name="discount_price" id="fDiscount" class="form-input"
                                       step="0.01" min="0" placeholder="Leave blank for none">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="fDesc" class="form-input form-textarea"
                                      placeholder="Short product description…"></textarea>
                        </div>
                    </div>

                    <!-- RIGHT: image -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Product Image</label>
                            <div class="img-upload-area" onclick="document.getElementById('fImage').click()" id="uploadArea">
                                <div id="imgPreview" style="display:none; flex-wrap:wrap; gap:10px;"></div>
                                <div id="uploadPlaceholder">
                                    <div class="upload-icon">🖼️</div>
                                    <div class="upload-text">Click to upload images</div>
                                    <div class="upload-sub">PNG, JPG, WEBP up to 5MB</div>
                                </div>
                            </div>
                            <input type="file" name="images[]" id="fImage" accept="image/*" style="display:none" multiple
                                   onchange="previewImages(this)">
                            <input type="hidden" name="existing_image" id="fExistingImage" value="">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveBtn">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────────────────────── -->
<div id="deleteModal" class="modal-overlay modal-sm" onclick="closeDeleteOnOverlay(event)">
    <div class="modal-box modal-box-sm">
        <div class="delete-icon-big">🗑️</div>
        <h3 class="delete-title">Delete Product?</h3>
        <p class="delete-sub" id="deleteProductName"></p>
        <p class="delete-warn">This will permanently remove the product and cannot be undone.</p>
        <div class="delete-actions">
            <button class="btn btn-ghost" onclick="document.getElementById('deleteModal').classList.remove('open')">Cancel</button>
            <form id="deleteForm" action="product-delete.php" method="POST" style="display:inline;">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
// ── Modal helpers ─────────────────────────────────────────────────────────────
function openModal() {
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('fExistingImage').value = '';
    document.getElementById('imgPreview').innerHTML = '';
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('uploadPlaceholder').style.display = 'flex';
    document.getElementById('saveBtn').textContent = 'Save Product';
    document.getElementById('productModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function openEditModal(p) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value   = p.id;
    document.getElementById('fName').value        = p.name        || '';
    document.getElementById('fSlug').value        = p.slug        || '';
    document.getElementById('fPrice').value       = p.price       || '';
    document.getElementById('fDesc').value        = p.description || '';
    document.getElementById('fDiscount').value    = p.discount_price || '';
    document.getElementById('fExistingImage').value = p.main_image || '';
    document.getElementById('saveBtn').textContent = 'Update Product';

    // Category
    const sel = document.getElementById('fCategory');
    for (let o of sel.options) o.selected = (o.value == p.category_id);

    // Image preview
    const previewContainer = document.getElementById('imgPreview');
    previewContainer.innerHTML = '';
    
    let allImages = [];
    if (p.main_image) allImages.push(p.main_image);
    if (p.gallery) {
        p.gallery.forEach(g => {
            if (g !== p.main_image && !allImages.includes(g)) allImages.push(g);
        });
    }

    if (allImages.length > 0) {
        allImages.forEach(src => {
            const img = document.createElement('img');
            img.src = '../' + src.replace(/^\/+/, '');
            img.style.maxWidth = '100px';
            img.style.maxHeight = '100px';
            img.style.borderRadius = '6px';
            previewContainer.appendChild(img);
        });
        previewContainer.style.display = 'flex';
        document.getElementById('uploadPlaceholder').style.display = 'none';
    } else {
        document.getElementById('imgPreview').innerHTML = '';
        document.getElementById('imgPreview').style.display = 'none';
        document.getElementById('uploadPlaceholder').style.display = 'flex';
    }

    document.getElementById('productModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('productModal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeModalOnOverlay(e) {
    if (e.target === document.getElementById('productModal')) closeModal();
}

// ── Slug auto-gen ─────────────────────────────────────────────────────────────
function autoSlug(val) {
    if (document.getElementById('productId').value) return; // don't override on edit
    document.getElementById('fSlug').value = val.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
}

// ── Image preview ─────────────────────────────────────────────────────────────
function previewImages(input) {
    const previewContainer = document.getElementById('imgPreview');
    previewContainer.innerHTML = ''; // clear existing previews
    
    if (input.files && input.files.length > 0) {
        document.getElementById('uploadPlaceholder').style.display = 'none';
        previewContainer.style.display = 'flex';
        
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                img.style.borderRadius = '6px';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    } else {
        document.getElementById('uploadPlaceholder').style.display = 'flex';
        previewContainer.style.display = 'none';
    }
}

// ── Delete modal ──────────────────────────────────────────────────────────────
function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('deleteModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeDeleteOnOverlay(e) {
    if (e.target === document.getElementById('deleteModal')) {
        document.getElementById('deleteModal').classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Close modals on Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        document.getElementById('deleteModal').classList.remove('open');
        document.body.style.overflow = '';
    }
});

// Auto-hide flash message
const flash = document.querySelector('.alert');
if (flash) setTimeout(() => { flash.style.opacity = '0'; flash.style.transform = 'translateY(-8px)'; setTimeout(() => flash.remove(), 400); }, 4000);
</script>
</body>
</html>
