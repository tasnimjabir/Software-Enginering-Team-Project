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

    <?php include 'components/Carousel.php'; ?>

    <!-- Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="section-header">
                <h2>Featured Products</h2>
                <p>Explore Our Latest Collection</p>
            </div>

            <?php
                require_once 'components/Classes/ProductBuilder.php';
                (new ProductBuilder('user'))->fetch()->render();
            ?>
        </div>
    </section>

<?php require_once 'components/page_close.php'; ?>