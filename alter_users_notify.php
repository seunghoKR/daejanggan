<?php
$pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409');

$cols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);

// 1. 컬럼 추가
if (!in_array('nickname', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN nickname VARCHAR(50) NULL AFTER name");
}
if (!in_array('telegram_id', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN telegram_id VARCHAR(100) NULL AFTER phone");
}
if (!in_array('kakao_id', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN kakao_id VARCHAR(100) NULL AFTER telegram_id");
}
if (!in_array('notify_kakao', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN notify_kakao TINYINT(1) DEFAULT 1 AFTER kakao_id");
}
if (!in_array('notify_telegram', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN notify_telegram TINYINT(1) DEFAULT 0 AFTER notify_kakao");
}
if (!in_array('notify_sms', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN notify_sms TINYINT(1) DEFAULT 1 AFTER notify_telegram");
}
if (!in_array('notify_email', $cols)) {
    $pdo->exec("ALTER TABLE users ADD COLUMN notify_email TINYINT(1) DEFAULT 1 AFTER notify_sms");
}

// 2. g5_member 원본의 mb_nick 동기화
$members = $pdo->query("SELECT mb_id, mb_nick FROM g5_member WHERE mb_nick != ''")->fetchAll(PDO::FETCH_ASSOC);
$updateNick = $pdo->prepare("UPDATE users SET nickname = ? WHERE username = ? AND (nickname IS NULL OR nickname = '')");
$syncedNick = 0;
foreach ($members as $m) {
    $updateNick->execute([$m['mb_nick'], $m['mb_id']]);
    $syncedNick++;
}

echo "Users table extended and synced {$syncedNick} nicknames from g5_member!\n";
