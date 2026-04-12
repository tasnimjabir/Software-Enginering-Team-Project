<?php 
require_once 'components/config-page.php'; 
require_once 'components/connection.php';
require_once 'components/Classes/ProductBuilder.php';

$db = DatabaseConnection::getInstance();

/* ── Metadata: shop hero image ───────────────────────────────── */
$metaRow  = $db->fetch("SELECT value FROM metadata WHERE name = 'shop_img' LIMIT 1");
$shopImg  = !empty($metaRow[0]['value'])
            ? 'upload/categories/' . htmlspecialchars($metaRow[0]['value'])
            : '';

/* ── Categories with product counts ─────────────────────────── */
$categories = $db->fetch(
    "SELECT c.id, c.name, c.slug, c.image, c.description,
            COUNT(p.id) AS product_count
     FROM   categories c
     LEFT JOIN products p ON c.id = p.category_id
     GROUP  BY c.id, c.name, c.slug, c.image, c.description"
);

/* ── Current filters from URL ───────────────────────────────── */
$currentCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$searchQuery     = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$sortBy          = isset($_GET['sort'])     ? $_GET['sort']           : 'recent';
$sqlorder = "created_at DESC"; // default order
switch ($sortBy) {
    case 'price_asc':
        $sqlorder = "discount_price ASC";
        break;
    case 'price_desc':
        $sqlorder = "discount_price DESC";
        break;
}
// Whitelist sort values
if (!in_array($sortBy, ['recent', 'price_asc', 'price_desc'])) $sortBy = 'recent';

/* ── Product count for results chip ─────────────────────────── */
if (!empty($currentCategory)) {
    $safeCat  = $currentCategory;
    $countRes = $db->fetch(
        "SELECT COUNT(p.id) AS total
         FROM   products p
         JOIN   categories c ON p.category_id = c.id
         WHERE  c.slug = '{$safeCat}'"
    );
} else {
    $countRes = $db->fetch("SELECT COUNT(*) AS total FROM products");
}
$totalProducts = $countRes[0]['total'] ?? 0;

/* ── Active category data (for hero) ───────────────────────── */
$activeCat = null;
if (!empty($currentCategory)) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $currentCategory) { $activeCat = $cat; break; }
    }
}

/* ── Hero background: use active-cat image or shop_img meta ─── */
$heroBg = $shopImg;
if ($activeCat && !empty($activeCat['image'])) {
    $heroBg = 'upload/categories/' . htmlspecialchars($activeCat['image']);
}
?>

<!-- ═══════════════════════════════════════════════════════════
     LINK STYLES
════════════════════════════════════════════════════════════ -->
<link rel="stylesheet" href="asset/css/shop.css">

<!-- ═══════════════════════════════════════════════════════════
     HERO HEADER
════════════════════════════════════════════════════════════ -->
<div class="shop-hero">

    <!-- Background image -->
    <?php if ($heroBg): ?>
    <div class="shop-hero__bg" style="background-image:url('<?= $heroBg ?>')"></div>
    <?php endif; ?>

    <!-- Text block -->
    <div class="shop-hero__inner">

        <!-- Breadcrumb -->
        <nav class="shop-breadcrumb">
            <a href="index.php">Home</a>
            <span class="sep">›</span>
            <?php if (!empty($currentCategory) && $activeCat): ?>
                <a href="shop.php">Shop</a>
                <span class="sep">›</span>
                <span class="current"><?= htmlspecialchars($activeCat['name']) ?></span>
            <?php else: ?>
                <span class="current">Shop</span>
            <?php endif; ?>
        </nav>

        <!-- Title -->
        <h1 class="shop-hero__title">
            <?php if (!empty($currentCategory) && $activeCat): ?>
                <?= htmlspecialchars($activeCat['name']) ?>
            <?php else: ?>
                Our&nbsp;<em>Collection</em>
            <?php endif; ?>
        </h1>

        <!-- Sub-text: category description or default -->
        <p class="shop-hero__sub">
            <?php if ($activeCat && !empty($activeCat['description'])): ?>
                <?= htmlspecialchars(mb_strimwidth($activeCat['description'], 0, 120, '…')) ?>
            <?php elseif (!empty($searchQuery)): ?>
                Showing results for: "<?= htmlspecialchars($searchQuery) ?>"
            <?php else: ?>
                Handpicked styles crafted for every occasion
            <?php endif; ?>
        </p>

    </div><!-- /.shop-hero__inner -->

    <!-- Right panel: category thumbnails (desktop only) -->
    <?php if (!empty($categories)): ?>
    <div class="shop-hero__cats">
        <?php
        $maxHeroCats = 5;
        $shown = 0;
        foreach ($categories as $cat):
            if ($cat['product_count'] < 1) continue;
            if ($shown >= $maxHeroCats) break;
            $shown++;
            $catBg = !empty($cat['image'])
                     ? 'upload/categories/' . htmlspecialchars($cat['image'])
                     : $shopImg;
            $isActive = ($cat['slug'] === $currentCategory) ? 'active' : '';
        ?>
        <a href="shop.php?category=<?= urlencode($cat['slug']) ?>"
           class="hero-cat-item <?= $isActive ?>">
            <?php if ($catBg): ?>
            <span class="hero-cat-item__bg"
                  style="background-image:url('<?= $catBg ?>')"></span>
            <?php endif; ?>
            <span class="hero-cat-item__name"><?= htmlspecialchars($cat['name']) ?></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div><!-- /.shop-hero -->


<!-- ═══════════════════════════════════════════════════════════
     SHOP BODY
════════════════════════════════════════════════════════════ -->
<section class="shop-section" id="shop">
    <div class="shop-container">
        <div class="shop-layout">

            <!-- ══════════════ SIDEBAR ══════════════ -->
            <aside class="sidebar-col">
                <div class="categories-sidebar">

                    <!-- Header -->
                    <div class="sidebar-top d-none d-md-block">
                        <div class="sidebar-top__label">Browse by</div>
                        <div class="sidebar-top__title">CATE<span>GORY</span></div>
                    </div>

                    <!-- All Products link -->
                    <a href="shop.php"
                       class="cat-all <?= empty($currentCategory) ? 'active' : '' ?>">
                        <span class="cat-all__name">All Products</span>
                        <span class="cat-all__count"><?= $totalProducts ?></span>
                    </a>

                    <!-- Scrollable category list -->
                    <div class="sidebar-cats-scroll">
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['product_count'] < 1) continue; ?>
                            <?php
                                $catBg   = !empty($cat['image'])
                                           ? 'upload/categories/' . htmlspecialchars($cat['image'])
                                           : $shopImg;
                                $isActive = ($cat['slug'] === $currentCategory) ? 'active' : '';
                                $desc     = !empty($cat['description'])
                                            ? mb_strimwidth($cat['description'], 0, 60, '…')
                                            : '';
                            ?>
                            <a href="shop.php?category=<?= urlencode($cat['slug']) ?>"
                               class="cat-card <?= $isActive ?>">
                                <?php if ($catBg): ?>
                                <span class="cat-card__bg"
                                      style="background-image:url('<?= $catBg ?>')"></span>
                                <?php endif; ?>
                                <span class="cat-card__body">
                                    <span class="cat-card__name"><?= htmlspecialchars($cat['name']) ?></span>
                                    <?php if ($desc): ?>
                                    <span class="cat-card__desc"><?= htmlspecialchars($desc) ?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="cat-card__count"><?= $cat['product_count'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div><!-- /.sidebar-cats-scroll -->

                    <!-- Promo strip -->
                    <div class="sidebar-promo">
                        <div class="sidebar-promo__tag">✦ Offer</div>
                        <div class="sidebar-promo__text">
                            Free shipping on orders above
                            <span class="sidebar-promo__amount">৳999</span>
                        </div>
                    </div>

                </div><!-- /.categories-sidebar -->
            </aside><!-- /sidebar-col -->

            <!-- ══════════════ PRODUCTS ══════════════ -->
            <main class="main-col">

                <!-- Sort / title bar -->
                <div class="sort-bar">
                    <div class="sort-bar__left">
                        <span class="sort-bar__heading">
                            <?php if (!empty($currentCategory) && $activeCat): ?>
                                <?= htmlspecialchars(strtoupper($activeCat['name'])) ?>
                            <?php elseif (!empty($searchQuery)): ?>
                                RESULTS
                            <?php else: ?>
                                ALL ITEMS
                            <?php endif; ?>
                        </span>
                        <span class="results-chip"><?= $totalProducts ?> item<?= $totalProducts != 1 ? 's' : '' ?></span>
                    </div>

                    <!-- Sort buttons -->
                    <div class="sort-bar__right">
                        <span class="sort-label">Sort:</span>

                        <?php
                        $baseUrl = 'shop.php?';
                        if (!empty($currentCategory))
                            $baseUrl .= 'category=' . urlencode($currentCategory) . '&';
                        if (!empty($searchQuery))
                            $baseUrl .= 'search=' . urlencode($searchQuery) . '&';
                        ?>

                        <a href="<?= $baseUrl ?>sort=recent"
                           class="sort-btn <?= $sortBy === 'recent' ? 'active' : '' ?>">
                            Recent
                        </a>
                        <a href="<?= $baseUrl ?>sort=price_asc"
                           class="sort-btn <?= $sortBy === 'price_asc' ? 'active' : '' ?>">
                            Price ↑
                        </a>
                        <a href="<?= $baseUrl ?>sort=price_desc"
                           class="sort-btn <?= $sortBy === 'price_desc' ? 'active' : '' ?>">
                            Price ↓
                        </a>
                    </div>
                </div><!-- /.sort-bar -->

                <!-- Products grid with AJAX container -->
                <div id="productsContainer">
                    <?php
                        $productBuilder = new ProductBuilder('user');

                        if (!empty($currentCategory))
                            $productBuilder->setCategoryBySlug($currentCategory);

                        if (!empty($searchQuery))
                            $productBuilder->setSearch($searchQuery);

                        // Pass sort to ProductBuilder — add a setSort() method
                        // in your ProductBuilder class if not already present.
                        if (method_exists($productBuilder, 'setOrder'))
                            $productBuilder->setOrder($sqlorder);

                        $productBuilder->fetch()->render();
                    ?>
                </div>

            </main><!-- /main-col -->

        </div><!-- /.shop-layout -->
    </div><!-- /.shop-container -->
</section>



    <!-- Product Modals & Styles -->
    <link rel="stylesheet" href="asset/css/productcard.css">


    <!-- Product Image Modal -->
    <div id="productImageModal" class="product-modal">
        <div class="modal-content">
            <button class="modal-close" type="button" aria-label="Close modal">&times;</button>
            <div class="modal-image-container">
                <img id="modalProductImage" src="" alt="Product image">
            </div>
            <div class="modal-info">
                <h3 id="modalProductName"></h3>
            </div>
        </div>
    </div>

    <!-- Add to Cart Modal -->
    <div id="addToCartModal" class="product-modal">
        <div class="modal-content cart-modal-content">
            <button class="modal-close" type="button" aria-label="Close modal">&times;</button>
            <div class="cart-modal-body">
                <div class="cart-modal-image">
                    <img id="cartModalImage" src="" alt="Product image">
                </div>
                <div class="cart-modal-info">
                    <h3 id="cartModalName"></h3>
                    <p id="cartModalPrice" class="cart-modal-price"></p>
                    <form id="addToCartForm">
                        <div class="form-group">
                            <label for="productSize">Size:</label>
                            <select id="productSize" name="size" class="form-control" required>
                                <option value="">-- Select Size --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="productQuantity">Quantity:</label>
                            <div class="quantity-selector">
                                <button type="button" class="qty-btn qty-minus">−</button>
                                <input type="number" id="productQuantity" name="quantity" value="1" min="1" max="100" class="form-control qty-input">
                                <button type="button" class="qty-btn qty-plus">+</button>
                            </div>
                        </div>
                        <button type="submit" class="btn cart-submit-btn">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Management Script -->
    <script src="asset/js/shop.js?v=2"></script>

<?php require_once 'components/page_close.php'; ?>