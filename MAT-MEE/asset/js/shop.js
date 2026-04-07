/**
 * MAT-MEE Shop Management System
 * Handles product categories, filtering, modals, and cart operations
 */

const ShopManager = {
    // Configuration
    config: {
        apiCategory: 'api/get-categories.php',
        apiProducts: 'api/get-products-by-category.php',
        apiSizes: 'api/get-sizes.php',
        cartPage: 'cart.php',
        fadeDelay: 200
    },

    // DOM Selectors
    selectors: {
        categoryContainer: '#categoryContainer',
        productsContainer: '#productsContainer',
        productImageModal: '#productImageModal',
        addToCartModal: '#addToCartModal',
        modalButtons: '.modal-close',
        viewButtons: '.view-btn',
        addToCartButtons: '.add-to-cart-btn',
        categoryButtons: '.category-btn',
        qtyButtons: '.qty-btn',
        addToCartForm: '#addToCartForm',
        productCardLinks: '.product-card-link'
    },

    // State
    state: {
        escapeKeyListener: null,
        activeCategory: null
    },

    /**
     * Initialize the shop manager
     */
    init() {
        this.loadCategories();
        this.setupCategoryListeners();
        this.initializeEventDelegation();
    },

    /**
     * Setup category button listeners (including "All Products")
     */
    setupCategoryListeners() {
        // Setup "All Products" button
        const allProductsBtn = document.querySelector('[data-category=""]');
        if (allProductsBtn) {
            allProductsBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.filterByCategory('');
            });
        }

        // Dynamic category buttons are handled by event delegation through loadCategories
    },

    /**
     * Setup event delegation for dynamic content
     */
    initializeEventDelegation() {
        const container = document.getElementById('productsContainer');
        
        if (container) {
            // View image button
            container.addEventListener('click', (e) => {
                if (e.target.closest(this.selectors.viewButtons)) {
                    this.handleViewImageClick(e);
                }
            });

            // Add to cart button
            container.addEventListener('click', (e) => {
                if (e.target.closest(this.selectors.addToCartButtons)) {
                    this.handleAddToCartClick(e);
                }
            });
        }

        // Setup modal quantity buttons (not delegated since they're outside products container)
        this.setupModalQuantityButtons();

        // Setup form submission
        this.setupFormSubmission();
    },

    /**
     * Setup quantity buttons in modal
     */
    setupModalQuantityButtons() {
        const cartModal = document.querySelector(this.selectors.addToCartModal);
        if (!cartModal) return;

        cartModal.addEventListener('click', (e) => {
            if (e.target.closest(this.selectors.qtyButtons)) {
                e.preventDefault();
                const btn = e.target.closest(this.selectors.qtyButtons);
                const qtyInput = btn.closest('.quantity-selector')?.querySelector('.qty-input');

                if (!qtyInput) return;

                let current = parseInt(qtyInput.value) || 1;

                if (btn.classList.contains('qty-minus')) {
                    if (current > 1) qtyInput.value = current - 1;
                } else if (btn.classList.contains('qty-plus')) {
                    if (current < 100) qtyInput.value = current + 1;
                }
            }
        });
    },

    /**
     * Setup form submission listener
     */
    setupFormSubmission() {
        const form = document.querySelector(this.selectors.addToCartForm);
        if (!form) return;

        form.addEventListener('submit', (e) => this.handleFormSubmit(e));
    },

    /**
     * Load categories from API
     */
    async loadCategories() {
        try {
            const response = await fetch(this.config.apiCategory);
            const data = await response.json();

            if (!data.success || data.categories.length === 0) return;

            const container = document.querySelector(this.selectors.categoryContainer);
            if (!container) return;

            container.innerHTML = '';

            data.categories.forEach(category => {
                const btn = this.createCategoryButton(category);
                container.appendChild(btn);
            });
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    },

    /**
     * Create a category button element
     */
    createCategoryButton(category) {
        const btn = document.createElement('button');
        btn.className = 'category-btn';
        btn.setAttribute('data-category', category.name);
        btn.innerHTML = `${category.name}<span class="category-count">${category.product_count}</span>`;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            this.filterByCategory(category.name);
        });

        return btn;
    },

    /**
     * Filter products by category
     */
    async filterByCategory(category) {
        try {
            this.state.activeCategory = category;
            this.updateActiveCategory(category);

            const url = category 
                ? `${this.config.apiProducts}?category=${encodeURIComponent(category)}`
                : this.config.apiProducts;

            const response = await fetch(url);
            const html = await response.text();

            this.updateProductsContainer(html);
        } catch (error) {
            console.error('Error filtering products:', error);
            alert('Error loading products. Please try again.');
        }
    },

    /**
     * Update active category button styling
     */
    updateActiveCategory(category) {
        document.querySelectorAll(this.selectors.categoryButtons).forEach(btn => {
            btn.classList.remove('active');
        });

        const selector = category 
            ? `[data-category="${category}"]`
            : '[data-category=""]';
        
        const activeBtn = document.querySelector(selector);
        if (activeBtn) activeBtn.classList.add('active');
    },

    /**
     * Update products container with fade effect
     */
    updateProductsContainer(html) {
        const container = document.querySelector(this.selectors.productsContainer);
        if (!container) return;

        container.classList.add('fade-out');

        setTimeout(() => {
            container.innerHTML = html;
            container.classList.remove('fade-out');
        }, this.config.fadeDelay);
    },

    /**
     * Handle view image button click
     */
    async handleViewImageClick(e) {
        e.preventDefault();
        e.stopPropagation();

        try {
            const btn = e.target.closest(this.selectors.viewButtons);
            const productCard = btn.closest('.product-card');
            const productName = btn.dataset.productName;
            const productImg = productCard?.querySelector('.product-img');

            if (!productImg) {
                console.error('Product image not found');
                return;
            }

            const modal = document.querySelector(this.selectors.productImageModal);
            if (!modal) return;

            const modalImg = modal.querySelector('#modalProductImage');
            const modalName = modal.querySelector('#modalProductName');

            if (modalImg) modalImg.src = productImg.src;
            if (modalName) modalName.textContent = productName;

            this.openModal(modal);
        } catch (error) {
            console.error('Error opening image modal:', error);
        }
    },

    /**
     * Handle add to cart button click
     */
    async handleAddToCartClick(e) {
        e.preventDefault();
        e.stopPropagation();

        try {
            const btn = e.target.closest(this.selectors.addToCartButtons);
            const productCard = btn.closest('.product-card');
            const productImg = productCard?.querySelector('.product-img');

            if (!productImg) {
                console.error('Product image not found');
                return;
            }

            const modal = document.querySelector(this.selectors.addToCartModal);
            if (!modal) return;

            // Populate modal with product data
            this.populateCartModal(modal, {
                productId: btn.dataset.productId,
                productName: btn.dataset.productName,
                productPrice: btn.dataset.productPrice,
                productImg: productImg.src
            });

            // Load sizes for this product
            await this.loadProductSizes(btn.dataset.productId);

            this.openModal(modal);
        } catch (error) {
            console.error('Error opening cart modal:', error);
        }
    },

    /**
     * Populate cart modal with product information
     */
    populateCartModal(modal, productData) {
        const cartImg = modal.querySelector('#cartModalImage');
        const cartName = modal.querySelector('#cartModalName');
        const cartPrice = modal.querySelector('#cartModalPrice');
        const form = modal.querySelector(this.selectors.addToCartForm);

        if (cartImg) cartImg.src = productData.productImg;
        if (cartName) cartName.textContent = productData.productName;
        if (cartPrice) cartPrice.textContent = productData.productPrice + ' Tk';
        if (form) form.dataset.productId = productData.productId;
    },

    /**
     * Load available sizes for a product
     */
    async loadProductSizes(productId) {
        try {
            const response = await fetch(`${this.config.apiSizes}?product_id=${productId}`);
            const data = await response.json();

            const sizeSelect = document.querySelector('#productSize');
            if (!sizeSelect) return;

            sizeSelect.innerHTML = '<option value="">-- Select Size --</option>';

            if (data.success && data.sizes && data.sizes.length > 0) {
                data.sizes.forEach(size => {
                    const option = document.createElement('option');
                    option.value = size.size_name;
                    option.textContent = size.size_name;
                    sizeSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error fetching sizes:', error);
        }
    },

    /**
     * Handle quantity button clicks
     */
    handleQuantityChange(e) {
        e.preventDefault();
        const btn = e.target.closest(this.selectors.qtyButtons);
        const qtyInput = btn.closest('.quantity-selector')?.querySelector('.qty-input');

        if (!qtyInput) return;

        let current = parseInt(qtyInput.value) || 1;

        if (btn.classList.contains('qty-minus')) {
            if (current > 1) qtyInput.value = current - 1;
        } else if (btn.classList.contains('qty-plus')) {
            if (current < 100) qtyInput.value = current + 1;
        }
    },

    /**
     * Re-initialize product event listeners (called after dynamic content load)
     */
    reinitializeProductEvents() {
        // Event listeners are now set up via initializeEventDelegation
        // and setupFormSubmission, no need to reinitialize
    },
    openModal(modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        this.setupModalListeners(modal);
    },

    /**
     * Close all modals
     */
    closeModals() {
        const imageModal = document.querySelector(this.selectors.productImageModal);
        const cartModal = document.querySelector(this.selectors.addToCartModal);

        if (imageModal) imageModal.style.display = 'none';
        if (cartModal) cartModal.style.display = 'none';

        document.body.style.overflow = 'auto';
    },

    /**
     * Setup modal event listeners (close buttons, click outside, escape key)
     */
    setupModalListeners(modal) {
        // Close button
        const closeBtn = modal.querySelector(this.selectors.modalButtons);
        if (closeBtn) {
            closeBtn.onclick = () => this.closeModals();
        }

        // Click outside modal
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this.closeModals();
        }, { once: true });

        // Escape key
        if (this.state.escapeKeyListener) {
            document.removeEventListener('keydown', this.state.escapeKeyListener);
        }

        this.state.escapeKeyListener = (e) => {
            if (e.key === 'Escape') this.closeModals();
        };

        document.addEventListener('keydown', this.state.escapeKeyListener);
    },

    /**
     * Re-initialize product event listeners (called after dynamic content load)
     */
    reinitializeProductEvents() {
        const form = document.querySelector(this.selectors.addToCartForm);
        if (!form) return;

        // Clone form to remove old listeners
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        // Setup form submission
        newForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
    },

    /**
     * Handle add to cart form submission
     */
    async handleFormSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const productId = form.dataset.productId;
        const quantity = document.querySelector('#productQuantity')?.value || 1;
        const size = document.querySelector('#productSize')?.value;

        // Validation
        if (!size) {
            alert('Please select a size');
            return;
        }

        if (!productId) {
            alert('Product not found');
            return;
        }

        try {
            const response = await fetch(this.config.cartPage, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&qty=${quantity}&size=${encodeURIComponent(size)}`
            });

            const data = await response.text();
            alert('Product added to cart successfully!');
            this.closeModals();
            
            // Refresh to show updated cart count
            location.reload();
        } catch (error) {
            console.error('Error adding to cart:', error);
            alert('Error adding product to cart. Please try again.');
        }
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ShopManager.init());
} else {
    ShopManager.init();
}
