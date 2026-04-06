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
        <div class="container">
            <!-- Section Title -->
            <div style="margin-bottom: 2rem; text-align: center;">
                <h2 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.5rem;">Our Collection</h2>
                <p style="color: #6b7280; font-size: 1.05rem;">Browse our latest and finest clothing collections</p>
            </div>
            
            <!-- Category Selector -->
            <div class="category-selector-wrapper">
                <div class="category-selector">
                    <button class="category-btn active" data-category="">
                        All Products
                    </button>
                    <div id="categoryContainer"></div>
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
    <style>
        .product-modal {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 9999 !important;
            display: none;
            align-items: center !important;
            justify-content: center !important;
            background: rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(2px);
        }

        .product-modal[style*="display: flex"] {
            display: flex !important;
        }

        .modal-content {
            position: relative;
            background: white;
            border-radius: 0;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .cart-modal-content {
            max-width: 700px;
        }

        .cart-modal-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 32px;
            height: 32px;
            border: none;
            background: #f7f6f4;
            color: #111111;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 10;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            background: #111111;
            color: white;
        }

        .modal-image-container {
            width: 100%;
            background: #f7f6f4;
        }

        .modal-image-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .cart-modal-image {
            background: #f7f6f4;
            border-radius: 4px;
            overflow: hidden;
        }

        .cart-modal-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .cart-modal-info h3 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .cart-modal-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .qty-input {
            width: 60px;
            text-align: center;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            border: 1px solid #e5e7eb;
            background: white;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: #f7f6f4;
            border-color: #d1d5db;
        }

        .cart-submit-btn {
            width: 100%;
            margin-top: 1rem;
        }

        @media (max-width: 576px) {
            .cart-modal-body {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .modal-content {
                width: 95%;
            }
        }

        .fade-out {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
    </style>

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
    <script src="asset/js/shop.js"></script>

<?php require_once 'components/page_close.php'; ?>