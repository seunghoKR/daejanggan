<?php

declare(strict_types=1);

/**
 * Captcha — 세션 기반 경량 산술 캡차
 *
 * 비회원 주문 조회 및 도서 문의 작성 시 스팸 차단용
 */
final class Captcha
{
    private const SESSION_KEY = '_captcha_answer';

    /**
     * 캡차 문제를 생성하고 HTML 문자열로 반환합니다.
     * 정답은 세션에 저장됩니다.
     *
     * @return string  예) "8 + 5 = ?"
     */
    public static function generate(): string
    {
        $a = random_int(1, 15);
        $b = random_int(1, 15);

        // 단순화: 항상 덧셈 (빼기는 음수 방지를 위해 큰 수 - 작은 수로만 사용)
        $ops = ['+', '-', '×'];
        $op  = $ops[array_rand($ops)];

        switch ($op) {
            case '+':
                $_SESSION[self::SESSION_KEY] = (string)($a + $b);
                break;
            case '-':
                [$a, $b] = $a >= $b ? [$a, $b] : [$b, $a]; // 음수 방지
                $_SESSION[self::SESSION_KEY] = (string)($a - $b);
                break;
            case '×':
                $a = random_int(1, 9);
                $b = random_int(1, 9);
                $_SESSION[self::SESSION_KEY] = (string)($a * $b);
                break;
        }

        return "{$a} {$op} {$b} = ?";
    }

    /**
     * 사용자가 입력한 답을 검증합니다.
     * 검증 후 세션에서 정답을 즉시 삭제합니다(재사용 방지).
     */
    public static function verify(string $answer): bool
    {
        $stored = $_SESSION[self::SESSION_KEY] ?? null;
        unset($_SESSION[self::SESSION_KEY]);

        if ($stored === null) {
            return false;
        }

        return hash_equals($stored, trim($answer));
    }

    /** 세션에 정답이 있는지 (폼 조작 여부 확인용) */
    public static function hasChallenge(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }
}
