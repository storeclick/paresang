<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;
    private static $instance = null;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            die("خطا در اجرای کوئری: " . $e->getMessage());
        }
    }

    public function insert($table, $data) {
        $fields = implode(',', array_keys($data));
        $values = implode(',', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$values})";
        
        return $this->query($sql, array_values($data));
    }

    public function update($table, $data, $where) {
        $fields = implode('=?,', array_keys($data)) . '=?';
        
        $conditions = [];
        $params = [];
        foreach ($where as $column => $value) {
            $conditions[] = "$column = ?";
            $params[] = $value;
        }
        $sql = "UPDATE {$table} SET {$fields} WHERE " . implode(' AND ', $conditions);
        
        return $this->query($sql, array_merge(array_values($data), $params));
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }

    public function get($table, $columns = '*', $where = []) {
        $sql = "SELECT $columns FROM $table";

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = :$column";
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($where as $column => $value) {
            $stmt->bindValue(":$column", $value);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}