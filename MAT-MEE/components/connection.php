<?php

class DatabaseConnection {

    private static $instance = null;
    private $connection;
    private $error;

    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $database = 'matmee';

    private function __construct() {
        $this->connection = new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->database
        );

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8");
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function prepareAndExecute($query, $params = []) {

        if (empty($params)) {
            return $this->connection->query($query);
        }

        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            $this->error = $this->connection->error;
            return false;
        }

        $types = '';
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function fetch($query, $params = []) {
        $result = $this->prepareAndExecute($query, $params);

        if (!$result) return [];

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOne($query, $params = []) {
        $result = $this->fetch($query, $params);
        return $result[0] ?? null;
    }

    public function execute($query, $params = []) {
        $result = $this->prepareAndExecute($query, $params);

        if ($result === false) return false;

        return $this->connection->affected_rows;
    }

    public function lastId() {
        return $this->connection->insert_id;
    }

    public function getError() {
        return $this->error;
    }

}