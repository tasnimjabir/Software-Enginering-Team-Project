
<?php

require_once $base_url.'components/config-component.php';
require_once 'Product.php';

class UserProducts extends Product {

    public function __construct() {
        parent::__construct();
    }

    public function display($limit = null, $renderModals = true) {
        $products = $this->getProducts();
        $id = 1; // sample
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
                    <div class="col-sm-6 col-lg-4">
                        <a href="product-view.php?id=<?php echo $product['id']; ?>" class="product-card-link">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="upload/products/<?php echo htmlspecialchars(!empty($product['main_image']) ? $product['main_image'] : 'image/logo.png'); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="product-img">
                                         <?php if (!empty($product['discount_price']) && (float)$product['discount_price'] < (float)$product['price']): ?>
                                         <span class="badge">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 4px;">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M8 12h8"></path>
                                            </svg>Sale
                                         </span>
                                         <?php endif; ?>
                                        
                                        <!-- View Button -->
                                        <button class="view-btn" type="button" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
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
                                        <button class="btn btn-sm add-to-cart-btn" type="button" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" data-product-price="<?php echo (!empty($product['discount_price']) && (float)$product['discount_price'] < (float)$product['price']) ? $product['discount_price'] : $product['price']; ?>">Add to Cart</button>
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
