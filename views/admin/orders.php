<?php
/**
 * 관리자 주문 목록
 */
$pageTitle = '주문 관리';
$activeMenu = 'orders';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="flex flex-col gap-4">
  <div class="flex items-center justify-between">
    <span class="text-sm text-gray-500">총 <?= number_format($total) ?>건의 주문</span>
  </div>

  <!-- 목록 테이블 -->
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 whitespace-nowrap">주문번호</th>
            <th class="px-4 py-3 whitespace-nowrap">주문일시</th>
            <th class="px-4 py-3">주문도서 (수량)</th>
            <th class="px-4 py-3 whitespace-nowrap">주문자 / 연락처</th>
            <th class="px-4 py-3">수령인 / 주소</th>
            <th class="px-4 py-3 text-right whitespace-nowrap">결제금액</th>
            <th class="px-4 py-3 text-center whitespace-nowrap">결제상태</th>
            <th class="px-4 py-3 text-center whitespace-nowrap">배송상태</th>
            <th class="px-4 py-3 text-center whitespace-nowrap">운송장번호</th>
            <th class="px-4 py-3 text-center whitespace-nowrap">상태변경</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php foreach ($orders as $o):
            $payColors = ['WAITING'=>'bg-yellow-100 text-yellow-700', 'PAID'=>'bg-green-100 text-green-700', 'CANCELLED'=>'bg-red-100 text-red-700', 'REFUNDED'=>'bg-gray-100 text-gray-600'];
            $delColors = ['PREPARING'=>'bg-gray-100 text-gray-600', 'SHIPPING'=>'bg-blue-100 text-blue-700', 'DELIVERED'=>'bg-green-100 text-green-700'];
          ?>
            <tr class="hover:bg-gray-50 transition-colors" id="order-row-<?= (int)$o['id'] ?>">
              <!-- 주문번호 -->
              <td class="px-4 py-3 font-mono text-xs text-gray-700 whitespace-nowrap font-medium">
                <?= htmlspecialchars($o['order_no']) ?>
              </td>

              <!-- 주문일시 -->
              <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">
                <?= !empty($o['created_at']) ? date('Y-m-d H:i', strtotime($o['created_at'])) : '-' ?>
              </td>

              <!-- 주문도서 (수량) -->
              <td class="px-4 py-3 min-w-[200px] max-w-[280px]">
                <?php if (!empty($o['items'])): ?>
                  <div class="space-y-1.5">
                    <?php foreach ($o['items'] as $item): ?>
                      <div class="flex items-start justify-between gap-2 text-xs">
                        <span class="font-medium text-gray-900 line-clamp-1" title="<?= htmlspecialchars($item['book_title']) ?>">
                          <?= htmlspecialchars($item['book_title']) ?>
                        </span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                          <?= number_format((int)$item['quantity']) ?>권
                        </span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <span class="text-xs text-gray-400">도서 정보 없음</span>
                <?php endif; ?>
              </td>

              <!-- 주문자 / 연락처 -->
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="font-medium text-gray-900"><?= htmlspecialchars($o['orderer_name']) ?></div>
                <div class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($o['orderer_phone']) ?></div>
              </td>

              <!-- 수령인 / 주소 -->
              <td class="px-4 py-3 min-w-[180px] max-w-[260px]">
                <div class="font-medium text-gray-900"><?= htmlspecialchars($o['receiver_name']) ?></div>
                <div class="text-xs text-gray-500 line-clamp-1" title="<?= htmlspecialchars($o['shipping_address1'] . ' ' . $o['shipping_address2']) ?>">
                  <?= htmlspecialchars($o['shipping_address1'] . ' ' . $o['shipping_address2']) ?>
                </div>
              </td>

              <!-- 결제금액 -->
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <div class="font-bold text-gray-900"><?= number_format((int)$o['total_pay_price']) ?>원</div>
                <div class="text-[11px] text-gray-400"><?= htmlspecialchars($o['pay_method'] ?? '신용카드') ?></div>
              </td>

              <!-- 결제상태 -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <select onchange="updateOrderStatus(<?= (int)$o['id'] ?>)" id="pay-status-<?= (int)$o['id'] ?>" class="text-xs border rounded-lg px-2 py-1 bg-white focus:ring-1 focus:ring-primary">
                  <option value="WAITING" <?= ($o['pay_status'] === 'WAITING') ? 'selected' : '' ?>>입금대기</option>
                  <option value="PAID" <?= ($o['pay_status'] === 'PAID') ? 'selected' : '' ?>>결제완료</option>
                  <option value="CANCELLED" <?= ($o['pay_status'] === 'CANCELLED') ? 'selected' : '' ?>>취소</option>
                  <option value="REFUNDED" <?= ($o['pay_status'] === 'REFUNDED') ? 'selected' : '' ?>>환불</option>
                </select>
              </td>

              <!-- 배송상태 -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <select onchange="updateOrderStatus(<?= (int)$o['id'] ?>)" id="del-status-<?= (int)$o['id'] ?>" class="text-xs border rounded-lg px-2 py-1 bg-white focus:ring-1 focus:ring-primary">
                  <option value="PREPARING" <?= ($o['delivery_status'] === 'PREPARING') ? 'selected' : '' ?>>배송준비</option>
                  <option value="SHIPPING" <?= ($o['delivery_status'] === 'SHIPPING') ? 'selected' : '' ?>>배송중</option>
                  <option value="DELIVERED" <?= ($o['delivery_status'] === 'DELIVERED') ? 'selected' : '' ?>>배송완료</option>
                </select>
              </td>

              <!-- 운송장번호 -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <input type="text" id="tracking-<?= (int)$o['id'] ?>" value="<?= htmlspecialchars($o['tracking_number'] ?? '') ?>" placeholder="운송장번호" class="text-xs border rounded-lg px-2 py-1 w-28 text-center font-mono focus:ring-1 focus:ring-primary" onchange="updateOrderStatus(<?= (int)$o['id'] ?>)"/>
              </td>

              <!-- 상태변경 버튼 -->
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <button onclick="updateOrderStatus(<?= (int)$o['id'] ?>)" class="text-xs px-2.5 py-1.5 bg-[#07131e] text-white rounded-lg hover:bg-gray-800 transition-colors inline-flex items-center gap-1 shadow-sm">
                  <span class="material-symbols-outlined text-xs">save</span>
                  저장
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
      <?php if ($page > 1): ?>
        <a href="/admin/orders?page=1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="첫 페이지">«</a>
        <a href="/admin/orders?page=<?= $page - 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="이전 페이지">‹</a>
      <?php endif; ?>

      <?php if ($startPage > 1): ?>
        <a href="/admin/orders?page=1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs">1</a>
        <?php if ($startPage > 2): ?>
          <span class="w-6 text-center text-gray-400 text-xs">…</span>
        <?php endif; ?>
      <?php endif; ?>

      <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="/admin/orders?page=<?= $i ?>"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                  <?= $i === $page ? 'bg-[#07131e] text-white font-bold shadow-sm' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($endPage < $totalPages): ?>
        <?php if ($endPage < $totalPages - 1): ?>
          <span class="w-6 text-center text-gray-400 text-xs">…</span>
        <?php endif; ?>
        <a href="/admin/orders?page=<?= $totalPages ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-xs"><?= $totalPages ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="/admin/orders?page=<?= $page + 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="다음 페이지">›</a>
        <a href="/admin/orders?page=<?= $totalPages ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold" title="마지막 페이지">»</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>

<script>
function updateOrderStatus(id) {
  const pay = document.getElementById(`pay-status-${id}`).value;
  const del = document.getElementById(`del-status-${id}`).value;
  const track = document.getElementById(`tracking-${id}`).value;

  fetch(`/admin/orders/${id}/status`, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `pay_status=${encodeURIComponent(pay)}&delivery_status=${encodeURIComponent(del)}&tracking_number=${encodeURIComponent(track)}`
  }).then(r => r.json()).then(d => {
    if (d.success) alert('주문 상태가 변경되었습니다.');
    else alert('상태 변경에 실패했습니다.');
  });
}
</script>

  </main>
</div>
</body>
</html>
