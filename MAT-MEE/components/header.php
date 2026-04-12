<?php
// header-component.php
require_once 'config-component.php';
?>

<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-sm navbar-light">
            <div class="container-fluid">
                <!-- Logo Left -->
                <a class="navbar-brand" href="#">
                    <div class="logo-wrapper">
                        <img src="image/logo.png" alt="MAT MEE" class="logo">
                        <span class="brand-name">MAT MEE</span>
                    </div>
                </a>

                <!-- Right side: search icon (mobile) + toggler -->
                <div class="d-flex align-items-center gap-1 ms-auto d-sm-none">
                    <!-- Mobile search toggle button -->
                    <button class="search-toggle-btn" id="searchToggleBtn" aria-label="Toggle search" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/>
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <!-- Cart Icon with Badge -->
                    <a href="cart.php" class="cart-icon-link d-flex d-sm-none ms-2" title="Shopping Cart" aria-label="Shopping Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.352L2.977 3H14.5a.5.5 0 0 1 .491.592l-1.5 8a.5.5 0 0 1-.491.408H2.968a.5.5 0 0 1-.491-.408l-1.5-8A.5.5 0 0 1 1 3H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                        <?php if (isset($cartCount) && $cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>

                                        

                    <!-- Bootstrap toggler -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                

                <!-- Menu -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php include 'menu.php'; ?>
                </div>
                
                <!-- Mobile search toggle button -->
                    <button class="search-toggle-btn d-none d-sm-flex d-md-none" id="searchToggleBtn" aria-label="Toggle search" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/>
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>
                    <!-- Cart Icon with Badge -->
                    <a href="cart.php" class="cart-icon-link d-none d-sm-flex d-md-none ms-2" title="Shopping Cart" aria-label="Shopping Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.352L2.977 3H14.5a.5.5 0 0 1 .491.592l-1.5 8a.5.5 0 0 1-.491.408H2.968a.5.5 0 0 1-.491-.408l-1.5-8A.5.5 0 0 1 1 3H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                        <?php if (isset($cartCount) && $cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>

                <!-- Desktop search — sits after menu, outside collapse -->
                <div class="search-bar-desktop d-none d-sm-flex align-items-center gap-3" id="searchBarDesktop">
                    <form class="search-form" action="shop.php" method="GET">
                        <div class="search-wrap">
                            <svg class="search-icon-inside" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <circle cx="11" cy="11" r="8"/>
                                <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                            </svg>
                            <input class="search-input" type="search" name="search" placeholder="Search products..."
                                aria-label="Search"
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            <button class="search-submit" type="submit" aria-label="Search">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                    
                    <!-- Cart Icon with Badge -->
                    <a href="cart.php" class="cart-icon-link" title="Shopping Cart" aria-label="Shopping Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.352L2.977 3H14.5a.5.5 0 0 1 .491.592l-1.5 8a.5.5 0 0 1-.491.408H2.968a.5.5 0 0 1-.491-.408l-1.5-8A.5.5 0 0 1 1 3H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                        <?php if (isset($cartCount) && $cartCount > 0): ?>
                            <span class="cart-badge"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>

            </div>
        </nav>

        <!-- Mobile search dropdown — hidden by default, toggled via JS -->
        <div class="search-bar-mobile d-md-none" id="searchBarMobile">
            <div class="container">
                <form class="search-form-mobile" action="shop.php" method="GET">
                    <div class="search-wrap-mobile">
                        <svg class="search-icon-inside" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/>
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                        </svg>
                        <input class="search-input-mobile" type="search" name="search" id="mobileSearchInput"
                            placeholder="Search products..."
                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button class="search-submit-mobile" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/8801670961534" target="_blank" class="whatsapp-btn" aria-label="Chat on WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
        </svg>
    </a>

    <script>
        (function () {
            var btns = document.querySelectorAll('.search-toggle-btn');
            var bar = document.getElementById('searchBarMobile');
            var input = document.getElementById('mobileSearchInput');
            if (btns.length === 0 || !bar) return;

            btns.forEach(function(btn) {
                btn.addEventListener('click', function () {
                    var isOpen = bar.classList.toggle('search-bar-mobile--open');
                    btns.forEach(function(b) {
                        b.classList.toggle('search-toggle-btn--active', isOpen);
                    });
                    if (isOpen) {
                        setTimeout(function () { input && input.focus(); }, 80);
                    }
                });
            });

            // Close if user clicks outside
            document.addEventListener('click', function (e) {
                var isClickInsideBtn = Array.from(btns).some(function(btn) {
                    return btn.contains(e.target);
                });
                
                if (!bar.contains(e.target) && !isClickInsideBtn) {
                    bar.classList.remove('search-bar-mobile--open');
                    btns.forEach(function(b) {
                        b.classList.remove('search-toggle-btn--active');
                    });
                }
            });
        })();
    </script>