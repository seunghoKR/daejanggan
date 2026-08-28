<?php

declare(strict_types=1);

/**
 * Cart — 세션(비회원) / DB(회원) 통합 장바구니
 *
 * 비회원: $_SESSION['_cart'] 에 배열로 저장
 * 회원: 세션 우선 조작, DB와 실시간 동기화
 */
final class Cart
{
    private const SESSION_KEY = '_cart';

    // ----------------------------------------------------------------
    // 아이템 추가
    // ----------------------------------------------------------------

    public static function add(int $bookId, int $qty = 1): void
    {
        $qty = max(1, $qty);

        if (Auth::check()) {
            // 회원: DB upsert
            Database::execute(
                "INSERT INTO cart_items (user_id, book_id, quantity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                [Auth::id(), $bookId, $qty, $qty]
            );
        } else {
            // 비회원: 세션
            $cart = $_SESSION[self::SESSION_KEY] ?? [];
            $cart[$bookId] = ($cart[$bookId] ?? 0) + $qty;
            $_SESSION[self::SESSION_KEY] = $cart;
        }
    }

    // ----------------------------------------------------------------
    // 수량 변경
    // ----------------------------------------------------------------

    public static function update(int $bookId, int $qty): void
    {
        $qty = max(1, $qty);

        if (Auth::check()) {
            Database::execute(
                "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND book_id = ?",
                [$qty, Auth::id(), $bookId]
            );
        } else {
            $cart = $_SESSION[self::SESSION_KEY] ?? [];
            if (isset($cart[$bookId])) {
                $cart[$bookId] = $qty;
                $_SESSION[self::SESSION_KEY] = $cart;
            }
        }
    }

    // ----------------------------------------------------------------
    // 아이템 제거
    // ----------------------------------------------------------------

    public static function remove(int $bookId): void
    {
        if (Auth::check()) {
            Database::execute(
                "DELETE FROM cart_items WHERE user_id = ? AND book_id = ?",
                [Auth::id(), $bookId]
            );
        } else {
            $cart = $_SESSION[self::SESSION_KEY] ?? [];
            unset($cart[$bookId]);
            $_SESSION[self::SESSION_KEY] = $cart;
        }
    }

    // ----------------------------------------------------------------
    // 장바구니 목록 조회 (도서 정보 포함)
    // ----------------------------------------------------------------

    public static function getItems(): array
    {
        if (Auth::check()) {
            return Database::fetchAll(
                "SELECT ci.book_id, ci.quantity, b.title, b.author, b.price, b.original_price,
                        b.cover_image, b.status, b.stock_qty
                 FROM cart_items ci
                 JOIN books b ON b.id = ci.book_id
                 WHERE ci.user_id = ?
                 ORDER BY ci.added_at DESC",
                [Auth::id()]
            );
        }

        $cart = $_SESSION[self::SESSION_KEY] ?? [];
        if (empty($cart)) {
            return [];
        }

        $ids = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $books = Database::fetchAll(
            "SELECT id AS book_id, title, author, price, original_price, cover_image, status, stock_qty
             FROM books WHERE id IN ($placeholders)",
            $ids
        );

        // 세션 수량 병합
        foreach ($books as &$book) {
            $book['quantity'] = $cart[$book['book_id']] ?? 1;
        }
        return $books;
    }

    // ----------------------------------------------------------------
    // 총 도서 금액
    // ----------------------------------------------------------------

    public static function getSubtotal(): int
    {
        $total = 0;
        foreach (self::getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    // ----------------------------------------------------------------
    // 배송비 계산 (3,000원; 30,000원 이상 무료)
    // ----------------------------------------------------------------

    public static function getShippingFee(): int
    {
        $settings  = self::loadSettings();
        $minFree   = (int)($settings['free_shipping_min'] ?? 30000);
        $fee       = (int)($settings['shipping_fee']      ?? 3000);
        $subtotal  = self::getSubtotal();

        if ($subtotal === 0) {
            return 0;
        }
        return $subtotal >= $minFree ? 0 : $fee;
    }

    /** 총 결제 금액 (포인트 차감 전) */
    public static function getTotal(): int
    {
        return self::getSubtotal() + self::getShippingFee();
    }

    /** 장바구니 비우기 */
    public static function clear(): void
    {
        if (Auth::check()) {
            Database::execute(
                "DELETE FROM cart_items WHERE user_id = ?",
                [Auth::id()]
            );
        }
        unset($_SESSION[self::SESSION_KEY]);
    }

    /** 장바구니 품목 수 (뱃지용) */
    public static function count(): int
    {
        if (Auth::check()) {
            $row = Database::fetchOne(
                "SELECT COALESCE(SUM(quantity),0) AS cnt FROM cart_items WHERE user_id = ?",
                [Auth::id()]
            );
            return (int)($row['cnt'] ?? 0);
        }
        return (int)array_sum($_SESSION[self::SESSION_KEY] ?? []);
    }

    // ----------------------------------------------------------------
    // 로그인 시 세션 → DB 동기화
    // ----------------------------------------------------------------

    public static function syncToDb(): void
    {
        if (!Auth::check()) {
            return;
        }
        $cart = $_SESSION[self::SESSION_KEY] ?? [];
        foreach ($cart as $bookId => $qty) {
            Database::execute(
                "INSERT INTO cart_items (user_id, book_id, quantity)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + ?",
                [Auth::id(), (int)$bookId, (int)$qty, (int)$qty]
            );
        }
        unset($_SESSION[self::SESSION_KEY]);
    }

    // ----------------------------------------------------------------
    // 내부 — site_settings 캐시
    // ----------------------------------------------------------------

    private static ?array $settings = null;

    private static function loadSettings(): array
    {
        if (self::$settings === null) {
            $rows = Database::fetchAll("SELECT key_name, key_value FROM site_settings");
            self::$settings = array_column($rows, 'key_value', 'key_name');
        }
        return self::$settings;
    }
}
