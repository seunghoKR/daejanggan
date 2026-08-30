<?php
/**
 * AiBookParser - 도서 원고 텍스트 스마트 분석기
 * 도서명, 부제, 저자, 역자, 감수, 출판사, 발행일, ISBN, 가격, 분류 등 메타데이터 및
 * 책소개/목차/저자소개 섹션을 분석하여 정형 데이터 및 서점 스타일 HTML을 생성합니다.
 */
class AiBookParser
{
    private const LOCAL_AI_URL   = 'http://49.170.204.109:1234/v1/chat/completions';
    private const LOCAL_AI_MODEL = 'google/gemma-4-e2b';

    /**
     * 메인 파싱 메서드
     * @param string $rawText 원고 텍스트
     * @param array $categories DB 도서분류 목록 (선택)
     * @param array $seriesList DB 시리즈 목록 (선택)
     * @return array 파싱된 도서 데이터
     */
    public static function parse(string $rawText, array $categories = [], array $seriesList = []): array
    {
        $rawText = trim($rawText);
        if (empty($rawText)) {
            return [];
        }

        // 1단계: 룰 기반 고속 정밀 파싱 (첫줄=제목, 둘째줄=부제목 일반 규칙 포함)
        $parsed = self::parseRuleBased($rawText);

        // 시리즈 자동 매칭 (부제 또는 본문 기준)
        if (!empty($seriesList)) {
            $matchedSeriesId = self::matchSeries($parsed['subtitle'] ?? '', $parsed['title'] ?? '', $rawText, $seriesList);
            if ($matchedSeriesId) {
                $parsed['series_id'] = $matchedSeriesId;
            }
        }

        // 도서분류 자동 매칭 (DB 도서분류 목록 기준)
        if (!empty($categories)) {
            $matchedCatId = self::matchCategory(
                $parsed['category_raw'] ?? '',
                $parsed['title'] ?? '',
                $parsed['subtitle'] ?? '',
                $categories
            );
            if ($matchedCatId) {
                $parsed['category_id'] = $matchedCatId;
            }
        }

        // 2단계: 로컬 AI 서버가 응답 가능하면 AI 요약 및 보정 시도 (타임아웃 2.5초)
        $aiEnhanced = self::tryLocalAiEnhance($rawText);
        if (!empty($aiEnhanced)) {
            if (empty($parsed['title']) && !empty($aiEnhanced['title'])) {
                $parsed['title'] = $aiEnhanced['title'];
            }
            if (empty($parsed['subtitle']) && !empty($aiEnhanced['subtitle'])) {
                $parsed['subtitle'] = $aiEnhanced['subtitle'];
            }
            if (!empty($aiEnhanced['summary'])) {
                $parsed['summary'] = $aiEnhanced['summary'];
            }
            if (!empty($aiEnhanced['description']) && strlen($aiEnhanced['description']) > strlen($parsed['description'] ?? '')) {
                $parsed['description'] = $aiEnhanced['description'];
            }
        }

        return $parsed;
    }

    /**
     * 패턴 및 구분자 기반 정밀 파싱
     */
    private static function parseRuleBased(string $text): array
    {
        $data = [
            'title'          => '',
            'subtitle'       => '',
            'author'         => '',
            'translator'     => '',
            'supervisor'     => '',
            'publisher'      => '도서출판 대장간',
            'publish_date'   => '',
            'isbn'           => '',
            'original_price' => 0,
            'price'          => 0,
            'stock_qty'      => 100,
            'page_count'     => '',
            'book_size'      => '',
            'category_raw'   => '',
            'summary'        => '',
            'description'    => '',
            'sections'       => [],
        ];

        // 구분자(* * * * * 또는 --- 등)로 본문 섹션 분리
        $sectionBlocks = preg_split('/(?:\n|\r\n)\s*(?:\*(?:\s*\*){3,}|-{3,})\s*(?:\n|\r\n)/u', $text);
        
        $metaBlock = $sectionBlocks[0] ?? $text;
        $bodyBlocks = array_slice($sectionBlocks, 1);

        // 1) 메타데이터 블록 파싱
        $lines = preg_split('/\r\n|\r|\n/', trim($metaBlock));
        $unlabelledLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            // 한 줄에 파이프(|)로 여러 항목이 있을 수 있으므로 분리 검사
            $segments = preg_split('/\s*\|\s*/u', $trimmed);
            $hasKeyInLine = false;

            foreach ($segments as $seg) {
                $seg = trim($seg);
                if ($seg === '') continue;

                if (preg_match('/^(?:부제|부\s*제목)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['subtitle'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:지은이|저자|글|저\s*자)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['author'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:옮긴이|역자|번역|역\s*자)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['translator'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:감수|감\s*수)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['supervisor'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:출판사|출판)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['publisher'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:발행일|출간일|출판일|발행)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['publish_date'] = self::formatDate(trim($m[1]));
                    $hasKeyInLine = true;
                } elseif (preg_match('/^ISBN\s*[:：]\s*(.+)$/iu', $seg, $m)) {
                    $data['isbn'] = mb_substr(trim($m[1]), 0, 100);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:페이지|쪽수|면수)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['page_count'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:판형|크기|규격)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['book_size'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:도서분류|분류|카테고리|주제)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $data['category_raw'] = trim($m[1]);
                    $hasKeyInLine = true;
                } elseif (preg_match('/^(?:가격|정가|판매가)\s*[:：]\s*(.+)$/u', $seg, $m)) {
                    $priceNum = (int)preg_replace('/[^0-9]/', '', $m[1]);
                    $data['original_price'] = $priceNum;
                    $data['price'] = $priceNum > 0 ? (int)round($priceNum * 0.9) : 0; // 기본 10% 할인가
                    $hasKeyInLine = true;
                }
            }

            // 특별한 구분 명칭이 없는 일반 텍스트 라인
            if (!$hasKeyInLine) {
                $unlabelledLines[] = $trimmed;
            }
        }

        // [일반 규칙 반영]:
        // 첫 번째 줄 -> 특별한 구분명칭이 없는 경우 도서제목(title) (양쪽 끝 기호 <>, 《》, "", '' 등 자동 제거)
        // 두 번째 줄 -> 특별한 구분명칭이 없는 경우 부제목(subtitle)
        if (!empty($unlabelledLines)) {
            if (empty($data['title']) && isset($unlabelledLines[0])) {
                $data['title'] = self::cleanTitle($unlabelledLines[0]);
            }
            if (empty($data['subtitle']) && isset($unlabelledLines[1])) {
                $data['subtitle'] = self::cleanTitle($unlabelledLines[1]);
            }
        }

        if (!empty($data['title'])) {
            $data['title'] = self::cleanTitle($data['title']);
        }
        if (!empty($data['subtitle'])) {
            $data['subtitle'] = self::cleanTitle($data['subtitle']);
        }

        // 역자에 감수자 병합 (예: 김태형 외 (감수: 이상억))
        if (!empty($data['supervisor'])) {
            if (!empty($data['translator'])) {
                $data['translator'] .= ' (감수: ' . $data['supervisor'] . ')';
            } else {
                $data['translator'] = '감수: ' . $data['supervisor'];
            }
        }

        // 2) 상세 섹션 블록들 파싱
        $sections = [];
        foreach ($bodyBlocks as $block) {
            $block = trim($block);
            if ($block === '') continue;

            if (preg_match('/^\[([^\]]+)\]\s*(.*)$/us', $block, $m)) {
                $secTitle = trim($m[1]);
                $secContent = trim($m[2]);
                $sections[$secTitle] = $secContent;
            } else {
                $sections['상세내용'] = $block;
            }
        }

        $data['sections'] = $sections;

        // 3) 한줄 요약(summary) 추출
        if (!empty($sections['책소개'])) {
            $intro = strip_tags($sections['책소개']);
            if (preg_match('/^([^.!?\n]+[.!?])/u', $intro, $m)) {
                $data['summary'] = trim($m[1]);
            } else {
                $data['summary'] = mb_substr($intro, 0, 120) . '...';
            }
        }

        // 4) 서점 스타일 HTML description 구성
        $htmlParts = [];
        
        $specItems = [];
        if (!empty($data['isbn'])) $specItems[] = '<span><strong>ISBN:</strong> ' . htmlspecialchars($data['isbn']) . '</span>';
        if (!empty($data['page_count'])) $specItems[] = '<span><strong>페이지:</strong> ' . htmlspecialchars($data['page_count']) . '</span>';
        if (!empty($data['book_size'])) $specItems[] = '<span><strong>판형:</strong> ' . htmlspecialchars($data['book_size']) . '</span>';
        if (!empty($data['publish_date'])) $specItems[] = '<span><strong>발행일:</strong> ' . htmlspecialchars($data['publish_date']) . '</span>';
        if (!empty($data['publisher'])) $specItems[] = '<span><strong>출판사:</strong> ' . htmlspecialchars($data['publisher']) . '</span>';

        if (!empty($specItems)) {
            $htmlParts[] = '<div class="book-spec-box" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; margin-bottom:28px; display:flex; flex-wrap:wrap; gap:16px; font-size:13px; color:#475569;">'
                         . implode('<span style="color:#cbd5e1;">|</span>', $specItems)
                         . '</div>';
        }

        foreach ($sections as $title => $content) {
            $contentHtml = nl2br(htmlspecialchars($content));
            $contentHtml = preg_replace('/(\d+[\.\)]\s+[^\n<]+)/u', '<span style="color:#1e293b; font-weight:500;">$1</span>', $contentHtml);

            $htmlParts[] = '<div class="book-section" style="margin-bottom:32px;">'
                         . '<h3 style="font-size:18px; font-weight:700; color:#0f172a; border-left:4px solid #1c2833; padding-left:12px; margin-bottom:14px; letter-spacing:-0.02em;">[' . htmlspecialchars($title) . ']</h3>'
                         . '<div style="font-size:14px; line-height:1.85; color:#334155; word-break:keep-all;">' . $contentHtml . '</div>'
                         . '</div>';
        }

        $data['description'] = implode("\n", $htmlParts);

        return $data;
    }

    /**
     * 날짜 포맷팅 (2026년 6월 29일 -> 2026-06-29)
     */
    private static function formatDate(string $str): string
    {
        if (preg_match('/(\d{4})[^\d]+(\d{1,2})[^\d]+(\d{1,2})/u', $str, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
        }
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $str, $m)) {
            return $m[1];
        }
        return $str;
    }

    /**
     * 시리즈 매칭
     */
    private static function matchSeries(string $subtitle, string $title, string $rawText, array $seriesList): ?int
    {
        $searchTarget = $subtitle . ' ' . $title . ' ' . mb_substr($rawText, 0, 300);
        foreach ($seriesList as $ser) {
            $serName = trim($ser['name']);
            if (empty($serName)) continue;
            if (mb_stripos($searchTarget, $serName) !== false || mb_stripos($serName, $subtitle) !== false) {
                return (int)$ser['id'];
            }
        }
        return null;
    }

    /**
     * 도서분류(카테고리) 매칭
     */
    private static function matchCategory(string $categoryRaw, string $title, string $subtitle, array $categories): ?int
    {
        $searchTarget = $categoryRaw . ' ' . $subtitle . ' ' . $title;
        
        // 1차: 직접 일치
        foreach ($categories as $cat) {
            if (!empty($categoryRaw) && mb_stripos($cat['name'], $categoryRaw) !== false) {
                return (int)$cat['id'];
            }
        }

        // 2차: 부제목(subtitle)과 도서분류명 매칭 (예: "신자들의 교회 성서주석")
        if (!empty($subtitle)) {
            foreach ($categories as $cat) {
                if (mb_stripos($cat['name'], $subtitle) !== false || mb_stripos($subtitle, $cat['name']) !== false) {
                    return (int)$cat['id'];
                }
            }
        }

        // 3차: 키워드 유사도 매칭
        $keywords = ['목회', '상담', '청소년', '신앙', '평화', '아나뱁티스트', '신학', '성서', '주석', '비공', '사회', '교회'];
        foreach ($keywords as $kw) {
            if (mb_stripos($searchTarget, $kw) !== false) {
                foreach ($categories as $cat) {
                    if (mb_stripos($cat['name'], $kw) !== false) {
                        return (int)$cat['id'];
                    }
                }
            }
        }

        return $categories[0]['id'] ?? 1;
    }

    /**
     * 로컬 AI (LM Studio) 하이브리드 연동
     */
    private static function tryLocalAiEnhance(string $rawText): array
    {
        if (!function_exists('curl_init')) {
            return [];
        }

        $prompt = "다음 도서 원고 정보를 분석하여 한줄 요약(summary)과 핵심 메타데이터를 JSON으로 출력해주세요.\n\n"
                . "[필수 규칙]\n"
                . "1. 원고 텍스트의 첫 번째 줄은 특별한 명칭(지은이:, 출판사: 등)이 없는 경우 반드시 '도서제목(title)'입니다.\n"
                . "2. 두 번째 줄은 특별한 명칭이 없는 경우 반드시 '부제목(subtitle)'입니다.\n"
                . "3. 본문 [책소개] 내용을 바탕으로 1~2문장의 매력적인 한줄 요약(summary)을 작성해주세요.\n\n"
                . "반드시 아래 JSON 형식만 반환하세요:\n"
                . "{\n"
                . '  "title": "도서명",' . "\n"
                . '  "subtitle": "부제",' . "\n"
                . '  "summary": "1~2문장의 감각적이고 매력적인 책소개 한줄 요약"' . "\n"
                . "}\n\n[원고 본문]\n" . mb_substr($rawText, 0, 1500);

        $payload = json_encode([
            'model' => self::LOCAL_AI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a professional Korean book editor and metadata parser. Strictly follow the title and subtitle position rules. Output pure JSON only.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.1,
            'max_tokens'  => 512,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init(self::LOCAL_AI_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST              => true,
            CURLOPT_POSTFIELDS        => $payload,
            CURLOPT_HTTPHEADER        => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_CONNECTTIMEOUT_MS => 1000,
            CURLOPT_TIMEOUT_MS        => 2500,
            CURLOPT_NOSIGNAL          => 1,
        ]);

        $res = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$res || $httpCode !== 200) {
            // 로컬 AI 연동 실패 시 관리자/개발자 텔레그램으로 장애 알림 자동 전송 (15분 쿨다운)
            require_once __DIR__ . '/Notifier.php';
            $errDetail = $curlErr ?: "HTTP 상태 코드: {$httpCode}";
            Notifier::sendAiFailureAlert(self::LOCAL_AI_URL, $errDetail, '도서 등록 원고 텍스트 AI 분석 시도 중');
            return [];
        }

        $json = json_decode($res, true);
        $content = $json['choices'][0]['message']['content'] ?? '';
        if (empty($content)) {
            return [];
        }

        // ```json ... ``` 정제
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $m)) {
            $content = $m[1];
        } elseif (preg_match('/```\s*(.*?)\s*```/s', $content, $m)) {
            $content = $m[1];
        }

        $parsed = json_decode($content, true);
        return is_array($parsed) ? $parsed : [];
    }

    /**
     * 도서 제목 및 부제목 양끝의 특수 기호(<>, 《》, 「」, 『』, "", '', [] 등) 자동 제거 헬퍼 (UTF-8 안전)
     */
    public static function cleanTitle(string $str): string
    {
        $str = trim($str);
        if ($str === '') {
            return '';
        }

        $patterns = [
            '/^<(.+)>$/us',
            '/^〈(.+)〉$/us',
            '/^《(.+)》$/us',
            '/^«(.+)»$/us',
            '/^「(.+)」$/us',
            '/^『(.+)』$/us',
            '/^\[(.+)\]$/us',
            '/^\{(.+)\}$/us',
            '/^\((.+)\)$/us',
            '/^"(.*)"$/us',
            "/^'(.*)'$/us",
            '/^“\s*(.*?)\s*”$/us',
            '/^‘\s*(.*?)\s*’$/us',
        ];

        $changed = true;
        $count = 0;
        while ($changed && $count < 5) {
            $prev = $str;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $str, $m)) {
                    $str = trim($m[1]);
                }
            }
            // 양 끝에 남은 특수 괄호 및 따옴표 기호들을 멀티바이트 안전 정규식(/u)으로 완벽 제거
            $str = preg_replace('/^[\s<〈《«「『\[{\("“‘]+|[\s>〉》»」』\]}\)"”’]+$/u', '', (string)$str);
            $str = trim((string)$str);
            $changed = ($prev !== $str);
            $count++;
        }

        return $str;
    }
}
