<?php
$pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409');

$settings = [
    'telegram_bot_token'      => '8660141426:AAG4EuHv4nrcijly4LS_Dj1iEN1gbjUtbTI',
    'telegram_notify_ai'      => '1',
    'telegram_notify_order'   => '1',
    'telegram_notify_member'  => '1',
];

$stmt = $pdo->prepare("INSERT INTO site_settings (key_name, key_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE key_value = VALUES(key_value)");

foreach ($settings as $k => $v) {
    $stmt->execute([$k, $v]);
}

echo "Bot token and notification settings updated successfully!\n";
