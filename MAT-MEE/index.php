<!-- 
MAT MEE - Premium Clothing Store
Making by:
    1. Tasnim Jabir
    2. Md Shadman Shakib Dip
    3. Shahedur Rahman Rafin
    4. Noor A Arabi
    A team of 4 members from the Software Engineering course at East West University.
Client: MAT MEE
    A real Client who is a clothing store owner. 
    They wanted a website to showcase their products and allow customers to shop online.
-->

<!-- ------------------------------------------ index.php ------------------------------------------ -->

<?php require_once 'components/config-page.php';?>

    <?php include 'components/Carousel.php'; ?>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container-xl">
            <!-- Section Title -->
            <div style="margin-bottom: 2rem; text-align: center;">
                <h2 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Our Collection</h2>
                <p style="color: #6b7280; font-size: 1.05rem;">Browse our latest and finest clothing collections</p>
            </div>
            
            <!-- Category Selector -->
            <div class="category-selector-wrapper container">
                <div class="category-selector" style="display: inline-flex; max-width: 100%; flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; scrollbar-width: thin; padding-bottom: 5px; justify-content: flex-start; align-items: center;">
                    <button class="category-btn active" data-category="" style="flex-shrink: 0; margin-left: 10px;">
                        All Products
                    </button>
                    <div id="categoryContainer" style="display: flex; flex-wrap: nowrap; flex-shrink: 0;"></div>
                </div>
            </div>

            <!-- Products Container -->
            <div id="productsContainer">
                <?php
                    require_once 'components/Classes/ProductBuilder.php';
                    (new ProductBuilder('user'))->fetch()->render();
                ?>
            </div>
        </div>
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


<style>
        .map {
            width: 100%;
            height: 300px;
            overflow: hidden;
            border: 10px solid #d4d4d4;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>

<div class="w-100">
    <div class="map">
        <iframe 
            src="https://www.google.com/maps?q=জাহাজ কোম্পানি শপিং কমপ্লেক্স, রংপুর,Bangladesh&output=embed">
        </iframe>
    </div>
</div>

<?php require_once 'components/page_close.php'; ?>