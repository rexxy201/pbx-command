<?php
require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function get(): PDO {
        if (self::$instance === null) {
            if (DB_DRIVER === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                    DB_HOST, DB_PORT, DB_NAME
                );
            } else {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    DB_HOST, DB_PORT, DB_NAME
                );
            }
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): array {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function row(string $sql, array $params = []): ?array {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function execute(string $sql, array $params = []): int {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function insert(string $sql, array $params = []): string|int {
        if (DB_DRIVER === 'pgsql') {
            $stmt = self::get()->prepare($sql . ' RETURNING id');
            $stmt->execute($params);
            $row = $stmt->fetch();
            return $row['id'];
        }
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return self::get()->lastInsertId();
    }
}
