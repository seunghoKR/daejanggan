<?php
/**
 * 마이페이지 주문 내역
 */
$pageTitle = '주문 내역';
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">
  <div class="flex items-center justify-between mb-6">
    <h1 class="font-serif text-2xl font-bold text-primary">주문 내역</h1>
    <a href="/mypage" class="text-xs text-on-surface-variant hover:text-primary">← 마이페이지로 돌아가기</a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="text-center py-20 bg-surface rounded-2xl border border-surface-variant text-on-surface-variant">
      <span class="material-symbols-outlined text-6xl mb-3 opacity-30">receipt_long</span>
      <p class="text-base font-medium">주문 내역이 없습니다.</p>
      <a href="/books" class="mt-4 inline-block px-6 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-semibold">도서 보러가기</a>
    </div>
  <?php else: ?>
    <div class="flex flex-col gap-4">
      <?php foreach ($orders as $order):
        $payLabels = ['WAITING'=>'입금 대기','PAID'=>'결제 완료','CANCELLED'=>'취소','REFUNDED'=>'환불'];
        $delLabels = ['PREPARING'=>'배송 준비','SHIPPING'=>'배송중','DELIVERED'=>'배송 완료'];
        $payColors = ['WAITING'=>'bg-tertiary-container text-on-tertiary-container','PAID'=>'bg-green-100 text-green-700','CANCELLED'=>'bg-error-container text-error','REFUNDED'=>'bg-surface-variant text-on-surface-variant'];
      ?>
        <div class="bg-surface rounded-2xl border border-surface-variant p-5">
          <div class="flex flex-wrap items-center justify-between pb-3 border-b border-outline-variant gap-2">
            <div>
              <span class="font-mono font-bold text-sm text-primary"><?= htmlspecialchars($order['order_no']) ?></span>
              <span class="text-xs text-on-surface-variant ml-2"><?= date('Y.m.d H:i', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs px-2.5 py-1 rounded-full font-medium <?= $payColors[$order['pay_status']] ?? '' ?>">
                <?= $payLabels[$order['pay_status']] ?? $order['pay_status'] ?>
              </span>
              <span class="text-xs px-2.5 py-1 rounded-full bg-surface-container text-on-surface font-medium">
                <?= $delLabels[$order['delivery_status']] ?? $order['delivery_status'] ?>
              </span>
            </div>
          </div>

          <div class="py-3 flex flex-col gap-2">
            <?php foreach ($order['items'] as $item): ?>
              <div class="flex justify-between items-center text-sm">
                <span class="text-on-surface line-clamp-1"><?= htmlspecialchars($item['book_title']) ?></span>
                <span class="text-xs text-on-surface-variant shrink-0 ml-2"><?= (int)$item['quantity'] ?>권 / <?= number_format((int)$item['total_price']) ?>원</span>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="pt-3 border-t border-outline-variant flex justify-between items-center">
            <span class="text-xs text-on-surface-variant">총 결제 금액</span>
            <span class="font-bold text-primary text-base"><?= number_format((int)$order['total_pay_price']) ?>원</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
