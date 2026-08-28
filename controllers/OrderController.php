<?php

declare(strict_types=1);

require_once APP_ROOT . '/core/InicisPayment.php';

final class OrderController
{
    // ----------------------------------------------------------------
    // 장바구니 뷰
    // ----------------------------------------------------------------
    public static function cart(array $params = []): void
    {
        $items      = Cart::getItems();
        $subtotal   = Cart::getSubtotal();
        $shipping   = Cart::getShippingFee();
        $total      = $subtotal + $shipping;
        $cartCount  = Cart::count();

        include APP_ROOT . '/views/order/cart.php';
    }

    // ----------------------------------------------------------------
    // 장바구니 담기 (AJAX)
    // ----------------------------------------------------------------
    public static function addToCart(array $params = []): void
    {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $qty    = max(1, (int)($_POST['qty'] ?? 1));

        if ($bookId === 0) {
            http_response_code(400);
            echo json_encode(['error' => '유효하지 않은 도서입니다.']);
            return;
        }

        // 재고 확인
        $book = Database::fetchOne(
            "SELECT id, title, stock_qty, status FROM books WHERE id = ?", [$bookId]
        );
        if (!$book || $book['status'] === 'HIDDEN' || $book['stock_qty'] < $qty) {
            echo json_encode(['error' => '품절 또는 판매 중단된 도서입니다.']);
            return;
        }

        Cart::add($bookId, $qty);
        echo json_encode(['success' => true, 'cart_count' => Cart::count()]);
    }

    // ----------------------------------------------------------------
    // 장바구니 수량 수정 (AJAX)
    // ----------------------------------------------------------------
    public static function updateCart(array $params = []): void
    {
        $bookId = (int)($_POST['book_id'] ?? 0);
        $qty    = max(1, (int)($_POST['qty'] ?? 1));
        Cart::update($bookId, $qty);

        $subtotal = Cart::getSubtotal();
        $shipping = Cart::getShippingFee();
        echo json_encode([
            'success'  => true,
            'subtotal' => number_format($subtotal),
            'shipping' => number_format($shipping),
            'total'    => number_format($subtotal + $shipping),
        ]);
    }

    // ----------------------------------------------------------------
    // 장바구니 삭제 (AJAX)
    // ----------------------------------------------------------------
    public static function removeFromCart(array $params = []): void
    {
        $bookId = (int)($_POST['book_id'] ?? 0);
        Cart::remove($bookId);
        echo json_encode(['success' => true, 'cart_count' => Cart::count()]);
    }

    // ----------------------------------------------------------------
    // 주문서 작성 페이지
    // ----------------------------------------------------------------
    public static function checkout(array $params = []): void
    {
        $items = Cart::getItems();
        if (empty($items)) {
            header('Location: /cart');
            exit;
        }

        $user = Auth::check() ? Database::fetchOne("SELECT * FROM users WHERE id = ?", [Auth::id()]) : null;
        $subtotal = Cart::getSubtotal();
        $shipping = Cart::getShippingFee();
        $total    = $subtotal + $shipping;
        $site     = $GLOBALS['site'] ?? [];

        // 상품 대표명 생성 (예: '예수의 정치학 외 2권')
        $firstItem = reset($items);
        $itemCount = count($items);
        $goodName  = $firstItem['title'];
        if ($itemCount > 1) {
            $goodName .= ' 외 ' . ($itemCount - 1) . '건';
        }

        // KG이니시스 결제 사전 파라미터 준비
        $inicisMid       = InicisPayment::getMid();
        $inicisMKey      = InicisPayment::makeMKey();
        $inicisTimestamp = (string)(time() * 1000);
        $inicisOid       = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $inicisSignature = InicisPayment::makeSignature($inicisOid, $total, $inicisTimestamp);

        include APP_ROOT . '/views/order/checkout.php';
    }

    // ----------------------------------------------------------------
    // 주문 처리 (무통장 입금 또는 PG 사전 저장)
    // ----------------------------------------------------------------
    public static function place(array $params = []): void
    {
        $items = Cart::getItems();
        if (empty($items)) {
            header('Location: /cart');
            exit;
        }

        $payMethod = $_POST['pay_method'] ?? 'BANK';
        $orderNo   = trim($_POST['order_no'] ?? '');
        if ($orderNo === '') {
            $orderNo = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        }

        $usedPoints = 0;
        if (Auth::check()) {
            $userPoints = (int)(Database::fetchOne("SELECT points FROM users WHERE id = ?", [Auth::id()])['points'] ?? 0);
            $usedPoints = min((int)($_POST['used_points'] ?? 0), $userPoints);
        }

        $subtotal = Cart::getSubtotal();
        $shipping = Cart::getShippingFee();
        $total    = max(0, $subtotal + $shipping - $usedPoints);

        // 전자결제(CARD, TRANS, VBANK, EASYPAY)인 경우 세션에 임시 주문 데이터 저장
        $_SESSION['_pending_order'] = [
            'order_no'          => $orderNo,
            'user_id'           => Auth::id(),
            'orderer_name'      => htmlspecialchars($_POST['orderer_name'] ?? ''),
            'orderer_phone'     => htmlspecialchars($_POST['orderer_phone'] ?? ''),
            'orderer_email'     => htmlspecialchars($_POST['orderer_email'] ?? ''),
            'receiver_name'     => htmlspecialchars($_POST['receiver_name'] ?? ''),
            'receiver_phone'    => htmlspecialchars($_POST['receiver_phone'] ?? ''),
            'shipping_zipcode'  => htmlspecialchars($_POST['shipping_zipcode'] ?? ''),
            'shipping_address1' => htmlspecialchars($_POST['shipping_address1'] ?? ''),
            'shipping_address2' => htmlspecialchars($_POST['shipping_address2'] ?? ''),
            'shipping_memo'     => htmlspecialchars($_POST['shipping_memo'] ?? ''),
            'subtotal'          => $subtotal,
            'shipping'          => $shipping,
            'used_points'       => $usedPoints,
            'total'             => $total,
            'pay_method'        => $payMethod,
            'bank_depositor'    => htmlspecialchars($_POST['bank_depositor'] ?? ''),
            'items'             => $items
        ];

        // 무통장 입금인 경우 즉시 주문 DB 생성
        if ($payMethod === 'BANK') {
            $orderId = self::saveOrderToDb($_SESSION['_pending_order'], 'WAITING', 'BANK');
            Cart::clear();
            unset($_SESSION['_pending_order']);
            header('Location: /order/complete/' . urlencode($orderNo));
            exit;
        }

        // PG 결제는 JS SDK가 처리하므로 정상 플로우에서는 return URL로 진입
        echo json_encode(['success' => true, 'order_no' => $orderNo]);
    }

    // ----------------------------------------------------------------
    // KG이니시스 결제 인증 리턴 (Return URL)
    // ----------------------------------------------------------------
    public static function inicisReturn(array $params = []): void
    {
        $resultCode   = $_POST['resultCode'] ?? '';
        $resultMsg    = $_POST['resultMsg'] ?? '';
        $authToken    = $_POST['authToken'] ?? '';
        $authUrl      = $_POST['authUrl'] ?? '';
        $netCancelUrl = $_POST['netCancelUrl'] ?? '';
        $mid          = $_POST['mid'] ?? '';

        // 1. 인증 실패 시
        if ($resultCode !== '0000' || empty($authToken) || empty($authUrl)) {
            $_SESSION['_flash_error'] = '결제 인증이 실패 또는 취소되었습니다: ' . $resultMsg;
            header('Location: /order/checkout');
            exit;
        }

        // 2. 이니시스 승인 요청
        $timestamp = (string)(time() * 1000);
        $authRes   = InicisPayment::requestAuth($authUrl, $authToken, $timestamp);

        if (($authRes['resultCode'] ?? '') !== '0000') {
            // 망취소 시도
            if (!empty($netCancelUrl)) {
                InicisPayment::requestNetCancel($netCancelUrl, $authToken, $timestamp);
            }
            $_SESSION['_flash_error'] = '결제 승인 실패: ' . ($authRes['resultMsg'] ?? '알 수 없는 오류');
            header('Location: /order/checkout');
            exit;
        }

        // 3. 승인 성공 -> 주문 생성 및 확정
        $pending = $_SESSION['_pending_order'] ?? null;
        if (!$pending) {
            // 세션 유실 대비 승인 파라미터에서 정보 복원
            $pending = [
                'order_no'          => $authRes['MOID'] ?? ('ORD-' . date('Ymd-His')),
                'user_id'           => Auth::id(),
                'orderer_name'      => $authRes['buyerName'] ?? '고객',
                'orderer_phone'     => $authRes['buyerTel'] ?? '',
                'orderer_email'     => $authRes['buyerEmail'] ?? '',
                'receiver_name'     => $authRes['buyerName'] ?? '고객',
                'receiver_phone'    => $authRes['buyerTel'] ?? '',
                'shipping_zipcode'  => '',
                'shipping_address1' => '',
                'shipping_address2' => '',
                'shipping_memo'     => '',
                'subtotal'          => (int)($authRes['TotPrice'] ?? 0),
                'shipping'          => 0,
                'used_points'       => 0,
                'total'             => (int)($authRes['TotPrice'] ?? 0),
                'pay_method'        => 'CARD',
                'items'             => Cart::getItems()
            ];
        }

        // 결제 상태 결정 (가상계좌(VBANK)는 WAITING, 카드/이체/간편결제는 PAID)
        $payMethod = match($authRes['payMethod'] ?? '') {
            'VBank'     => 'VBANK',
            'DirectBank'=> 'TRANS',
            'Kakaopay'  => 'EASYPAY',
            default     => 'CARD',
        };
        $payStatus = ($payMethod === 'VBANK') ? 'WAITING' : 'PAID';

        $orderId = self::saveOrderToDb($pending, $payStatus, $payMethod, [
            'pg_tid'         => $authRes['tid'] ?? '',
            'pg_app_no'      => $authRes['applNum'] ?? '',
            'pg_result_code' => $authRes['resultCode'] ?? '',
            'pg_result_msg'  => $authRes['resultMsg'] ?? '',
            'vbank_name'     => $authRes['vactBankName'] ?? null,
            'vbank_num'      => $authRes['vactNum'] ?? null,
            'vbank_holder'   => $authRes['vactName'] ?? null,
            'vbank_date'     => !empty($authRes['vactDate']) ? date('Y-m-d H:i:s', strtotime($authRes['vactDate'] . ' ' . ($authRes['vactTime'] ?? '235959'))) : null,
        ]);

        // 장바구니 비우기
        Cart::clear();
        unset($_SESSION['_pending_order']);

        header('Location: /order/complete/' . urlencode($pending['order_no']));
        exit;
    }

    // ----------------------------------------------------------------
    // KG이니시스 결제창 닫힘 핸들러 (Close URL)
    // ----------------------------------------------------------------
    public static function inicisClose(array $params = []): void
    {
        echo "<script>window.close(); if(opener) { opener.location.href='/order/checkout'; }</script>";
        exit;
    }

    // ----------------------------------------------------------------
    // 주문 완료 페이지
    // ----------------------------------------------------------------
    public static function complete(array $params = []): void
    {
        $orderNo = $params['no'] ?? '';
        $order   = Database::fetchOne(
            "SELECT * FROM orders WHERE order_no = ?", [$orderNo]
        );

        if (!$order) {
            http_response_code(404);
            include APP_ROOT . '/views/404.php';
            return;
        }

        $orderItems = Database::fetchAll(
            "SELECT * FROM order_items WHERE order_id = ?", [(int)$order['id']]
        );

        $site = $GLOBALS['site'] ?? [];
        include APP_ROOT . '/views/order/complete.php';
    }

    // ----------------------------------------------------------------
    // 비회원 주문 조회 폼
    // ----------------------------------------------------------------
    public static function lookup(array $params = []): void
    {
        $captchaQuestion = Captcha::generate();
        $order           = null;
        $orderItems      = [];
        $notFound        = false;

        include APP_ROOT . '/views/order/lookup.php';
    }

    // ----------------------------------------------------------------
    // 비회원 주문 조회 처리
    // ----------------------------------------------------------------
    public static function doLookup(array $params = []): void
    {
        $orderNo = trim($_POST['order_no'] ?? '');
        $phone   = trim($_POST['orderer_phone'] ?? '');
        $captcha = trim($_POST['captcha'] ?? '');

        if (!Captcha::verify($captcha)) {
            $captchaQuestion = Captcha::generate();
            $notFound        = false;
            $order           = null;
            $orderItems      = [];
            $error           = '보안 문자(캡차) 정답이 일치하지 않습니다.';
            include APP_ROOT . '/views/order/lookup.php';
            return;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $order = Database::fetchOne(
            "SELECT * FROM orders
             WHERE order_no = ? AND REPLACE(orderer_phone, '-', '') = ?",
            [$orderNo, $cleanPhone]
        );

        $orderItems      = $order ? Database::fetchAll("SELECT * FROM order_items WHERE order_id = ?", [(int)$order['id']]) : [];
        $notFound        = !$order;
        $captchaQuestion = Captcha::generate();

        include APP_ROOT . '/views/order/lookup.php';
    }

    // ----------------------------------------------------------------
    // 내부 — 주문 데이터베이스 저장 트랜잭션 헬퍼
    // ----------------------------------------------------------------
    private static function saveOrderToDb(array $orderData, string $payStatus, string $payMethod, array $pgInfo = []): int
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            Database::execute(
                "INSERT INTO orders
                 (order_no, user_id, orderer_name, orderer_phone, orderer_email,
                  receiver_name, receiver_phone, shipping_zipcode, shipping_address1,
                  shipping_address2, shipping_memo, total_books_price, shipping_fee,
                  used_points, total_pay_price, pay_method, bank_depositor, pay_status,
                  pg_tid, pg_app_no, pg_result_code, pg_result_msg, vbank_name, vbank_num, vbank_holder, vbank_date)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $orderData['order_no'],
                    $orderData['user_id'],
                    $orderData['orderer_name'],
                    $orderData['orderer_phone'],
                    $orderData['orderer_email'],
                    $orderData['receiver_name'],
                    $orderData['receiver_phone'],
                    $orderData['shipping_zipcode'],
                    $orderData['shipping_address1'],
                    $orderData['shipping_address2'],
                    $orderData['shipping_memo'],
                    $orderData['subtotal'],
                    $orderData['shipping'],
                    $orderData['used_points'],
                    $orderData['total'],
                    $payMethod,
                    $orderData['bank_depositor'] ?? null,
                    $payStatus,
                    $pgInfo['pg_tid'] ?? null,
                    $pgInfo['pg_app_no'] ?? null,
                    $pgInfo['pg_result_code'] ?? null,
                    $pgInfo['pg_result_msg'] ?? null,
                    $pgInfo['vbank_name'] ?? null,
                    $pgInfo['vbank_num'] ?? null,
                    $pgInfo['vbank_holder'] ?? null,
                    $pgInfo['vbank_date'] ?? null
                ]
            );

            $orderId = (int)Database::lastInsertId();

            // 주문 품목 INSERT & 재고 차감
            foreach ($orderData['items'] as $item) {
                Database::execute(
                    "INSERT INTO order_items (order_id, book_id, book_title, price, quantity, total_price)
                     VALUES (?,?,?,?,?,?)",
                    [
                        $orderId,
                        (int)$item['book_id'],
                        $item['title'],
                        (int)$item['price'],
                        (int)$item['quantity'],
                        (int)$item['price'] * (int)$item['quantity'],
                    ]
                );

                // 재고 차감
                Database::execute(
                    "UPDATE books SET stock_qty = GREATEST(0, stock_qty - ?) WHERE id = ?",
                    [(int)$item['quantity'], (int)$item['book_id']]
                );
            }

            // 적립금 차감
            if (!empty($orderData['used_points']) && !empty($orderData['user_id'])) {
                Database::execute(
                    "UPDATE users SET points = GREATEST(0, points - ?) WHERE id = ?",
                    [(int)$orderData['used_points'], (int)$orderData['user_id']]
                );
            }

            // 결제 완료 시 적립금 지급 (설정된 point_rate %)
            if ($payStatus === 'PAID' && !empty($orderData['user_id'])) {
                $pointRate = (int)($GLOBALS['site']['point_rate'] ?? 5);
                $earnPoints = (int)round($orderData['subtotal'] * $pointRate / 100);
                if ($earnPoints > 0) {
                    Database::execute(
                        "UPDATE users SET points = points + ? WHERE id = ?",
                        [$earnPoints, (int)$orderData['user_id']]
                    );
                }
            }

            $pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
