<?php
/**
 * 게시판 목록 (회사소개 / 출판문의 / 대장간이벤트 / 글 먹는 시간 / 자료실 / 공지사항)
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
$boardTitle = $typeNames[$type] ?? '게시판';
$pageTitle  = $boardTitle;
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">
  <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-outline-variant/60">
    <div>
      <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-1">
        <a href="/" class="hover:text-primary">홈</a>
        <span>›</span>
        <span>커뮤니티</span>
        <span>›</span>
        <span class="text-primary font-medium"><?= $boardTitle ?></span>
      </div>
      <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary"><?= $boardTitle ?></h1>
      <p class="text-xs text-on-surface-variant mt-1">도서출판 대장간의 소식과 다양한 컨텐츠를 만나보세요. (총 <?= number_format($total ?? 0) ?>건)</p>
    </div>
  </div>

  <?php if ($type === 'gallery'): ?>
    <!-- 🖼️ '글 먹는 시간' 카드뉴스 갤러리 뷰 -->
    <?php if (empty($posts)): ?>
      <div class="bg-surface rounded-2xl border border-outline-variant/60 p-12 text-center text-xs text-on-surface-variant">등록된 글이 없습니다.</div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($posts as $post): ?>
          <a href="/community/<?= htmlspecialchars($type) ?>/<?= (int)$post['id'] ?>"
             class="group bg-surface rounded-2xl border border-outline-variant/60 overflow-hidden hover:border-primary hover:shadow-lg transition-all flex flex-col">
            <div class="aspect-[4/3] bg-surface-container-low overflow-hidden relative">
              <?php if (!empty($post['file_path'])): ?>
                <img src="<?= htmlspecialchars($post['file_path']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
              <?php else: ?>
                <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-gradient-to-br from-surface-container-low to-surface-container">
                  <span class="material-symbols-outlined text-4xl text-primary/40 mb-2">auto_stories</span>
                  <span class="text-xs font-serif font-bold text-primary line-clamp-2"><?= htmlspecialchars($post['title']) ?></span>
                </div>
              <?php endif; ?>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-between">
              <h3 class="font-bold text-sm text-primary group-hover:text-secondary transition-colors line-clamp-2 leading-snug mb-2">
                <?= htmlspecialchars($post['title']) ?>
              </h3>
              <div class="flex items-center justify-between text-[11px] text-gray-500 pt-2 border-t border-outline-variant/40 mt-auto">
                <span><?= htmlspecialchars($post['author_name'] ?? '도서출판 대장간') ?></span>
                <span><?= date('Y.m.d', strtotime($post['created_at'])) ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <!-- 📋 일반 테이블/리스트 뷰 -->
    <div class="bg-surface rounded-2xl border border-outline-variant/80 overflow-hidden shadow-sm">
      <div class="divide-y divide-outline-variant/60">
        <?php if (empty($posts)): ?>
          <div class="p-12 text-center text-xs text-on-surface-variant">등록된 게시물이 없습니다.</div>
        <?php else: ?>
          <?php foreach ($posts as $post): ?>
            <a href="/community/<?= htmlspecialchars($type) ?>/<?= (int)$post['id'] ?>"
               class="p-4 md:p-5 flex items-center justify-between hover:bg-surface-container-low transition-colors block group">
              <div class="flex-1 min-w-0 pr-4">
                <h3 class="font-medium text-sm md:text-base text-primary group-hover:text-secondary transition-colors line-clamp-1">
                  <?= htmlspecialchars($post['title']) ?>
                </h3>
                <div class="flex items-center gap-3 text-xs text-on-surface-variant mt-1">
                  <span><?= htmlspecialchars($post['author_name'] ?? '관리자') ?></span>
                  <span>•</span>
                  <span><?= date('Y.m.d', strtotime($post['created_at'])) ?></span>
                  <span>•</span>
                  <span>조회 <?= (int)$post['view_count'] ?></span>
                </div>
              </div>
              <span class="material-symbols-outlined text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all">chevron_right</span>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($totalPages > 1):
    $window = 7;
    $startPage = max(1, $page - (int)floor($window / 2));
    $endPage = min($totalPages, $startPage + $window - 1);
    if ($endPage - $startPage + 1 < $window) {
        $startPage = max(1, $endPage - $window + 1);
    }
  ?>
    <nav class="flex items-center justify-center gap-1.5 mt-8" aria-label="Pagination">
      <?php if ($page > 1): ?>
        <a href="/community/<?= htmlspecialchars($type) ?>?page=1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="첫 페이지">«</a>
        <a href="/community/<?= htmlspecialchars($type) ?>?page=<?= $page - 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="이전 페이지">‹</a>
      <?php endif; ?>

      <?php if ($startPage > 1): ?>
        <a href="/community/<?= htmlspecialchars($type) ?>?page=1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs">1</a>
        <?php if ($startPage > 2): ?>
          <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="/community/<?= htmlspecialchars($type) ?>?page=<?= $i ?>"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                  <?= $i === $page ? 'bg-[#07131e] text-white font-bold shadow-sm' : 'border border-outline-variant text-on-surface-variant hover:bg-surface-variant' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
          <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
        <?php endif; ?>
        <a href="/community/<?= htmlspecialchars($type) ?>?page=<?= $totalPages ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="/community/<?= htmlspecialchars($type) ?>?page=<?= $page + 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="다음 페이지">›</a>
        <a href="/community/<?= htmlspecialchars($type) ?>?page=<?= $totalPages ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="마지막 페이지">»</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
