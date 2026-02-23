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

<?php require_once 'components/config-page.php'; ?>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>ঈদ স্পেশাল অফার!</h1>
            <p>সীমিত সময়ের জন্য পাচ্ছেন বিশেষ ছাড় — এখনই কিনুন, ঈদ হোক আরও আনন্দময়!</p>
            <a href="#products" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Explore Our Latest Collection</p>
            </div>

            <?php
                (new Product($conn))->fetch()->render();
            ?>
        </div>
    </section>

<?php require_once 'components/page_close.php'; ?>