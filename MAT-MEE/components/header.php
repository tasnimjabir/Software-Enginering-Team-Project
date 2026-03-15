<?php
// header-component.php
require_once 'config-component.php';
?>

<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-sm navbar-light">
            <div class="container">
                <!-- Logo Left -->
                <a class="navbar-brand" href="#">
                    <div class="logo-wrapper">
                        <img src="image/logo.png" alt="MAT MEE" class="logo">
                        <span class="brand-name">MAT MEE</span>
                    </div>
                </a>

                <!-- Right side: search icon (mobile) + toggler -->
                <div class="d-flex align-items-center gap-2 ms-auto d-sm-none">
                    <!-- Mobile search toggle button -->
                    <button class="search-toggle-btn" id="searchToggleBtn" aria-label="Toggle search" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"/>
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                        </svg>
                    </button>

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

                <!-- Desktop search — sits after menu, outside collapse -->
                <div class="search-bar-desktop d-none d-sm-flex" id="searchBarDesktop">
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
                </div>

            </div>
        </nav>

        <!-- Mobile search dropdown — hidden by default, toggled via JS -->
        <div class="search-bar-mobile d-sm-none" id="searchBarMobile">
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
            var btn = document.getElementById('searchToggleBtn');
            var bar = document.getElementById('searchBarMobile');
            var input = document.getElementById('mobileSearchInput');
            if (!btn || !bar) return;

            btn.addEventListener('click', function () {
                var isOpen = bar.classList.toggle('search-bar-mobile--open');
                btn.classList.toggle('search-toggle-btn--active', isOpen);
                if (isOpen) {
                    setTimeout(function () { input && input.focus(); }, 80);
                }
            });

            // Close if user clicks outside
            document.addEventListener('click', function (e) {
                if (!bar.contains(e.target) && !btn.contains(e.target)) {
                    bar.classList.remove('search-bar-mobile--open');
                    btn.classList.remove('search-toggle-btn--active');
                }
            });
        })();
    </script>