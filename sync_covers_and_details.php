<?php
$host = 'localhost';
$db   = 'ndaejanggan';
$user = 'ndaejanggan';
$pass = '#seungho0409';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "=== Syncing Official 1st Covers (it_img1) & Detail Images ===\n";

$items = $pdo->query("SELECT it_id, it_img1, it_img2, it_img3, it_img4, it_img5 FROM g5_shop_item")->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $pdo->prepare("UPDATE books SET cover_image = ?, detail_images = ? WHERE book_code = ?");
$count = 0;

foreach ($items as $it) {
    $code = $it['it_id'];
    $images = [];

    // 1. g5_shop_item 의 it_img1 ~ it_img5 순서대로 수집
    for ($i = 1; $i <= 5; $i++) {
        $img = trim($it['it_img' . $i] ?? '');
        if ($img) {
            $fullPath = __DIR__ . '/data/item/' . $img;
            if (file_exists($fullPath)) {
                $images[] = '/data/item/' . $img;
            }
        }
    }

    // 2. 만약 it_img1~5가 비어있다면 /data/item/{code}/ 디렉토리에서 수집
    if (empty($images)) {
        $idDir = __DIR__ . '/data/item/' . $code;
        if (is_dir($idDir)) {
            $files = array_diff(scandir($idDir), ['.', '..']);
            foreach ($files as $f) {
                if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f) && !str_starts_with($f, 'thumb-')) {
                    $images[] = "/data/item/{$code}/{$f}";
                }
            }
        }
    }

    // 대표 표지는 1번 이미지(it_img1) 또는 첫 번째 이미지
    $coverImage = !empty($images) ? $images[0] : '/assets/images/default_book.png';
    $detailImagesJson = !empty($images) ? json_encode(array_values(array_unique($images)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '[]';

    $updateStmt->execute([$coverImage, $detailImagesJson, $code]);
    $count++;
}

echo "Successfully updated {$count} books with official it_img1 covers and detail gallery images!\n";

// 샘플 확인
$sample = $pdo->query("SELECT book_code, title, cover_image, detail_images FROM books WHERE book_code = '2026082000'")->fetch(PDO::FETCH_ASSOC);
echo "\n[Sample Check: 2026082000]\n";
echo "Cover Image: " . $sample['cover_image'] . "\n";
echo "Detail Images: " . $sample['detail_images'] . "\n";
