<?php

class DatabaseConnection {
    private $connection;
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $database = 'matmee';
    private $error;

    /**
     * Constructor - Establish MySQL connection
     */
    public function __construct() {
        $this->connect();
    }

    /**
     * Connect to MySQL database
     */
    private function connect() {
        $this->connection = new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->database
        );

        // Check connection
        if ($this->connection->connect_error) {
            $this->error = "Connection failed: " . $this->connection->connect_error;
            die($this->error);
        }

        // Set charset to utf8
        $this->connection->set_charset("utf8");
    }

    /**
     * Execute query (INSERT, UPDATE, DELETE)
     * @param string $query SQL query
     * @param array $params Query parameters for prepared statements
     * @return bool|int Returns true on success or affected rows
     */
    public function execute($query, $params = []) {
        if (empty($params)) {
            // Direct query without parameters
            if ($this->connection->query($query) === TRUE) {
                return $this->connection->affected_rows;
            } else {
                $this->error = $this->connection->error;
                return false;
            }
        } else {
            // Prepared statement with parameters
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                $this->error = $this->connection->error;
                return false;
            }

            // Bind parameters
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                $affected_rows = $stmt->affected_rows;
                $stmt->close();
                return $affected_rows;
            } else {
                $this->error = $stmt->error;
                $stmt->close();
                return false;
            }
        }
    }

    /**
     * Fetch all records from query
     * @param string $query SQL query
     * @param array $params Query parameters for prepared statements
     * @return array Returns array of results or empty array
     */
    public function fetch($query, $params = []) {
        $result = [];

        if (empty($params)) {
            // Direct query without parameters
            $result_set = $this->connection->query($query);
            if (!$result_set) {
                $this->error = $this->connection->error;
                return $result;
            }
        } else {
            // Prepared statement with parameters
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                $this->error = $this->connection->error;
                return $result;
            }

            // Bind parameters
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
            }

            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) {
                $this->error = $stmt->error;
                $stmt->close();
                return $result;
            }

            $result_set = $stmt->get_result();
        }

        // Fetch all rows
        while ($row = $result_set->fetch_assoc()) {
            $result[] = $row;
        }

        if (isset($stmt)) {
            $stmt->close();
        }

        return $result;
    }

    /**
     * Fetch a single record
     * @param string $query SQL query
     * @param array $params Query parameters for prepared statements
     * @return array|null Returns single row or null
     */
    public function fetchOne($query, $params = []) {
        $results = $this->fetch($query, $params);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get last inserted ID
     * @return int Last insert id
     */
    public function getLastId() {
        return $this->connection->insert_id;
    }

    /**
     * Escape string for SQL safety
     * @param string $string
     * @return string Escaped string
     */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    /**
     * Get last error
     * @return string Error message
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Get connection object
     * @return mysqli Connection object
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Close database connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Destructor - Close connection when object is destroyed
     */
    public function __destruct() {
        $this->close();
    }
}
?>
