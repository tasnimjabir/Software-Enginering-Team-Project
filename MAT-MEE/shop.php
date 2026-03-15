<?php 
require_once 'components/config-page.php'; 
require_once 'components/connection.php';
require_once 'components/Classes/ProductBuilder.php';

$db = DatabaseConnection::getInstance();
// Fetch categories with product counts
$query = "SELECT c.id, c.name, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id, c.name";
$categories = $db->fetch($query);

// Get current category from URL
$currentCategory = isset($_GET['category']) ? $_GET['category'] : '';
// Get search query from URL
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<!-- Shop Header -->
<div class="shop-header bg-light py-5 text-center mb-5">
    <div class="container">
        <h1 class="display-4 fw-bold">Shop</h1>
        <p class="lead text-muted">Explore Our Latest Collection</p>
    </div>
</div>

<!-- Shop Content -->
<section class="shop-section pb-5" id="shop">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="categories-sidebar p-4 bg-white rounded shadow-sm border">
                    <h5 class="mb-3 text-uppercase fw-bold border-bottom pb-2">Categories</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="shop.php" class="text-decoration-none d-flex justify-content-between align-items-center <?php echo empty($currentCategory) ? 'fw-bold text-primary' : 'text-dark'; ?>" style="transition: color 0.3s;">
                                <span>All Products</span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <?php if ($cat['product_count'] > 0): ?>
                            <li class="mb-2">
                                <a href="shop.php?category=<?php echo urlencode($cat['name']); ?>" class="text-decoration-none d-flex justify-content-between align-items-center <?php echo ($currentCategory === $cat['name']) ? 'fw-bold text-primary' : 'text-dark'; ?>" style="transition: color 0.3s;">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <span class="badge bg-light text-dark border rounded-pill"><?php echo $cat['product_count']; ?></span>
                                </a>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9 col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h4 class="mb-0">
                        <?php echo empty($currentCategory) ? 'All Products' : htmlspecialchars($currentCategory); ?>
                    </h4>
                </div>
                
                <?php
                    $productBuilder = new ProductBuilder('user');
                    if (!empty($currentCategory)) {
                        $productBuilder->setCategory($currentCategory);
                    }
                    if (!empty($searchQuery)) {
                        $productBuilder->setSearch($searchQuery);
                    }
                    $productBuilder->fetch()->render(); 
                ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'components/page_close.php'; ?>