<?php
/**
 * 주문서 작성 & KG이니시스 결제 페이지
 */
$pageTitle = '주문서 작성';
include APP_ROOT . '/views/layouts/header.php';
$site     = $GLOBALS['site'] ?? [];
$kakaoKey = htmlspecialchars($site['kakao_map_key'] ?? '');
?>

<!-- KG이니시스 웹표준 결제창 SDK -->
<script src="https://stdpay.inicis.com/stdjs/INIStdPay.js"></script>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full" x-data="{
  payMethod: 'CARD',
  subtotal: <?= $subtotal ?>,
  shipping: <?= $shipping ?>,
  userPoints: <?= (int)($user['points'] ?? 0) ?>,
  usedPoints: 0,
  get finalTotal() {
    return Math.max(0, this.subtotal + this.shipping - this.usedPoints);
  }
}">
  <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary mb-6">주문서 작성</h1>

  <form action="/order/place" method="POST" id="orderForm" class="flex flex-col lg:flex-row gap-8">

    <!-- ==================== 이니시스 필수 Hidden 파라미터 ==================== -->
    <input type="hidden" name="version" value="1.0"/>
    <input type="hidden" name="mid" value="<?= htmlspecialchars($inicisMid) ?>"/>
    <input type="hidden" name="mKey" value="<?= htmlspecialchars($inicisMKey) ?>"/>
    <input type="hidden" name="signature" value="<?= htmlspecialchars($inicisSignature) ?>"/>
    <input type="hidden" name="timestamp" value="<?= htmlspecialchars($inicisTimestamp) ?>"/>
    <input type="hidden" name="oid" id="inicis_oid" value="<?= htmlspecialchars($inicisOid) ?>"/>
    <input type="hidden" name="order_no" value="<?= htmlspecialchars($inicisOid) ?>"/>
    <input type="hidden" name="price" :value="finalTotal"/>
    <input type="hidden" name="currency" value="WON"/>
    <input type="hidden" name="goodname" value="<?= htmlspecialchars($goodName) ?>"/>
    <input type="hidden" name="buyername" id="inicis_buyername" value="<?= htmlspecialchars($user['name'] ?? '') ?>"/>
    <input type="hidden" name="buyertel" id="inicis_buyertel" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"/>
    <input type="hidden" name="buyeremail" id="inicis_buyeremail" value="<?= htmlspecialchars($user['email'] ?? '') ?>"/>
    <input type="hidden" name="returnUrl" value="http://ndaejanggan.iwinv.net/order/inicis/return"/>
    <input type="hidden" name="closeUrl" value="http://ndaejanggan.iwinv.net/order/inicis/close"/>
    <input type="hidden" name="gopaymethod" id="inicis_gopaymethod" value="Card"/>
    <input type="hidden" name="acceptmethod" value="HPP(1):below1000:center:va_receipt"/>

    <!-- 좌측: 주문 정보 & 결제 수단 입력 -->
    <div class="flex-1 flex flex-col gap-6">

      <!-- 1. 주문자 정보 -->
      <section class="bg-surface rounded-2xl border border-outline-variant/70 p-6 shadow-sm">
        <h2 class="font-serif font-bold text-primary text-base mb-4 flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-xl">person</span>
          주문자 정보
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">이름 *</label>
            <input type="text" name="orderer_name" required
              value="<?= htmlspecialchars($user['name'] ?? '') ?>"
              @input="document.getElementById('inicis_buyername').value = $el.value"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
          <div>
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">연락처 *</label>
            <input type="tel" name="orderer_phone" required
              value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
              @input="document.getElementById('inicis_buyertel').value = $el.value"
              placeholder="010-0000-0000"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
          <div class="md:col-span-2">
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">이메일 *</label>
            <input type="email" name="orderer_email" required
              value="<?= htmlspecialchars($user['email'] ?? '') ?>"
              @input="document.getElementById('inicis_buyeremail').value = $el.value"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
        </div>
      </section>

      <!-- 2. 배송지 정보 -->
      <section class="bg-surface rounded-2xl border border-outline-variant/70 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-serif font-bold text-primary text-base flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-xl">local_shipping</span>
            배송지 정보
          </h2>
          <label class="flex items-center gap-1.5 text-xs text-on-surface-variant cursor-pointer">
            <input type="checkbox" id="sameAsOrderer" onchange="fillSame(this)"
              class="w-3.5 h-3.5 rounded text-primary focus:ring-primary"/>
            주문자와 동일
          </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">수령인 *</label>
            <input type="text" name="receiver_name" required id="receiverName"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
          <div>
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">수령인 연락처 *</label>
            <input type="tel" name="receiver_phone" required id="receiverPhone"
              placeholder="010-0000-0000"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
          <div class="md:col-span-2">
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">배송 주소 *</label>
            <div class="flex gap-2 mb-2">
              <input type="text" name="shipping_zipcode" id="zipcode" required readonly
                value="<?= htmlspecialchars($user['zipcode'] ?? '') ?>"
                placeholder="우편번호"
                class="w-28 border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container"/>
              <button type="button" onclick="searchAddress()"
                class="px-4 py-2 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary-container transition-colors">
                우편번호 검색
              </button>
            </div>
            <input type="text" name="shipping_address1" id="address1" required readonly
              value="<?= htmlspecialchars($user['address1'] ?? '') ?>"
              placeholder="기본 주소"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface bg-surface-container mb-2"/>
            <input type="text" name="shipping_address2" id="address2" required
              value="<?= htmlspecialchars($user['address2'] ?? '') ?>"
              placeholder="상세 주소 (동, 호수 등)"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
          <div class="md:col-span-2">
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">배송 요청사항</label>
            <select name="shipping_memo"
              class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none bg-surface">
              <option value="">배송 시 요청사항을 선택해 주세요</option>
              <option value="부재 시 문 앞에 놓아주세요">부재 시 문 앞에 놓아주세요</option>
              <option value="배송 전 미리 연락 바랍니다">배송 전 미리 연락 바랍니다</option>
              <option value="경비실에 맡겨 주세요">경비실에 맡겨 주세요</option>
              <option value="택배함에 보관해 주세요">택배함에 보관해 주세요</option>
            </select>
          </div>
        </div>
      </section>

      <!-- 3. 결제 수단 선택 (KG이니시스 전자결제 + 무통장 입금) -->
      <section class="bg-surface rounded-2xl border border-outline-variant/70 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-serif font-bold text-primary text-base flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary text-xl">payments</span>
            결제 수단 선택
          </h2>
          <span class="text-[11px] text-gray-400 font-sans">KG이니시스 안전결제 시스템</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
          <!-- 1) 신용카드 -->
          <label class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all text-center gap-1.5"
                 :class="payMethod === 'CARD' ? 'border-primary bg-primary-container/10 font-bold text-primary shadow-sm' : 'border-outline-variant/50 hover:bg-surface-container text-on-surface-variant'">
            <input type="radio" name="pay_method" value="CARD" x-model="payMethod" class="sr-only"/>
            <span class="material-symbols-outlined text-2xl" :class="payMethod === 'CARD' ? 'text-primary' : 'text-on-surface-variant'">credit_card</span>
            <span class="text-xs">신용카드</span>
          </label>

          <!-- 2) 카카오페이 / 간편결제 -->
          <label class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all text-center gap-1.5"
                 :class="payMethod === 'EASYPAY' ? 'border-[#FEE500] bg-[#FEE500]/15 font-bold text-primary shadow-sm' : 'border-outline-variant/50 hover:bg-surface-container text-on-surface-variant'">
            <input type="radio" name="pay_method" value="EASYPAY" x-model="payMethod" class="sr-only"/>
            <span class="material-symbols-outlined text-2xl text-[#3C1E1E]">account_balance_wallet</span>
            <span class="text-xs">카카오페이 / 간편결제</span>
          </label>

          <!-- 3) 실시간 계좌이체 -->
          <label class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all text-center gap-1.5"
                 :class="payMethod === 'TRANS' ? 'border-primary bg-primary-container/10 font-bold text-primary shadow-sm' : 'border-outline-variant/50 hover:bg-surface-container text-on-surface-variant'">
            <input type="radio" name="pay_method" value="TRANS" x-model="payMethod" class="sr-only"/>
            <span class="material-symbols-outlined text-2xl" :class="payMethod === 'TRANS' ? 'text-primary' : 'text-on-surface-variant'">currency_exchange</span>
            <span class="text-xs">실시간 계좌이체</span>
          </label>

          <!-- 4) 가상계좌 (자동 입금확인) -->
          <label class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all text-center gap-1.5"
                 :class="payMethod === 'VBANK' ? 'border-primary bg-primary-container/10 font-bold text-primary shadow-sm' : 'border-outline-variant/50 hover:bg-surface-container text-on-surface-variant'">
            <input type="radio" name="pay_method" value="VBANK" x-model="payMethod" class="sr-only"/>
            <span class="material-symbols-outlined text-2xl" :class="payMethod === 'VBANK' ? 'text-primary' : 'text-on-surface-variant'">pin</span>
            <span class="text-xs">가상계좌 (자동확인)</span>
          </label>

          <!-- 5) 일반 무통장 입금 -->
          <label class="flex flex-col items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all text-center gap-1.5 sm:col-span-2"
                 :class="payMethod === 'BANK' ? 'border-secondary bg-secondary/10 font-bold text-secondary shadow-sm' : 'border-outline-variant/50 hover:bg-surface-container text-on-surface-variant'">
            <input type="radio" name="pay_method" value="BANK" x-model="payMethod" class="sr-only"/>
            <span class="material-symbols-outlined text-2xl" :class="payMethod === 'BANK' ? 'text-secondary' : 'text-on-surface-variant'">account_balance</span>
            <span class="text-xs">일반 무통장 입금 (우체국 계좌)</span>
          </label>
        </div>

        <!-- 무통장 입금 선택 시 안내 -->
        <div x-show="payMethod === 'BANK'" x-cloak class="p-4 bg-surface-container-low rounded-xl border border-outline-variant/60 flex flex-col gap-3">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">account_balance</span>
            <div>
              <p class="text-xs text-on-surface-variant font-medium">입금 계좌 안내</p>
              <p class="text-sm font-bold text-primary"><?= htmlspecialchars($site['bank_account'] ?? '우체국 311639-02-150821 배용하') ?></p>
            </div>
          </div>
          <div>
            <label class="text-xs text-on-surface-variant mb-1 block font-medium">입금자 성함 *</label>
            <input type="text" name="bank_depositor" value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="입금자명을 정확히 입력해 주세요"
                   class="w-full border border-outline-variant rounded-lg px-4 py-2 text-xs outline-none bg-white"/>
          </div>
        </div>

        <!-- 전자결제(이니시스) 선택 시 안내 -->
        <div x-show="payMethod !== 'BANK'" class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600 flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-600 text-base">verified_user</span>
          <span>주문하기 클릭 시 <strong>KG이니시스 보안 결제창</strong>이 안전하게 실행됩니다.</span>
        </div>
      </section>

      <!-- 4. 적립금 사용 -->
      <?php if (Auth::check() && ($user['points'] ?? 0) > 0): ?>
        <section class="bg-surface rounded-2xl border border-outline-variant/70 p-6 shadow-sm">
          <h2 class="font-serif font-bold text-primary text-base mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-tertiary text-xl">savings</span>
            적립금 사용
          </h2>
          <div class="flex items-center gap-3">
            <span class="text-xs text-on-surface-variant">보유 적립금: <strong class="text-primary text-sm"><?= number_format($user['points']) ?> P</strong></span>
            <input type="number" name="used_points" min="0" :max="userPoints" step="100"
                   x-model.number="usedPoints"
                   class="w-32 border border-outline-variant rounded-lg px-3 py-2 text-xs font-mono outline-none focus:border-primary"/>
            <span class="text-xs text-on-surface-variant">P</span>
            <button type="button" @click="usedPoints = Math.min(userPoints, subtotal)"
                    class="px-3 py-2 text-xs bg-surface-container hover:bg-surface-variant border border-outline-variant rounded-lg font-medium">
              전액 사용
            </button>
          </div>
        </section>
      <?php else: ?>
        <input type="hidden" name="used_points" value="0"/>
      <?php endif; ?>

    </div>

    <!-- 우측: 주문 상품 요약 & 결제 확정 버튼 -->
    <aside class="lg:w-80 shrink-0">
      <div class="bg-surface-container-low rounded-2xl border border-outline-variant/70 p-6 sticky top-24 shadow-sm">
        <h3 class="font-serif font-bold text-primary text-base mb-4 pb-2 border-b border-outline-variant/60">주문 상품 확인</h3>

        <!-- 도서 목록 -->
        <div class="flex flex-col gap-3 mb-4 max-h-64 overflow-y-auto pr-1">
          <?php foreach ($items as $item): ?>
            <div class="flex gap-3 items-center">
              <img src="<?= htmlspecialchars($item['cover_image'] ?? '/assets/images/default_book.png') ?>"
                   alt="<?= htmlspecialchars($item['title']) ?>"
                   class="w-10 h-14 object-cover rounded shadow-sm shrink-0"
                   onerror="this.src='/assets/images/default_book.png'"/>
              <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-primary line-clamp-1"><?= htmlspecialchars($item['title']) ?></p>
                <p class="text-[11px] text-on-surface-variant"><?= (int)$item['quantity'] ?>권 × <?= number_format((int)$item['price']) ?>원</p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="border-t border-outline-variant/60 pt-3 flex flex-col gap-2 text-xs">
          <div class="flex justify-between text-on-surface-variant">
            <span>도서 금액</span>
            <span class="font-medium text-on-surface"><?= number_format($subtotal) ?>원</span>
          </div>
          <div class="flex justify-between text-on-surface-variant">
            <span>배송비</span>
            <span class="font-medium text-on-surface"><?= $shipping > 0 ? number_format($shipping) . '원' : '무료' ?></span>
          </div>
          <div class="flex justify-between text-secondary" x-show="usedPoints > 0">
            <span>적립금 할인</span>
            <span class="font-bold">- <span x-text="usedPoints.toLocaleString()"></span>원</span>
          </div>
        </div>

        <div class="border-t border-outline-variant/60 my-4"></div>

        <div class="flex justify-between items-center mb-4">
          <span class="font-bold text-on-surface text-sm">최종 결제 금액</span>
          <span class="text-2xl font-bold text-secondary font-mono">
            <span x-text="finalTotal.toLocaleString()"></span>원
          </span>
        </div>

        <!-- 개인정보 수집 이용 동의 -->
        <label class="flex items-start gap-2 mb-4 cursor-pointer">
          <input type="checkbox" id="agreePrivacy" required class="mt-0.5 w-4 h-4 rounded text-primary focus:ring-primary"/>
          <span class="text-[11px] text-on-surface-variant leading-tight">
            주문 상품 정보 및 결제 대행을 위한 개인정보 제공에 동의합니다. (필수)
          </span>
        </label>

        <!-- 결제 실행 버튼 -->
        <button type="button" onclick="submitPayment()"
          class="w-full py-4 bg-secondary hover:bg-secondary/90 text-white rounded-xl font-bold text-sm transition-all shadow-lg flex items-center justify-center gap-1.5">
          <span class="material-symbols-outlined text-lg">lock</span>
          <span x-text="payMethod === 'BANK' ? '무통장 입금 주문하기' : '안전 결제 진행하기'"></span>
        </button>
      </div>
    </aside>

  </form>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

<!-- 카카오 주소검색 API -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<script>
function searchAddress() {
  new daum.Postcode({
    oncomplete: function(data) {
      document.getElementById('zipcode').value  = data.zonecode;
      document.getElementById('address1').value = data.roadAddress || data.jibunAddress;
      document.getElementById('address2').focus();
    }
  }).open();
}

function fillSame(cb) {
  if (cb.checked) {
    document.getElementById('receiverName').value  = document.querySelector('[name=orderer_name]').value;
    document.getElementById('receiverPhone').value = document.querySelector('[name=orderer_phone]').value;
  } else {
    document.getElementById('receiverName').value  = '';
    document.getElementById('receiverPhone').value = '';
  }
}

function submitPayment() {
  const form = document.getElementById('orderForm');

  // 필수 폼 검증
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  if (!document.getElementById('agreePrivacy').checked) {
    alert('개인정보 제공 동의에 체크해 주세요.');
    return;
  }

  const payMethod = document.querySelector('input[name="pay_method"]:checked')?.value || 'CARD';

  // 1. 일반 무통장 입금인 경우 -> 폼 바로 제출
  if (payMethod === 'BANK') {
    form.action = '/order/place';
    form.submit();
    return;
  }

  // 2. KG이니시스 전자결제 (CARD, TRANS, VBANK, EASYPAY)
  // 결제 수단 코드 매핑
  const goPayMap = {
    'CARD': 'Card',
    'TRANS': 'DirectBank',
    'VBANK': 'VBank',
    'EASYPAY': 'Kakaopay'
  };
  document.getElementById('inicis_gopaymethod').value = goPayMap[payMethod] || 'Card';

  // 먼저 서버 세션에 임시 주문 정보를 저장
  const formData = new FormData(form);
  fetch('/order/place', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // KG이니시스 표준 결제창 호출!
      INIStdPay.pay('orderForm');
    } else {
      alert(data.error || '주문 준비 중 오류가 발생했습니다.');
    }
  })
  .catch(err => {
    // 네트워크 오류 fallback으로도 결제창 호출 시도
    INIStdPay.pay('orderForm');
  });
}
</script>
