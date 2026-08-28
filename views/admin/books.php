<?php
/**
 * 관리자 도서 목록
 */
$pageTitle = '도서 관리';
$activeMenu = 'books';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="flex flex-col gap-4">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="text-sm text-gray-500">총 <?= number_format($total) ?>권</span>
    </div>
    <a href="/admin/books/create" class="px-4 py-2 bg-[#07131e] text-white text-sm font-medium rounded-lg hover:bg-[#1c2833]">
      + 새 도서 등록
    </a>
  </div>

  <!-- 검색 / 필터 -->
  <form method="GET" class="bg-white p-4 rounded-xl border border-gray-200 flex flex-wrap gap-3 items-center">
    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="도서명 또는 저자 검색"
           class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm flex-1 min-w-[200px] outline-none focus:border-blue-500"/>
    <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm outline-none">
      <option value="">전체 상태</option>
      <option value="SALE" <?= (($_GET['status'] ?? '') === 'SALE') ? 'selected' : '' ?>>판매중</option>
      <option value="SOLDOUT" <?= (($_GET['status'] ?? '') === 'SOLDOUT') ? 'selected' : '' ?>>품절</option>
      <option value="HIDDEN" <?= (($_GET['status'] ?? '') === 'HIDDEN') ? 'selected' : '' ?>>숨김</option>
    </select>
    <button type="submit" class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700">검색</button>
  </form>

  <!-- 목록 테이블 -->
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200">
          <tr>
            <th class="px-4 py-3">코드</th>
            <th class="px-4 py-3">도서분류</th>
            <th class="px-4 py-3">도서명</th>
            <th class="px-4 py-3">저자</th>
            <th class="px-4 py-3 text-right">판매가</th>
            <th class="px-4 py-3 text-center">재고</th>
            <th class="px-4 py-3 text-center">상태</th>
            <th class="px-4 py-3 text-center">관리</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($books as $b):
            $statusLabels = ['SALE'=>'판매중', 'SOLDOUT'=>'품절', 'HIDDEN'=>'숨김'];
            $statusColors = ['SALE'=>'bg-green-100 text-green-700', 'SOLDOUT'=>'bg-red-100 text-red-700', 'HIDDEN'=>'bg-gray-100 text-gray-600'];
          ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs text-gray-500"><?= htmlspecialchars($b['book_code']) ?></td>
              <td class="px-4 py-3 text-xs text-gray-600"><?= htmlspecialchars($b['category_name'] ?? '-') ?></td>
              <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($b['title']) ?></td>
              <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($b['author']) ?></td>
              <td class="px-4 py-3 text-right font-medium"><?= number_format((int)$b['price']) ?>원</td>
              <td class="px-4 py-3 text-center"><?= (int)$b['stock_qty'] ?></td>
              <td class="px-4 py-3 text-center">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $statusColors[$b['status']] ?? '' ?>">
                  <?= $statusLabels[$b['status']] ?? $b['status'] ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <a href="/admin/books/<?= (int)$b['id'] ?>/edit" class="text-blue-600 hover:underline text-xs mr-2">수정</a>
                <button onclick="deleteBook(<?= (int)$b['id'] ?>)" class="text-red-600 hover:underline text-xs">삭제</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($totalPages > 1):
    $window = 7;
    $startPage = max(1, $page - (int)floor($window / 2));
    $endPage = min($totalPages, $startPage + $window - 1);
    if ($endPage - $startPage + 1 < $window) {
        $startPage = max(1, $endPage - $window + 1);
    }
    $queryStr = '&q=' . urlencode($_GET['q'] ?? '') . '&status=' . urlencode($_GET['status'] ?? '');
  ?>
    <nav class="flex items-center justify-center gap-1.5 mt-6" aria-label="Pagination">
      <?php if ($page > 1): ?>
        <a href="/admin/books?page=1<?= $queryStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="첫 페이지">«</a>
        <a href="/admin/books?page=<?= $page - 1 ?><?= $queryStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="이전 페이지">‹</a>
      <?php endif; ?>

      <?php if ($startPage > 1): ?>
        <a href="/admin/books?page=1<?= $queryStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">1</a>
        <?php if ($startPage > 2): ?>
          <span class="w-6 text-center text-gray-400 text-xs">…</span>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="/admin/books?page=<?= $i ?><?= $queryStr ?>"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                  <?= $i === $page ? 'bg-[#07131e] text-white font-bold shadow-sm' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
          <span class="w-6 text-center text-gray-400 text-xs">…</span>
        <?php endif; ?>
        <a href="/admin/books?page=<?= $totalPages ?><?= $queryStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="/admin/books?page=<?= $page + 1 ?><?= $queryStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="다음 페이지">›</a>
        <a href="/admin/books?page=<?= $totalPages ?><?= $queryStr ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="마지막 페이지">»</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>

<script>
function deleteBook(id) {
  if (!confirm('정말 이 도서를 삭제하시겠습니까?')) return;
  fetch(`/admin/books/${id}/delete`, {method: 'POST'})
    .then(r => r.json())
    .then(d => {
      if (d.success) location.reload();
      else alert('삭제에 실패했습니다.');
    });
}
</script>

  </main>
</div>
</body>
</html>
