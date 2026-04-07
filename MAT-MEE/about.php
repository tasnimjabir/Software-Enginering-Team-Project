<?php 
$page_title = 'About Us';
require_once 'components/config-page.php';
?>
<link rel="stylesheet" href="asset/css/about-contact.css">
<body>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <!-- Main About Section -->
            <div class="section-header">
                <h2>Who We Are</h2>
                <p>Discover our story, passion, and commitment to excellence</p>
            </div>

            <div class="about-grid">
                <div class="about-text">
                    <h3>Our Journey</h3>
                    <p>
                        MAT MEE is a premier fashion destination located in the heart of Rangpur. We are dedicated to providing high-quality clothing that combines traditional elegance with contemporary style. Our journey began with a passion for fashion and a commitment to serve our community with the finest products.
                    </p>
                    <p>
                        With years of experience in the fashion industry, we have built a reputation for excellence, quality, and exceptional customer service. Every piece in our collection is carefully curated to ensure it meets our high standards.
                    </p>
                    <p>
                        Our mission is to make premium fashion accessible to everyone and to help our customers express their unique style and personality through our diverse collection of clothing.
                    </p>
                </div>
                <div class="about-image">
                    <svg width="100%" height="400" viewBox="0 0 500 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="500" height="400" fill="#f3f4f6"/>
                        <circle cx="250" cy="150" r="80" fill="#dc2626" opacity="0.1"/>
                        <circle cx="150" cy="250" r="60" fill="#991b1b" opacity="0.1"/>
                        <circle cx="380" cy="280" r="50" fill="#dc2626" opacity="0.08"/>
                        <text x="250" y="200" font-size="48" fill="#800000" text-anchor="middle" font-weight="bold">MAT MEE</text>
                        <text x="250" y="240" font-size="16" fill="#6b7280" text-anchor="middle">Fashion Store</text>
                    </svg>
                </div>
            </div>

            <!-- Features Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="section-header">
                        <h2>Why Choose Us</h2>
                        <p>Excellence in every aspect of our service</p>
                    </div>
                </div>
            </div>

            <div class="about-features">
                <div class="feature-box">
                    <i class="bi bi-star-fill"></i>
                    <h4>Premium Quality</h4>
                    <p>We source only the finest materials and maintain strict quality control standards.</p>
                </div>
                <div class="feature-box">
                    <i class="bi bi-truck"></i>
                    <h4>Fast Delivery</h4>
                    <p>Quick and reliable delivery service to get your purchases at your doorstep.</p>
                </div>
                <div class="feature-box">
                    <i class="bi bi-heart"></i>
                    <h4>Customer Care</h4>
                    <p>Dedicated support team to help you with any questions or concerns.</p>
                </div>
                <div class="feature-box">
                    <i class="bi bi-shield-check"></i>
                    <h4>Secure Shopping</h4>
                    <p>Safe and secure payment options for your peace of mind.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="about-content">
        <div class="container text-center">
            <h2 style="font-size: 2rem; color: var(--text-dark); margin-bottom: 1rem;">Ready to Explore Fashion?</h2>
            <p style="color: var(--text-light); margin-bottom: 2rem; font-size: 1.1rem;">Visit our shop and discover our latest collection</p>
            <a href="shop.php" class="btn btn-lg" style="background: var(--primary-color); color: #fff; border: none; padding: 0.75rem 2.5rem; font-weight: 600; border-radius: 8px; text-decoration: none; transition: var(--transition);">
                Shop Now <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </section>

</body>

<?php require_once 'components/page_close.php'; ?>
