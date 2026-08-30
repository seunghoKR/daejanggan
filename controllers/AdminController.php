<?php

declare(strict_types=1);

final class AdminController
{
    private static function boot(): void
    {
        Auth::requireAdmin();
    }

    // ----------------------------------------------------------------
    // 대시보드 KPI
    // ----------------------------------------------------------------
    public static function dashboard(array $params = []): void
    {
        self::boot();

        $stats = [
            'today_orders'    => (int)(Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM orders WHERE DATE(created_at) = CURDATE()"
            )['cnt'] ?? 0),
            'waiting_payment' => (int)(Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM orders WHERE pay_status = 'WAITING'"
            )['cnt'] ?? 0),
            'preparing_ship'  => (int)(Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM orders WHERE delivery_status = 'PREPARING' AND pay_status = 'PAID'"
            )['cnt'] ?? 0),
            'total_books'     => (int)(Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM books WHERE status = 'SALE'"
            )['cnt'] ?? 0),
            'total_users'     => (int)(Database::fetchOne(
                "SELECT COUNT(*) AS cnt FROM users WHERE role = 'USER'"
            )['cnt'] ?? 0),
            'monthly_revenue' => (int)(Database::fetchOne(
                "SELECT COALESCE(SUM(total_pay_price),0) AS rev FROM orders
                 WHERE pay_status = 'PAID' AND DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m')"
            )['rev'] ?? 0),
        ];

        // 최근 주문 10건
        $recentOrders = Database::fetchAll(
            "SELECT order_no, orderer_name, total_pay_price, pay_status, delivery_status, created_at
             FROM orders ORDER BY created_at DESC LIMIT 10"
        );

        include APP_ROOT . '/views/admin/dashboard.php';
    }

    // ----------------------------------------------------------------
    // 도서 목록
    // ----------------------------------------------------------------
    public static function books(array $params = []): void
    {
        self::boot();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;
        $q       = trim($_GET['q'] ?? '');
        $status  = $_GET['status'] ?? '';

        $where  = ['1=1'];
        $bindParams = [];

        if ($q !== '') {
            $where[]      = '(b.title LIKE ? OR b.author LIKE ?)';
            $bindParams[] = "%$q%";
            $bindParams[] = "%$q%";
        }
        if (in_array($status, ['SALE','SOLDOUT','HIDDEN'], true)) {
            $where[]      = 'b.status = ?';
            $bindParams[] = $status;
        }

        $whereStr = implode(' AND ', $where);

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS cnt FROM books b WHERE $whereStr",
            $bindParams
        )['cnt'] ?? 0);

        $books = Database::fetchAll(
            "SELECT b.id, b.book_code, b.title, b.author, b.price, b.stock_qty, b.status,
                    b.is_new, b.is_best, b.created_at, c.name AS category_name
             FROM books b
             LEFT JOIN categories c ON c.id = b.category_id
             WHERE $whereStr
             ORDER BY b.created_at DESC LIMIT ? OFFSET ?",
            array_merge($bindParams, [$perPage, $offset])
        );

        $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY sort_order");
        $totalPages = (int)ceil($total / $perPage);

        include APP_ROOT . '/views/admin/books.php';
    }

    // ----------------------------------------------------------------
    // 도서 등록 폼
    // ----------------------------------------------------------------
    public static function bookCreate(array $params = []): void
    {
        self::boot();
        $categories = Database::fetchAll("SELECT id, code, parent_code, name, type FROM categories ORDER BY sort_order ASC, code ASC");
        $seriesList = Database::fetchAll("SELECT id, name FROM series ORDER BY sort_order ASC");
        include APP_ROOT . '/views/admin/book_form.php';
    }

    // ----------------------------------------------------------------
    // 도서 원고 AI 텍스트 파싱 API
    // ----------------------------------------------------------------
    public static function bookAiParse(array $params = []): void
    {
        self::boot();
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true);
        $rawText = $input['raw_text'] ?? ($_POST['raw_text'] ?? '');

        if (empty(trim((string)$rawText))) {
            echo json_encode(['success' => false, 'message' => '분석할 원고 텍스트를 입력해 주세요.']);
            exit;
        }

        $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY sort_order");
        $seriesList = Database::fetchAll("SELECT id, name FROM series ORDER BY sort_order");
        require_once APP_ROOT . '/core/AiBookParser.php';

        $parsed = AiBookParser::parse((string)$rawText, $categories, $seriesList);

        echo json_encode([
            'success' => true,
            'data'    => $parsed,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ----------------------------------------------------------------
    // 도서 이미지 비동기 업로드 API
    // ----------------------------------------------------------------
    public static function uploadBookImage(array $params = []): void
    {
        self::boot();
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_FILES['image'])) {
            echo json_encode(['success' => false, 'message' => '업로드할 이미지 파일이 없습니다.']);
            exit;
        }

        try {
            $uploader = new FileUploader('books', ALLOWED_IMG_EXT, 15 * 1024 * 1024, 1600, 1600);
            $url = $uploader->upload($_FILES['image']);
            echo json_encode([
                'success' => true,
                'url'     => $url,
            ], JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        exit;
    }

    // ----------------------------------------------------------------
    // 도서 등록 처리 (철저한 예외 처리 및 카테고리 자동 동기화)
    // ----------------------------------------------------------------
    public static function bookStore(array $params = []): void
    {
        self::boot();

        try {
            $title = trim($_POST['title'] ?? '');
            if (empty($title)) {
                throw new \InvalidArgumentException('도서명을 입력해 주세요.');
            }

            $coverImage = DEFAULT_BOOK_IMG;
            $detailImages = null;

            // 다중 이미지 드래그 업로드 목록 확인 (JSON 형태 전달)
            $imageListRaw = trim($_POST['image_list'] ?? '');
            if (!empty($imageListRaw)) {
                $imgs = json_decode($imageListRaw, true);
                if (is_array($imgs) && count($imgs) > 0) {
                    $validImgs = array_values(array_filter($imgs, fn($u) => !empty($u) && is_string($u)));
                    if (!empty($validImgs)) {
                        $coverImage = $validImgs[0];
                        $detailImages = json_encode($validImgs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    }
                }
            }

            // 단일 파일 업로드 폴백
            if ($coverImage === DEFAULT_BOOK_IMG && !empty($_FILES['cover_image']['name'])) {
                $uploader   = new FileUploader('books');
                $coverImage = $uploader->upload($_FILES['cover_image']);
                $detailImages = json_encode([$coverImage], JSON_UNESCAPED_SLASHES);
            }

            // 날짜 형식 정제 (YYYY-MM-DD)
            $publishDate = trim($_POST['publish_date'] ?? '');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishDate)) {
                $publishDate = null;
            }

            // 카테고리 정보 조회 (code 매칭용)
            $categoryId = (int)($_POST['category_id'] ?? 0);
            $cat = Database::fetchOne("SELECT id, code FROM categories WHERE id = ?", [$categoryId]);
            if (!$cat) {
                // 기본 첫 번째 카테고리로 매칭
                $cat = Database::fetchOne("SELECT id, code FROM categories ORDER BY id ASC LIMIT 1");
                $categoryId = (int)($cat['id'] ?? 1);
            }
            $catCode = $cat['code'] ?? '1040';

            // 시리즈 ID 유효성 체크
            $seriesId = !empty($_POST['series_id']) ? (int)$_POST['series_id'] : null;
            if ($seriesId !== null) {
                $serExists = Database::fetchOne("SELECT id FROM series WHERE id = ?", [$seriesId]);
                if (!$serExists) {
                    $seriesId = null;
                }
            }

            $bookCode = trim($_POST['book_code'] ?? '') ?: ('BK' . date('ymdHis'));
            $bookCode = mb_substr($bookCode, 0, 100);

            $isbn = trim($_POST['isbn'] ?? '');
            $isbn = !empty($isbn) ? mb_substr($isbn, 0, 100) : null;

            Database::execute(
                "INSERT INTO books
                 (book_code, category_id, category_codes, series_id, title, subtitle, author, translator,
                  publisher, publish_date, isbn, original_price, price, stock_qty,
                  summary, description, cover_image, detail_images, is_new, is_best, is_recommend, is_discount, status)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $bookCode,
                    $categoryId,
                    $catCode,
                    $seriesId,
                    mb_substr($title, 0, 255),
                    trim($_POST['subtitle']       ?? '') ? mb_substr(trim($_POST['subtitle']), 0, 255) : null,
                    mb_substr(trim($_POST['author'] ?? '도서출판 대장간'), 0, 150),
                    trim($_POST['translator']     ?? '') ? mb_substr(trim($_POST['translator']), 0, 150) : null,
                    mb_substr(trim($_POST['publisher'] ?? '도서출판 대장간') ?: '도서출판 대장간', 0, 150),
                    $publishDate,
                    $isbn,
                    max(0, (int)($_POST['original_price'] ?? 0)),
                    max(0, (int)($_POST['price']          ?? 0)),
                    max(0, (int)($_POST['stock_qty']      ?? 100)),
                    trim($_POST['summary']         ?? '') ?: null,
                    $_POST['description']          ?? null,
                    $coverImage,
                    $detailImages,
                    isset($_POST['is_new'])       ? 1 : 0,
                    isset($_POST['is_best'])      ? 1 : 0,
                    isset($_POST['is_recommend']) ? 1 : 0,
                    isset($_POST['is_discount'])  ? 1 : 0,
                    $_POST['status']              ?? 'SALE',
                ]
            );

            $newBookId = (int)Database::lastInsertId();

            // book_categories 테이블에 다중 카테고리 매핑 자동 등록
            if ($newBookId > 0 && !empty($catCode)) {
                Database::execute(
                    "INSERT INTO book_categories (book_id, category_code) VALUES (?, ?)",
                    [$newBookId, $catCode]
                );
            }

            $_SESSION['_flash_success'] = '도서가 성공적으로 등록되었습니다.';
            header('Location: /admin/books');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['_flash_error'] = '도서 등록 중 오류가 발생했습니다: ' . $e->getMessage();
            header('Location: /admin/books/create');
            exit;
        }
    }

    // ----------------------------------------------------------------
    // 도서 수정 폼
    // ----------------------------------------------------------------
    public static function bookEdit(array $params = []): void
    {
        self::boot();
        $bookId     = (int)($params['id'] ?? 0);
        $book       = Database::fetchOne("SELECT * FROM books WHERE id = ?", [$bookId]);
        if (!$book) { http_response_code(404); exit; }
        $categories = Database::fetchAll("SELECT id, code, parent_code, name, type FROM categories ORDER BY sort_order ASC, code ASC");
        $seriesList = Database::fetchAll("SELECT id, name FROM series ORDER BY sort_order ASC");
        include APP_ROOT . '/views/admin/book_form.php';
    }

    // ----------------------------------------------------------------
    // 도서 수정 처리 (철저한 예외 처리 및 카테고리 자동 동기화)
    // ----------------------------------------------------------------
    public static function bookUpdate(array $params = []): void
    {
        self::boot();
        $bookId = (int)($params['id'] ?? 0);
        $book   = Database::fetchOne("SELECT * FROM books WHERE id = ?", [$bookId]);
        if (!$book) {
            $_SESSION['_flash_error'] = '해당 도서를 찾을 수 없습니다.';
            header('Location: /admin/books');
            exit;
        }

        try {
            $title = trim($_POST['title'] ?? '');
            if (empty($title)) {
                throw new \InvalidArgumentException('도서명을 입력해 주세요.');
            }

            $coverImage = $book['cover_image'];
            $detailImages = $book['detail_images'];

            // 다중 이미지 드래그 업로드 목록 확인
            $imageListRaw = trim($_POST['image_list'] ?? '');
            if (!empty($imageListRaw)) {
                $imgs = json_decode($imageListRaw, true);
                if (is_array($imgs) && count($imgs) > 0) {
                    $validImgs = array_values(array_filter($imgs, fn($u) => !empty($u) && is_string($u)));
                    if (!empty($validImgs)) {
                        $coverImage = $validImgs[0];
                        $detailImages = json_encode($validImgs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    }
                }
            }

            // 단일 파일 업로드 폴백
            if (!empty($_FILES['cover_image']['name'])) {
                $uploader   = new FileUploader('books');
                $newImg     = $uploader->upload($_FILES['cover_image']);
                $coverImage = $newImg;
                $detailImages = json_encode([$newImg], JSON_UNESCAPED_SLASHES);
            }

            // 날짜 형식 정제
            $publishDate = trim($_POST['publish_date'] ?? '');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishDate)) {
                $publishDate = null;
            }

            // 카테고리 정보 조회
            $categoryId = (int)($_POST['category_id'] ?? $book['category_id']);
            $cat = Database::fetchOne("SELECT id, code FROM categories WHERE id = ?", [$categoryId]);
            $catCode = $cat['code'] ?? ($book['category_codes'] ?: '1040');

            // 시리즈 ID 유효성 체크
            $seriesId = !empty($_POST['series_id']) ? (int)$_POST['series_id'] : null;
            if ($seriesId !== null) {
                $serExists = Database::fetchOne("SELECT id FROM series WHERE id = ?", [$seriesId]);
                if (!$serExists) {
                    $seriesId = null;
                }
            }

            $isbn = trim($_POST['isbn'] ?? '');
            $isbn = !empty($isbn) ? mb_substr($isbn, 0, 100) : null;

            Database::execute(
                "UPDATE books SET
                    category_id    = ?,
                    category_codes = ?,
                    series_id      = ?,
                    title          = ?,
                    subtitle       = ?,
                    author         = ?,
                    translator     = ?,
                    publisher      = ?,
                    publish_date   = ?,
                    isbn           = ?,
                    original_price = ?,
                    price          = ?,
                    stock_qty      = ?,
                    summary        = ?,
                    description    = ?,
                    cover_image    = ?,
                    detail_images  = ?,
                    is_new         = ?,
                    is_best        = ?,
                    is_recommend   = ?,
                    is_discount    = ?,
                    status         = ?
                 WHERE id = ?",
                [
                    $categoryId,
                    $catCode,
                    $seriesId,
                    mb_substr($title, 0, 255),
                    trim($_POST['subtitle']       ?? '') ? mb_substr(trim($_POST['subtitle']), 0, 255) : null,
                    mb_substr(trim($_POST['author'] ?? '도서출판 대장간'), 0, 150),
                    trim($_POST['translator']     ?? '') ? mb_substr(trim($_POST['translator']), 0, 150) : null,
                    mb_substr(trim($_POST['publisher'] ?? '도서출판 대장간') ?: '도서출판 대장간', 0, 150),
                    $publishDate,
                    $isbn,
                    max(0, (int)($_POST['original_price'] ?? 0)),
                    max(0, (int)($_POST['price']          ?? 0)),
                    max(0, (int)($_POST['stock_qty']      ?? 100)),
                    trim($_POST['summary']         ?? '') ?: null,
                    $_POST['description']          ?? null,
                    $coverImage,
                    $detailImages,
                    isset($_POST['is_new'])       ? 1 : 0,
                    isset($_POST['is_best'])      ? 1 : 0,
                    isset($_POST['is_recommend']) ? 1 : 0,
                    isset($_POST['is_discount'])  ? 1 : 0,
                    $_POST['status']              ?? 'SALE',
                    $bookId,
                ]
            );

            // book_categories 매핑 업데이트
            if (!empty($catCode)) {
                $exists = Database::fetchOne(
                    "SELECT id FROM book_categories WHERE book_id = ? AND category_code = ?",
                    [$bookId, $catCode]
                );
                if (!$exists) {
                    Database::execute(
                        "INSERT INTO book_categories (book_id, category_code) VALUES (?, ?)",
                        [$bookId, $catCode]
                    );
                }
            }

            $_SESSION['_flash_success'] = '도서 정보가 성공적으로 수정되었습니다.';
            header('Location: /admin/books');
            exit;

        } catch (\Throwable $e) {
            $_SESSION['_flash_error'] = '도서 수정 중 오류가 발생했습니다: ' . $e->getMessage();
            header('Location: /admin/books/' . $bookId . '/edit');
            exit;
        }
    }

    // ----------------------------------------------------------------
    // 도서 삭제
    // ----------------------------------------------------------------
    public static function bookDelete(array $params = []): void
    {
        self::boot();
        $bookId = (int)($params['id'] ?? 0);
        Database::execute("DELETE FROM books WHERE id = ?", [$bookId]);
        echo json_encode(['success' => true]);
    }

    // ----------------------------------------------------------------
    // 도서 일괄 상태 변경
    // ----------------------------------------------------------------
    public static function bookBatch(array $params = []): void
    {
        self::boot();
        $ids    = array_map('intval', (array)($_POST['ids'] ?? []));
        $status = $_POST['status'] ?? 'SALE';
        if (empty($ids) || !in_array($status, ['SALE','SOLDOUT','HIDDEN'], true)) {
            echo json_encode(['error' => 'invalid']);
            return;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        Database::execute(
            "UPDATE books SET status = ? WHERE id IN ($placeholders)",
            array_merge([$status], $ids)
        );
        echo json_encode(['success' => true, 'updated' => count($ids)]);
    }

    // ----------------------------------------------------------------
    // 주문 목록
    // ----------------------------------------------------------------
    public static function orders(array $params = []): void
    {
        self::boot();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;
        $payStatus = $_GET['pay_status'] ?? '';
        $delStatus = $_GET['delivery_status'] ?? '';

        $where  = ['1=1'];
        $bind   = [];
        if (in_array($payStatus, ['WAITING','PAID','CANCELLED','REFUNDED'], true)) {
            $where[] = 'pay_status = ?'; $bind[] = $payStatus;
        }
        if (in_array($delStatus, ['PREPARING','SHIPPING','DELIVERED'], true)) {
            $where[] = 'delivery_status = ?'; $bind[] = $delStatus;
        }
        $whereStr = implode(' AND ', $where);

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS cnt FROM orders WHERE $whereStr", $bind
        )['cnt'] ?? 0);

        $orders = Database::fetchAll(
            "SELECT * FROM orders WHERE $whereStr ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($bind, [$perPage, $offset])
        );

        $totalPages = (int)ceil($total / $perPage);
        include APP_ROOT . '/views/admin/orders.php';
    }

    // ----------------------------------------------------------------
    // 주문 상태 변경
    // ----------------------------------------------------------------
    public static function orderStatus(array $params = []): void
    {
        self::boot();
        $orderId        = (int)($params['id'] ?? 0);
        $payStatus      = $_POST['pay_status']      ?? null;
        $deliveryStatus = $_POST['delivery_status'] ?? null;
        $tracking       = trim($_POST['tracking_number'] ?? '');

        $updates  = [];
        $bindings = [];

        if ($payStatus && in_array($payStatus, ['WAITING','PAID','CANCELLED','REFUNDED'], true)) {
            $updates[]  = 'pay_status = ?';
            $bindings[] = $payStatus;
        }
        if ($deliveryStatus && in_array($deliveryStatus, ['PREPARING','SHIPPING','DELIVERED'], true)) {
            $updates[]  = 'delivery_status = ?';
            $bindings[] = $deliveryStatus;
        }
        if ($tracking !== '') {
            $updates[]  = 'tracking_number = ?';
            $bindings[] = $tracking;
        }

        if (!empty($updates)) {
            $bindings[] = $orderId;
            Database::execute(
                "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?",
                $bindings
            );
        }

        echo json_encode(['success' => true]);
    }

    // ----------------------------------------------------------------
    // 회원 목록
    // ----------------------------------------------------------------
    public static function members(array $params = []): void
    {
        self::boot();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;
        $q       = trim($_GET['q'] ?? '');

        $where  = ['role = "USER"'];
        $bind   = [];
        if ($q !== '') {
            $where[] = '(username LIKE ? OR name LIKE ? OR email LIKE ?)';
            $like    = "%$q%";
            $bind    = [$like, $like, $like];
        }

        $whereStr = implode(' AND ', $where);
        $total    = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS cnt FROM users WHERE $whereStr", $bind
        )['cnt'] ?? 0);

        $members = Database::fetchAll(
            "SELECT id, username, name, email, phone, points, created_at, last_login
             FROM users WHERE $whereStr ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($bind, [$perPage, $offset])
        );

        $totalPages = (int)ceil($total / $perPage);
        include APP_ROOT . '/views/admin/members.php';
    }

    // ----------------------------------------------------------------
    // 적립금 조정
    // ----------------------------------------------------------------
    public static function adjustPoints(array $params = []): void
    {
        self::boot();
        $userId = (int)($params['id'] ?? 0);
        $amount = (int)($_POST['amount'] ?? 0);
        $note   = trim($_POST['note'] ?? '관리자 조정');

        Database::execute(
            "UPDATE users SET points = GREATEST(0, points + ?) WHERE id = ?",
            [$amount, $userId]
        );
        echo json_encode(['success' => true]);
    }

    // ----------------------------------------------------------------
    // 환경설정
    // ----------------------------------------------------------------
    public static function settings(array $params = []): void
    {
        self::boot();
        $settings = array_column(
            Database::fetchAll("SELECT key_name, key_value, description FROM site_settings"),
            null, 'key_name'
        );
        include APP_ROOT . '/views/admin/settings.php';
    }

    public static function saveSettings(array $params = []): void
    {
        self::boot();

        $allowed = ['site_name','ceo_name','biz_number','cs_phone','cs_hours',
                    'email','bank_account','address','shipping_fee','free_shipping_min',
                    'point_rate','kakao_map_key',
                    'inicis_mid','inicis_signkey','inicis_keypass','inicis_test',
                    'inicis_card_use','inicis_bank_use','inicis_vbank_use','inicis_kakaopay',
                    'telegram_bot_token','telegram_admin_chat_id',
                    'telegram_notify_ai','telegram_notify_order','telegram_notify_member','telegram_notify_inquiry',
                    'kakao_rest_key','kakao_admin_phone'];

        foreach ($allowed as $key) {
            $val = isset($_POST[$key]) ? trim((string)$_POST[$key]) : '0';
            if (str_starts_with($key, 'inicis_') && str_ends_with($key, '_use') && !isset($_POST[$key])) {
                $val = '0';
            }
            if (str_starts_with($key, 'telegram_notify_') && !isset($_POST[$key])) {
                $val = '0';
            }
            Database::execute(
                "INSERT INTO site_settings (key_name, key_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)",
                [$key, $val]
            );
        }

        // 전역 캐시 갱신
        $GLOBALS['site'] = array_column(
            Database::fetchAll("SELECT key_name, key_value FROM site_settings"),
            'key_value', 'key_name'
        );

        $_SESSION['_flash_success'] = '환경설정이 저장되었습니다.';
        header('Location: /admin/settings');
        exit;
    }

    // ----------------------------------------------------------------
    // 텔레그램 알림 테스트 API
    // ----------------------------------------------------------------
    public static function testTelegram(array $params = []): void
    {
        self::boot();
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $botToken = trim((string)($input['bot_token'] ?? ''));
        $chatId   = trim((string)($input['chat_id'] ?? ''));

        require_once APP_ROOT . '/core/Notifier.php';

        $siteName = $GLOBALS['site']['site_name'] ?? '도서출판 대장간';
        $now = date('Y-m-d H:i:s');
        $msg = "🎉 <b>[{$siteName}] 텔레그램 알림 연동 테스트 성공!</b>\n\n"
             . "대장간 쇼핑몰과 텔레그램 알림 봇이 정상적으로 연결되었습니다.\n"
             . "• 🤖 <b>로컬 AI 연동 장애 실시간 경보</b>\n"
             . "• 🛒 <b>신규 주문 및 결제 접수 알림</b>\n"
             . "• 👤 <b>신규 회원가입 알림</b>\n\n"
             . "⏱️ <i>테스트 시각: {$now}</i>";

        if (!empty($chatId)) {
            $res = Notifier::sendTelegram($chatId, $msg, $botToken ?: null);
        } else {
            $res = Notifier::sendAdminTelegram($msg);
        }

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ----------------------------------------------------------------
    // 로컬 AI 실시간 헬스 체크 API
    // ----------------------------------------------------------------
    public static function checkAiHealth(array $params = []): void
    {
        self::boot();
        header('Content-Type: application/json; charset=utf-8');

        require_once APP_ROOT . '/core/Notifier.php';
        $res = Notifier::checkAiHealth();

        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ----------------------------------------------------------------
    // 도서분류 관리
    // ----------------------------------------------------------------
    public static function categories(array $params = []): void
    {
        self::boot();

        $categories = Database::fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM books b WHERE b.category_id = c.id) AS book_count
             FROM categories c
             ORDER BY c.sort_order ASC, c.code ASC"
        );

        include APP_ROOT . '/views/admin/categories.php';
    }

    public static function categoryStore(array $params = []): void
    {
        self::boot();

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'TOPIC';
        $sort = (int)($_POST['sort_order'] ?? 0);

        if ($code === '' || $name === '') {
            $_SESSION['_flash_error'] = '코드와 도서분류명을 입력해 주세요.';
            header('Location: /admin/categories');
            exit;
        }

        try {
            Database::execute(
                "INSERT INTO categories (code, name, type, sort_order) VALUES (?, ?, ?, ?)",
                [$code, $name, $type, $sort]
            );
            $_SESSION['_flash_success'] = '도서분류가 추가되었습니다.';
        } catch (\Throwable $e) {
            $_SESSION['_flash_error'] = '중복된 코드이거나 등록 중 오류가 발생했습니다.';
        }

        header('Location: /admin/categories');
        exit;
    }

    public static function categoryUpdate(array $params = []): void
    {
        self::boot();
        $id   = (int)($params['id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'TOPIC';
        $sort = (int)($_POST['sort_order'] ?? 0);

        Database::execute(
            "UPDATE categories SET code = ?, name = ?, type = ?, sort_order = ? WHERE id = ?",
            [$code, $name, $type, $sort, $id]
        );

        $_SESSION['_flash_success'] = '도서분류가 수정되었습니다.';
        header('Location: /admin/categories');
        exit;
    }

    public static function categoryDelete(array $params = []): void
    {
        self::boot();
        $id = (int)($params['id'] ?? 0);

        $bookCount = (int)(Database::fetchOne("SELECT COUNT(*) AS cnt FROM books WHERE category_id = ?", [$id])['cnt'] ?? 0);
        if ($bookCount > 0) {
            echo json_encode(['error' => '해당 도서분류에 속한 도서가 있어 삭제할 수 없습니다.']);
            return;
        }

        Database::execute("DELETE FROM categories WHERE id = ?", [$id]);
        echo json_encode(['success' => true]);
    }

    // ----------------------------------------------------------------
    // 배너 관리 (포스터, 신간, 인기, 이벤트)
    // ----------------------------------------------------------------
    public static function banners(array $params = []): void
    {
        self::boot();

        $banners = Database::fetchAll(
            "SELECT * FROM banners ORDER BY sort_order ASC, created_at DESC"
        );

        include APP_ROOT . '/views/admin/banners.php';
    }

    public static function bannerCreate(array $params = []): void
    {
        self::boot();
        include APP_ROOT . '/views/admin/banner_form.php';
    }

    public static function bannerStore(array $params = []): void
    {
        self::boot();

        $imageUrl = DEFAULT_BOOK_IMG;
        if (!empty($_FILES['image']['name'])) {
            try {
                $uploader = new FileUploader('banners');
                $imageUrl = $uploader->upload($_FILES['image']);
            } catch (\RuntimeException $e) {
                $_SESSION['_flash_error'] = $e->getMessage();
                header('Location: /admin/banners/create');
                exit;
            }
        }

        Database::execute(
            "INSERT INTO banners (title, subtitle, banner_type, image_url, link_url, badge_text, size_memo, sort_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                trim($_POST['title'] ?? ''),
                trim($_POST['subtitle'] ?? '') ?: null,
                $_POST['banner_type'] ?? 'HERO_MAIN',
                $imageUrl,
                trim($_POST['link_url'] ?? '') ?: '#',
                trim($_POST['badge_text'] ?? '') ?: null,
                trim($_POST['size_memo'] ?? '') ?: null,
                (int)($_POST['sort_order'] ?? 0),
                isset($_POST['is_active']) ? 1 : 0
            ]
        );

        $_SESSION['_flash_success'] = '배너가 성공적으로 등록되었습니다.';
        header('Location: /admin/banners');
        exit;
    }

    public static function bannerEdit(array $params = []): void
    {
        self::boot();
        $id     = (int)($params['id'] ?? 0);
        $banner = Database::fetchOne("SELECT * FROM banners WHERE id = ?", [$id]);
        if (!$banner) { http_response_code(404); exit; }
        include APP_ROOT . '/views/admin/banner_form.php';
    }

    public static function bannerUpdate(array $params = []): void
    {
        self::boot();
        $id     = (int)($params['id'] ?? 0);
        $banner = Database::fetchOne("SELECT * FROM banners WHERE id = ?", [$id]);
        if (!$banner) { http_response_code(404); exit; }

        $imageUrl = $banner['image_url'];
        if (!empty($_FILES['image']['name'])) {
            try {
                $uploader = new FileUploader('banners');
                $newImg   = $uploader->upload($_FILES['image']);
                if ($imageUrl && $imageUrl !== DEFAULT_BOOK_IMG && !str_starts_with($imageUrl, 'http')) {
                    FileUploader::delete($imageUrl);
                }
                $imageUrl = $newImg;
            } catch (\RuntimeException $e) {
                $_SESSION['_flash_error'] = $e->getMessage();
                header('Location: /admin/banners/' . $id . '/edit');
                exit;
            }
        }

        Database::execute(
            "UPDATE banners SET title = ?, subtitle = ?, banner_type = ?, image_url = ?, link_url = ?, badge_text = ?, size_memo = ?, sort_order = ?, is_active = ? WHERE id = ?",
            [
                trim($_POST['title'] ?? ''),
                trim($_POST['subtitle'] ?? '') ?: null,
                $_POST['banner_type'] ?? 'HERO_MAIN',
                $imageUrl,
                trim($_POST['link_url'] ?? '') ?: '#',
                trim($_POST['badge_text'] ?? '') ?: null,
                trim($_POST['size_memo'] ?? '') ?: null,
                (int)($_POST['sort_order'] ?? 0),
                isset($_POST['is_active']) ? 1 : 0,
                $id
            ]
        );

        $_SESSION['_flash_success'] = '배너가 수정되었습니다.';
        header('Location: /admin/banners');
        exit;
    }

    public static function bannerDelete(array $params = []): void
    {
        self::boot();
        $id     = (int)($params['id'] ?? 0);
        $banner = Database::fetchOne("SELECT image_url FROM banners WHERE id = ?", [$id]);
        if ($banner && !empty($banner['image_url']) && !str_starts_with($banner['image_url'], 'http') && $banner['image_url'] !== DEFAULT_BOOK_IMG) {
            FileUploader::delete($banner['image_url']);
        }
        Database::execute("DELETE FROM banners WHERE id = ?", [$id]);
        echo json_encode(['success' => true]);
    }

    public static function bannerToggle(array $params = []): void
    {
        self::boot();
        $id = (int)($params['id'] ?? 0);
        Database::execute("UPDATE banners SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?", [$id]);
        echo json_encode(['success' => true]);
    }

    public static function bannerArchive(array $params = []): void
    {
        self::boot();

        // 1. 이윰 슬라이더 원본 배너
        $legacySliders = [];
        try {
            $legacySliders = Database::fetchAll(
                "SELECT ei_no, ei_title, ei_subtitle, ei_text, ei_link, ei_img, ei_regdt
                 FROM g5_eyoom_slider_item
                 WHERE ei_img IS NOT NULL AND ei_img != ''
                 ORDER BY ei_no DESC"
            );
        } catch (\Exception $e) {}

        // 2. 갤러리 및 이벤트 게시판 이미지
        $legacyGallery = [];
        try {
            $legacyGallery = Database::fetchAll(
                "SELECT f.bo_table, f.wr_id, f.bf_file, f.bf_source, f.bf_datetime,
                        COALESCE(g.wr_subject, e.wr_subject, '기획 이미지') AS title
                 FROM g5_board_file f
                 LEFT JOIN g5_write_gallery g ON g.wr_id = f.wr_id AND f.bo_table = 'gallery'
                 LEFT JOIN g5_write_event e ON e.wr_id = f.wr_id AND f.bo_table = 'event'
                 WHERE f.bf_file != ''
                 ORDER BY f.bf_datetime DESC
                 LIMIT 50"
            );
        } catch (\Exception $e) {}

        include APP_ROOT . '/views/admin/banner_archive.php';
    }
}
