<?php

declare(strict_types=1);

/**
 * Database — PDO 싱글톤 (PHP 8.4, MariaDB 10.6+, UTF8MB4)
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn     = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST, DB_PORT, DB_NAME);

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND =>
                    "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, " .
                    "sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION'",
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // 운영 환경에선 상세 메시지를 노출하지 않음
                error_log('[DB ERROR] ' . $e->getMessage());
                http_response_code(500);
                exit('데이터베이스 연결에 실패했습니다. 잠시 후 다시 시도해 주세요.');
            }
        }

        return self::$instance;
    }

    /** 편의 메서드 — 짧은 쿼리 실행 */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** 단일 행 조회 */
    public static function fetchOne(string $sql, array $params = []): array|false
    {
        return self::query($sql, $params)->fetch();
    }

    /** 다수 행 조회 */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** INSERT / UPDATE / DELETE 후 영향받은 행 수 */
    public static function execute(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    /** 마지막 INSERT id */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}
