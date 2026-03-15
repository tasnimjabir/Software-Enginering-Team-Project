<?php

require_once 'components/config-component.php';
require_once 'Product.php';

class UserProducts extends Product {

    public function __construct() {
        parent::__construct();
    }

    public function display($limit = null) {
        $this->products = $this->getProducts();
        $id = 1; // sample
        ob_start();

        $productsToDisplay = $this->products;
        if ($limit !== null && $limit > 0) {
            $productsToDisplay = array_slice($this->products, 0, $limit);
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
                                    <!-- <img src="<?php echo $product['main_image'] ?: 'image/logo.png'; ?>"  -->
                                     <!-- sample -->
                                    <img src="upload/sample/sample<?php if($id==1){echo 1; $id=2;}else {echo 2; $id=1;}?>.png" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="product-img">
                                        <span class="badge">Sale</span>
                                        
                                        <!-- Image Navigation Arrows -->
                                        <button class="img-nav-btn img-prev" aria-label="Previous image" type="button" onclick="event.stopPropagation();">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="15 18 9 12 15 6"></polyline>
                                            </svg>
                                        </button>
                                        <button class="img-nav-btn img-next" aria-label="Next image" type="button" onclick="event.stopPropagation();">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="9 18 15 12 9 6"></polyline>
                                            </svg>
                                        </button>
                                        
                                        <!-- View Button -->
                                        <button class="view-btn" type="button" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" onclick="event.stopPropagation();">
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
                                        <span class="price"><?php echo number_format($product['price'], 0); ?> Tk</span>
                                        <button class="btn btn-sm add-to-cart-btn" type="button" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" data-product-price="<?php echo $product['price']; ?>" onclick="event.stopPropagation();">Add to Cart</button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        endif;
        ?>

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
                                    <option value="S">Small</option>
                                    <option value="M">Medium</option>
                                    <option value="L">Large</option>
                                    <option value="XL">Extra Large</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="productQuantity">Quantity:</label>
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn qty-minus">-</button>
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

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const imageModal = document.getElementById("productImageModal");
            const cartModal = document.getElementById("addToCartModal");
            const modalImage = document.getElementById("modalProductImage");
            const modalName = document.getElementById("modalProductName");
            const viewButtons = document.querySelectorAll(".view-btn");
            const addToCartButtons = document.querySelectorAll(".add-to-cart-btn");
            const closeButtons = document.querySelectorAll(".modal-close");
            
            // Image Modal functionality
            viewButtons.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    
                    const productImg = this.closest(".product-card").querySelector(".product-img");
                    
                    modalImage.src = productImg.src;
                    modalName.textContent = productName;
                    imageModal.style.display = "flex";
                    document.body.style.overflow = "hidden";
                });
            });

            // Add to Cart Modal functionality
            addToCartButtons.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const productId = this.dataset.productId;
                    const productName = this.dataset.productName;
                    const productPrice = this.dataset.productPrice;
                    const productImg = this.closest(".product-card").querySelector(".product-img");
                    
                    document.getElementById("cartModalImage").src = productImg.src;
                    document.getElementById("cartModalName").textContent = productName;
                    document.getElementById("cartModalPrice").textContent = productPrice + " Tk";
                    document.getElementById("addToCartForm").dataset.productId = productId;
                    
                    cartModal.style.display = "flex";
                    document.body.style.overflow = "hidden";
                });
            });

            // Close buttons
            closeButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    imageModal.style.display = "none";
                    cartModal.style.display = "none";
                    document.body.style.overflow = "auto";
                });
            });

            // Close modals when clicking outside
            [imageModal, cartModal].forEach(modal => {
                modal.addEventListener("click", function(e) {
                    if (e.target === modal) {
                        modal.style.display = "none";
                        document.body.style.overflow = "auto";
                    }
                });
            });

            // Close on Escape key
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") {
                    imageModal.style.display = "none";
                    cartModal.style.display = "none";
                    document.body.style.overflow = "auto";
                }
            });

            // Image Navigation - cycle through product images
            const imgPrevBtns = document.querySelectorAll(".img-prev");
            const imgNextBtns = document.querySelectorAll(".img-next");
            
            imgPrevBtns.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const productCard = this.closest(".product-card");
                    const productImg = productCard.querySelector(".product-img");
                    const productId = productCard.querySelector(".add-to-cart-btn").dataset.productId;
                    
                    // Get current image index
                    let currentIndex = parseInt(productImg.dataset.imageIndex) || 1;
                    currentIndex = currentIndex > 1 ? 1 : 2;
                    
                    // Update image and store index
                    productImg.src = "upload/sample/sample" + currentIndex + ".png";
                    productImg.dataset.imageIndex = currentIndex;
                });
            });

            imgNextBtns.forEach(btn => {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    const productCard = this.closest(".product-card");
                    const productImg = productCard.querySelector(".product-img");
                    const productId = productCard.querySelector(".add-to-cart-btn").dataset.productId;
                    
                    // Get current image index and cycle
                    let currentIndex = parseInt(productImg.dataset.imageIndex) || 1;
                    currentIndex = currentIndex === 1 ? 2 : 1;
                    
                    // Update image and store index
                    productImg.src = "upload/sample/sample" + currentIndex + ".png";
                    productImg.dataset.imageIndex = currentIndex;
                    
                    // Check if image loaded, if not redirect to product view
                    productImg.onerror = function() {
                        window.location.href = "product-view.php?id=" + productId;
                    };
                });
            });

            // Quantity selector
            const qtyMinus = document.querySelector(".qty-minus");
            const qtyPlus = document.querySelector(".qty-plus");
            const qtyInput = document.getElementById("productQuantity");

            if (qtyMinus) {
                qtyMinus.addEventListener("click", function(e) {
                    e.preventDefault();
                    const current = parseInt(qtyInput.value) || 1;
                    if (current > 1) {
                        qtyInput.value = current - 1;
                    }
                });
            }

            if (qtyPlus) {
                qtyPlus.addEventListener("click", function(e) {
                    e.preventDefault();
                    const current = parseInt(qtyInput.value) || 1;
                    if (current < 100) {
                        qtyInput.value = current + 1;
                    }
                });
            }

            // Form submission
            const form = document.getElementById("addToCartForm");
            if (form) {
                form.addEventListener("submit", function(e) {
                    e.preventDefault();
                    const productId = this.dataset.productId;
                    const quantity = document.getElementById("productQuantity").value;
                    const size = document.getElementById("productSize").value;
                    const image = document.getElementById("productImage").value;

                    // You can use AJAX here to add to cart
                    console.log({
                        product_id: productId,
                        quantity: quantity,
                        size: size,
                        image: image
                    });

                    alert("Product added to cart! Quantity: " + quantity + ", Size: " + size);
                    cartModal.style.display = "none";
                    document.body.style.overflow = "auto";
                    
                    // Reset form
                    form.reset();
                });
            }
        });
        </script>

        <?php

        $content = ob_get_clean();
        
        return $content;
    }
}

?>
