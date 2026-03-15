<?php
require_once 'components/config-component.php';
require_once 'Product.php';
class ProductBuilder {
    private Product $product;
    public function __construct(string $type) {
        if ($type === 'user') {
            require_once 'UserProducts.php';
            $this->product = new UserProducts();
        } else {
            $this->product = new Product();
        }
    }
    public function setCategory(string $category) {
        $this->product->addCondition("c.name = ?", $category);
        return $this;
    }
    public function setSearch(string $search) {
        $this->product->addCondition("p.name LIKE ?", "%".$search."%");
        return $this;
    }
    public function setOrder(string $order) {
        $this->product->addOrder("p.".$order);
        return $this;
    }
    public function fetch() {
        $this->product->fetch();
        return $this;
    }
    public function render($limit = null) {
        return $this->product->render($limit);
    }
}