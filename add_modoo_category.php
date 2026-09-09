<?php
$pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409');

$stmt = $pdo->prepare("INSERT INTO categories (code, name, type, sort_order) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), type = VALUES(type), sort_order = VALUES(sort_order)");

// 1. 대분류: 모두의 책
$stmt->execute(['1070', '모두의 책', 'MODOO', 50]);

// 2. 소분류: 자본론
$stmt->execute(['107010', '자본론', 'MODOO', 10]);

// 3. 소분류: 전집
$stmt->execute(['107020', '전집', 'MODOO', 20]);

$cats = $pdo->query("SELECT id, code, name, type, sort_order FROM categories WHERE code LIKE '1070%' ORDER BY code ASC")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
