<?php

namespace App\Utils;

use PDO;
use PDOException;
use RuntimeException;

class Database {
    private static ?PDO $connection = null;
    private array $config;
    private ?string $testDsn = null;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/database.php';
    }

    public static function getInstance(): self {
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * For testing purposes: set an in-memory DSN to avoid file I/O.
     */
    public function setTestDsn(string $dsn): void {
        $this->testDsn = $dsn;
        self::$connection = null; // Reset connection for fresh test state
    }

    public function getConnection(): PDO {
        if (self::$connection === null) {
            $dsn = $this->testDsn ?? "sqlite:" . $this->config['sqlite']['path'];
            try {
                self::$connection = new PDO($dsn);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    public function query(string $sql, array $params = []): array {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): int {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string {
        return $this->getConnection()->lastInsertId();
    }
}
