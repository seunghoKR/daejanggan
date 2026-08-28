<?php

declare(strict_types=1);

final class BookController
{
    // ----------------------------------------------------------------
    // 도서 목록
    // ----------------------------------------------------------------
    public static function index(array $params = []): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset  = ($page - 1) * $perPage;
        $sort    = $_GET['sort'] ?? 'new';

        $orderBy = match($sort) {
            'price_asc'  => 'b.price ASC',
            'price_desc' => 'b.price DESC',
            'popular'    => 'b.view_count DESC',
            default      => 'b.created_at DESC',
        };

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS cnt FROM books b WHERE b.status = 'SALE'"
        )['cnt'] ?? 0);

        $books = Database::fetchAll(
            "SELECT b.id, b.book_code, b.title, b.author, b.price, b.original_price,
                    b.cover_image, b.is_new, b.is_best
             FROM books b
             WHERE b.status = 'SALE'
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $categories  = self::getCategories();
        $totalPages  = (int)ceil($total / $perPage);
        $cartCount   = Cart::count();

        include APP_ROOT . '/views/book/list.php';
    }

    // ----------------------------------------------------------------
    // 카테고리별 도서 목록 (영카트 ca_id 계층 접두사 매칭 지원)
    // ----------------------------------------------------------------
    public static function category(array $params = []): void
    {
        $code = $params['code'] ?? '';
        $category = Database::fetchOne(
            "SELECT * FROM categories WHERE code = ?", [$code]
        );

        if (!$category) {
            http_response_code(404);
            include APP_ROOT . '/views/404.php';
            return;
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset  = ($page - 1) * $perPage;
        $sort    = $_GET['sort'] ?? 'new';

        $orderBy = match($sort) {
            'price_asc'  => 'b.price ASC',
            'price_desc' => 'b.price DESC',
            'popular'    => 'b.view_count DESC',
            default      => 'b.created_at DESC',
        };

        // 하위 카테고리 및 다중 등록 카테고리까지 완벽 조회 (bc.category_code LIKE '104010%')
        $likeCode = $code . '%';

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(DISTINCT b.id) AS cnt
             FROM books b
             JOIN book_categories bc ON bc.book_id = b.id
             WHERE bc.category_code LIKE ? AND b.status = 'SALE'",
            [$likeCode]
        )['cnt'] ?? 0);

        $books = Database::fetchAll(
            "SELECT DISTINCT b.id, b.book_code, b.title, b.author, b.price, b.original_price,
                    b.cover_image, b.is_new, b.is_best
             FROM books b
             JOIN book_categories bc ON bc.book_id = b.id
             WHERE bc.category_code LIKE ? AND b.status = 'SALE'
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?",
            [$likeCode, $perPage, $offset]
        );

        $categories = self::getCategories();
        $totalPages = (int)ceil($total / $perPage);
        $cartCount  = Cart::count();

        // 3차 하위 카테고리 또는 동일 레벨 형제 카테고리 탐색 + 각 카테고리별 실시간 도서 권수 카운트
        $subCategories = Database::fetchAll(
            "SELECT c.*, (
                SELECT COUNT(DISTINCT b.id) 
                FROM books b 
                JOIN book_categories bc ON bc.book_id = b.id 
                WHERE bc.category_code LIKE CONCAT(c.code, '%') AND b.status = 'SALE'
            ) AS book_count
            FROM categories c 
            WHERE c.parent_code = ? 
            ORDER BY c.sort_order ASC, c.name ASC",
            [$category['code']]
        );

        $parentCategory = null;
        if (!empty($category['parent_code'])) {
            $parentCategory = Database::fetchOne(
                "SELECT * FROM categories WHERE code = ?",
                [$category['parent_code']]
            );
            if (empty($subCategories)) {
                // 현재 카테고리가 3차 분류인 경우 형제 3차 분류 목록 로드
                $subCategories = Database::fetchAll(
                    "SELECT c.*, (
                        SELECT COUNT(DISTINCT b.id) 
                        FROM books b 
                        JOIN book_categories bc ON bc.book_id = b.id 
                        WHERE bc.category_code LIKE CONCAT(c.code, '%') AND b.status = 'SALE'
                    ) AS book_count
                    FROM categories c 
                    WHERE c.parent_code = ? 
                    ORDER BY c.sort_order ASC, c.name ASC",
                    [$category['parent_code']]
                );
            }
        }

        include APP_ROOT . '/views/book/list.php';
    }

    // ----------------------------------------------------------------
    // 저자별 도서 탐색 (전체 저자 태그 + 선택된 저자 도서)
    // ----------------------------------------------------------------
    public static function authors(array $params = []): void
    {
        $selectedAuthor = trim($_GET['author'] ?? ($params['name'] ?? ''));
        if ($selectedAuthor !== '') {
            $selectedAuthor = urldecode($selectedAuthor);
        }

        // 전체 저자 및 도서 수 집계
        $authorsList = Database::fetchAll(
            "SELECT author, COUNT(*) AS book_count
             FROM books
             WHERE status = 'SALE' AND author IS NOT NULL AND author != ''
             GROUP BY author
             ORDER BY book_count DESC, author ASC"
        );

        // 선택된 저자가 없으면 첫 번째(가장 도서가 많은 저자)를 기본 선택
        if ($selectedAuthor === '' && !empty($authorsList)) {
            $selectedAuthor = $authorsList[0]['author'];
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset  = ($page - 1) * $perPage;
        $sort    = $_GET['sort'] ?? 'new';

        $orderBy = match($sort) {
            'price_asc'  => 'b.price ASC',
            'price_desc' => 'b.price DESC',
            'popular'    => 'b.view_count DESC',
            default      => 'b.created_at DESC',
        };

        $books = [];
        $total = 0;

        if ($selectedAuthor !== '') {
            $total = (int)(Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM books WHERE author = ? AND status = 'SALE'",
                [$selectedAuthor]
            )['cnt'] ?? 0);

            $books = Database::fetchAll(
                "SELECT b.id, b.book_code, b.title, b.author, b.price, b.original_price,
                        b.cover_image, b.is_new, b.is_best, b.summary
                 FROM books b
                 WHERE b.author = ? AND b.status = 'SALE'
                 ORDER BY {$orderBy}
                 LIMIT ? OFFSET ?",
                [$selectedAuthor, $perPage, $offset]
            );
        }

        $totalPages = (int)ceil($total / $perPage);
        $cartCount  = Cart::count();

        include APP_ROOT . '/views/book/authors.php';
    }

    // ----------------------------------------------------------------
    // 시리즈별 도서 목록
    // ----------------------------------------------------------------
    public static function series(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $series = Database::fetchOne("SELECT * FROM series WHERE id = ?", [$id]);

        if (!$series) {
            http_response_code(404);
            include APP_ROOT . '/views/404.php';
            return;
        }

        $books = Database::fetchAll(
            "SELECT id, book_code, title, author, price, original_price, cover_image, is_new, is_best
             FROM books WHERE series_id = ? AND status = 'SALE'
             ORDER BY publish_date ASC, created_at DESC",
            [$id]
        );

        $cartCount = Cart::count();
        include APP_ROOT . '/views/book/series.php';
    }

    // ----------------------------------------------------------------
    // 도서 상세
    // ----------------------------------------------------------------
    public static function detail(array $params = []): void
    {
        $code = $params['code'] ?? '';

        $book = Database::fetchOne(
            "SELECT b.*, c.name AS category_name, s.name AS series_name
             FROM books b
             LEFT JOIN categories c ON c.id = b.category_id
             LEFT JOIN series s     ON s.id = b.series_id
             WHERE b.book_code = ? AND b.status != 'HIDDEN'",
            [$code]
        );

        if (!$book) {
            http_response_code(404);
            include APP_ROOT . '/views/404.php';
            return;
        }

        // 조회수 증가
        Database::execute(
            "UPDATE books SET view_count = view_count + 1 WHERE id = ?",
            [(int)$book['id']]
        );

        // 리뷰 목록
        $reviews = Database::fetchAll(
            "SELECT r.*, u.name AS reviewer_name
             FROM book_reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.book_id = ?
             ORDER BY r.created_at DESC",
            [(int)$book['id']]
        );

        // 평균 평점
        $avgRating = (float)(Database::fetchOne(
            "SELECT AVG(rating) AS avg FROM book_reviews WHERE book_id = ?",
            [(int)$book['id']]
        )['avg'] ?? 5.0);

        // 같은 시리즈 다른 도서
        $relatedBooks = [];
        if ($book['series_id']) {
            $relatedBooks = Database::fetchAll(
                "SELECT id, book_code, title, cover_image, price
                 FROM books WHERE series_id = ? AND id != ? AND status = 'SALE'
                 LIMIT 4",
                [(int)$book['series_id'], (int)$book['id']]
            );
        }

        // 위시리스트 여부
        $isWishlisted = false;
        if (Auth::check()) {
            $wish = Database::fetchOne(
                "SELECT id FROM wishlists WHERE user_id = ? AND book_id = ?",
                [Auth::id(), (int)$book['id']]
            );
            $isWishlisted = (bool)$wish;
        }

        $cartCount = Cart::count();
        include APP_ROOT . '/views/book/detail.php';
    }

    // ----------------------------------------------------------------
    // 서평 등록
    // ----------------------------------------------------------------
    public static function addReview(array $params = []): void
    {
        Auth::requireLogin();

        $bookId  = (int)($params['id'] ?? 0);
        $title   = trim($_POST['title']   ?? '');
        $content = trim($_POST['content'] ?? '');
        $rating  = max(1, min(5, (int)($_POST['rating'] ?? 5)));

        if ($bookId === 0 || $title === '' || $content === '') {
            http_response_code(400);
            echo json_encode(['error' => '필수 항목을 입력해 주세요.']);
            return;
        }

        // 중복 리뷰 방지
        $existing = Database::fetchOne(
            "SELECT id FROM book_reviews WHERE book_id = ? AND user_id = ?",
            [$bookId, Auth::id()]
        );
        if ($existing) {
            echo json_encode(['error' => '이미 서평을 작성하셨습니다.']);
            return;
        }

        Database::execute(
            "INSERT INTO book_reviews (book_id, user_id, title, content, rating)
             VALUES (?, ?, ?, ?, ?)",
            [$bookId, Auth::id(), $title, $content, $rating]
        );

        $book = Database::fetchOne("SELECT book_code FROM books WHERE id = ?", [$bookId]);
        header('Location: /book/' . ($book['book_code'] ?? ''));
        exit;
    }

    // ----------------------------------------------------------------
    // 내부 — 카테고리 목록 (계층 구조)
    // ----------------------------------------------------------------
    private static function getCategories(): array
    {
        return Database::fetchAll(
            "SELECT id, code, name, type, sort_order FROM categories ORDER BY sort_order ASC"
        );
    }
}
