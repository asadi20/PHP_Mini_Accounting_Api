<?php

namespace core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        require_once __DIR__ . '/Config.php';

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            echo ("Database Connection Error: " . $e->getMessage());
            die('database connection error.');
        }
    }

    public static function getConnection(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new PDOException('error in query! ', (int)$e->getCode(), $e);
        }
    }
    
    public function lastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}
