<?php

require_once 'components/config-component.php';

class Product {
    private $connection;
    private $products = [];
    private $sql = '';
    private $params = [];

    public function __construct() {

        $this->connection = DatabaseConnection::getInstance();

        $this->sql = "SELECT p.id, p.name, p.slug, p.price, p.main_image, c.name as category 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id";

        // if (!empty($cat)) {
        //     $this->sql .= " WHERE c.name = ?";
        //     $this->params[] = $cat;
        // }

        // $this->sql .= " ORDER BY p." . $order;
    }

    public function fetch() {
        $this->products = $this->connection->fetch($this->sql, $this->params);
        return $this;
    }

    public function setSql($sql) {
        $this->sql = $sql;
        return $this;
    }

    public function addCondition($condition, $param) {
        if (strpos($this->sql, 'WHERE') === false) {
            $this->sql .= " WHERE " . $condition;
        } else {
            $this->sql .= " AND " . $condition;
        }
        $this->params[] = $param;
        return $this;
    }

    public function addOrder($order) {
        $this->sql .= " ORDER BY " . $order;
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