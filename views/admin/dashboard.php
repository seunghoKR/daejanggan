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
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-xs text-gray-500">
        <tr>
          <th class="px-4 py-3 text-left">주문번호</th>
          <th class="px-4 py-3 text-left">주문자</th>
          <th class="px-4 py-3 text-right">금액</th>
          <th class="px-4 py-3 text-center">결제</th>
          <th class="px-4 py-3 text-center">배송</th>
          <th class="px-4 py-3 text-left">주문일</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50">
        <?php foreach ($recentOrders as $order):
          $payColors = ['WAITING'=>'bg-yellow-100 text-yellow-700','PAID'=>'bg-green-100 text-green-700',
                        'CANCELLED'=>'bg-red-100 text-red-700','REFUNDED'=>'bg-gray-100 text-gray-600'];
          $payLabels = ['WAITING'=>'입금대기','PAID'=>'결제완료','CANCELLED'=>'취소','REFUNDED'=>'환불'];
          $delColors = ['PREPARING'=>'bg-gray-100 text-gray-600','SHIPPING'=>'bg-blue-100 text-blue-700','DELIVERED'=>'bg-green-100 text-green-700'];
          $delLabels = ['PREPARING'=>'배송준비','SHIPPING'=>'배송중','DELIVERED'=>'완료'];
        ?>
          <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 font-mono text-xs text-gray-600"><?= htmlspecialchars($order['order_no']) ?></td>
            <td class="px-4 py-3 text-gray-800"><?= htmlspecialchars($order['orderer_name']) ?></td>
            <td class="px-4 py-3 text-right font-medium text-gray-800"><?= number_format((int)$order['total_pay_price']) ?>원</td>
            <td class="px-4 py-3 text-center">
              <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium <?= $payColors[$order['pay_status']] ?? '' ?>">
                <?= $payLabels[$order['pay_status']] ?? '' ?>
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="inline-block text-xs px-2 py-0.5 rounded-full font-medium <?= $delColors[$order['delivery_status']] ?? '' ?>">
                <?= $delLabels[$order['delivery_status']] ?? '' ?>
              </span>
            </td>
            <td class="px-4 py-3 text-gray-500 text-xs"><?= date('m/d H:i', strtotime($order['created_at'])) ?></td>
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
