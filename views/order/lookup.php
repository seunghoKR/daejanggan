<?php
/**
 * 비회원 주문조회 (캡차 포함)
 * @var string $captchaQuestion
 * @var array|null $order
 * @var array  $orderItems
 * @var bool   $notFound
 */
$pageTitle = '주문 조회';
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-2xl mx-auto px-4 py-10 pb-28 md:pb-10">

  <div class="text-center mb-8">
    <span class="material-symbols-outlined text-4xl text-primary">local_shipping</span>
    <h1 class="font-serif text-2xl font-bold text-primary mt-2">주문 / 배송 조회</h1>
    <p class="text-sm text-on-surface-variant mt-1">주문번호와 주문 시 입력한 연락처로 조회하세요.</p>
  </div>

  <!-- 조회 폼 -->
  <form action="/order/lookup" method="POST" class="bg-surface rounded-2xl border border-outline-variant p-6 flex flex-col gap-4 mb-8">

    <div>
      <label class="text-xs text-on-surface-variant mb-1 block">주문번호 *</label>
      <input type="text" name="order_no" required
        placeholder="예: ORD-20240528-ABC123"
        class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm focus:ring-1 focus:ring-primary outline-none"/>
    </div>

    <div>
      <label class="text-xs text-on-surface-variant mb-1 block">주문자 연락처 *</label>
      <input type="tel" name="orderer_phone" required
        placeholder="010-0000-0000 (숫자만)"
        class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm focus:ring-1 focus:ring-primary outline-none"/>
    </div>

    <!-- 캡차 -->
    <div class="bg-surface-container-low rounded-xl p-4">
      <label class="text-xs text-on-surface-variant mb-2 block">보안 문자 확인 (스팸 방지)</label>
      <div class="flex items-center gap-4">
        <div class="bg-primary-container text-on-primary px-5 py-3 rounded-lg font-mono text-lg font-bold tracking-widest select-none">
          <?= htmlspecialchars($captchaQuestion ?? '') ?>
        </div>
        <input type="text" name="captcha" required
          placeholder="답을 입력하세요"
          maxlength="4"
          class="flex-1 border border-outline-variant rounded-lg px-4 py-2.5 text-sm focus:ring-1 focus:ring-primary outline-none"/>
      </div>
    </div>

    <button type="submit"
      class="py-3 bg-primary text-on-primary rounded-lg font-semibold text-sm hover:bg-primary-container transition-colors">
      조회하기
    </button>
  </form>

  <!-- 조회 결과 -->
  <?php if (isset($notFound) && $notFound): ?>
    <div class="p-4 bg-error-container text-error rounded-xl text-sm text-center">
      주문 정보를 찾을 수 없습니다. 주문번호와 연락처를 다시 확인해 주세요.
    </div>

  <?php elseif (!empty($order)): ?>
    <div class="bg-surface rounded-2xl border border-outline-variant overflow-hidden">
      <!-- 주문 헤더 -->
      <div class="bg-primary px-6 py-4">
        <p class="text-xs text-on-primary-container">주문번호</p>
        <p class="font-mono font-bold text-on-primary text-lg"><?= htmlspecialchars($order['order_no']) ?></p>
        <p class="text-xs text-on-primary-container mt-1"><?= date('Y년 m월 d일 H:i', strtotime($order['created_at'])) ?> 주문</p>
      </div>

      <div class="p-6 flex flex-col gap-4">
        <!-- 결제/배송 상태 -->
        <div class="grid grid-cols-2 gap-3">
          <div class="bg-surface-container-low rounded-xl p-3 text-center">
            <p class="text-xs text-on-surface-variant mb-1">결제 상태</p>
            <?php
            $payColors = ['WAITING'=>'text-tertiary','PAID'=>'text-green-600','CANCELLED'=>'text-error','REFUNDED'=>'text-on-surface-variant'];
            $payLabels = ['WAITING'=>'입금 대기','PAID'=>'결제 완료','CANCELLED'=>'취소','REFUNDED'=>'환불'];
            $payStatus = $order['pay_status'];
            ?>
            <p class="font-bold text-sm <?= $payColors[$payStatus] ?? '' ?>">
              <?= $payLabels[$payStatus] ?? $payStatus ?>
            </p>
          </div>
          <div class="bg-surface-container-low rounded-xl p-3 text-center">
            <p class="text-xs text-on-surface-variant mb-1">배송 상태</p>
            <?php
            $delColors = ['PREPARING'=>'text-on-surface-variant','SHIPPING'=>'text-tertiary','DELIVERED'=>'text-green-600'];
            $delLabels = ['PREPARING'=>'배송 준비중','SHIPPING'=>'배송중','DELIVERED'=>'배송 완료'];
            $delStatus = $order['delivery_status'];
            ?>
            <p class="font-bold text-sm <?= $delColors[$delStatus] ?? '' ?>">
              <?= $delLabels[$delStatus] ?? $delStatus ?>
            </p>
          </div>
        </div>

        <!-- 운송장 -->
        <?php if ($order['tracking_number']): ?>
          <div class="bg-surface-container-low rounded-xl p-4 flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">package_2</span>
            <div>
              <p class="text-xs text-on-surface-variant">운송장 번호</p>
              <p class="font-mono font-bold text-on-surface"><?= htmlspecialchars($order['tracking_number']) ?></p>
            </div>
          </div>
        <?php endif; ?>

        <!-- 무통장 입금 안내 -->
        <?php if ($order['pay_status'] === 'WAITING' && $order['pay_method'] === 'BANK'): ?>
          <div class="bg-tertiary-container/30 border border-tertiary-container rounded-xl p-4">
            <p class="text-sm font-semibold text-on-surface mb-1">📌 입금 안내</p>
            <p class="text-xs text-on-surface-variant leading-relaxed">
              입금 계좌: <strong class="text-on-surface"><?= htmlspecialchars($GLOBALS['site']['bank_account'] ?? '') ?></strong><br/>
              입금자명: <strong class="text-on-surface"><?= htmlspecialchars($order['bank_depositor'] ?? $order['orderer_name']) ?></strong><br/>
              입금 금액: <strong class="text-primary"><?= number_format((int)$order['total_pay_price']) ?>원</strong>
            </p>
          </div>
        <?php endif; ?>

        <!-- 배송지 -->
        <div class="border-t border-outline-variant pt-4">
          <h3 class="font-semibold text-on-surface text-sm mb-2">배송지</h3>
          <p class="text-sm text-on-surface"><?= htmlspecialchars($order['receiver_name']) ?></p>
          <p class="text-sm text-on-surface-variant"><?= htmlspecialchars($order['receiver_phone']) ?></p>
          <p class="text-sm text-on-surface-variant"><?= htmlspecialchars($order['shipping_address1'] . ' ' . $order['shipping_address2']) ?></p>
        </div>

        <!-- 주문 품목 -->
        <div class="border-t border-outline-variant pt-4">
          <h3 class="font-semibold text-on-surface text-sm mb-3">주문 도서</h3>
          <div class="flex flex-col gap-2">
            <?php foreach ($orderItems as $item): ?>
              <div class="flex justify-between items-center text-sm py-1.5 border-b border-outline-variant last:border-0">
                <span class="text-on-surface flex-1 min-w-0 line-clamp-1"><?= htmlspecialchars($item['book_title']) ?></span>
                <span class="text-on-surface-variant shrink-0 ml-2"><?= (int)$item['quantity'] ?>권 × <?= number_format((int)$item['price']) ?>원</span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- 결제 금액 -->
        <div class="bg-surface-container-low rounded-xl p-4">
          <div class="flex justify-between text-sm mb-1">
            <span class="text-on-surface-variant">상품 금액</span>
            <span><?= number_format((int)$order['total_books_price']) ?>원</span>
          </div>
          <div class="flex justify-between text-sm mb-1">
            <span class="text-on-surface-variant">배송비</span>
            <span><?= $order['shipping_fee'] > 0 ? number_format((int)$order['shipping_fee']) . '원' : '무료' ?></span>
          </div>
          <?php if ($order['used_points'] > 0): ?>
            <div class="flex justify-between text-sm mb-1 text-secondary">
              <span>적립금 할인</span>
              <span>- <?= number_format((int)$order['used_points']) ?>원</span>
            </div>
          <?php endif; ?>
          <div class="flex justify-between font-bold text-primary pt-2 border-t border-outline-variant mt-2">
            <span>최종 결제 금액</span>
            <span><?= number_format((int)$order['total_pay_price']) ?>원</span>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
