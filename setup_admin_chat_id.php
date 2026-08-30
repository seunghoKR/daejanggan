<?php
$pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409');

// 1. site_settings 에 관리자 Chat ID 저장
$stmt = $pdo->prepare("INSERT INTO site_settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");
$stmt->execute(['telegram_admin_chat_id', '5618137472']);
$stmt->execute(['telegram_bot_token', '8660141426:AAG4EuHv4nrcijly4LS_Dj1iEN1gbjUtbTI']);

// 2. admin 사용자의 telegram_id 도 동기화
$pdo->exec("UPDATE users SET telegram_id = '5618137472', notify_telegram = 1 WHERE username = 'admin' OR id = 1");

// 3. 텔레그램 테스트 메시지 즉시 전송
require_once __DIR__ . '/core/Notifier.php';

$testMsg = "🎉 <b>[도서출판 대장간] 텔레그램 실시간 알림 연동 완료!</b>\n\n"
         . "대표님(Chat ID: <code>5618137472</code>)의 텔레그램 계정이 쇼핑몰 관리자 알림 센터에 정상 등록되었습니다.\n\n"
         . "<b>🔔 실시간 자동 발송 알림 항목:</b>\n"
         . "• 🤖 <b>로컬 AI 연동 장애 긴급 경보</b> (LM Studio 응답 끊김 시)\n"
         . "• 🛒 <b>신규 주문 및 결제 접수 알림</b>\n"
         . "• 👤 <b>신규 회원가입 알림</b>\n\n"
         . "<i>대장간 쇼핑몰의 모든 시스템이 안전하게 감시 중입니다! ✨</i>";

$res = Notifier::sendTelegram('5618137472', $testMsg, '8660141426:AAG4EuHv4nrcijly4LS_Dj1iEN1gbjUtbTI');

echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
