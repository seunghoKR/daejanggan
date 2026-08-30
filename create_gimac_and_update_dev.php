<?php
$pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409');

// 1. 기존 admin (이승호) 계정을 '개발자'로 수정
$stmtAdmin = $pdo->prepare("UPDATE users SET 
    name = '이승호',
    nickname = '개발자',
    role = 'ADMIN'
WHERE username = 'admin' OR id = 1");
$stmtAdmin->execute();

// 2. gimac 관리자 계정 (배용하 대표) 생성 또는 업데이트
$hash = password_hash('a14241425', PASSWORD_ARGON2ID);
$stmtGimac = $pdo->prepare("
    INSERT INTO users (username, password_hash, password_type, name, nickname, email, phone, role, notify_kakao, notify_telegram, notify_email, points)
    VALUES ('gimac', ?, 'ARGON2ID', '배용하', '배용하 대표', 'jlife@daejanggan.org', '041-742-1424', 'ADMIN', 1, 1, 1, 10000)
    ON DUPLICATE KEY UPDATE 
        password_hash = VALUES(password_hash),
        password_type = 'ARGON2ID',
        name = '배용하',
        nickname = '배용하 대표',
        role = 'ADMIN',
        notify_kakao = 1,
        notify_telegram = 1,
        notify_email = 1
");
$stmtGimac->execute([$hash]);

// 3. 결과 확인
$users = $pdo->query("SELECT id, username, name, nickname, role, email, phone, telegram_id FROM users WHERE username IN ('admin', 'gimac')")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'message' => 'gimac 관리자 계정 생성 및 admin 계정 개발자 수정 완료',
    'users'   => $users
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
