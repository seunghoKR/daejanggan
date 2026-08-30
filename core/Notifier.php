<?php

declare(strict_types=1);

/**
 * Notifier - 대장간 통합 알림 서비스 모듈
 * 텔레그램 봇, 카카오 알림톡, SMS, 시스템 장애 경보(로컬 AI 연동 끊김 등)를 통합 처리합니다.
 */
class Notifier
{
    /**
     * 텔레그램 메시지 발송
     * @param string|int $chatId 수신자 Chat ID
     * @param string $messageHtml HTML 포맷 메시지
     * @param string|null $botToken 커스텀 봇 토큰 (없으면 site_settings 조회)
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendTelegram($chatId, string $messageHtml, ?string $botToken = null): array
    {
        if (empty($chatId)) {
            return ['success' => false, 'message' => 'Chat ID가 지정되지 않았습니다.'];
        }

        $botToken = $botToken ?: self::getSetting('telegram_bot_token', '');
        if (empty($botToken)) {
            return ['success' => false, 'message' => '텔레그램 봇 토큰(telegram_bot_token)이 설정되지 않았습니다.'];
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $payload = [
            'chat_id'                  => $chatId,
            'text'                     => $messageHtml,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => json_encode($payload),
            CURLOPT_HTTPHEADER        => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_CONNECTTIMEOUT_MS => 3000,
            CURLOPT_TIMEOUT_MS        => 6000,
            CURLOPT_SSL_VERIFYPEER    => true,
        ]);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $httpCode !== 200) {
            return [
                'success' => false,
                'message' => "텔레그램 발송 실패 (HTTP {$httpCode}): " . ($err ?: (string)$res)
            ];
        }

        $json = json_decode((string)$res, true);
        if (!empty($json['ok'])) {
            return ['success' => true, 'message' => '발송 성공'];
        }

        return [
            'success' => false,
            'message' => $json['description'] ?? '알 수 없는 텔레그램 API 오류'
        ];
    }

    /**
     * 관리자/개발자 전체에게 텔레그램 알림 발송
     * (콤마, 줄바꿈으로 구분된 모든 관리자 Chat ID 대상)
     */
    public static function sendAdminTelegram(string $messageHtml): array
    {
        $chatIdsRaw = self::getSetting('telegram_admin_chat_id', '');
        if (empty($chatIdsRaw)) {
            return ['success' => false, 'message' => '관리자 Chat ID가 설정되지 않았습니다.'];
        }

        $chatIds = array_values(array_filter(array_map('trim', preg_split('/[,\s\n]+/', $chatIdsRaw))));
        $results = [];

        foreach ($chatIds as $cid) {
            $results[$cid] = self::sendTelegram($cid, $messageHtml);
        }

        return ['success' => true, 'results' => $results];
    }

    /**
     * 🤖 로컬 AI 연동 끊김 장애 알림 (쓰로틀링 적용: 15분 내 중복 발송 방지)
     */
    public static function sendAiFailureAlert(string $endpoint, string $errorDetails = '', string $context = ''): bool
    {
        // AI 알림 사용 여부 체크
        $notifyEnabled = self::getSetting('telegram_notify_ai', '1');
        if ($notifyEnabled !== '1') {
            return false;
        }

        // 마지막 발송 시간 체크 (최근 15분 이내 발송 이력이 있으면 스킵)
        $lastAlertTime = (int)self::getSetting('last_ai_alert_time', '0');
        if (time() - $lastAlertTime < 900) {
            return false; // 15분 쿨다운
        }

        // 쿨다운 시간 갱신
        self::saveSetting('last_ai_alert_time', (string)time());

        $now = date('Y-m-d H:i:s');
        $siteName = self::getSetting('site_name', '도서출판 대장간');
        
        $msg = "🚨 <b>[{$siteName}] 로컬 AI 연동 장애 경보</b>\n\n"
             . "⚠️ <b>장애 내용:</b> 로컬 LM Studio AI 서버 응답 없음\n"
             . "🌐 <b>엔드포인트:</b> <code>{$endpoint}</code>\n"
             . "⏱️ <b>발생 시각:</b> {$now}\n";
        
        if (!empty($context)) {
            $msg .= "📍 <b>발생 위치:</b> {$context}\n";
        }
        if (!empty($errorDetails)) {
            $msg .= "🔍 <b>오류 상세:</b> <code>" . htmlspecialchars(mb_substr($errorDetails, 0, 200)) . "</code>\n";
        }

        $msg .= "\n💡 <i>도서 등록 등 핵심 기능은 룰 기반 정밀 파서로 자동 폴백(Fallback)되어 정상 동작 중입니다.</i>\n"
              . "👉 LM Studio 또는 로컬 호스트(49.170.204.109) 구동 상태를 확인해 주세요.";

        $res = self::sendAdminTelegram($msg);
        return $res['success'] ?? false;
    }

    /**
     * 🛒 신규 주문 발생 알림
     */
    public static function sendOrderAlert(array $order): bool
    {
        $notifyEnabled = self::getSetting('telegram_notify_order', '1');
        if ($notifyEnabled !== '1') return false;

        $orderNo = $order['order_no'] ?? '-';
        $orderer = $order['orderer_name'] ?? '고객';
        $amount  = number_format((int)($order['total_amount'] ?? 0));
        $payMethod = $order['pay_method'] ?? '신용카드';
        $siteName = self::getSetting('site_name', '도서출판 대장간');
        $now = date('Y-m-d H:i:s');

        $msg = "🛒 <b>[{$siteName}] 신규 주문 접수</b>\n\n"
             . "📦 <b>주문번호:</b> <code>{$orderNo}</code>\n"
             . "👤 <b>주문자:</b> {$orderer} 님\n"
             . "💰 <b>결제금액:</b> {$amount}원 ({$payMethod})\n"
             . "⏱️ <b>주문일시:</b> {$now}\n\n"
             . "👉 <a href=\"http://ndaejanggan.iwinv.net/admin/orders\">관리자 주문관리 바로가기</a>";

        $res = self::sendAdminTelegram($msg);
        return $res['success'] ?? false;
    }

    /**
     * 👤 신규 회원 가입 알림
     */
    public static function sendMemberAlert(array $user): bool
    {
        $notifyEnabled = self::getSetting('telegram_notify_member', '1');
        if ($notifyEnabled !== '1') return false;

        $username = $user['username'] ?? '-';
        $name     = $user['name'] ?? '-';
        $email    = $user['email'] ?? '-';
        $siteName = self::getSetting('site_name', '도서출판 대장간');
        $now = date('Y-m-d H:i:s');

        $msg = "👤 <b>[{$siteName}] 신규 회원 가입</b>\n\n"
             . "🆔 <b>아이디:</b> {$username}\n"
             . "✨ <b>이름:</b> {$name}\n"
             . "📧 <b>이메일:</b> {$email}\n"
             . "⏱️ <b>가입일시:</b> {$now}\n";

        $res = self::sendAdminTelegram($msg);
        return $res['success'] ?? false;
    }

    /**
     * 🤖 로컬 AI 서버 실시간 헬스 체크
     * @param string|null $url 엔드포인트 URL (기본값: http://49.170.204.109:1234/v1/models)
     * @return array ['is_online' => bool, 'latency_ms' => int, 'model' => string, 'error' => string]
     */
    public static function checkAiHealth(?string $url = null): array
    {
        $url = $url ?: 'http://49.170.204.109:1234/v1/models';
        $start = microtime(true);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_CONNECTTIMEOUT_MS => 1500,
            CURLOPT_TIMEOUT_MS        => 2500,
            CURLOPT_NOSIGNAL          => 1,
        ]);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $latency = (int)round((microtime(true) - $start) * 1000);

        if ($err || $httpCode !== 200) {
            return [
                'is_online'  => false,
                'latency_ms' => $latency,
                'model'      => '',
                'error'      => $err ?: "HTTP 상태 코드: {$httpCode}",
            ];
        }

        $json = json_decode((string)$res, true);
        $models = [];
        if (!empty($json['data']) && is_array($json['data'])) {
            foreach ($json['data'] as $m) {
                if (!empty($m['id'])) $models[] = $m['id'];
            }
        }

        return [
            'is_online'  => true,
            'latency_ms' => $latency,
            'model'      => !empty($models) ? implode(', ', $models) : 'google/gemma-4-e2b',
            'error'      => '',
        ];
    }

    /**
     * 💬 카카오 알림톡 연동 인터페이스 (향후 솔라피/알리고/비즈뿌리오 Provider 확장 구조)
     */
    public static function sendKakaoAlimtalk(string $phone, string $templateCode, array $params = []): array
    {
        // 카카오 알림톡 API 연동 구조 준비
        $restKey = self::getSetting('kakao_rest_key', '');
        if (empty($restKey)) {
            return ['success' => false, 'message' => '카카오 REST API 키가 설정되지 않았습니다.'];
        }

        // TODO: 알림톡 Provider(Solapi/Aligo) API 규격에 맞춰 페이로드 전송
        return ['success' => true, 'message' => '알림톡 발송 대기 (준비완료)'];
    }

    /**
     * 📱 SMS 문자 발송 인터페이스
     */
    public static function sendSms(string $phone, string $message): array
    {
        // SMS 발송 모듈 준비
        return ['success' => true, 'message' => 'SMS 발송 대기 (준비완료)'];
    }

    // ------------------------------------------------------------
    // 내부 헬퍼 (site_settings 안전 조회 및 저장)
    // ------------------------------------------------------------

    private static function getSetting(string $key, string $default = ''): string
    {
        if (isset($GLOBALS['site'][$key])) {
            return (string)$GLOBALS['site'][$key];
        }

        try {
            if (class_exists('Database')) {
                $row = Database::fetchOne("SELECT key_value FROM site_settings WHERE key_name = ?", [$key]);
                return $row['key_value'] ?? $default;
            }
        } catch (\Throwable $e) {}

        return $default;
    }

    private static function saveSetting(string $key, string $value): void
    {
        try {
            if (class_exists('Database')) {
                Database::execute(
                    "INSERT INTO site_settings (key_name, key_value) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)",
                    [$key, $value]
                );
                $GLOBALS['site'][$key] = $value;
            }
        } catch (\Throwable $e) {}
    }
}
