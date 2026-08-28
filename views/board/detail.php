<?php
/**
 * 게시판 상세 보기
 */
$typeNames = [
  'company' => '회사소개',
  'inquiry' => '출판 문의',
  'event'   => '대장간이벤트',
  'gallery' => '글 먹는 시간',
  'archive' => '자료실',
  'notice'  => '공지사항',
  'press'   => '언론보도'
];
$boardTitle = $typeNames[$type] ?? ($typeNames[$post['type'] ?? ''] ?? '커뮤니티');
$pageTitle  = $post['title'];
$ogDescription = mb_substr(strip_tags($post['content']), 0, 120) . '...';
$ogImage    = !empty($post['file_path']) ? 'http://ndaejanggan.iwinv.net' . $post['file_path'] : 'http://ndaejanggan.iwinv.net/assets/images/logo.png';
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">
  <div class="mb-4">
    <a href="/community/<?= htmlspecialchars($post['type'] ?? $type) ?>" class="text-xs text-on-surface-variant hover:text-primary flex items-center gap-1">
      <span class="material-symbols-outlined text-sm">arrow_back</span>
      <?= $boardTitle ?> 목록으로 돌아가기
    </a>
  </div>

  <article class="bg-surface rounded-2xl border border-outline-variant/80 p-6 md:p-10 shadow-sm">
    <header class="pb-6 border-b border-outline-variant/60">
      <div class="flex items-center gap-2 text-xs text-secondary font-semibold mb-2">
        <span class="bg-secondary/10 px-2.5 py-0.5 rounded-full"><?= $boardTitle ?></span>
      </div>
      <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary mb-4 leading-snug"><?= htmlspecialchars($post['title']) ?></h1>
      <div class="flex flex-wrap items-center gap-4 text-xs text-on-surface-variant">
        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">person</span> <?= htmlspecialchars($post['author_name'] ?? '도서출판 대장간') ?></span>
        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">schedule</span> <?= date('Y.m.d H:i', strtotime($post['created_at'])) ?></span>
        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">visibility</span> 조회수 <?= number_format((int)$post['view_count']) ?></span>
      </div>
    </header>

    <!-- 첨부 이미지 렌더링 -->
    <?php if (!empty($post['file_path'])): ?>
      <div class="my-6 text-center bg-surface-container-low rounded-2xl p-4 overflow-hidden border border-outline-variant/60">
        <img src="<?= htmlspecialchars($post['file_path']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
             class="max-h-[600px] w-auto mx-auto rounded-xl shadow-sm object-contain"/>
      </div>
    <?php endif; ?>

    <div class="py-8 prose max-w-none text-on-surface leading-relaxed text-sm md:text-base">
      <?php if (str_contains($post['content'], '<p') || str_contains($post['content'], '<div') || str_contains($post['content'], '<table')): ?>
        <?= $post['content'] /* HTML 본문 */ ?>
      <?php else: ?>
        <?= nl2br(htmlspecialchars($post['content'])) ?>
      <?php endif; ?>
    </div>

    <!-- 📤 게시물 SNS 퍼가기 & 공유하기 바 -->
    <div class="py-4 my-2 border-y border-outline-variant/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-surface-container-low/60 rounded-xl px-4">
      <span class="text-xs text-gray-600 font-semibold flex items-center gap-1.5 shrink-0">
        <span class="material-symbols-outlined text-base text-secondary">share</span>
        이 소식 퍼가기 &amp; 공유하기
      </span>
      <div class="flex flex-wrap items-center gap-2">
        <!-- 카카오톡 (공식 말풍선) -->
        <button type="button" onclick="shareSNS('kakao', '<?= addslashes($post['title']) ?>', null, null, '<?= addslashes($ogDescription) ?>')"
                class="w-8 h-8 rounded-full bg-[#FEE500] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="카카오톡 공유">
          <svg class="w-4 h-4 text-[#191919] fill-current" viewBox="0 0 24 24">
            <path d="M12 3c-5.523 0-10 3.582-10 8 0 2.85 1.867 5.347 4.688 6.72-.206.76-.745 2.756-.853 3.18-.135.534.195.526.41.383.17-.113 2.705-1.84 3.79-2.58.643.093 1.303.143 1.965.143 5.523 0 10-3.582 10-8s-4.477-8-10-8z"/>
          </svg>
        </button>

        <!-- 페이스북 (공식 F 로고) -->
        <button type="button" onclick="shareSNS('facebook', '<?= addslashes($post['title']) ?>')"
                class="w-8 h-8 rounded-full bg-[#1877F2] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="페이스북 공유">
          <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
          </svg>
        </button>

        <!-- X (공식 𝕏 로고) -->
        <button type="button" onclick="shareSNS('x', '<?= addslashes($post['title']) ?> - 도서출판 대장간')"
                class="w-8 h-8 rounded-full bg-[#000000] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="X (트위터) 공유">
          <svg class="w-3.5 h-3.5 text-white fill-current" viewBox="0 0 24 24">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
          </svg>
        </button>

        <!-- 네이버 블로그/카페 (공식 N 로고) -->
        <button type="button" onclick="shareSNS('naver', '<?= addslashes($post['title']) ?> - 도서출판 대장간')"
                class="w-8 h-8 rounded-full bg-[#03C75A] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="네이버 공유">
          <svg class="w-3.5 h-3.5 text-white fill-current" viewBox="0 0 24 24">
            <path d="M16.273 12.845L7.376 0H0v24h7.727V11.155L16.624 24H24V0h-7.727z"/>
          </svg>
        </button>

        <!-- 네이버 밴드 (공식 밴드 로고) -->
        <button type="button" onclick="shareSNS('band', '<?= addslashes($post['title']) ?> - 도서출판 대장간')"
                class="w-8 h-8 rounded-full bg-[#00D362] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="네이버 밴드 공유">
          <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2.5c-.28 0-.5.22-.5.5V12h3l-.5 3h-2v6.8c4.56-.93 8-4.96 8-9.8 0-5.52-4.48-10-10-10z"/>
          </svg>
        </button>

        <!-- 텔레그램 (공식 종이비행기 로고) -->
        <button type="button" onclick="shareSNS('telegram', '<?= addslashes($post['title']) ?> - 도서출판 대장간')"
                class="w-8 h-8 rounded-full bg-[#24A1DE] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="텔레그램 공유">
          <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
          </svg>
        </button>

        <!-- 링크 복사 -->
        <button type="button" onclick="shareSNS('copy')"
                class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1 text-xs font-semibold shadow-sm hover:scale-105 transition-transform" title="게시물 링크 복사">
          <span class="material-symbols-outlined text-sm">link</span>
          <span>복사</span>
        </button>
      </div>
    </div>

    <div class="pt-4 flex items-center justify-between">
      <a href="/community/<?= htmlspecialchars($type) ?>" class="text-xs text-gray-500 hover:text-primary">
        ← 이전 목록
      </a>
      <a href="/community/<?= htmlspecialchars($type) ?>" class="px-5 py-2.5 bg-primary text-on-primary rounded-xl text-xs font-semibold hover:bg-primary-container transition-colors shadow-sm">
        목록 보기
      </a>
    </div>
  </article>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
