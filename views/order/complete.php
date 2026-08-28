<?php
/**
 * 주문 완료 페이지
 */
$pageTitle = '주문 완료';
include APP_ROOT . '/views/layouts/header.php';
$site = $GLOBALS['site'] ?? [];
?>

<main class="max-w-2xl mx-auto px-4 py-12 pb-28 md:pb-12 text-center">

  <!-- 완료 아이콘 -->
  <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
    <span class="material-symbols-outlined text-5xl text-green-600">check_circle</span>
  </div>

  <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary mb-2">
    <?= ($order['pay_status'] === 'PAID') ? '결제 및 주문이 완료되었습니다!' : '주문이 정상 접수되었습니다!' ?>
  </h1>
  <p class="text-sm text-on-surface-variant mb-8">
    주문번호: <strong class="font-mono text-primary text-base"><?= htmlspecialchars($order['order_no']) ?></strong>
  </p>

  <!-- 1. 신용카드 / 계좌이체 / 카카오페이 결제 완료 안내 -->
  <?php if ($order['pay_status'] === 'PAID'): ?>
    <div class="bg-surface rounded-2xl border border-green-200 p-6 text-left mb-6 shadow-sm">
      <div class="flex items-center gap-2 mb-4 pb-3 border-b border-green-100">
        <span class="material-symbols-outlined text-green-600">verified</span>
        <h2 class="font-bold text-green-800 text-sm">전자결제 정상 승인 완료</h2>
      </div>
      <div class="grid grid-cols-2 gap-3 text-xs">
        <div class="bg-gray-50 p-3 rounded-xl">
          <span class="text-gray-500 block mb-1">결제 수단</span>
          <span class="font-bold text-gray-800">
            <?= match($order['pay_method']) { 'CARD'=>'신용카드', 'TRANS'=>'실시간 계좌이체', 'EASYPAY'=>'카카오페이/간편결제', default=>$order['pay_method'] } ?>
          </span>
        </div>
        <div class="bg-gray-50 p-3 rounded-xl">
          <span class="text-gray-500 block mb-1">결제 금액</span>
          <span class="font-bold text-primary text-sm font-mono"><?= number_format((int)$order['total_pay_price']) ?>원</span>
        </div>
        <?php if (!empty($order['pg_app_no'])): ?>
          <div class="bg-gray-50 p-3 rounded-xl col-span-2">
            <span class="text-gray-500 block mb-1">승인 번호</span>
            <span class="font-mono text-gray-800"><?= htmlspecialchars($order['pg_app_no']) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- 2. 가상계좌 (VBANK) 입금 안내 -->
  <?php if ($order['pay_method'] === 'VBANK' && $order['pay_status'] === 'WAITING'): ?>
    <div class="bg-surface-container rounded-2xl border border-blue-200 p-6 text-left mb-6 shadow-sm">
      <h2 class="font-serif font-bold text-primary mb-4 text-center">🏦 가상계좌 입금 안내 (자동 확인)</h2>
      <div class="flex flex-col gap-3">
        <div class="p-4 bg-primary text-white rounded-xl">
          <p class="text-xs text-white/70">입금 가상계좌</p>
          <p class="font-bold text-lg mt-0.5"><?= htmlspecialchars($order['vbank_name'] ?? '') ?> <?= htmlspecialchars($order['vbank_num'] ?? '') ?></p>
          <p class="text-xs text-white/80 mt-1">예금주: <?= htmlspecialchars($order['vbank_holder'] ?? '도서출판 대장간') ?></p>
        </div>
        <div class="grid grid-cols-2 gap-3 text-xs">
          <div class="bg-white p-3 rounded-xl text-center border">
            <span class="text-gray-500 block mb-1">입금하실 금액</span>
            <span class="font-bold text-secondary text-sm font-mono"><?= number_format((int)$order['total_pay_price']) ?>원</span>
          </div>
          <div class="bg-white p-3 rounded-xl text-center border">
            <span class="text-gray-500 block mb-1">입금 기한</span>
            <span class="font-medium text-gray-800"><?= !empty($order['vbank_date']) ? date('m/d H:i', strtotime($order['vbank_date'])) : '주문 후 24시간' ?></span>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- 3. 일반 무통장 입금 안내 -->
  <?php if ($order['pay_method'] === 'BANK' && $order['pay_status'] === 'WAITING'): ?>
    <div class="bg-surface-container rounded-2xl border border-outline-variant p-6 text-left mb-6 shadow-sm">
      <h2 class="font-serif font-bold text-primary mb-4 text-center">💳 무통장 입금 계좌 안내</h2>
      <div class="flex flex-col gap-3">
        <div class="flex items-center gap-3 p-4 bg-primary text-white rounded-xl">
          <span class="material-symbols-outlined text-2xl text-secondary-container">account_balance</span>
          <div>
            <p class="text-xs text-white/70">입금 계좌</p>
            <p class="font-bold text-base"><?= htmlspecialchars($site['bank_account'] ?? '우체국 311639-02-150821 배용하') ?></p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
          <div class="bg-white rounded-xl p-3 text-center border">
            <p class="text-gray-500 mb-1">입금자명</p>
            <p class="font-bold text-gray-900"><?= htmlspecialchars($order['bank_depositor'] ?? $order['orderer_name']) ?></p>
          </div>
          <div class="bg-white rounded-xl p-3 text-center border">
            <p class="text-gray-500 mb-1">입금 금액</p>
            <p class="font-bold text-secondary text-base font-mono"><?= number_format((int)$order['total_pay_price']) ?>원</p>
          </div>
        </div>
      </div>

      <p class="text-xs text-on-surface-variant text-center mt-4 leading-relaxed">
        입금 확인 후 신속하게 배송 준비가 시작됩니다.<br/>
        문의: <?= htmlspecialchars($site['cs_phone'] ?? '041-742-1424') ?>
      </p>
    </div>
  <?php endif; ?>

  <!-- 주문 요약 -->
  <div class="bg-surface rounded-2xl border border-outline-variant p-5 text-left mb-6 shadow-sm">
    <h3 class="font-semibold text-on-surface text-sm mb-3">주문 도서</h3>
    <?php foreach ($orderItems as $item): ?>
      <div class="flex justify-between py-2 text-xs border-b border-outline-variant/60 last:border-0">
        <span class="text-on-surface font-medium"><?= htmlspecialchars($item['book_title']) ?></span>
        <span class="text-on-surface-variant"><?= (int)$item['quantity'] ?>권 / <?= number_format((int)$item['total_price']) ?>원</span>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="flex justify-center gap-3">
    <a href="/books" class="px-6 py-3 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-container transition-colors">
      도서 계속 둘러보기
    </a>
    <a href="<?= Auth::check() ? '/mypage/orders' : '/order/lookup' ?>" class="px-6 py-3 border border-outline-variant text-on-surface rounded-xl text-xs font-medium hover:bg-surface-variant transition-colors">
      주문 / 배송 조회
    </a>
  </div>

</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
