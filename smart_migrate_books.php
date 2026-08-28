<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 1. Categories
    $catRows = $pdo->query("SELECT id, code, name FROM categories")->fetchAll();
    $catByCode = [];
    $authorCats = [];
    foreach ($catRows as $c) {
        $catByCode[$c['code']] = $c;
        if (str_starts_with($c['code'], '1010') && $c['code'] !== '1010') {
            $authorCats[$c['code']] = $c['name'];
        }
    }

    // 2. Fetch all items from g5_shop_item
    $items = $pdo->query("SELECT it_id, it_name, ca_id, ca_id2, ca_id3, it_maker, it_origin, it_basic, it_explan FROM g5_shop_item")->fetchAll();

    $results = [];
    $stats = [
        'total' => count($items),
        'from_1010_cat' => 0,
        'from_bracket_tag' => 0,
        'from_author_img_header' => 0,
        'from_author_intro_sentence' => 0,
        'from_tail_bio' => 0,
        'from_title_pattern' => 0,
        'fallback_daejanggan' => 0,
    ];

    foreach ($items as $item) {
        $itId   = $item['it_id'];
        $title  = trim($item['it_name']);
        $explan = $item['it_explan'] ?? '';
        $ca1    = trim($item['ca_id'] ?? '');
        $ca2    = trim($item['ca_id2'] ?? '');
        $ca3    = trim($item['ca_id3'] ?? '');

        $author = '';
        $method = '';

        // Category mapping
        $bestCat = null;
        // Prioritize non-1010 categories for the primary category if possible
        foreach ([$ca1, $ca2, $ca3] as $ca) {
            if (!empty($ca) && !str_starts_with($ca, '1010') && isset($catByCode[$ca])) {
                $bestCat = $catByCode[$ca];
                break;
            }
        }
        if (!$bestCat) {
            foreach ([$ca1, $ca2, $ca3] as $ca) {
                if (!empty($ca) && isset($catByCode[$ca])) {
                    $bestCat = $catByCode[$ca];
                    break;
                }
            }
        }
        if (!$bestCat) {
            // Prefix search
            foreach ([$ca1, $ca2, $ca3] as $ca) {
                if (empty($ca)) continue;
                foreach ($catByCode as $code => $c) {
                    if (str_starts_with($ca, $code) || str_starts_with($code, $ca)) {
                        $bestCat = $c;
                        break 2;
                    }
                }
            }
        }
        $categoryId = $bestCat['id'] ?? 1;
        $categoryName = $bestCat['name'] ?? '도서전체보기';

        // PASS 1: Check if any ca_id is in 1010% author category
        foreach ([$ca1, $ca2, $ca3] as $ca) {
            if (!empty($ca) && isset($authorCats[$ca])) {
                $author = $authorCats[$ca];
                $method = '1010_category';
                $stats['from_1010_cat']++;
                break;
            }
        }

        // PASS 2: [지은이], [저자], [글쓴이], [저자소개] 등 대괄호 태그 파싱
        if (empty($author) && !empty($explan)) {
            if (preg_match('/\[(?:지은이|저\s*자|글쓴이|지은이\s*소개|저자\s*소개|지은이\s*및\s*옮긴이)\]\s*(?:&nbsp;|\s|<[^>]+>)*([가-힣a-zA-Z\s·]+?)(?:<|\r|\n|&nbsp;|\(|\/|,|역자|옮긴이)/u', $explan, $m)) {
                $cand = trim(strip_tags($m[1]));
                if (isValidAuthorName($cand)) {
                    $author = cleanAuthorName($cand);
                    $method = 'bracket_tag';
                    $stats['from_bracket_tag']++;
                }
            }
        }

        // PASS 3: 저자소개 이미지 헤더 뒤의 이름 파싱 (도서상세설명_저자소개, 도서상세설명_지은이 등)
        if (empty($author) && !empty($explan)) {
            if (preg_match('/(?:도서상세설명_저자소개|도서상세설명_지은이|도서상세설명_글쓴이)[^>]*>[\s\r\n]*(?:<(?:p|div|b|h\d|span)[^>]*>)*(?:<br\s*\/?>|\s|&nbsp;)*([가-힣\s]{2,8})(?:<\/(?:p|div|b|h\d|span)>|<br\s*\/?>|\r|\n)/u', $explan, $m)) {
                $cand = trim(strip_tags($m[1]));
                if (isValidAuthorName($cand)) {
                    $author = cleanAuthorName($cand);
                    $method = 'author_img_header';
                    $stats['from_author_img_header']++;
                }
            }
        }

        // PASS 4: "저자 OOO은/는", "지은이 OOO은/는"
        if (empty($author) && !empty($explan)) {
            if (preg_match('/(?:저자|지은이)\s+([가-힣]{2,4})(?:은|는|이|가|\s*\(|교수|목사|박사)/u', $explan, $m)) {
                $cand = trim($m[1]);
                if (isValidAuthorName($cand)) {
                    $author = cleanAuthorName($cand);
                    $method = 'author_intro_sentence';
                    $stats['from_author_intro_sentence']++;
                }
            }
        }

        // PASS 5: explan 마지막 부분의 2~4글자 단독 이름 및 bio 문단
        if (empty($author) && !empty($explan)) {
            $plain = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>', '</h3>', '</h2>', '</h4>', '</b>'], "\n", $explan));
            $lines = array_values(array_filter(array_map('trim', explode("\n", $plain))));
            
            // 뒤에서부터 탐색
            $revLines = array_reverse($lines);
            for ($i = 0; $i < min(15, count($revLines)); $i++) {
                $l = $revLines[$i];
                if (preg_match('/^[가-힣]\s*[가-힣]\s*[가-힣](?:\s*[가-힣])?$/u', $l)) {
                    $cand = cleanAuthorName($l);
                    if (isValidAuthorName($cand)) {
                        $author = $cand;
                        $method = 'tail_bio';
                        $stats['from_tail_bio']++;
                        break;
                    }
                }
            }
        }

        // PASS 6: Title patterns (e.g. 조성희 신앙에세이, 김선주 시집 등)
        if (empty($author)) {
            if (preg_match('/[—\-\/]\s*([가-힣]{2,4})\s*(?:신앙에세이|에세이|시집|주석|사진집|이야기)/u', $title, $m)) {
                $cand = trim($m[1]);
                if (isValidAuthorName($cand)) {
                    $author = $cand;
                    $method = 'title_pattern';
                    $stats['from_title_pattern']++;
                }
            } elseif (preg_match('/([가-힣]{2,4})\s*지음/u', $title, $m)) {
                $cand = trim($m[1]);
                if (isValidAuthorName($cand)) {
                    $author = $cand;
                    $method = 'title_pattern';
                    $stats['from_title_pattern']++;
                }
            }
        }

        if (empty($author)) {
            $author = '대장간 편집부';
            $method = 'fallback_daejanggan';
            $stats['fallback_daejanggan']++;
        }

        $results[] = [
            'it_id' => $itId,
            'title' => $title,
            'author' => $author,
            'method' => $method,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
        ];
    }

    echo json_encode([
        'stats' => $stats,
        'samples' => array_slice($results, 0, 50),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function cleanAuthorName(string $name): string
{
    $name = trim(strip_tags($name));
    // Remove space between single Korean characters: "정 사 부" -> "정사부"
    if (preg_match('/^[가-힣](\s+[가-힣]){1,3}$/u', $name)) {
        $name = preg_replace('/\s+/', '', $name);
    }
    return $name;
}

function isValidAuthorName(string $name): bool
{
    $name = cleanAuthorName($name);
    $len = mb_strlen($name);
    if ($len < 2 || $len > 20) return false;
    $banned = ['도서', '출판', '대장간', '특징', '목차', '소개', '머리말', '서문', '추천', '판권', '차례', '부록', '후기', '인성', '정서', '탐구', '여정', '일러두기'];
    foreach ($banned as $b) {
        if (mb_strpos($name, $b) !== false) return false;
    }
    return true;
}
