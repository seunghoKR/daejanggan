<?php

declare(strict_types=1);

// ============================================================
// 환경 설정 — .env 또는 서버 환경변수 우선, fallback 기본값
// ============================================================
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'ndaejanggan');
define('DB_USER', $_ENV['DB_USER'] ?? 'ndaejanggan');
define('DB_PASS', $_ENV['DB_PASS'] ?? '#seungho0409');

define('APP_ROOT', dirname(__DIR__));
define('APP_ENV',  $_ENV['APP_ENV'] ?? 'production'); // 'development' or 'production'
define('APP_DEBUG', APP_ENV === 'development');
define('APP_VERSION', 'v1.2.0');
define('APP_NAME', '도서출판 대장간');

define('UPLOAD_PATH',    APP_ROOT . '/uploads');
define('UPLOAD_URL',     '/uploads');
define('DEFAULT_BOOK_IMG', '/assets/images/default_book.png');

// 허용 이미지 확장자
define('ALLOWED_IMG_EXT', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// 세션 설정
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// 오류 표시 설정
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 타임존
date_default_timezone_set('Asia/Seoul');
