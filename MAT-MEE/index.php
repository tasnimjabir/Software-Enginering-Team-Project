<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAT MEE - Premium Clothing Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="asset/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <!-- Logo Left -->
                <a class="navbar-brand order-lg-1" href="#">
                    <img src="image/logo.png" alt="MAT MEE" class="logo">
                </a>
                
                <!-- Menu Right -->
                <button class="navbar-toggler order-lg-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse order-lg-2" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#products">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Summer Collection 2026</h1>
            <p>Discover Premium Fashion Styles</p>
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

            <div class="row g-4">
                <!-- Product Card 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="image/logo.png" alt="Product 1">
                            <span class="badge">Sale</span>
                        </div>
                        <div class="product-info">
                            <h5>Premium T-Shirt</h5>
                            <p class="product-category">Casual Wear</p>
                            <div class="product-rating">
                                <span class="stars">★★★★★</span> (120)
                            </div>
                            <div class="product-footer">
                                <span class="price">$29.99</span>
                                <button class="btn btn-sm btn-primary">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Card 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="image/logo.png" alt="Product 2">
                            <span class="badge hot">Hot</span>
                        </div>
                        <div class="product-info">
                            <h5>Trendy Hoodie</h5>
                            <p class="product-category">Streetwear</p>
                            <div class="product-rating">
                                <span class="stars">★★★★☆</span> (98)
                            </div>
                            <div class="product-footer">
                                <span class="price">$49.99</span>
                                <button class="btn btn-sm btn-primary">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Card 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="image/logo.png" alt="Product 3">
                        </div>
                        <div class="product-info">
                            <h5>Elegant Dress</h5>
                            <p class="product-category">Formal Wear</p>
                            <div class="product-rating">
                                <span class="stars">★★★★★</span> (156)
                            </div>
                            <div class="product-footer">
                                <span class="price">$79.99</span>
                                <button class="btn btn-sm btn-primary">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h6>About MAT MEE</h6>
                    <p>Premium clothing for modern lifestyle.</p>
                </div>
                <div class="col-md-4">
                    <h6>Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6>Contact Us</h6>
                    <p>Email: info@matmee.com<br>Phone: +1 (555) 123-4567</p>
                </div>
            </div>
            <hr>
            <div class="footer-bottom">
                <p>&copy; 2026 MAT MEE. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>