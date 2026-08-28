<?php
/**
 * 저자별 도서 탐색 뷰
 * @var array  $authorsList     전체 저자 목록 [author, book_count]
 * @var string $selectedAuthor  현재 선택된 저자
 * @var array  $books           현재 저자의 도서 목록
 * @var int    $total           도서 총 수
 * @var int    $totalPages      총 페이지 수
 * @var int    $page            현재 페이지
 */
$pageTitle = $selectedAuthor ? $selectedAuthor . ' 저자 도서' : '저자별 도서';
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8">

  <!-- 브레드크럼 & 헤더 -->
  <div class="mb-6">
    <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-1">
      <a href="/" class="hover:text-primary">홈</a>
      <span>›</span>
      <a href="/books" class="hover:text-primary">도서전체보기</a>
      <span>›</span>
      <span class="text-primary font-medium">저자별</span>
    </div>
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-secondary text-2xl">person_search</span>
      <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary">저자별 도서</h1>
    </div>
    <p class="text-xs text-on-surface-variant mt-1">도서출판 대장간의 깊이 있는 저자별 저작을 만나보세요.</p>
  </div>

  <!-- ==================== 1. 저자 태그 / 칩 바 (Author Chips) ==================== -->
  <section class="bg-surface rounded-2xl border border-outline-variant/70 p-5 mb-8 shadow-sm" x-data="{ searchAuthor: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 mb-3 border-b border-outline-variant/40">
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-primary uppercase tracking-wider">주요 저자 목록</span>
        <span class="text-xs text-on-surface-variant">(총 <?= count($authorsList) ?>명)</span>
      </div>
      <!-- 저자 빠른 검색 -->
      <div class="relative w-full sm:w-60">
        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-base">search</span>
        <input type="text" x-model="searchAuthor" placeholder="저자 이름 검색"
               class="w-full bg-surface-container border border-outline-variant/60 rounded-full py-1.5 pl-8 pr-3 text-xs outline-none focus:border-primary"/>
      </div>
    </div>

    <!-- 저자 태그 클라우드 -->
    <div class="flex flex-wrap gap-2 max-h-60 overflow-y-auto pr-1">
      <?php foreach ($authorsList as $a):
        $isCurrent = ($a['author'] === $selectedAuthor);
      ?>
        <a href="/author/<?= urlencode($a['author']) ?>"
           x-show="!searchAuthor || '<?= addslashes($a['author']) ?>'.toLowerCase().includes(searchAuthor.toLowerCase())"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-all
                  <?= $isCurrent ? 'bg-[#1c2833] text-white shadow-sm ring-2 ring-secondary' : 'bg-surface-container hover:bg-surface-container-high text-on-surface border border-outline-variant/40 hover:border-outline-variant' ?>">
          <span><?= htmlspecialchars($a['author']) ?></span>
          <span class="text-[11px] px-1.5 py-0.2 rounded-full <?= $isCurrent ? 'bg-secondary text-white' : 'bg-surface text-on-surface-variant border border-outline-variant/30' ?>">
            <?= (int)$a['book_count'] ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ==================== 2. 선택된 저자 정보 & 도서 목록 ==================== -->
  <?php if ($selectedAuthor): ?>
    <section>
      <!-- 저자 헤더 배너 -->
      <div class="bg-gradient-to-r from-primary-container to-[#2c3e50] text-white rounded-2xl p-6 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-md">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-white/10 rounded-full flex items-center justify-center border border-white/20 shrink-0">
            <span class="material-symbols-outlined text-3xl text-secondary-container">auto_stories</span>
          </div>
          <div>
            <span class="text-xs text-white/70 uppercase tracking-widest">Author Collection</span>
            <h2 class="font-serif text-2xl font-bold mt-0.5"><?= htmlspecialchars($selectedAuthor) ?></h2>
            <p class="text-xs text-white/80 mt-1">대장간 출간 도서 <strong class="text-tertiary-container"><?= number_format($total) ?>권</strong></p>
          </div>
        </div>

        <!-- 정렬 선택 -->
        <div class="flex items-center gap-2 self-end sm:self-center">
          <select onchange="location.href=this.value"
            class="bg-white/10 text-white border border-white/20 rounded-lg px-3 py-1.5 text-xs outline-none">
            <option value="?author=<?= urlencode($selectedAuthor) ?>&sort=new"       <?= (($_GET['sort'] ?? 'new') === 'new') ? 'selected' : '' ?> class="text-gray-900">최신순</option>
            <option value="?author=<?= urlencode($selectedAuthor) ?>&sort=popular"   <?= (($_GET['sort'] ?? '') === 'popular') ? 'selected' : '' ?> class="text-gray-900">인기순</option>
            <option value="?author=<?= urlencode($selectedAuthor) ?>&sort=price_asc" <?= (($_GET['sort'] ?? '') === 'price_asc') ? 'selected' : '' ?> class="text-gray-900">가격 낮은순</option>
            <option value="?author=<?= urlencode($selectedAuthor) ?>&sort=price_desc"<?= (($_GET['sort'] ?? '') === 'price_desc') ? 'selected' : '' ?> class="text-gray-900">가격 높은순</option>
          </select>
        </div>
      </div>

      <!-- 도서 그리드 -->
      <?php if (empty($books)): ?>
        <div class="flex flex-col items-center justify-center py-20 bg-surface rounded-2xl border border-surface-variant text-on-surface-variant">
          <span class="material-symbols-outlined text-5xl mb-3 opacity-30">menu_book</span>
          <p class="text-sm font-medium">등록된 도서가 없습니다.</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <?php foreach ($books as $book): ?>
            <a href="/book/<?= htmlspecialchars($book['book_code']) ?>"
               class="book-card flex flex-col bg-surface rounded-xl border border-surface-variant overflow-hidden hover:shadow-lg transition-all duration-200 group">
              <!-- 표지 -->
              <div class="relative aspect-[3/4] bg-surface-container overflow-hidden">
                <img
                  src="<?= htmlspecialchars($book['cover_image'] ?? '/assets/images/default_book.png') ?>"
                  alt="<?= htmlspecialchars($book['title']) ?>"
                  class="book-cover w-full h-full object-cover"
                  onerror="this.src='/assets/images/default_book.png'"
                />
                <?php if (!empty($book['is_new'])): ?>
                  <span class="absolute top-2 left-2 bg-secondary text-white text-[11px] px-2 py-0.5 rounded font-semibold">NEW</span>
                <?php elseif (!empty($book['is_best'])): ?>
                  <span class="absolute top-2 left-2 bg-tertiary text-white text-[11px] px-2 py-0.5 rounded font-semibold">BEST</span>
                <?php endif; ?>

                <!-- 호버 장바구니 담기 -->
                <div class="absolute inset-0 bg-primary/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                  <button
                    onclick="event.preventDefault(); addToCart(<?= (int)$book['id'] ?>)"
                    class="bg-white text-primary text-xs font-semibold px-4 py-2 rounded-full hover:bg-surface-variant transition-colors shadow-md">
                    장바구니 담기
                  </button>
                </div>
              </div>

              <!-- 도서 정보 -->
              <div class="p-3 flex flex-col gap-1">
                <h3 class="font-serif text-sm font-semibold text-primary line-clamp-2 leading-snug group-hover:text-secondary transition-colors">
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
          $sStr = '&sort=' . urlencode($_GET['sort'] ?? '');
          $authorUrl = '/author/' . urlencode($selectedAuthor);
        ?>
          <nav class="flex items-center justify-center gap-1.5 mt-10" aria-label="Pagination">
            <?php if ($page > 1): ?>
              <a href="<?= $authorUrl ?>?page=1<?= $sStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="첫 페이지">«</a>
              <a href="<?= $authorUrl ?>?page=<?= $page - 1 ?><?= $sStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="이전 페이지">‹</a>
            <?php endif; ?>

            <?php if ($startPage > 1): ?>
              <a href="<?= $authorUrl ?>?page=1<?= $sStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs">1</a>
              <?php if ($startPage > 2): ?>
                <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
              <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
              <a href="<?= $authorUrl ?>?page=<?= $i ?><?= $sStr ?>"
                 class="w-9 h-9 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                        <?= $i === $page ? 'bg-[#07131e] text-white font-bold shadow-md' : 'border border-outline-variant text-on-surface-variant hover:bg-surface-variant' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
              <?php if ($endPage < $totalPages - 1): ?>
                <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
              <?php endif; ?>
              <a href="<?= $authorUrl ?>?page=<?= $totalPages ?><?= $sStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs"><?= $totalPages ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
              <a href="<?= $authorUrl ?>?page=<?= $page + 1 ?><?= $sStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="다음 페이지">›</a>
              <a href="<?= $authorUrl ?>?page=<?= $totalPages ?><?= $sStr ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="마지막 페이지">»</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  <?php endif; ?>

</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

<script>
function addToCart(bookId) {
  fetch('/cart/add', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `book_id=${bookId}&qty=1`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) alert('장바구니에 담았습니다!');
    else alert(data.error || '오류가 발생했습니다.');
  });
}
</script>
