<?php
require_once 'config-component.php';

// Initialize session and cart if not already done
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure cart is initialized for this session
if (!isset($cartCount)) {
    require_once 'connection.php';
    $conn = DatabaseConnection::getInstance();
    
    $sessionId = session_id();
    $cart = $conn->fetchOne("SELECT id FROM cart WHERE session_id = ?", [$sessionId]);
    if (!$cart) {
        $conn->execute("INSERT INTO cart (session_id) VALUES (?)", [$sessionId]);
        $cartId = $conn->lastId();
    } else {
        $cartId = $cart['id'];
    }
    
    $cartCount = (int)($conn->fetchOne("SELECT COALESCE(SUM(quantity), 0) AS count FROM cart_items WHERE cart_id = ?", [$cartId])['count'] ?? 0);
}
$categoriesForDropdown = $conn->fetch("SELECT slug, name, image FROM categories ORDER BY name ASC");
?>

<style>
/* ══════════════════════════════════════════════════════════
   MEGA MENU — hover-only, desktop only, white design
══════════════════════════════════════════════════════════ */

/* Desktop: pure CSS hover reveal */
@media (min-width: 992px) {
    /* Ensure the navbar is the positioning parent */
    .navbar-collapse {
        position: static;
    }
    .nav-item.shop-item {
        position: static;
    }

    .mega-dropdown-menu {
        display: none;
        position: absolute;
        /* Centre under the full navbar */
        left: 50%;
        transform: translateX(-50%);
        top: 100%;
        width: max-content;
        max-width: 820px;
        background: #ffffff;
        border: none;
        border-top: 3px solid #800000;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 18px 54px rgba(0, 0, 0, 0.11);
        padding: 1.8rem 2.2rem 2rem;
        z-index: 9999;
        white-space: normal;
        /* Smooth appear */
        animation: megaFade 0.2s ease forwards;
    }

    /* Show when hovering the <li> OR the panel itself */
    .nav-item.shop-item:hover .mega-dropdown-menu,
    .mega-dropdown-menu:hover {
        display: block;
    }
}

@keyframes megaFade {
    from { opacity: 0; transform: translateX(-50%) translateY(-6px); }
    to   { opacity: 1; transform: translateX(-50%) translateY(0); }
}

/* Mobile: mega menu completely hidden, Shop is a plain link */
@media (max-width: 991px) {
    .mega-dropdown-menu {
        display: none !important;
    }
    /* Remove Bootstrap's dropdown caret */
    .shop-toggle::after {
        display: none !important;
    }
}

/* ── Section header inside mega ───────────────────────── */
.mega-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
    color: #bbb;
    margin-bottom: 1.2rem;
    padding-bottom: 0.7rem;
    border-bottom: 1px solid #f0f0f0;
}

/* ── Category card ────────────────────────────────────── */
.category-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-decoration: none !important;
    width: 96px;
    transition: transform 0.25s ease;
}
.category-card:hover {
    transform: translateY(-5px);
}

.cat-img-circle {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    overflow: hidden;
    background: #f5f5f5;
    border: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: border-color 0.25s, box-shadow 0.25s;
}
.category-card:hover .cat-img-circle {
    border-color: #cc0000;
    box-shadow: 0 6px 18px rgba(128, 0, 0, 0.15);
}

/* "All products" placeholder circle */
.cat-all-circle {
    background: linear-gradient(135deg, #f0f0f0, #e2e2e2);
    color: #888;
    font-size: 1.5rem;
}
.category-card:hover .cat-all-circle {
    background: linear-gradient(135deg, #fff0f0, #ffd6d6);
    color: #800000;
}

.cat-label {
    margin-top: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #444;
    text-align: center;
    line-height: 1.25;
    max-width: 90px;
    transition: color 0.2s;
}
.category-card:hover .cat-label {
    color: #800000;
}
</style>

<!-- Menu Right -->
<ul class="navbar-nav ms-auto mb-lg-0 align-items-center">
    <li class="nav-item">
        <a class="nav-link" href="index.php">Home</a>
    </li>

    <!-- Shop — hover mega menu on desktop; plain link on mobile -->
    <li class="nav-item shop-item" style="position: static;">
        <!--
            NO data-bs-toggle="dropdown" here.
            Clicking always navigates to shop.php.
            On desktop hovering the <li> reveals the mega panel via CSS.
        -->
        <a class="nav-link shop-toggle" href="shop.php">
            Shop <i class="bi bi-chevron-down d-none d-sm-inline" style="font-size:0.65rem; opacity:0.7;"></i>
        </a>

        <div class="mega-dropdown-menu">
            <div class="mega-label">Browse by Category</div>
            <div class="d-flex flex-wrap gap-3">

                <!-- All Products -->
                <a href="shop.php" class="category-card" style="background-color: azure !important;">
                    <div class="cat-img-circle cat-all-circle">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <span class="cat-label">All Products</span>
                </a>

                <!-- Dynamic categories from DB -->
                <?php foreach ($categoriesForDropdown as $cat): ?>
                    <a href="shop.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="category-card" style="background-color: azure !important;">
                        <div class="cat-img-circle">
                            <?php if (!empty($cat['image'])): ?>
                                <img src="upload/categories/<?= htmlspecialchars($cat['image']) ?>"
                                     alt="<?= htmlspecialchars($cat['name']) ?>"
                                     style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <i class="bi bi-tag fs-5 text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <span class="cat-label"><?= htmlspecialchars($cat['name']) ?></span>
                    </a>
                <?php endforeach; ?>

            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="about.php">About</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="contact.php">Contact</a>
    </li>
</ul>