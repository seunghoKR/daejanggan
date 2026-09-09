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

    public static function company(array $params = []): void
    {
        $companyIntro = $GLOBALS['site']['company_intro_html'] ?? '';
        if (empty($companyIntro)) {
            $companyIntro = Database::fetchOne("SELECT key_value FROM site_settings WHERE key_name = 'company_intro_html'")['key_value'] ?? '';
        }
        $cartCount = Cart::count();
        include APP_ROOT . '/views/community/company.php';
    }

    public static function inquiry(array $params = []): void
    {
        $cartCount = Cart::count();
        include APP_ROOT . '/views/community/inquiry.php';
    }

    public static function inquirySubmit(array $params = []): void
    {
        $name      = trim($_POST['name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $bookType  = trim($_POST['book_type'] ?? '단행본');
        $title     = trim($_POST['title'] ?? '');
        $pageCount = trim($_POST['page_count'] ?? '');
        $content   = trim($_POST['content'] ?? '');
        $captcha   = trim($_POST['captcha'] ?? '');

        if (empty($name) || empty($phone) || empty($email) || empty($title) || empty($content)) {
            $_SESSION['_flash_error'] = '필수 항목(* 표시)을 모두 입력해 주세요.';
            header('Location: /community/inquiry');
            exit;
        }

        // 캡챠 검증
        if (!Captcha::verify($captcha)) {
            $_SESSION['_flash_error'] = '스팸 방지 보안 퀴즈 정답이 올바르지 않습니다. 다시 입력해 주세요.';
            header('Location: /community/inquiry');
            exit;
        }

        // 파일 첨부 처리
        $filePath = null;
        $fileName = null;
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploader = new FileUploader('inquiries', ['hwp','hwpx','pdf','doc','docx','zip','txt','epub','ppt','pptx','jpg','png'], 30 * 1024 * 1024);
                $filePath = $uploader->uploadDocument($_FILES['attachment']);
                $fileName = $_FILES['attachment']['name'] ?? basename($filePath);
            } catch (\Throwable $e) {
                $_SESSION['_flash_error'] = '첨부파일 업로드 실패: ' . $e->getMessage();
                header('Location: /community/inquiry');
                exit;
            }
        }

        // DB 저장
        try {
            Database::execute(
                "INSERT INTO publication_inquiries (name, phone, email, book_type, title, page_count, content, file_path, file_name, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')",
                [$name, $phone, $email, $bookType, $title, $pageCount, $content, $filePath, $fileName]
            );

            // 텔레그램 알림 자동 발송
            require_once APP_ROOT . '/core/Notifier.php';
            Notifier::sendInquiryAlert([
                'name'       => $name,
                'phone'      => $phone,
                'email'      => $email,
                'book_type'  => $bookType,
                'title'      => $title,
                'page_count' => $pageCount,
                'file_path'  => $filePath,
            ]);

            $_SESSION['_flash_success'] = '출판 의뢰 문의가 성공적으로 접수되었습니다. 담당자가 확인 후 빠른 시일 내에 연락드리겠습니다.';
        } catch (\Throwable $e) {
            $_SESSION['_flash_error'] = '문의 접수 중 오류가 발생했습니다: ' . $e->getMessage();
        }

        header('Location: /community/inquiry');
        exit;
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

        // 회사소개 및 출판문의는 전용 핸들러로 라우팅
        if ($type === 'company') {
            self::company($params);
            return;
        }
        if ($type === 'inquiry') {
            self::inquiry($params);
            return;
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = ($type === 'gallery') ? 12 : 15;
        $offset  = ($page - 1) * $perPage;

        $posts = Database::fetchAll(
            "SELECT id, type, title, content, author_name, view_count, is_notice, is_secret, file_path, created_at
             FROM posts
             WHERE type = ?
             ORDER BY is_notice DESC, created_at DESC, id DESC
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
