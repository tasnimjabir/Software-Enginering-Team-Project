<?php

require_once 'config-component.php';

class Product {
    private $connection;
    private $products = [];
    private $sql = '';


    public function __construct($conn, $cat = '', $order = 'created_at DESC') {
        $this->connection = $conn;

        $this->sql = "SELECT p.id, p.name, p.slug, p.price, p.main_image, c.name as category 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id";
        
        if (!empty($cat)) {
            $this->sql .= " WHERE c.name = '" . $this->connection->escape($cat) . "'";
        }
        
        $this->sql .= " ORDER BY p." . $order;
        
    }

    public function fetch() {
        $this->products = $this->connection->fetch($this->sql);
        return $this;
    }

    public function setSql($sql) {
        $this->sql = $sql;
        return $this;
    }


    public function getProducts() {
        return $this->products;
    }


    public function display($limit = null) {
        $id = 1; // sample
        ob_start();

        $productsToDisplay = $this->products;
        if ($limit !== null && $limit > 0) {
            $productsToDisplay = array_slice($this->products, 0, $limit);
        }

        if (empty($productsToDisplay)):
            ?>
            <div class="alert alert-info text-center">
                <p>No products available at the moment.</p>
            </div>
            <?php
        else:
            ?>
            <div class="row g-4">
                <?php foreach ($productsToDisplay as $product): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="product-card">
                            <div class="product-image">
                                <!-- <img src="<?php echo $product['main_image'] ?: 'image/logo.png'; ?>"  -->
                                 <!-- sample -->
                                <img src="upload/sample/sample<?php if($id==1){echo 1; $id=2;}else {echo 2; $id=1;}?>.png" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <span class="badge">Sale</span>
                            </div>
                            <div class="product-info">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="product-category">
                                    <?php echo $product['category'] ?: 'Uncategorized'; ?>
                                </p>
                                <div class="product-footer">
                                    <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                                    <button class="btn btn-sm btn-primary">Add to Cart</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        endif;

        $content = ob_get_clean();
        
        return $content;
    }


    public function render($limit = null) {
        echo $this->display($limit);
    }


    public function count() {
        return count($this->products);
    }

    public function getById($id) {
        foreach ($this->products as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }
        return null;
    }
}

?>
