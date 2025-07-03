<?php
// includes/database.php - Kelas untuk mengelola koneksi database

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    // Singleton pattern untuk memastikan hanya ada satu koneksi database
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Mendapatkan objek PDO
    public function getConnection() {
        return $this->pdo;
    }

    // Metode untuk menjalankan query dan mengembalikan hasil
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Metode untuk mendapatkan satu baris data
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // Metode untuk mendapatkan semua baris data
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // Metode untuk mendapatkan nilai kolom tertentu
    public function fetchColumn($sql, $params = [], $column = 0) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }

    // Metode untuk insert data dan mengembalikan ID terakhir
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return $this->pdo->lastInsertId();
    }

    // Metode untuk update data
    public function update($table, $data, $where, $whereParams = []) {
        $setClauses = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $setClause = implode(', ', $setClauses);
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $stmt = $this->query($sql, array_merge($params, $whereParams));
        return $stmt->rowCount();
    }

    // Metode untuk delete data
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}
