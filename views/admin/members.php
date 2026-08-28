<?php
/**
 * 관리자 회원 관리
 */
$pageTitle = '회원 관리';
$activeMenu = 'members';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="flex flex-col gap-4">
  <div class="flex items-center justify-between">
    <span class="text-sm text-gray-500">총 <?= number_format($total) ?>명의 회원</span>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200">
          <tr>
            <th class="px-4 py-3">아이디</th>
            <th class="px-4 py-3">실명 (닉네임)</th>
            <th class="px-4 py-3">이메일</th>
            <th class="px-4 py-3">연락처</th>
            <th class="px-4 py-3 text-center">알림/SNS 연동</th>
            <th class="px-4 py-3 text-right">보유 적립금</th>
            <th class="px-4 py-3 text-center">가입일시</th>
            <th class="px-4 py-3 text-center">적립금 조정</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($members as $m): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-800"><?= htmlspecialchars($m['username']) ?></td>
              <td class="px-4 py-3 font-medium text-gray-900">
                <span><?= htmlspecialchars($m['name']) ?></span>
                <?php if (!empty($m['nickname']) && $m['nickname'] !== $m['name']): ?>
                  <span class="text-xs text-gray-500 block font-normal">(<?= htmlspecialchars($m['nickname']) ?>)</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-gray-600 text-xs"><?= htmlspecialchars($m['email']) ?></td>
              <td class="px-4 py-3 text-gray-600 text-xs"><?= htmlspecialchars($m['phone'] ?? '-') ?></td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1">
                  <!-- 카카오톡 연동 여부 -->
                  <?php if (!empty($m['notify_kakao'])): ?>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-800" title="카카오톡 알림톡 수신">TALK</span>
                  <?php endif; ?>
                  <!-- 텔레그램 연동 여부 -->
                  <?php if (!empty($m['telegram_id'])): ?>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800" title="텔레그램 ID: <?= htmlspecialchars($m['telegram_id']) ?>">TG</span>
                  <?php else: ?>
                    <span class="text-gray-300 text-xs">-</span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-4 py-3 text-right font-bold text-gray-800"><?= number_format((int)$m['points']) ?> P</td>
              <td class="px-4 py-3 text-center text-xs text-gray-500"><?= date('Y.m.d', strtotime($m['created_at'])) ?></td>
              <td class="px-4 py-3 text-center">
                <button onclick="adjustPoints(<?= (int)$m['id'] ?>, '<?= htmlspecialchars($m['name']) ?>')" class="text-xs px-2.5 py-1 bg-blue-50 text-blue-600 rounded border border-blue-200 hover:bg-blue-100">
                  적립금 지급/차감
                </button>
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
  ?>
    <nav class="flex items-center justify-center gap-1.5 mt-6" aria-label="Pagination">
      <!-- 처음으로 -->
      <?php if ($page > 1): ?>
        <a href="/admin/members?page=1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="첫 페이지">«</a>
        <a href="/admin/members?page=<?= $page - 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="이전 페이지">‹</a>
      <?php endif; ?>

      <!-- 1페이지 생략 처리 -->
      <?php if ($startPage > 1): ?>
        <a href="/admin/members?page=1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">1</a>
        <?php if ($startPage > 2): ?>
          <span class="w-6 text-center text-gray-400 text-xs">…</span>
        <?php endif; ?>
      <?php endif; ?>

      <!-- 중앙 7개 번호 윈도우 -->
      <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="/admin/members?page=<?= $i ?>"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                  <?= $i === $page ? 'bg-[#07131e] text-white font-bold shadow-sm' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <!-- 마지막 페이지 생략 처리 -->
      <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
          <span class="w-6 text-center text-gray-400 text-xs">…</span>
        <?php endif; ?>
        <a href="/admin/members?page=<?= $totalPages ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs"><?= $totalPages ?></a>
      <?php endif; ?>

      <!-- 다음 / 마지막 -->
      <?php if ($page < $totalPages): ?>
        <a href="/admin/members?page=<?= $page + 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="다음 페이지">›</a>
        <a href="/admin/members?page=<?= $totalPages ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="마지막 페이지">»</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>

<script>
function adjustPoints(id, name) {
  const amount = prompt(`${name} 회원님에게 적용할 적립금 금액을 입력하세요 (예: 1000 또는 -500):`);
  if (!amount || isNaN(amount)) return;
  fetch(`/admin/members/${id}/points`, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `amount=${parseInt(amount)}`
  }).then(r => r.json()).then(d => {
    if (d.success) {
      alert('적립금이 성공적으로 조정되었습니다.');
      location.reload();
    } else {
      alert('오류가 발생했습니다.');
    }
  });
}
</script>

  </main>
</div>
</body>
</html>
