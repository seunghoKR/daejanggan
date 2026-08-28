<?php

declare(strict_types=1);

/**
 * Auth — 레거시 SHA256 호환 로그인 + 세션 기반 인증 관리
 *
 * 영카트/그누보드의 SHA256 해시와 PHP password_hash() (Bcrypt/Argon2)를 모두 지원합니다.
 * 로그인 성공 시 레거시 해시를 ARGON2ID로 자동 업그레이드합니다.
 */
final class Auth
{
    private const SESSION_KEY = '_dj_user';

    // ----------------------------------------------------------------
    // 비밀번호 검증 (레거시 SHA256 포함)
    // ----------------------------------------------------------------

    public static function verifyPassword(string $input, string $stored): bool
    {
        // 1순위: PHP 표준 password_hash (Bcrypt / Argon2)
        if (password_verify($input, $stored)) {
            return true;
        }

        // 2순위: 그누보드/영카트 SHA256 (소문자 hex)
        if (hash_equals($stored, hash('sha256', $input))) {
            return true;
        }

        // 3순위: 그누보드 레거시 SHA256 대문자 (* 접두사 없음, 그누보드 일부 버전)
        if (hash_equals($stored, strtoupper(hash('sha256', $input)))) {
            return true;
        }

        // 4순위: MySQL PASSWORD() 함수 형식 (*ABCDEF...)
        if (str_starts_with($stored, '*')) {
            $expected = '*' . strtoupper(sha1(sha1($input, true)));
            if (hash_equals($stored, $expected)) {
                return true;
            }
        }

        return false;
    }

    // ----------------------------------------------------------------
    // 해시를 최신 Argon2ID로 업그레이드
    // ----------------------------------------------------------------

    private static function upgradeHash(int $userId, string $newPassword): void
    {
        $newHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        Database::execute(
            "UPDATE users SET password_hash = ?, password_type = 'ARGON2ID' WHERE id = ?",
            [$newHash, $userId]
        );
    }

    // ----------------------------------------------------------------
    // 로그인
    // ----------------------------------------------------------------

    public static function login(string $username, string $password): bool
    {
        $user = Database::fetchOne(
            "SELECT id, password_hash, password_type, role, name FROM users WHERE username = ? LIMIT 1",
            [trim($username)]
        );

        if ($user === false) {
            return false;
        }

        if (!self::verifyPassword($password, $user['password_hash'])) {
            return false;
        }

        // 레거시 해시라면 자동 업그레이드
        if ($user['password_type'] !== 'ARGON2ID' && $user['password_type'] !== 'BCRYPT') {
            self::upgradeHash((int)$user['id'], $password);
        }

        // 최신 Bcrypt인데 rehash가 필요한 경우
        if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID)) {
            self::upgradeHash((int)$user['id'], $password);
        }

        // 마지막 로그인 기록
        Database::execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [(int)$user['id']]
        );

        // 세션 재생성 (세션 고정 공격 방지)
        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id'   => (int)$user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ];

        return true;
    }

    // ----------------------------------------------------------------
    // 로그아웃
    // ----------------------------------------------------------------

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_regenerate_id(true);
    }

    // ----------------------------------------------------------------
    // 상태 확인
    // ----------------------------------------------------------------

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::SESSION_KEY])
            ? (int)$_SESSION[self::SESSION_KEY]['id']
            : null;
    }

    public static function isAdmin(): bool
    {
        return ($_SESSION[self::SESSION_KEY]['role'] ?? '') === 'ADMIN';
    }

    // ----------------------------------------------------------------
    // 미들웨어 — 로그인 필요
    // ----------------------------------------------------------------

    public static function requireLogin(string $redirect = '/login'): void
    {
        if (!self::check()) {
            $isJson = (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
                   || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));
            if ($isJson) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '로그인이 필요합니다.']);
                exit;
            }
            $_SESSION['_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirect);
            exit;
        }
    }

    // ----------------------------------------------------------------
    // 미들웨어 — 관리자 필요
    // ----------------------------------------------------------------

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            exit('접근 권한이 없습니다.');
        }
    }
}
