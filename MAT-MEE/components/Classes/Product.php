<?php

require_once $base_url.'components/config-component.php';

class Product {
    private $connection;
    private $products = [];
    private $baseSql = '';
    private $groupSql = '';
    private $orderSql = '';
    private $conditions = [];
    private $params = [];

    public function __construct() {

        $this->connection = DatabaseConnection::getInstance();

        $this->baseSql = "SELECT p.id, p.name, p.slug, p.price, p.discount_price, p.main_image, c.name as category,
                             GROUP_CONCAT(pi.image ORDER BY pi.id ASC SEPARATOR '|') AS extra_images
                      FROM products p
                      LEFT JOIN categories c ON p.category_id = c.id
                      LEFT JOIN product_images pi ON pi.product_id = p.id";
                      
        $this->groupSql = "GROUP BY p.id";
    }

    public function fetch() {
        $sql = $this->baseSql;
        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(" AND ", $this->conditions);
        }
        $sql .= " " . $this->groupSql;
        if (!empty($this->orderSql)) {
            $sql .= " " . $this->orderSql;
        }
        
        $this->products = $this->connection->fetch($sql, $this->params);
        return $this;
    }

    public function setSql($sql) {
        $this->baseSql = $sql;
        $this->groupSql = '';
        $this->orderSql = '';
        $this->conditions = [];
        return $this;
    }

    public function addCondition($condition, $param) {
        $this->conditions[] = $condition;
        $this->params[] = $param;
        return $this;
    }

    public function addOrder($order) {
        $this->orderSql = "ORDER BY " . $order;
        return $this;
    }

    public function getProducts() {
        return $this->products;
    }

    public function display($limit = null) {
        return "Products will be displayed here.";
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