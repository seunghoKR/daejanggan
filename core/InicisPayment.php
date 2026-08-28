<?php

declare(strict_types=1);

/**
 * KG이니시스(KG Inicis) 웹표준 결제 처리 모듈
 */
final class InicisPayment
{
    /** 상점아이디 (MID) */
    public static function getMid(): string
    {
        $site = $GLOBALS['site'] ?? [];
        $isTest = (int)($site['inicis_test'] ?? 0);
        if ($isTest === 1) {
            return 'INIpayTest'; // 이니시스 공용 테스트 MID
        }
        return trim($site['inicis_mid'] ?? 'SIRdjgnbks');
    }

    /** 웹결제 사인키 (SignKey) */
    public static function getSignKey(): string
    {
        $site = $GLOBALS['site'] ?? [];
        $isTest = (int)($site['inicis_test'] ?? 0);
        if ($isTest === 1) {
            return 'SU5JTElURV9UU1RLRVlfU1VNTE1URV9LRVk='; // 테스트용 SignKey
        }
        return trim($site['inicis_signkey'] ?? 'NHRLWnM0bGFXTIRnbU1uRENmL29vdz0=');
    }

    /** mKey 생성: sha256(SignKey) */
    public static function makeMKey(string $signKey = ''): string
    {
        if ($signKey === '') {
            $signKey = self::getSignKey();
        }
        return hash('sha256', $signKey);
    }

    /** 결제 요청 signature 생성: sha256("oid={oid}&price={price}&timestamp={timestamp}") */
    public static function makeSignature(string $oid, int|string $price, string $timestamp): string
    {
        $params = "oid={$oid}&price={$price}&timestamp={$timestamp}";
        return hash('sha256', $params);
    }

    /** 승인 요청 signature 생성: sha256("authToken={authToken}&timestamp={timestamp}") */
    public static function makeAuthSignature(string $authToken, string $timestamp): string
    {
        $params = "authToken={$authToken}&timestamp={$timestamp}";
        return hash('sha256', $params);
    }

    /**
     * 이니시스 승인 서버로 HTTP POST 요청
     * @param string $authUrl       이니시스 승인 URL
     * @param string $authToken     인증 토큰
     * @param string $timestamp     타임스탬프
     * @return array
     */
    public static function requestAuth(string $authUrl, string $authToken, string $timestamp): array
    {
        $mid       = self::getMid();
        $signature = self::makeAuthSignature($authToken, $timestamp);

        $postData = http_build_query([
            'mid'       => $mid,
            'authToken' => $authToken,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'charset'   => 'UTF-8',
            'format'    => 'JSON'
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $authUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return [
                'resultCode' => 'FAIL',
                'resultMsg'  => '승인 통신 오류: ' . ($error ?: "HTTP $httpCode")
            ];
        }

        $decoded = json_decode($response, true);
        if (!$decoded) {
            parse_str($response, $parsed);
            return $parsed ?: ['resultCode' => 'FAIL', 'resultMsg' => '응답 파싱 오류'];
        }

        return $decoded;
    }

    /**
     * 망취소 요청 (승인 처리 실패 시)
     */
    public static function requestNetCancel(string $netCancelUrl, string $authToken, string $timestamp): void
    {
        $mid       = self::getMid();
        $signature = self::makeAuthSignature($authToken, $timestamp);

        $postData = http_build_query([
            'mid'       => $mid,
            'authToken' => $authToken,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'charset'   => 'UTF-8',
            'format'    => 'JSON'
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $netCancelUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_exec($ch);
        curl_close($ch);
    }
}
