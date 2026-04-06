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
$search    = trim($_GET['search'] ?? '');
$catFilter = intval($_GET['cat']  ?? 0);
$sort      = in_array($_GET['sort'] ?? '', ['name','price_asc','price_desc','newest']) ? $_GET['sort'] : 'newest';

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
$productIds      = array_column($products, 'id');
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
$totalProducts   = $db->fetchOne('SELECT COUNT(*) AS c FROM products')['c']   ?? 0;
$totalCategories = count($categories);
$avgPrice        = $db->fetchOne('SELECT AVG(price) AS a FROM products')['a'] ?? 0;
$discountedItems = $db->fetchOne('SELECT COUNT(*) AS c FROM products WHERE discount_price IS NOT NULL AND discount_price > 0 AND discount_price < price')['c'] ?? 0;
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
                                ? '../upload/products/' . ltrim($p['main_image'], '/')
                                : 'https://via.placeholder.com/300x200/2a2a2a/888?text=No+Image';
                        ?>
                        <div class="product-card" id="pcard-<?= $p['id'] ?>">
                            <div class="product-img-wrap">
                                <img src="<?= htmlspecialchars($img)?>" alt="<?= htmlspecialchars($p['name']) ?>"
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

<!-- ══════════════════════════════════════════════════════════════
     PRODUCT MODAL
══════════════════════════════════════════════════════════════ -->
<div id="productModal" class="modal-overlay" onclick="closeModalOnOverlay(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add Product</h2>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>

        <form id="productForm" action="product-save.php" method="POST" enctype="multipart/form-data" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="id"             id="productId"      value="">
            <input type="hidden" name="existing_image" id="fExistingImage" value="">
            <input type="hidden" name="remove_main"    id="fRemoveMain"    value="0">

            <div class="modal-body">
                <div class="form-grid">

                    <!-- ── LEFT: fields ── -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Product Name <span class="req">*</span></label>
                            <input type="text" name="name" id="fName" class="form-input" required
                                   placeholder="e.g. T-shirt" oninput="autoSlug(this.value)">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="fSlug" class="form-input" placeholder="auto-generated">
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
                            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
                                <label class="form-label" style="display:flex;align-items:center;gap:8px;margin:0;cursor:pointer;">
                                    <input type="checkbox" name="enable_discount" id="fEnableDiscount"
                                           style="width:16px;height:16px;accent-color:var(--red);cursor:pointer;"
                                           onchange="toggleDiscountField()">
                                    <span>Enable Sale Price</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group" id="discountFieldGroup" style="display:none;">
                            <label class="form-label">Sale Price ($)</label>
                            <input type="number" name="discount_price" id="fDiscount" class="form-input"
                                   step="0.01" min="0" placeholder="Enter sale price">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="fDesc" class="form-input form-textarea"
                                      placeholder="Short product description…"></textarea>
                        </div>
                    </div>

                    <!-- ── RIGHT: image upload ── -->
                    <div class="form-col">

                        <!-- ─ MAIN IMAGE ─ -->
                        <div class="form-group">
                            <label class="form-label upload-section-label">
                                <span class="upload-label-icon">⭐</span>
                                Main Image
                                <span class="upload-label-hint">shown on product card</span>
                            </label>

                            <!-- Drop zone -->
                            <div class="img-upload-zone main-zone"
                                 id="mainZone"
                                 onclick="document.getElementById('fMainImage').click()"
                                 ondragover="zoneDragOver(event,this)"
                                 ondragleave="zoneDragLeave(this)"
                                 ondrop="zoneDrop(event,'main')">

                                <!-- Preview -->
                                <div class="zone-preview" id="mainPreview" style="display:none;">
                                    <img id="mainPreviewImg" src="" alt="Main">
                                    <div class="zone-preview-actions">
                                        <button type="button" class="zpbtn zpbtn-change"
                                                onclick="event.stopPropagation();document.getElementById('fMainImage').click()">
                                            ✎ Change
                                        </button>
                                        <button type="button" class="zpbtn zpbtn-remove"
                                                onclick="event.stopPropagation();removeMainImage()">
                                            ✕ Remove
                                        </button>
                                    </div>
                                </div>

                                <!-- Placeholder -->
                                <div class="zone-placeholder" id="mainPlaceholder">
                                    <div class="zone-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                                            <circle cx="8.5" cy="8.5" r="1.5"/>
                                            <polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </div>
                                    <div class="zone-text">Click or drag image here</div>
                                    <div class="zone-sub">PNG · JPG · WEBP — max 5 MB</div>
                                </div>
                            </div>

                            <!-- Hidden file input — name="images[]" keeps backend happy, first file = main -->
                            <input type="file" name="images[]" id="fMainImage"
                                   accept="image/*" style="display:none"
                                   onchange="onMainImageChange(this)">
                        </div>

                        <!-- ─ GALLERY IMAGES ─ -->
                        <div class="form-group" style="margin-top:4px;">
                            <label class="form-label upload-section-label">
                                <span class="upload-label-icon">🖼</span>
                                Gallery Images
                                <span class="upload-label-hint">additional photos</span>
                            </label>

                            <!-- Gallery drop zone -->
                            <div class="img-upload-zone gallery-zone"
                                 id="galleryZone"
                                 onclick="document.getElementById('fGalleryImages').click()"
                                 ondragover="zoneDragOver(event,this)"
                                 ondragleave="zoneDragLeave(this)"
                                 ondrop="zoneDrop(event,'gallery')">

                                <!-- Thumbnails row -->
                                <div class="gallery-thumbs" id="galleryThumbs"></div>

                                <!-- Placeholder (hidden when thumbs exist) -->
                                <div class="zone-placeholder gallery-placeholder" id="galleryPlaceholder">
                                    <div class="zone-icon small">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                                            <line x1="12" y1="8" x2="12" y2="16"/>
                                            <line x1="8"  y1="12" x2="16" y2="12"/>
                                        </svg>
                                    </div>
                                    <div class="zone-text">Click or drag to add photos</div>
                                    <div class="zone-sub">Multiple files allowed</div>
                                </div>
                            </div>

                            <!-- Hidden file input — name="images[]" appends more files -->
                            <input type="file" name="images[]" id="fGalleryImages"
                                   accept="image/*" multiple style="display:none"
                                   onchange="onGalleryImagesChange(this)">
                        </div>

                    </div><!-- /form-col right -->
                </div><!-- /form-grid -->
            </div><!-- /modal-body -->

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

<!-- ══════════════════════════════════════════════════════════════
     UPLOAD STYLES (scoped inside modal)
══════════════════════════════════════════════════════════════ -->
<style>
/* Upload section label */
.upload-section-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #ccc;
    margin-bottom: 8px;
}
.upload-label-icon { font-size: 14px; }
.upload-label-hint {
    margin-left: auto;
    font-size: 11px;
    font-weight: 400;
    color: #666;
    letter-spacing: 0.3px;
}

/* ── Drop zone base ── */
.img-upload-zone {
    border: 2px dashed #3a3a3a;
    background: #222;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    position: relative;
    overflow: hidden;
}
.img-upload-zone:hover,
.img-upload-zone.drag-over {
    border-color: #c62828;
    background: #2a1515;
}

/* ── Main zone ── */
.main-zone {
    height: 190px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── Gallery zone ── */
.gallery-zone {
    min-height: 110px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

/* ── Placeholders ── */
.zone-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    pointer-events: none;
    padding: 10px;
}
.zone-icon { color: #555; transition: color 0.2s; }
.img-upload-zone:hover .zone-icon,
.img-upload-zone.drag-over .zone-icon { color: #c62828; }
.zone-text { font-size: 13px; color: #888; font-weight: 500; }
.zone-sub  { font-size: 11px; color: #555; }
.zone-icon.small { }

/* ── Main preview ── */
.zone-preview {
    width: 100%; height: 100%;
    position: relative;
}
.zone-preview img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.zone-preview-actions {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    opacity: 0;
    transition: opacity 0.2s;
}
.zone-preview:hover .zone-preview-actions { opacity: 1; }

.zpbtn {
    padding: 6px 14px;
    border: none;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
    border-radius: 4px;
}
.zpbtn:hover { transform: translateY(-1px); }
.zpbtn-change { background: #c62828; color: #fff; }
.zpbtn-change:hover { background: #b71c1c; }
.zpbtn-remove { background: #333; color: #ccc; border: 1px solid #555; }
.zpbtn-remove:hover { background: #444; color: #fff; }

/* ── Gallery thumbnails ── */
.gallery-thumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    width: 100%;
    justify-content: flex-start;
}
.gallery-thumbs:empty { display: none; }

.gthumb {
    position: relative;
    width: 68px;
    height: 68px;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
    border: 2px solid #3a3a3a;
    transition: border-color 0.2s, transform 0.2s;
    background: #111;
}
.gthumb:hover { border-color: #c62828; transform: scale(1.04); }

.gthumb img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.gthumb-remove {
    position: absolute;
    top: 2px; right: 2px;
    background: rgba(198,40,40,0.9);
    border: none;
    color: #fff;
    width: 20px; height: 20px;
    font-size: 11px;
    border-radius: 3px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
    line-height: 1;
    padding: 0;
}
.gthumb:hover .gthumb-remove { opacity: 1; }

/* Add-more tile inside gallery */
.gthumb-add {
    border: 2px dashed #3a3a3a;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #555;
    font-size: 22px;
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s, background 0.2s;
    background: transparent;
}
.gthumb-add:hover {
    border-color: #c62828;
    color: #c62828;
    background: #2a1515;
}

/* Gallery placeholder: hide when thumbs exist */
.gallery-has-items .gallery-placeholder { display: none; }
.gallery-has-items { align-items: flex-start; justify-content: flex-start; }
</style>

<!-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════════════════════════════ -->
<script>
/* ── Internal state ───────────────────────────────────────────────────────── */
// We keep DataTransfer objects so we can pass real File lists to the hidden inputs.
// mainDT  → feeds #fMainImage (name="images[]", first = main image for backend)
// galleryDT → feeds #fGalleryImages (name="images[]")
let mainDT    = new DataTransfer();
let galleryDT = new DataTransfer();

/* ═══════════════════════════
   MAIN IMAGE
═══════════════════════════ */
function onMainImageChange(input) {
    if (!input.files.length) return;
    const file = input.files[0]; // only first file matters for main
    mainDT = new DataTransfer();
    mainDT.items.add(file);
    renderMainPreview(file, null);
}

function renderMainPreview(file, url) {
    const preview     = document.getElementById('mainPreview');
    const placeholder = document.getElementById('mainPlaceholder');
    const img         = document.getElementById('mainPreviewImg');

    if (file) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; };
        reader.readAsDataURL(file);
    } else if (url) {
        img.src = url;
    }
    preview.style.display     = 'block';
    placeholder.style.display = 'none';
}

function removeMainImage() {
    mainDT = new DataTransfer();
    document.getElementById('fMainImage').files      = mainDT.files;
    document.getElementById('fRemoveMain').value     = '1';   // tell backend to delete
    document.getElementById('fExistingImage').value  = '';
    document.getElementById('mainPreview').style.display     = 'none';
    document.getElementById('mainPlaceholder').style.display = 'flex';
    document.getElementById('mainPreviewImg').src            = '';
}

/* ═══════════════════════════
   GALLERY IMAGES
═══════════════════════════ */
// galleryItems: [{src, filename, file, isExisting}]
// filename = bare filename used by DB/filesystem (for removal POST)
let galleryItems = [];
let removedGalleryFilenames = []; // filenames of existing images the user removed

function onGalleryImagesChange(input) {
    Array.from(input.files).forEach(file => {
        galleryDT.items.add(file);
        galleryItems.push({ src: null, filename: null, file, isExisting: false });
    });
    document.getElementById('fGalleryImages').files = galleryDT.files;
    renderGalleryThumbs();
}

function removeGalleryItem(idx) {
    const item = galleryItems[idx];
    if (item.isExisting) {
        // Track filename so we can POST it for deletion
        if (item.filename) removedGalleryFilenames.push(item.filename);
    } else {
        // Rebuild DataTransfer without this new file
        const newDT = new DataTransfer();
        galleryItems.forEach((g, i) => {
            if (i !== idx && !g.isExisting && g.file) newDT.items.add(g.file);
        });
        galleryDT = newDT;
        document.getElementById('fGalleryImages').files = galleryDT.files;
    }
    galleryItems.splice(idx, 1);
    renderGalleryThumbs();
}

function renderGalleryThumbs() {
    const container = document.getElementById('galleryThumbs');
    const zone      = document.getElementById('galleryZone');
    container.innerHTML = '';

    if (galleryItems.length > 0) {
        zone.classList.add('gallery-has-items');
    } else {
        zone.classList.remove('gallery-has-items');
    }

    galleryItems.forEach((item, idx) => {
        const wrap = document.createElement('div');
        wrap.className = 'gthumb';

        const img = document.createElement('img');
        if (item.src) {
            img.src = item.src;
        } else if (item.file) {
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; };
            reader.readAsDataURL(item.file);
        }

        const removeBtn = document.createElement('button');
        removeBtn.type      = 'button';
        removeBtn.className = 'gthumb-remove';
        removeBtn.innerHTML = '✕';
        removeBtn.title     = 'Remove';
        removeBtn.onclick   = (e) => { e.stopPropagation(); removeGalleryItem(idx); };

        wrap.appendChild(img);
        wrap.appendChild(removeBtn);
        container.appendChild(wrap);
    });

    // "Add more" tile
    if (galleryItems.length > 0) {
        const addTile = document.createElement('div');
        addTile.className = 'gthumb gthumb-add';
        addTile.innerHTML = '+';
        addTile.title     = 'Add more images';
        addTile.onclick   = (e) => {
            e.stopPropagation();
            document.getElementById('fGalleryImages').click();
        };
        container.appendChild(addTile);
    }
}

/* ═══════════════════════════
   DRAG & DROP
═══════════════════════════ */
function zoneDragOver(e, el) {
    e.preventDefault();
    el.classList.add('drag-over');
}
function zoneDragLeave(el) {
    el.classList.remove('drag-over');
}
function zoneDrop(e, target) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    if (!files.length) return;

    if (target === 'main') {
        mainDT = new DataTransfer();
        mainDT.items.add(files[0]);
        document.getElementById('fMainImage').files = mainDT.files;
        renderMainPreview(files[0], null);
    } else {
        files.forEach(file => {
            galleryDT.items.add(file);
            galleryItems.push({ src: null, file, isExisting: false });
        });
        document.getElementById('fGalleryImages').files = galleryDT.files;
        renderGalleryThumbs();
    }
}

/* ═══════════════════════════
   RESET upload UI
═══════════════════════════ */
function resetUploadUI() {
    mainDT    = new DataTransfer();
    galleryDT = new DataTransfer();
    galleryItems             = [];
    removedGalleryFilenames  = [];

    document.getElementById('fMainImage').files     = mainDT.files;
    document.getElementById('fGalleryImages').files = galleryDT.files;
    document.getElementById('fExistingImage').value = '';
    document.getElementById('fRemoveMain').value    = '0';

    document.getElementById('mainPreview').style.display     = 'none';
    document.getElementById('mainPlaceholder').style.display = 'flex';
    document.getElementById('mainPreviewImg').src            = '';

    document.getElementById('galleryThumbs').innerHTML = '';
    document.getElementById('galleryZone').classList.remove('gallery-has-items');
}

/* ═══════════════════════════
   LOAD existing images on edit
═══════════════════════════ */
function loadExistingImages(mainImage, gallery) {
    resetUploadUI();

    // Main image
    if (mainImage) {
        document.getElementById('fExistingImage').value = mainImage;
        renderMainPreview(null, '../upload/products/' + mainImage.replace(/^\/+/, ''));
    }

    // Gallery (exclude main to avoid duplicates)
    gallery.forEach(src => {
        if (src === mainImage) return;
        const filename = src.replace(/^\/+/, '');
        galleryItems.push({
            src:        '../upload/products/' + filename,
            filename:   filename,   // bare name for DELETE POST
            file:       null,
            isExisting: true
        });
    });
    renderGalleryThumbs();
}

/* ═══════════════════════════
   MODAL HELPERS
═══════════════════════════ */
function openModal() {
    document.getElementById('modalTitle').textContent = 'Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('fEnableDiscount').checked = false;
    document.getElementById('discountFieldGroup').style.display = 'none';
    document.getElementById('saveBtn').textContent = 'Save Product';
    resetUploadUI();
    document.getElementById('productModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function openEditModal(p) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value    = p.id;
    document.getElementById('fName').value         = p.name        || '';
    document.getElementById('fSlug').value         = p.slug        || '';
    document.getElementById('fPrice').value        = p.price       || '';
    document.getElementById('fDesc').value         = p.description || '';
    document.getElementById('saveBtn').textContent = 'Update Product';

    const hasDiscount = p.discount_price && p.discount_price > 0;
    document.getElementById('fEnableDiscount').checked = hasDiscount;
    document.getElementById('fDiscount').value         = hasDiscount ? p.discount_price : '';
    document.getElementById('discountFieldGroup').style.display = hasDiscount ? 'block' : 'none';

    const sel = document.getElementById('fCategory');
    for (let o of sel.options) o.selected = (o.value == p.category_id);

    loadExistingImages(p.main_image || '', p.gallery || []);

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

/* ═══════════════════════════
   MISC
═══════════════════════════ */
function autoSlug(val) {
    if (document.getElementById('productId').value) return;
    document.getElementById('fSlug').value = val.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
}

function toggleDiscountField() {
    const on = document.getElementById('fEnableDiscount').checked;
    document.getElementById('discountFieldGroup').style.display = on ? 'block' : 'none';
    if (!on) document.getElementById('fDiscount').value = '';
}

function handleFormSubmit(e) {
    if (!document.getElementById('fEnableDiscount').checked)
        document.getElementById('fDiscount').value = '';

    // Inject remove_gallery[] hidden inputs for each removed existing gallery image
    const form = document.getElementById('productForm');
    // Remove any previously injected ones to avoid duplicates on re-submit
    form.querySelectorAll('.injected-remove-gallery').forEach(el => el.remove());
    removedGalleryFilenames.forEach(filename => {
        const inp = document.createElement('input');
        inp.type      = 'hidden';
        inp.name      = 'remove_gallery[]';
        inp.value     = filename;
        inp.className = 'injected-remove-gallery';
        form.appendChild(inp);
    });
}

/* Delete modal */
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

/* Escape key */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal();
        document.getElementById('deleteModal').classList.remove('open');
        document.body.style.overflow = '';
    }
});

/* Auto-hide flash */
const flash = document.querySelector('.alert');
if (flash) setTimeout(() => {
    flash.style.transition = 'opacity .4s, transform .4s';
    flash.style.opacity    = '0';
    flash.style.transform  = 'translateY(-8px)';
    setTimeout(() => flash.remove(), 400);
}, 4000);
</script>
</body>
</html>