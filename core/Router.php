<?php

declare(strict_types=1);

/**
 * Router — Front Controller 라우터
 *
 * RESTful URL 매핑 + 영카트 레거시 URL 301 리디렉션
 */
final class Router
{
    /** @var array<int, array{pattern: string, method: string, handler: callable}> */
    private static array $routes = [];

    public static function get(string $pattern, callable $handler): void
    {
        self::$routes[] = ['pattern' => $pattern, 'method' => 'GET', 'handler' => $handler];
    }

    public static function post(string $pattern, callable $handler): void
    {
        self::$routes[] = ['pattern' => $pattern, 'method' => 'POST', 'handler' => $handler];
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        $uri    = '/' . trim($uri, '/');

        // 영카트 레거시 URL 301 리디렉션
        self::handleLegacyRedirects($uri);

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $regex  = self::patternToRegex($route['pattern']);
            if (preg_match($regex, $uri, $matches)) {
                // named subpattern만 추출
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                call_user_func($route['handler'], $params);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        include APP_ROOT . '/views/404.php';
    }

    /** URL 패턴을 정규식으로 변환 (:param → named group) */
    private static function patternToRegex(string $pattern): string
    {
        $pattern = '/' . trim($pattern, '/');
        if ($pattern === '/') {
            return '#^/$#';
        }
        $regex = preg_replace('/\/:([a-zA-Z_]+)/', '/(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }

    /** 영카트 레거시 URL 301 리디렉션 처리 */
    private static function handleLegacyRedirects(string $uri): void
    {
        $query = $_SERVER['QUERY_STRING'] ?? '';

        // shop/item.php?it_id=XXX → /book/XXX
        if ($uri === '/shop/item.php' && preg_match('/it_id=([a-zA-Z0-9_-]+)/', $query, $m)) {
            header('Location: /book/' . urlencode($m[1]), true, 301);
            exit;
        }

        // shop/list.php?ca_id=10 → /category/10
        if ($uri === '/shop/list.php' && preg_match('/ca_id=([0-9a-zA-Z]+)/', $query, $m)) {
            header('Location: /category/' . $m[1], true, 301);
            exit;
        }

        // bbs/board.php?bo_table=notice → /community/notice
        if ($uri === '/bbs/board.php' && preg_match('/bo_table=([a-zA-Z0-9_]+)/', $query, $m)) {
            header('Location: /community/' . urlencode($m[1]), true, 301);
            exit;
        }

        // shop/item.php (it_id 없음) → /books
        if ($uri === '/shop/item.php') {
            header('Location: /books', true, 301);
            exit;
        }
    }
}
