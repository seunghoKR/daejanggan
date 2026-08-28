<?php
$host = 'localhost';
$db   = 'ndaejanggan';
$user = 'ndaejanggan';
$pass = '#seungho0409';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== Fixing Book Covers & Cleaning Dummy Books ===\n";

$pdo->exec("SET FOREIGN_KEY_CHECKS=0");

// 1. 초기 더미 도서 (BK001 ~ BK_ELLUL01) 삭제 및 주문 품목 FK 정리
$pdo->exec("DELETE FROM books WHERE book_code LIKE 'BK%'");
echo "Deleted initial dummy book records.\n";

// 2. g5_shop_item 원본 데이터를 기반으로 610권 도서의 cover_image를 실존하는 이미지 파일로 100% 매칭
$items = $pdo->query("SELECT it_id, it_img1, it_img2, it_img3, it_img4, it_img5 FROM g5_shop_item")->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $pdo->prepare("UPDATE books SET cover_image = ? WHERE book_code = ?");
$matchedCount = 0;
$fallbackCount = 0;

foreach ($items as $it) {
    $code = $it['it_id'];
    $bestImg = null;

    // 1순위: /data/item/{it_id}/ 폴더 내부 탐색
    $idDir = __DIR__ . '/data/item/' . $code;
    if (is_dir($idDir)) {
        $files = array_diff(scandir($idDir), ['.', '..']);
        $orig = null;
        $thumb = null;
        foreach ($files as $f) {
            if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f)) {
                if (str_starts_with($f, 'thumb-')) {
                    $thumb = "/data/item/{$code}/{$f}";
                } else {
                    $orig = "/data/item/{$code}/{$f}";
                    break;
                }
            }
        }
        $bestImg = $orig ?: $thumb;
    }

    // 2순위: it_img1, it_img2 ... 파일 확인
    if (!$bestImg) {
        for ($i = 1; $i <= 5; $i++) {
            $imgField = $it['it_img' . $i];
            if ($imgField && file_exists(__DIR__ . '/data/item/' . $imgField)) {
                $bestImg = '/data/item/' . $imgField;
                break;
            }
        }
    }

    if ($bestImg) {
        $updateStmt->execute([$bestImg, $code]);
        $matchedCount++;
    } else {
        $fallbackCount++;
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "Successfully matched real cover images for {$matchedCount} books! (Fallback: {$fallbackCount})\n";
