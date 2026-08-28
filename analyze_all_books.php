<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 1. Get all categories mapped by code
    $catRows = $pdo->query("SELECT id, code, name FROM categories")->fetchAll();
    $catByCode = [];
    $authorCats = []; // code => name for author categories (1010...)
    foreach ($catRows as $c) {
        $catByCode[$c['code']] = $c;
        if (str_starts_with($c['code'], '1010') && $c['code'] !== '1010') {
            $authorCats[$c['code']] = $c['name'];
        }
    }

    // 2. Get all items from g5_shop_item
    $items = $pdo->query("SELECT it_id, it_name, ca_id, ca_id2, ca_id3, it_maker, it_origin, it_basic, it_explan FROM g5_shop_item")->fetchAll();

    $extracted = [];
    $stats = [
        'total' => count($items),
        'from_author_cat' => 0,
        'from_explan_tag' => 0,
        'from_title_pattern' => 0,
        'unresolved' => 0,
        'category_matched' => 0,
        'category_unmatched' => 0,
    ];

    foreach ($items as $item) {
        $itId   = $item['it_id'];
        $title  = $item['it_name'];
        $explan = $item['it_explan'];
        $ca1    = $item['ca_id'];
        $ca2    = $item['ca_id2'];
        $ca3    = $item['ca_id3'];

        $author = '';
        $authorSource = '';
        $primaryCatId = 0;
        $primaryCatName = '';

        // Category matching: find best category
        // Prioritize non-author category if possible, or fallback to any valid category
        $validCats = [];
        foreach ([$ca1, $ca2, $ca3] as $caCode) {
            if (!empty($caCode) && isset($catByCode[$caCode])) {
                $validCats[] = $catByCode[$caCode];
            }
        }

        // If not found, try prefix matches (e.g. 1040k0 -> 1040k060 or 1040)
        if (empty($validCats)) {
            foreach ([$ca1, $ca2, $ca3] as $caCode) {
                if (empty($caCode)) continue;
                foreach ($catByCode as $code => $c) {
                    if (str_starts_with($caCode, $code) || str_starts_with($code, $caCode)) {
                        $validCats[] = $c;
                        break;
                    }
                }
            }
        }

        if (!empty($validCats)) {
            $stats['category_matched']++;
            // Prefer topic/series over general
            $primaryCatId = $validCats[0]['id'];
            $primaryCatName = $validCats[0]['name'];
        } else {
            $stats['category_unmatched']++;
            $primaryCatId = 1; // 도서전체보기
            $primaryCatName = '도서전체보기';
        }

        // Author matching:
        // Method A: Check if ca_id, ca_id2, ca_id3 is an author category (1010...)
        foreach ([$ca1, $ca2, $ca3] as $caCode) {
            if (!empty($caCode) && isset($authorCats[$caCode])) {
                $author = $authorCats[$caCode];
                $authorSource = 'category_1010';
                $stats['from_author_cat']++;
                break;
            }
        }

        // Method B: Parse it_explan for [지은이], [저자], [글], [저자 소개] etc.
        if (empty($author) && !empty($explan)) {
            // Pattern 1: [지은이]<b>이름</b> or [지은이]&nbsp;이름
            if (preg_match('/\[(?:지은이|저\s*자|저자소개|지은이\s*소개|지은이\s*및\s*옮긴이)\]\s*(?:&nbsp;|\s|<[^>]+>)*([가-힣a-zA-Z\s·]+?)(?:<|\r|\n|&nbsp;|\(|\/|,|역자|옮긴이)/u', $explan, $m)) {
                $cand = trim(strip_tags($m[1]));
                if (mb_strlen($cand) >= 2 && mb_strlen($cand) <= 25 && !preg_match('/(도서|출판|대장간|특징|목차|소개|머리말)/u', $cand)) {
                    $author = $cand;
                    $authorSource = 'explan_bracket';
                    $stats['from_explan_tag']++;
                }
            }
            
            // Pattern 2: <b>지은이 : 이름</b> or 저자 : 이름
            if (empty($author) && preg_match('/(?:지은이|저\s*자|글)\s*[:：]\s*([가-힣a-zA-Z\s·]+?)(?:<|\r|\n|\||,|\(|\/)/u', $explan, $m)) {
                $cand = trim(strip_tags($m[1]));
                if (mb_strlen($cand) >= 2 && mb_strlen($cand) <= 25 && !preg_match('/(도서|출판|대장간|특징|목차|소개)/u', $cand)) {
                    $author = $cand;
                    $authorSource = 'explan_colon';
                    $stats['from_explan_tag']++;
                }
            }
        }

        // Method C: Title patterns (e.g. "하나님은 그런 분이 아닙니다-조성희 신앙에세이" -> 조성희)
        if (empty($author)) {
            if (preg_match('/[—\-]\s*([가-힣]{2,4})\s*(?:신앙에세이|에세이|시집|주석|사진집)/u', $title, $m)) {
                $author = trim($m[1]);
                $authorSource = 'title_pattern';
                $stats['from_title_pattern']++;
            } elseif (preg_match('/([가-힣]{2,4})\s*지음/u', $title, $m)) {
                $author = trim($m[1]);
                $authorSource = 'title_jieum';
                $stats['from_title_pattern']++;
            }
        }

        if (empty($author)) {
            $author = '대장간 편집부';
            $authorSource = 'fallback';
            $stats['unresolved']++;
        }

        // Clean author name (remove whitespace inside Korean names like "조 성 희" -> "조성희")
        if (preg_match('/^[가-힣]\s+[가-힣]\s+[가-힣]$/u', $author)) {
            $author = str_replace(' ', '', $author);
        }

        $extracted[] = [
            'it_id' => $itId,
            'title' => $title,
            'author' => $author,
            'author_source' => $authorSource,
            'category_id' => $primaryCatId,
            'category_name' => $primaryCatName,
        ];
    }

    echo json_encode([
        'stats' => $stats,
        'samples' => array_slice($extracted, 0, 30),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
