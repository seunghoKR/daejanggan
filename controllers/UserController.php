<?php

declare(strict_types=1);

final class UserController
{
    // ----------------------------------------------------------------
    // 로그인 폼
    // ----------------------------------------------------------------
    public static function login(array $params = []): void
    {
        if (Auth::check()) {
            header('Location: /mypage');
            exit;
        }
        $cartCount = Cart::count();
        include APP_ROOT . '/views/user/login.php';
    }

    // ----------------------------------------------------------------
    // 로그인 처리
    // ----------------------------------------------------------------
    public static function doLogin(array $params = []): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (Auth::login($username, $password)) {
            Cart::syncToDb(); // 비회원 장바구니 → DB 병합
            $redirect = $_SESSION['_after_login'] ?? '/mypage';
            unset($_SESSION['_after_login']);
            header('Location: ' . $redirect);
        } else {
            $_SESSION['_flash_error'] = '아이디 또는 비밀번호가 올바르지 않습니다.';
            header('Location: /login');
        }
        exit;
    }

    // ----------------------------------------------------------------
    // 로그아웃
    // ----------------------------------------------------------------
    public static function logout(array $params = []): void
    {
        Auth::logout();
        header('Location: /');
        exit;
    }

    // ----------------------------------------------------------------
    // 회원가입 폼
    // ----------------------------------------------------------------
    public static function register(array $params = []): void
    {
        if (Auth::check()) {
            header('Location: /mypage');
            exit;
        }
        $cartCount = Cart::count();
        include APP_ROOT . '/views/user/register.php';
    }

    // ----------------------------------------------------------------
    // 회원가입 처리 (실명, 닉네임, 전화번호, 이메일, 주소, 알림 설정)
    // ----------------------------------------------------------------
    public static function doRegister(array $params = []): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $name     = trim($_POST['name'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '') ?: $name;
        $email    = trim($_POST['email'] ?? '');
        $phone    = preg_replace('/[^0-9-]/', '', $_POST['phone'] ?? '');
        $zipcode  = trim($_POST['zipcode'] ?? '');
        $address1 = trim($_POST['address1'] ?? '');
        $address2 = trim($_POST['address2'] ?? '');

        // 알림 설정
        $telegramId     = trim($_POST['telegram_id'] ?? '');
        $notifyKakao    = isset($_POST['notify_kakao']) ? 1 : 0;
        $notifyTelegram = !empty($telegramId) || isset($_POST['notify_telegram']) ? 1 : 0;
        $notifySms      = isset($_POST['notify_sms']) ? 1 : 0;
        $notifyEmail    = isset($_POST['notify_email']) ? 1 : 0;

        // 기본 검증
        $errors = [];
        if (strlen($username) < 4) {
            $errors[] = '아이디는 4자 이상이어야 합니다.';
        }
        if (strlen($password) < 8) {
            $errors[] = '비밀번호는 8자 이상이어야 합니다.';
        }
        if ($password !== ($_POST['password_confirm'] ?? '')) {
            $errors[] = '비밀번호 확인이 일치하지 않습니다.';
        }
        if (empty($name)) {
            $errors[] = '실명을 입력해 주세요.';
        }
        if (empty($phone)) {
            $errors[] = '휴대전화번호를 입력해 주세요.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '유효한 이메일 주소를 입력해 주세요.';
        }

        // 중복 확인
        if (empty($errors)) {
            $dup = Database::fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
            if ($dup) {
                $errors[] = '이미 사용 중인 아이디입니다.';
            }
            $dupEmail = Database::fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
            if ($dupEmail) {
                $errors[] = '이미 등록된 이메일입니다.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['_reg_errors'] = $errors;
            $_SESSION['_reg_input']  = compact('username', 'name', 'nickname', 'email', 'phone', 'zipcode', 'address1', 'address2', 'telegramId');
            header('Location: /register');
            exit;
        }

        $hash = password_hash($password, PASSWORD_ARGON2ID);
        Database::execute(
            "INSERT INTO users (username, password_hash, password_type, name, nickname, email, phone, zipcode, address1, address2, telegram_id, notify_kakao, notify_telegram, notify_sms, notify_email)
             VALUES (?, ?, 'ARGON2ID', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$username, $hash, $name, $nickname, $email, $phone, $zipcode, $address1, $address2, $telegramId, $notifyKakao, $notifyTelegram, $notifySms, $notifyEmail]
        );

        Auth::login($username, $password);
        Cart::syncToDb();

        // 관리자 텔레그램 신규 회원가입 알림 발송
        try {
            require_once APP_ROOT . '/core/Notifier.php';
            Notifier::sendMemberAlert([
                'username' => $username,
                'name'     => $name,
                'email'    => $email,
            ]);
        } catch (\Throwable $e) {}

        header('Location: /mypage?welcome=1');
        exit;
    }

    // ----------------------------------------------------------------
    // 마이페이지 홈 (회원정보 + 알림 설정)
    // ----------------------------------------------------------------
    public static function mypage(array $params = []): void
    {
        Auth::requireLogin();

        $user = Database::fetchOne(
            "SELECT id, username, name, nickname, email, phone, zipcode, address1, address2,
                    telegram_id, kakao_id, notify_kakao, notify_telegram, notify_sms, notify_email,
                    points, created_at, last_login
             FROM users WHERE id = ?",
            [Auth::id()]
        );

        // 최근 주문 5건
        $recentOrders = Database::fetchAll(
            "SELECT order_no, created_at, total_pay_price, pay_status, delivery_status
             FROM orders WHERE user_id = ?
             ORDER BY created_at DESC LIMIT 5",
            [Auth::id()]
        );

        $cartCount = Cart::count();
        include APP_ROOT . '/views/user/mypage.php';
    }

    // ----------------------------------------------------------------
    // 회원정보 수정
    // ----------------------------------------------------------------
    public static function updateProfile(array $params = []): void
    {
        Auth::requireLogin();

        $name     = trim($_POST['name'] ?? '');
        $nickname = trim($_POST['nickname'] ?? '') ?: $name;
        $phone    = preg_replace('/[^0-9-]/', '', $_POST['phone'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $zipcode  = trim($_POST['zipcode'] ?? '');
        $address1 = trim($_POST['address1'] ?? '');
        $address2 = trim($_POST['address2'] ?? '');
        $newPass  = $_POST['new_password'] ?? '';

        if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['_flash_error'] = '이름과 올바른 이메일을 입력해 주세요.';
            header('Location: /mypage');
            exit;
        }

        if (!empty($newPass)) {
            if (strlen($newPass) < 8) {
                $_SESSION['_flash_error'] = '새 비밀번호는 8자 이상이어야 합니다.';
                header('Location: /mypage');
                exit;
            }
            $hash = password_hash($newPass, PASSWORD_ARGON2ID);
            Database::execute(
                "UPDATE users SET name = ?, nickname = ?, phone = ?, email = ?, zipcode = ?, address1 = ?, address2 = ?, password_hash = ?, password_type = 'ARGON2ID' WHERE id = ?",
                [$name, $nickname, $phone, $email, $zipcode, $address1, $address2, $hash, Auth::id()]
            );
        } else {
            Database::execute(
                "UPDATE users SET name = ?, nickname = ?, phone = ?, email = ?, zipcode = ?, address1 = ?, address2 = ? WHERE id = ?",
                [$name, $nickname, $phone, $email, $zipcode, $address1, $address2, Auth::id()]
            );
        }

        $_SESSION['_flash_success'] = '회원 정보가 성공적으로 수정되었습니다.';
        header('Location: /mypage');
        exit;
    }

    // ----------------------------------------------------------------
    // 알림 및 SNS 연동 설정 저장 (AJAX/POST)
    // ----------------------------------------------------------------
    public static function updateNotify(array $params = []): void
    {
        Auth::requireLogin();

        $telegramId     = trim($_POST['telegram_id'] ?? '');
        $notifyKakao    = isset($_POST['notify_kakao']) ? 1 : 0;
        $notifyTelegram = !empty($telegramId) && isset($_POST['notify_telegram']) ? 1 : (isset($_POST['notify_telegram']) ? 1 : 0);
        $notifySms      = isset($_POST['notify_sms']) ? 1 : 0;
        $notifyEmail    = isset($_POST['notify_email']) ? 1 : 0;

        Database::execute(
            "UPDATE users SET telegram_id = ?, notify_kakao = ?, notify_telegram = ?, notify_sms = ?, notify_email = ? WHERE id = ?",
            [$telegramId, $notifyKakao, $notifyTelegram, $notifySms, $notifyEmail, Auth::id()]
        );

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => '알림 설정이 저장되었습니다.']);
            exit;
        }

        $_SESSION['_flash_success'] = '알림 및 연동 설정이 성공적으로 저장되었습니다.';
        header('Location: /mypage');
        exit;
    }

    // ----------------------------------------------------------------
    // 주문 내역
    // ----------------------------------------------------------------
    public static function orders(array $params = []): void
    {
        Auth::requireLogin();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $total = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?",
            [Auth::id()]
        )['cnt'] ?? 0);

        $orders = Database::fetchAll(
            "SELECT * FROM orders WHERE user_id = ?
             ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [Auth::id(), $perPage, $offset]
        );

        // 각 주문의 품목 로드
        foreach ($orders as &$order) {
            $order['items'] = Database::fetchAll(
                "SELECT * FROM order_items WHERE order_id = ?",
                [(int)$order['id']]
            );
        }

        $totalPages = (int)ceil($total / $perPage);
        $cartCount  = Cart::count();
        include APP_ROOT . '/views/user/orders.php';
    }

    // ----------------------------------------------------------------
    // 찜 목록
    // ----------------------------------------------------------------
    public static function wishlist(array $params = []): void
    {
        Auth::requireLogin();

        $wishlist = Database::fetchAll(
            "SELECT b.id, b.book_code, b.title, b.author, b.price, b.cover_image, b.status
             FROM wishlists w
             JOIN books b ON b.id = w.book_id
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC",
            [Auth::id()]
        );

        $cartCount = Cart::count();
        include APP_ROOT . '/views/user/wishlist.php';
    }

    // ----------------------------------------------------------------
    // 찜 추가/제거 (AJAX 토글)
    // ----------------------------------------------------------------
    public static function addWishlist(array $params = []): void
    {
        if (!Auth::check()) {
            echo json_encode(['error' => 'login_required']);
            return;
        }

        $bookId = (int)($_POST['book_id'] ?? 0);
        if ($bookId === 0) {
            echo json_encode(['error' => 'invalid']);
            return;
        }

        $existing = Database::fetchOne(
            "SELECT id FROM wishlists WHERE user_id = ? AND book_id = ?",
            [Auth::id(), $bookId]
        );

        if ($existing) {
            Database::execute(
                "DELETE FROM wishlists WHERE user_id = ? AND book_id = ?",
                [Auth::id(), $bookId]
            );
            echo json_encode(['action' => 'removed']);
        } else {
            Database::execute(
                "INSERT INTO wishlists (user_id, book_id) VALUES (?, ?)",
                [Auth::id(), $bookId]
            );
            echo json_encode(['action' => 'added']);
        }
    }
}
