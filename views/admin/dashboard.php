<?php
/**
 * 관리자 대시보드
 */
$pageTitle = '대시보드';
$activeMenu = 'dashboard';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<!-- KPI 카드 -->
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
  <?php
  $kpiCards = [
    ['오늘 주문',    $stats['today_orders'],    'shopping_cart', 'bg-blue-50 text-blue-600'],
    ['입금 대기',    $stats['waiting_payment'], 'payments',      'bg-yellow-50 text-yellow-600'],
    ['배송 준비중',  $stats['preparing_ship'],  'inventory_2',   'bg-orange-50 text-orange-600'],
    ['전체 도서',    $stats['total_books'],     'menu_book',     'bg-green-50 text-green-600'],
    ['전체 회원',    $stats['total_users'],     'group',         'bg-purple-50 text-purple-600'],
    ['이달 매출',    number_format($stats['monthly_revenue']) . '원', 'paid', 'bg-red-50 text-red-600'],
  ];
  foreach ($kpiCards as [$label, $value, $icon, $color]):
  ?>
    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-gray-500"><?= $label ?></span>
        <div class="w-8 h-8 <?= $color ?> rounded-lg flex items-center justify-center">
          <span class="material-symbols-outlined text-base"><?= $icon ?></span>
        </div>
      </div>
      <p class="text-xl font-bold text-gray-800"><?= $value ?></p>
    </div>
  <?php endforeach; ?>
</div>

<!-- 최근 주문 -->
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
  <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
    <h2 class="font-semibold text-gray-800">최근 주문</h2>
    <a href="/admin/orders" class="text-xs text-blue-600 hover:underline">전체 보기 →</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm text-left">
      <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
        <tr>
          <th class="px-4 py-3 whitespace-nowrap">주문번호</th>
          <th class="px-4 py-3 whitespace-nowrap">주문일시</th>
          <th class="px-4 py-3">주문도서 (수량)</th>
          <th class="px-4 py-3 whitespace-nowrap">주문자 / 연락처</th>
          <th class="px-4 py-3 text-right whitespace-nowrap">결제금액</th>
          <th class="px-4 py-3 text-center whitespace-nowrap">결제상태</th>
          <th class="px-4 py-3 text-center whitespace-nowrap">배송상태</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php foreach ($recentOrders as $order):
          $payColors = ['WAITING'=>'bg-yellow-100 text-yellow-700','PAID'=>'bg-green-100 text-green-700',
                        'CANCELLED'=>'bg-red-100 text-red-700','REFUNDED'=>'bg-gray-100 text-gray-600'];
          $payLabels = ['WAITING'=>'입금대기','PAID'=>'결제완료','CANCELLED'=>'취소','REFUNDED'=>'환불'];
          $delColors = ['PREPARING'=>'bg-gray-100 text-gray-600','SHIPPING'=>'bg-blue-100 text-blue-700','DELIVERED'=>'bg-green-100 text-green-700'];
          $delLabels = ['PREPARING'=>'배송준비','SHIPPING'=>'배송중','DELIVERED'=>'배송완료'];
        ?>
          <tr class="hover:bg-gray-50/80 transition-colors">
            <!-- 주문번호 -->
            <td class="px-4 py-3 font-mono text-xs text-gray-700 whitespace-nowrap font-medium">
              <a href="/admin/orders" class="hover:text-blue-600 hover:underline"><?= htmlspecialchars($order['order_no']) ?></a>
            </td>

            <!-- 주문일시 -->
            <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">
              <?= !empty($order['created_at']) ? date('Y-m-d H:i', strtotime($order['created_at'])) : '-' ?>
            </td>

            <!-- 주문도서 (수량) -->
            <td class="px-4 py-3 min-w-[200px] max-w-[280px]">
              <?php if (!empty($order['items'])): ?>
                <div class="space-y-1">
                  <?php foreach ($order['items'] as $item): ?>
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
              <div class="font-medium text-gray-800"><?= htmlspecialchars($order['orderer_name']) ?></div>
              <?php if (!empty($order['orderer_phone'])): ?>
                <div class="text-[11px] text-gray-500 font-mono"><?= htmlspecialchars($order['orderer_phone']) ?></div>
              <?php endif; ?>
            </td>

            <!-- 결제금액 -->
            <td class="px-4 py-3 text-right font-bold text-gray-900 whitespace-nowrap">
              <?= number_format((int)$order['total_pay_price']) ?>원
            </td>

            <!-- 결제상태 -->
            <td class="px-4 py-3 text-center whitespace-nowrap">
              <span class="inline-block text-xs px-2.5 py-0.5 rounded-full font-medium <?= $payColors[$order['pay_status']] ?? 'bg-gray-100 text-gray-600' ?>">
                <?= $payLabels[$order['pay_status']] ?? ($order['pay_status'] ?? '-') ?>
              </span>
            </td>

            <!-- 배송상태 -->
            <td class="px-4 py-3 text-center whitespace-nowrap">
              <span class="inline-block text-xs px-2.5 py-0.5 rounded-full font-medium <?= $delColors[$order['delivery_status']] ?? 'bg-gray-100 text-gray-600' ?>">
                <?= $delLabels[$order['delivery_status']] ?? ($order['delivery_status'] ?? '-') ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

  </main>
</div>
</body>
</html>
