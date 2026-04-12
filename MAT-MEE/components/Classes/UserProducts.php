
<?php

require_once $base_url.'components/config-component.php';
require_once 'Product.php';

class UserProducts extends Product {

    public function __construct() {
        parent::__construct();
    }

    public function display($limit = null, $renderModals = true) {
        $products = $this->getProducts();
        ob_start();

        $productsToDisplay = $products;
        if ($limit !== null && $limit > 0) {
            $productsToDisplay = array_slice($products, 0, $limit);
        }
        if (empty($productsToDisplay)):
            ?>
            <div class="alert alert-info text-center">
                <p>No products available at the moment.</p>
            </div>
            <?php
        else:
            ?>
            <div class="row g-2">
                <?php foreach ($productsToDisplay as $product): ?>
                    <?php
                    // ── Build full images array from DB ────────────────
                    $allImages = [];
                    if (!empty($product['main_image'])) {
                        $allImages[] = 'upload/products/' . $product['main_image'];
                    }
                    if (!empty($product['extra_images'])) {
                        foreach (explode('|', $product['extra_images']) as $img) {
                            $img = trim($img);
                            if ($img !== '' && $img !== $product['main_image']) {
                                $allImages[] = 'upload/products/' . $img;
                            }
                        }
                    }
                    if (empty($allImages)) {
                        $allImages[] = 'image/logo.png';
                    }
                    $imagesJson  = htmlspecialchars(json_encode($allImages), ENT_QUOTES, 'UTF-8');
                    $mainImg     = $allImages[0];
                    $hasMultiple = count($allImages) > 1;
                    ?>
                    <div class="col-6 col-sm-6 col-md-4 col-xxl-3">
                        <a href="product-view.php?slug=<?php echo urlencode($product['slug']); ?>" class="product-card-link">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($mainImg); ?>"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="product-img"
                                         data-images="<?php echo $imagesJson; ?>"
                                         data-image-index="0">

                                    <?php if (!empty($product['discount_price']) && (float)$product['discount_price'] < (float)$product['price']): ?>
                                    <span class="badge">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline;margin-right:4px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M8 12h8"></path>
                                        </svg>Sale
                                    </span>
                                    <?php endif; ?>

                                    <?php if ($hasMultiple): ?>
                                    <!-- Image Navigation Arrows -->
                                    <button class="img-nav-btn img-prev" aria-label="Previous image" type="button" onclick="event.preventDefault();">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="15 18 9 12 15 6"></polyline>
                                        </svg>
                                    </button>
                                    <button class="img-nav-btn img-next" aria-label="Next image" type="button" onclick="event.preventDefault();">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="9 18 15 12 9 6"></polyline>
                                        </svg>
                                    </button>
                                    <!-- Dot indicators -->
                                    <div class="img-dots">
                                        <?php for ($i = 0; $i < count($allImages); $i++): ?>
                                            <span class="img-dot<?php echo $i === 0 ? ' active' : ''; ?>"></span>
                                        <?php endfor; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- View Button -->
                                    <button class="view-btn" type="button"
                                            data-product-id="<?php echo $product['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            onclick="event.preventDefault();">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        View
                                    </button>
                                </div>
                                <div class="product-info">
                                    <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="product-category">
                                        <?php echo $product['category'] ?: 'Uncategorized'; ?>
                                    </p>
                                    <div class="product-footer">
                                        <div class="price-section">
                                            <?php if (!empty($product['discount_price']) && (float)$product['discount_price'] < (float)$product['price']): ?>
                                                <span class="price"><?php echo number_format($product['discount_price'], 0); ?> Tk</span>
                                                <span class="price-original"><?php echo number_format($product['price'], 0); ?> Tk</span>
                                            <?php else: ?>
                                                <span class="price"><?php echo number_format($product['price'], 0); ?> Tk</span>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-sm add-to-cart-btn" type="button"
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                data-product-price="<?php echo (!empty($product['discount_price']) && (float)$product['discount_price'] < (float)$product['price']) ? $product['discount_price'] : $product['price']; ?>"
                                                onclick="event.preventDefault();">Add to Cart</button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        endif;

        $content = ob_get_clean();
        
        return $content;
    }
}

?>
