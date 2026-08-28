<?php
/**
 * 검색 결과 페이지
 */
$pageTitle = '도서 검색: ' . ($keyword ?: '전체');
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8">
  <div class="mb-6">
    <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary">
      <?php if ($keyword): ?>
        '<span class="text-secondary"><?= htmlspecialchars($keyword) ?></span>' 검색 결과
      <?php else: ?>
        도서 검색
      <?php endif; ?>
    </h1>
    <p class="text-sm text-on-surface-variant mt-1">총 <?= number_format($total) ?>권의 도서가 검색되었습니다.</p>
  </div>

  <?php if (empty($books)): ?>
    <div class="flex flex-col items-center justify-center py-20 bg-surface rounded-2xl border border-surface-variant text-on-surface-variant">
      <span class="material-symbols-outlined text-6xl mb-3 opacity-30">search_off</span>
      <p class="text-base font-medium">검색된 도서가 없습니다.</p>
      <p class="text-xs mt-1">다른 검색어로 다시 시도해 보세요.</p>
      <a href="/books" class="mt-4 px-6 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
        전체 도서 보기
      </a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <?php foreach ($books as $book): ?>
        <a href="/book/<?= htmlspecialchars($book['book_code']) ?>"
           class="book-card flex flex-col bg-surface rounded-xl border border-surface-variant overflow-hidden hover:shadow-lg transition-all duration-200 group">
          <div class="relative aspect-[3/4] bg-surface-container overflow-hidden">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>"
                 alt="<?= htmlspecialchars($book['title']) ?>"
                 class="book-cover w-full h-full object-cover"
                 onerror="this.src='/assets/images/default_book.png'"/>
          </div>
          <div class="p-3 flex flex-col gap-1">
            <h3 class="font-serif text-sm font-semibold text-primary line-clamp-2 leading-snug">
              <?= htmlspecialchars($book['title']) ?>
            </h3>
            <p class="text-xs text-on-surface-variant"><?= htmlspecialchars($book['author']) ?></p>
            <p class="text-sm font-bold text-primary mt-1"><?= number_format((int)$book['price']) ?>원</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- 페이지네이션 (7개 윈도우 + 양쪽 생략) -->
    <?php if ($totalPages > 1):
      $window = 7;
      $startPage = max(1, $page - (int)floor($window / 2));
      $endPage = min($totalPages, $startPage + $window - 1);
      if ($endPage - $startPage + 1 < $window) {
          $startPage = max(1, $endPage - $window + 1);
      }
      $qStr = '&q=' . urlencode($keyword);
    ?>
      <nav class="flex items-center justify-center gap-1.5 mt-10" aria-label="Pagination">
        <?php if ($page > 1): ?>
          <a href="/search?page=1<?= $qStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="첫 페이지">«</a>
          <a href="/search?page=<?= $page - 1 ?><?= $qStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="이전 페이지">‹</a>
        <?php endif; ?>

        <?php if ($startPage > 1): ?>
          <a href="/search?page=1<?= $qStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs">1</a>
          <?php if ($startPage > 2): ?>
            <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
          <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
          <a href="/search?page=<?= $i ?><?= $qStr ?>"
             class="w-9 h-9 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                    <?= $i === $page ? 'bg-[#07131e] text-white font-bold shadow-md' : 'border border-outline-variant text-on-surface-variant hover:bg-surface-variant' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
          <?php if ($endPage < $totalPages - 1): ?>
            <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
          <?php endif; ?>
          <a href="/search?page=<?= $totalPages ?><?= $qStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs"><?= $totalPages ?></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
          <a href="/search?page=<?= $page + 1 ?><?= $qStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="다음 페이지">›</a>
          <a href="/search?page=<?= $totalPages ?><?= $qStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="마지막 페이지">»</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
