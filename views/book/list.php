<?php
/**
 * 도서 목록 (카테고리/검색 결과 공용)
 * Stitch: 도서 카테고리 화면 기반
 */
$pageTitle = $category['name'] ?? '도서 목록';
include APP_ROOT . '/views/layouts/header.php';

// 카테고리 그룹 분류 (2차 분류)
$seriesCats = array_filter($categories ?? [], fn($c) => ($c['type'] === 'SERIES' && ($c['parent_code'] === '1030' || strlen($c['code']) > 4)));
$topicCats  = array_filter($categories ?? [], fn($c) => ($c['type'] === 'TOPIC' && ($c['parent_code'] === '1040' || strlen($c['code']) > 4)));
$bigongCats = array_filter($categories ?? [], fn($c) => ($c['type'] === 'BIGONG' && ($c['parent_code'] === '1050' || strlen($c['code']) > 4)));
$nicsCats   = array_filter($categories ?? [], fn($c) => ($c['type'] === 'NICS' && ($c['parent_code'] === '1060' || strlen($c['code']) > 4)));
$currentCode = $category['code'] ?? '';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">

  <!-- 헤더 -->
  <div class="mb-6 flex flex-col gap-4 pb-4 border-b border-outline-variant/60 w-full">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
      <div>
        <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-1">
          <a href="/" class="hover:text-primary">홈</a>
          <span>›</span>
          <a href="/books" class="hover:text-primary">도서</a>
          <?php if (!empty($parentCategory)): ?>
            <span>›</span>
            <a href="/category/<?= htmlspecialchars($parentCategory['code']) ?>" class="hover:text-primary"><?= htmlspecialchars($parentCategory['name']) ?></a>
          <?php endif; ?>
          <?php if (isset($category)): ?>
            <span>›</span>
            <span class="text-primary font-medium"><?= htmlspecialchars($category['name']) ?></span>
          <?php endif; ?>
        </div>
        <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary">
          <?= htmlspecialchars($category['name'] ?? '전체 도서') ?>
        </h1>
        <p class="text-xs text-on-surface-variant mt-1">총 <?= number_format($total ?? 0) ?>권의 도서가 등록되어 있습니다.</p>
      </div>

      <!-- 정렬 -->
      <div class="flex items-center gap-2">
        <label class="text-xs text-on-surface-variant">정렬 기준:</label>
        <select onchange="location.href=this.value"
          class="bg-surface border border-outline-variant rounded-lg px-3 py-1.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none">
          <option value="?sort=new"       <?= (($_GET['sort'] ?? 'new') === 'new')        ? 'selected' : '' ?>>최신순</option>
          <option value="?sort=popular"   <?= (($_GET['sort'] ?? '') === 'popular')        ? 'selected' : '' ?>>인기순</option>
          <option value="?sort=price_asc" <?= (($_GET['sort'] ?? '') === 'price_asc')      ? 'selected' : '' ?>>가격 낮은순</option>
          <option value="?sort=price_desc"<?= (($_GET['sort'] ?? '') === 'price_desc')     ? 'selected' : '' ?>>가격 높은순</option>
        </select>
      </div>
    </div>

    <!-- 3차 상세 소분류 칩 바 (있는 경우) -->
    <?php if (!empty($subCategories)): ?>
      <div class="flex items-center gap-2 overflow-x-auto py-2 hide-scrollbar w-full">
        <span class="text-xs font-semibold text-gray-500 shrink-0">세부 분류:</span>
        <a href="/category/<?= htmlspecialchars($parentCategory['code'] ?? $currentCode) ?>"
           class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0 <?= empty($parentCategory) || $currentCode === $parentCategory['code'] ? 'bg-primary text-white font-bold shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
          전체보기
        </a>
        <?php foreach ($subCategories as $sub): ?>
          <a href="/category/<?= htmlspecialchars($sub['code']) ?>"
             class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-medium transition-colors shrink-0 <?= ($currentCode === $sub['code']) ? 'bg-secondary text-white font-bold shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            <span><?= htmlspecialchars($sub['name']) ?></span>
            <span class="text-[11px] px-1.5 py-0.2 rounded-full <?= ($currentCode === $sub['code']) ? 'bg-white/20 text-white' : 'bg-white text-gray-600 border border-gray-200' ?>">
              <?= (int)($sub['book_count'] ?? 0) ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="flex flex-col md:flex-row gap-8 items-start w-full">

    <!-- 사이드바: 대장간 공식 계층형 카테고리 필터 -->
    <aside class="hidden md:block w-60 shrink-0" x-data="{ openSec: '<?= str_starts_with($currentCode, '1030') ? 'series' : (str_starts_with($currentCode, '1040') ? 'topic' : (str_starts_with($currentCode, '1050') ? 'bigong' : (str_starts_with($currentCode, '1060') ? 'nics' : 'series'))) ?>' }">
      <div class="bg-surface-container-low rounded-2xl p-4 border border-outline-variant/60 sticky top-24 shadow-sm">
        <h3 class="font-serif font-bold text-primary text-sm mb-3 pb-2 border-b border-outline-variant/60 flex items-center justify-between">
          <span>도서 분류</span>
          <a href="/books" class="text-[11px] font-sans font-normal text-secondary hover:underline">전체보기</a>
        </h3>

        <nav class="flex flex-col gap-2 text-xs">
          <!-- 1. 전체 도서 -->
          <a href="/books"
             class="px-3 py-2 rounded-lg font-medium transition-colors <?= empty($currentCode) ? 'bg-[#07131e] text-white font-semibold' : 'text-on-surface hover:bg-surface-variant' ?>">
            📚 도서전체보기
          </a>

          <!-- 2. 시리즈 그룹 -->
          <div class="border-t border-outline-variant/40 pt-2">
            <button @click="openSec = (openSec === 'series' ? '' : 'series')" class="w-full px-2 py-1.5 flex items-center justify-between font-semibold text-primary hover:text-secondary text-left">
              <span>시리즈 (<?= count($seriesCats) ?>)</span>
              <span class="material-symbols-outlined text-xs" x-text="openSec === 'series' ? 'expand_less' : 'expand_more'"></span>
            </button>
            <div x-show="openSec === 'series'" class="pl-2 flex flex-col gap-0.5 mt-1">
              <?php foreach ($seriesCats as $sc): ?>
                <a href="/category/<?= htmlspecialchars($sc['code']) ?>"
                   class="px-2 py-1.5 rounded-md transition-colors <?= ($currentCode === $sc['code']) ? 'bg-secondary text-white font-semibold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-variant' ?>">
                  • <?= htmlspecialchars($sc['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- 3. 주제별/장르별 그룹 -->
          <div class="border-t border-outline-variant/40 pt-2">
            <button @click="openSec = (openSec === 'topic' ? '' : 'topic')" class="w-full px-2 py-1.5 flex items-center justify-between font-semibold text-primary hover:text-secondary text-left">
              <span>주제별/장르별</span>
              <span class="material-symbols-outlined text-xs" x-text="openSec === 'topic' ? 'expand_less' : 'expand_more'"></span>
            </button>
            <div x-show="openSec === 'topic'" class="pl-2 flex flex-col gap-0.5 mt-1">
              <?php foreach ($topicCats as $tc): ?>
                <a href="/category/<?= htmlspecialchars($tc['code']) ?>"
                   class="px-2 py-1.5 rounded-md transition-colors <?= ($currentCode === $tc['code']) ? 'bg-secondary text-white font-semibold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-variant' ?>">
                  • <?= htmlspecialchars($tc['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- 4. 도서출판비공 그룹 -->
          <div class="border-t border-outline-variant/40 pt-2">
            <button @click="openSec = (openSec === 'bigong' ? '' : 'bigong')" class="w-full px-2 py-1.5 flex items-center justify-between font-semibold text-primary hover:text-secondary text-left">
              <span>도서출판비공 (<?= count($bigongCats) ?>)</span>
              <span class="material-symbols-outlined text-xs" x-text="openSec === 'bigong' ? 'expand_less' : 'expand_more'"></span>
            </button>
            <div x-show="openSec === 'bigong'" class="pl-2 flex flex-col gap-0.5 mt-1">
              <?php foreach ($bigongCats as $bc): ?>
                <a href="/category/<?= htmlspecialchars($bc['code']) ?>"
                   class="px-2 py-1.5 rounded-md transition-colors <?= ($currentCode === $bc['code']) ? 'bg-secondary text-white font-semibold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-variant' ?>">
                  • <?= htmlspecialchars($bc['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- 5. NICS 그룹 -->
          <div class="border-t border-outline-variant/40 pt-2">
            <button @click="openSec = (openSec === 'nics' ? '' : 'nics')" class="w-full px-2 py-1.5 flex items-center justify-between font-semibold text-primary hover:text-secondary text-left">
              <span>NICS</span>
              <span class="material-symbols-outlined text-xs" x-text="openSec === 'nics' ? 'expand_less' : 'expand_more'"></span>
            </button>
            <div x-show="openSec === 'nics'" class="pl-2 flex flex-col gap-0.5 mt-1">
              <?php foreach ($nicsCats as $nc): ?>
                <a href="/category/<?= htmlspecialchars($nc['code']) ?>"
                   class="px-2 py-1.5 rounded-md transition-colors <?= ($currentCode === $nc['code']) ? 'bg-secondary text-white font-semibold' : 'text-on-surface-variant hover:text-primary hover:bg-surface-variant' ?>">
                  • <?= htmlspecialchars($nc['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

        </nav>
      </div>
    </aside>

    <!-- 도서 그리드 / 빈 상태 (전체 너비 100% 꽉 채움) -->
    <div class="flex-1 min-w-0 w-full">
      <?php if (empty($books)): ?>
        <div class="w-full flex flex-col items-center justify-center min-h-[460px] p-8 md:p-16 bg-surface-container-low rounded-2xl border border-outline-variant/60 text-center shadow-sm">
          <div class="w-20 h-20 rounded-2xl bg-surface-container flex items-center justify-center mb-5 text-on-surface-variant/40 shadow-inner">
            <span class="material-symbols-outlined text-5xl">menu_book</span>
          </div>
          <h3 class="font-serif text-xl font-bold text-primary mb-2">해당 도서분류에 등록된 도서가 없습니다.</h3>
          <p class="text-xs text-on-surface-variant max-w-md mb-8 leading-relaxed">
            현재 준비 중이거나 해당 분류의 판매 도서가 없습니다.<br/>
            다른 도서분류를 선택하시거나 전체 도서 목록을 확인해 보세요.
          </p>
          <div class="flex flex-wrap items-center justify-center gap-3">
            <a href="/books" class="px-6 py-3 bg-[#07131e] text-white rounded-xl text-xs font-semibold hover:bg-[#1c2833] transition-all shadow-md">
              전체 도서 보기
            </a>
            <?php if (!empty($parentCategory)): ?>
              <a href="/category/<?= htmlspecialchars($parentCategory['code']) ?>" class="px-6 py-3 bg-white border border-outline-variant text-on-surface rounded-xl text-xs font-medium hover:bg-surface-variant transition-all shadow-sm">
                상위 분류(<?= htmlspecialchars($parentCategory['name']) ?>) 전체보기
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <?php foreach ($books as $book): ?>
            <a href="/book/<?= htmlspecialchars($book['book_code']) ?>"
               class="book-card flex flex-col bg-surface rounded-xl border border-surface-variant overflow-hidden hover:shadow-xl transition-all duration-300 group">

              <!-- 표지 -->
              <div class="relative aspect-[3/4] bg-surface-container overflow-hidden">
                <img
                  src="<?= htmlspecialchars($book['cover_image'] ?? '/assets/images/default_book.png') ?>"
                  alt="<?= htmlspecialchars($book['title']) ?>"
                  class="book-cover w-full h-full object-cover"
                  onerror="this.src='/assets/images/default_book.png'"
                />
                <?php if (!empty($book['is_new'])): ?>
                  <span class="absolute top-2 left-2 bg-secondary text-white text-[11px] px-2 py-0.5 rounded font-semibold shadow-sm">NEW</span>
                <?php elseif (!empty($book['is_best'])): ?>
                  <span class="absolute top-2 left-2 bg-tertiary text-white text-[11px] px-2 py-0.5 rounded font-semibold shadow-sm">BEST</span>
                <?php endif; ?>

                <!-- 담기 오버레이 (호버) -->
                <div class="absolute inset-0 bg-primary/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                  <button
                    onclick="event.preventDefault(); addToCart(<?= (int)$book['id'] ?>)"
                    class="bg-white text-primary text-xs font-semibold px-4 py-2 rounded-full hover:bg-surface-variant transition-colors shadow-md">
                    장바구니 담기
                  </button>
                </div>
              </div>

              <!-- 도서 정보 -->
              <div class="p-3.5 flex flex-col gap-1">
                <h3 class="font-serif text-sm font-semibold text-primary line-clamp-2 leading-snug group-hover:text-secondary transition-colors">
                  <?= htmlspecialchars($book['title']) ?>
                </h3>
                <p class="text-xs text-on-surface-variant"><?= htmlspecialchars($book['author']) ?></p>
                <div class="flex items-baseline gap-2 mt-1">
                  <span class="text-sm font-bold text-primary"><?= number_format((int)$book['price']) ?>원</span>
                  <?php if (($book['original_price'] ?? 0) > $book['price']): ?>
                    <span class="text-xs text-on-surface-variant line-through"><?= number_format((int)$book['original_price']) ?>원</span>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- 페이지네이션 (7개 윈도우 + 양쪽 생략) -->
        <?php if (($totalPages ?? 1) > 1):
          $currentPage = (int)($_GET['page'] ?? 1);
          $baseUrl     = strtok($_SERVER['REQUEST_URI'], '?');
          $queryParams = $_GET;

          $window = 7;
          $startPage = max(1, $currentPage - (int)floor($window / 2));
          $endPage = min($totalPages, $startPage + $window - 1);
          if ($endPage - $startPage + 1 < $window) {
              $startPage = max(1, $endPage - $window + 1);
          }

          $buildUrl = function($p) use ($baseUrl, $queryParams) {
              $queryParams['page'] = $p;
              return $baseUrl . '?' . http_build_query($queryParams);
          };
        ?>
          <nav class="flex items-center justify-center gap-1.5 mt-12" aria-label="Pagination">
            <?php if ($currentPage > 1): ?>
              <a href="<?= htmlspecialchars($buildUrl(1)) ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="첫 페이지">«</a>
              <a href="<?= htmlspecialchars($buildUrl($currentPage - 1)) ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="이전 페이지">‹</a>
            <?php endif; ?>

            <?php if ($startPage > 1): ?>
              <a href="<?= htmlspecialchars($buildUrl(1)) ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs">1</a>
              <?php if ($startPage > 2): ?>
                <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
              <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
              <a href="<?= htmlspecialchars($buildUrl($i)) ?>"
                 class="w-9 h-9 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                        <?= $i === $currentPage ? 'bg-[#07131e] text-white font-bold shadow-md' : 'border border-outline-variant text-on-surface-variant hover:bg-surface-variant' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
              <?php if ($endPage < $totalPages - 1): ?>
                <span class="w-6 text-center text-on-surface-variant/40 text-xs">…</span>
              <?php endif; ?>
              <a href="<?= htmlspecialchars($buildUrl($totalPages)) ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface hover:bg-surface-variant text-xs"><?= $totalPages ?></a>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
              <a href="<?= htmlspecialchars($buildUrl($currentPage + 1)) ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="다음 페이지">›</a>
              <a href="<?= htmlspecialchars($buildUrl($totalPages)) ?>" class="w-9 h-9 flex items-center justify-center rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-variant text-xs font-bold" title="마지막 페이지">»</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
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
    if (data.success) {
      alert('장바구니에 도서를 담았습니다!');
      location.reload();
    } else {
      alert(data.error || '오류가 발생했습니다.');
    }
  });
}
</script>
