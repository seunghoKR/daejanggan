<?php
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=ndaejanggan;charset=utf8mb4', 'ndaejanggan', '#seungho0409', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 0. Create backup of books table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `books_backup_20260829` AS SELECT * FROM `books`");

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

    $updatedCount = 0;
    $bookCatInserted = 0;

    $stmtUpdateBook = $pdo->prepare("
        UPDATE `books`
        SET `author` = :author,
            `category_id` = :category_id,
            `publisher` = :publisher
        WHERE `book_code` = :book_code
    ");

    $stmtFindBook = $pdo->prepare("SELECT id FROM `books` WHERE `book_code` = :book_code LIMIT 1");
    $stmtInsertBookCat = $pdo->prepare("
        INSERT IGNORE INTO `book_categories` (`book_id`, `category_code`)
        VALUES (:book_id, :category_code)
    ");

    $results = [];

    foreach ($items as $item) {
        $itId   = trim($item['it_id']);
        $title  = trim($item['it_name']);
        $explan = $item['it_explan'] ?? '';
        $ca1    = trim($item['ca_id'] ?? '');
        $ca2    = trim($item['ca_id2'] ?? '');
        $ca3    = trim($item['ca_id3'] ?? '');
        $maker  = trim($item['it_maker'] ?? '');

        // 1. Primary Category Mapping
        $bestCat = null;
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

        // 2. Author Extraction
        $author = '';
        $method = '';

        // Step A: 1010 author category
        foreach ([$ca1, $ca2, $ca3] as $ca) {
            if (!empty($ca) && isset($authorCats[$ca])) {
                $author = $authorCats[$ca];
                $method = 'category_1010';
                break;
            }
        }

        // Step B: Explicit title patterns
        if (empty($author)) {
            if (preg_match('/[—\-\/]\s*([가-힣]{2,4})\s*(?:신앙에세이|에세이|시집|주석|사진집|이야기)/u', $title, $m)) {
                $cand = cleanName($m[1]);
                if (isValidName($cand)) {
                    $author = $cand;
                    $method = 'title_pattern';
                }
            } elseif (preg_match('/([가-힣]{2,4})\s*지음/u', $title, $m)) {
                $cand = cleanName($m[1]);
                if (isValidName($cand)) {
                    $author = $cand;
                    $method = 'title_pattern';
                }
            }
        }

        // Step C: Text & Image tags & Bio blocks
        if (empty($author) && !empty($explan)) {
            $res = parseAuthorFromExplanAdvanced($title, $explan);
            if ($res) {
                $author = $res['author'];
                $method = $res['method'];
            }
        }

        if (empty($author)) {
            $author = '대장간';
            $method = 'fallback_daejanggan';
        }

        $publisher = (!empty($maker) && $maker !== '한국:논산' && $maker !== '한국/논산') ? $maker : '도서출판 대장간';

        // Execute UPDATE on books table
        $stmtUpdateBook->execute([
            ':author' => $author,
            ':category_id' => $categoryId,
            ':publisher' => $publisher,
            ':book_code' => $itId,
        ]);
        $updatedCount++;

        // Multi-category sync to book_categories table
        $stmtFindBook->execute([':book_code' => $itId]);
        $bookRow = $stmtFindBook->fetch();
        if ($bookRow) {
            $bookId = $bookRow['id'];
            foreach ([$ca1, $ca2, $ca3] as $caCode) {
                if (!empty($caCode)) {
                    $stmtInsertBookCat->execute([
                        ':book_id' => $bookId,
                        ':category_code' => $caCode,
                    ]);
                    $bookCatInserted++;
                }
            }
        }

        $results[] = [
            'book_code' => $itId,
            'title' => $title,
            'author' => $author,
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'publisher' => $publisher,
            'method' => $method,
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => '도서 마이그레이션 저자 및 카테고리 정상 갱신 완료!',
        'total_updated' => $updatedCount,
        'book_categories_linked' => $bookCatInserted,
        'samples' => array_slice($results, 0, 30),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}

function parseAuthorFromExplanAdvanced($title, $explan) {
    $html = preg_replace('/<img[^>]*(?:도서상세설명_지은이|도서상세설명_저자소개|저자소개|지은이소개)[^>]*>/ui', "\n[지은이]\n", $explan);
    $html = preg_replace('/<img[^>]*(?:도서상세설명_옮긴이|도서상세설명_역자|옮긴이소개|역자소개)[^>]*>/ui', "\n[옮긴이]\n", $html);
    $html = preg_replace('/<img[^>]*(?:도서상세설명_목차|목차)[^>]*>/ui', "\n[목차]\n", $html);
    $html = preg_replace('/<img[^>]*(?:도서상세설명_책내용소개|도서상세설명_책소개|책내용소개)[^>]*>/ui', "\n[책소개]\n", $html);

    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/<(?:p|div|h\d|br|li|tr|blockquote)[^>]*>/i', "\n", $html);
    $text = strip_tags($text);
    $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn($l) => $l !== ''));

    // Method 1: Look for [지은이] or [저자]
    for ($i = 0; $i < count($lines); $i++) {
        $l = $lines[$i];
        if (preg_match('/^\[(?:지은이|저\s*자|글쓴이|지은이\s*소개|저자\s*소개|지은이\s*및\s*옮긴이|글)\]\s*(.*)$/u', $l, $m)) {
            $cand = cleanName($m[1]);
            if ($cand !== '' && isValidName($cand)) {
                return ['author' => $cand, 'method' => 'tag_same_line'];
            }
            for ($j = $i + 1; $j < min(count($lines), $i + 4); $j++) {
                $next = cleanName($lines[$j]);
                if (isValidName($next)) {
                    return ['author' => $next, 'method' => 'tag_next_line'];
                }
            }
        }
    }

    // Method 2: "저자 OOO", "지은이 OOO"
    for ($i = 0; $i < count($lines); $i++) {
        $l = $lines[$i];
        if (preg_match('/^(?:지은이|저\s*자|글쓴이|글)\s*[:：\s]\s*([가-힣a-zA-Z\s·]+?)(?:은|는|이|가|\s*\(|교수|목사|박사|\/|$)/u', $l, $m)) {
            $cand = cleanName($m[1]);
            if (isValidName($cand)) return ['author' => $cand, 'method' => 'colon_prefix'];
        }
        if (preg_match('/(?:저자|지은이)\s+([가-힣]{2,4})(?:은|는|이|가|\s*\(|교수|목사|박사)/u', $l, $m)) {
            $cand = cleanName($m[1]);
            if (isValidName($cand)) return ['author' => $cand, 'method' => 'colon_prefix'];
        }
    }

    // Method 3: Bio block with English name e.g. "조셉 스톨(Joseph Stoll)" or "그래함 H. 트웰프트리 Graham H. Twelftree"
    $bioKeywords = '/(대학|출생|교수|목사|목회|사역|저서|전공|연구|활동|학위|석사|박사|섬기고|지냈|공부|출간|집필|지은|옮긴|칼럼|기자|대표|총장|신학|철학|출신|태어나|거주하는|저술가)/u';
    for ($i = 0; $i < count($lines); $i++) {
        $l = $lines[$i];
        if (preg_match('/^([가-힣\s·\.]+?)\s*(?:\([a-zA-Z\s,\.\-]+\)|[a-zA-Z\s,\.\-]+)$/u', $l, $m)) {
            $cand = cleanName($m[1]);
            if (isValidName($cand)) {
                for ($j = $i + 1; $j < min(count($lines), $i + 4); $j++) {
                    if (preg_match($bioKeywords, $lines[$j])) {
                        return ['author' => $cand, 'method' => 'bio_english_name'];
                    }
                }
            }
        }
    }

    // Method 4: Bottom bio lines (pure Korean name)
    for ($i = count($lines) - 1; $i >= max(0, count($lines) - 15); $i--) {
        $l = cleanName($lines[$i]);
        if (preg_match('/^[가-힣]{2,4}$/u', $l) && isValidName($l)) {
            $hasBio = false;
            for ($j = $i + 1; $j < min(count($lines), $i + 4); $j++) {
                if (preg_match($bioKeywords, $lines[$j])) {
                    $hasBio = true;
                    break;
                }
            }
            if ($hasBio) {
                return ['author' => $l, 'method' => 'bio_bottom_korean'];
            }
        }
    }

    return null;
}

function cleanName($name) {
    $name = trim(strip_tags($name));
    $name = preg_replace('/[\[\]\<\>\"\']+/u', '', $name);
    // Remove English name after slash: "그레이스 지선 김 / Grace Ji-Sun Kim" -> "그레이스 지선 김"
    if (preg_match('/^([가-힣\s\.\·]+?)\s*\/.*$/u', $name, $m)) {
        $name = trim($m[1]);
    }
    // Remove parenthesized English name: "존 E. 토우즈(John E. Toews)" -> "존 E. 토우즈"
    if (preg_match('/^([가-힣\s\.\·a-zA-Z]+?)\s*\(.*?\)/u', $name, $m)) {
        $name = trim($m[1]);
    }
    // Remove trailing title
    $name = preg_replace('/\s*(?:목사|교수|박사|총장|지음|저|글)\s*$/u', '', $name);
    if (preg_match('/^[가-힣](\s+[가-힣]){1,3}$/u', $name)) {
        $name = preg_replace('/\s+/', '', $name);
    }
    return trim($name);
}

function isValidName($name) {
    $len = mb_strlen($name);
    if ($len < 2 || $len > 12) return false;
    $banned = [
        '도서', '출판', '대장간', '특징', '목차', '소개', '머리말', '서문', '추천', '판권', '차례',
        '부록', '후기', '인성', '정서', '탐구', '여정', '일러두기', '작가의', '저자의', '참고문헌',
        '감사의', '지은 책', '옮긴 책', '지은이', '저자', '역자', '글쓴이', '엮은이', '블로그',
        '홈페이지', '아카데미', '프로젝트', '주요 저서', '아닌', '위한', '통해', '이야기', '기록',
        '교회', '하나님', '그리스도', '신앙', '선교', '사역', '제자', '공동체', '그림자', '인간', '사람', '독자',
        '창세기', '출애굽기', '레위기', '민수기', '신명기', '여호수아', '사사기', '룻기', '사무엘', '열왕기',
        '역대', '에스라', '느헤미야', '에스더', '욥기', '시편', '잠언', '전도서', '아가', '이사야',
        '예레미야', '에스겔', '다니엘', '호세아', '요엘', '아모스', '오바댜', '요나', '미가', '나훔',
        '하박국', '스바냐', '학개', '스가랴', '말라기', '마태복음', '마가복음', '누가복음', '요한복음', '사도행전',
        '로마서', '고린도', '갈라디아서', '에베소서', '빌립보서', '골로새서', '데살로니가', '디모데', '디도서', '빌레몬서',
        '히브리서', '야고보서', '베드로', '요한일서', '요한이서', '요한삼서', '유다서', '요한계시록', '성서주석'
    ];
    foreach ($banned as $b) {
        if (mb_strpos($name, $b) !== false) return false;
    }
    return true;
}
