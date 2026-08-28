<?php

declare(strict_types=1);

final class HomeController
{
    public static function index(array $params = []): void
    {
        // 신간 도서 (최신 6권)
        $newBooks = Database::fetchAll(
            "SELECT id, book_code, title, author, price, original_price, cover_image
             FROM books WHERE status = 'SALE' AND is_new = 1
             ORDER BY created_at DESC LIMIT 6"
        );

        // 추천 도서 (최신 6권)
        $recommendBooks = Database::fetchAll(
            "SELECT id, book_code, title, author, price, original_price, cover_image
             FROM books WHERE status = 'SALE' AND is_recommend = 1
             ORDER BY view_count DESC LIMIT 6"
        );

        // 베스트셀러
        $bestBooks = Database::fetchAll(
            "SELECT id, book_code, title, author, price, original_price, cover_image
             FROM books WHERE status = 'SALE' AND is_best = 1
             ORDER BY view_count DESC LIMIT 4"
        );

        // 시리즈 목록
        $seriesList = Database::fetchAll(
            "SELECT s.id, s.name, s.description, s.cover_image,
                    COUNT(b.id) AS book_count
             FROM series s
             LEFT JOIN books b ON b.series_id = s.id AND b.status = 'SALE'
             GROUP BY s.id ORDER BY s.sort_order ASC LIMIT 6"
        );

        // 위치별 배너 목록
        $heroBanners = Database::fetchAll(
            "SELECT * FROM banners WHERE is_active = 1 AND banner_type IN ('HERO_MAIN', 'HERO') ORDER BY sort_order ASC, created_at DESC"
        );
        $floatLeftBanners = Database::fetchAll(
            "SELECT * FROM banners WHERE is_active = 1 AND banner_type = 'FLOAT_LEFT' ORDER BY sort_order ASC, created_at DESC"
        );
        $floatRightTopBanners = Database::fetchAll(
            "SELECT * FROM banners WHERE is_active = 1 AND banner_type = 'FLOAT_RIGHT_TOP' ORDER BY sort_order ASC, created_at DESC"
        );
        $floatRightBottomBanners = Database::fetchAll(
            "SELECT * FROM banners WHERE is_active = 1 AND banner_type = 'FLOAT_RIGHT_BOTTOM' ORDER BY sort_order ASC, created_at DESC"
        );
        $eventGridBanners = Database::fetchAll(
            "SELECT * FROM banners WHERE is_active = 1 AND banner_type IN ('EVENT_GRID', 'POSTER', 'EVENT') ORDER BY sort_order ASC, created_at DESC"
        );
        $middleWideBanners = Database::fetchAll(
            "SELECT * FROM banners WHERE is_active = 1 AND banner_type = 'MIDDLE_WIDE' ORDER BY sort_order ASC, created_at DESC"
        );

        // 장바구니 수
        $cartCount = Cart::count();

        include APP_ROOT . '/views/main.php';
    }

    public static function search(array $params = []): void
    {
        $keyword = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset  = ($page - 1) * $perPage;

        $books = [];
        $total = 0;

        if ($keyword !== '') {
            $like = '%' . $keyword . '%';
            $total = (int)(Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM books
                 WHERE status != 'HIDDEN'
                   AND (title LIKE ? OR author LIKE ? OR summary LIKE ?)",
                [$like, $like, $like]
            )['cnt'] ?? 0);

            $books = Database::fetchAll(
                "SELECT id, book_code, title, author, price, original_price, cover_image, status
                 FROM books
                 WHERE status != 'HIDDEN'
                   AND (title LIKE ? OR author LIKE ? OR summary LIKE ?)
                 ORDER BY view_count DESC
                 LIMIT ? OFFSET ?",
                [$like, $like, $like, $perPage, $offset]
            );
        }

        $totalPages = (int)ceil($total / $perPage);
        $cartCount  = Cart::count();

        include APP_ROOT . '/views/search.php';
    }

    public static function board(array $params = []): void
    {
        $type = $params['type'] ?? 'notice';
        $allowedTypes = ['company', 'gallery', 'event', 'archive', 'inquiry', 'notice', 'press'];
        if (!in_array($type, $allowedTypes, true)) {
            http_response_code(404);
            include APP_ROOT . '/views/404.php';
            return;
        }

        // 회사소개(단일 페이지)인 경우 바로 회사소개 상세 글로 리디렉션 또는 렌더링
        if ($type === 'company') {
            $post = Database::fetchOne("SELECT * FROM posts WHERE type = 'company' LIMIT 1");
            if ($post) {
                $pageTitle = '회사소개';
                $cartCount = Cart::count();
                include APP_ROOT . '/views/board/detail.php';
                return;
            }
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = ($type === 'gallery') ? 12 : 15;
        $offset  = ($page - 1) * $perPage;

        $posts = Database::fetchAll(
            "SELECT id, title, content, author_name, view_count, file_path, created_at
             FROM posts
             WHERE type = ?
             ORDER BY created_at DESC, id DESC
             LIMIT ? OFFSET ?",
            [$type, $perPage, $offset]
        );

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS cnt FROM posts WHERE type = ?",
            [$type]
        )['cnt'] ?? 0);
        $totalPages = (int)ceil($total / $perPage);
        $cartCount  = Cart::count();

        include APP_ROOT . '/views/board/list.php';
    }

    public static function boardDetail(array $params = []): void
    {
        $type = $params['type'] ?? 'notice';
        $id   = (int)($params['id'] ?? 0);

        $post = Database::fetchOne(
            "SELECT * FROM posts WHERE id = ?",
            [$id]
        );

        if (!$post) {
            http_response_code(404);
            include APP_ROOT . '/views/404.php';
            return;
        }

        Database::execute(
            "UPDATE posts SET view_count = view_count + 1 WHERE id = ?",
            [$id]
        );
        $post['view_count']++;

        $cartCount = Cart::count();
        include APP_ROOT . '/views/board/detail.php';
    }
}
