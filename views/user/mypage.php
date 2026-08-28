<?php
/**
 * 마이페이지 홈 (회원정보 + 알림 및 메신저 연동 설정)
 */
$pageTitle = '마이페이지';
include APP_ROOT . '/views/layouts/header.php';

$flashSuccess = $_SESSION['_flash_success'] ?? '';
$flashError   = $_SESSION['_flash_error']   ?? '';
unset($_SESSION['_flash_success'], $_SESSION['_flash_error']);
?>

<!-- 다음 우편번호 검색 서비스 SDK -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">

  <!-- 성공 / 오류 알림 배너 -->
  <?php if ($flashSuccess): ?>
    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-xs flex items-center gap-2">
      <span class="material-symbols-outlined text-green-600">check_circle</span>
      <span><?= htmlspecialchars($flashSuccess) ?></span>
    </div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="mb-6 p-4 bg-error-container border border-error/20 text-error rounded-xl text-xs flex items-center gap-2">
      <span class="material-symbols-outlined text-error">error</span>
      <span><?= htmlspecialchars($flashError) ?></span>
    </div>
  <?php endif; ?>

  <!-- 1. 상단 프로필 & 적립금 현황 카드 -->
  <div class="bg-gradient-to-r from-[#07131e] via-[#1c2833] to-[#2c3e50] text-white rounded-2xl p-6 md:p-8 mb-8 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
    <div class="flex items-center gap-4">
      <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center border border-white/20 shrink-0">
        <span class="material-symbols-outlined text-3xl text-secondary-container">person</span>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="font-serif text-2xl font-bold"><?= htmlspecialchars($user['name'] ?? '') ?> 님</h1>
          <?php if (!empty($user['nickname']) && $user['nickname'] !== $user['name']): ?>
            <span class="text-xs bg-white/15 px-2.5 py-0.5 rounded-full text-white/80">(<?= htmlspecialchars($user['nickname']) ?>)</span>
          <?php endif; ?>
        </div>
        <p class="text-xs text-white/70 mt-1"><?= htmlspecialchars($user['email'] ?? '') ?> • 가입일 <?= date('Y.m.d', strtotime($user['created_at'])) ?></p>
      </div>
    </div>

    <!-- 적립금 박스 -->
    <div class="bg-white/10 border border-white/15 rounded-xl px-6 py-4 flex items-center gap-4 w-full md:w-auto">
      <div class="w-10 h-10 rounded-full bg-secondary/30 flex items-center justify-center text-secondary-container">
        <span class="material-symbols-outlined">savings</span>
      </div>
      <div>
        <span class="text-xs text-white/70 block">보유 적립금</span>
        <span class="font-bold text-xl text-tertiary-container"><?= number_format((int)($user['points'] ?? 0)) ?> P</span>
      </div>
    </div>
  </div>

  <!-- 2. 빠른 바로가기 메뉴 -->
  <div class="grid grid-cols-3 gap-3 md:gap-4 mb-8">
    <a href="/mypage/orders"
       class="flex flex-col items-center justify-center gap-2 p-5 bg-surface rounded-2xl border border-outline-variant/60 hover:border-primary hover:shadow-md transition-all group">
      <span class="material-symbols-outlined text-3xl text-primary group-hover:scale-110 transition-transform">receipt_long</span>
      <span class="text-xs text-on-surface font-semibold">주문 내역</span>
    </a>
    <a href="/mypage/wishlist"
       class="flex flex-col items-center justify-center gap-2 p-5 bg-surface rounded-2xl border border-outline-variant/60 hover:border-secondary hover:shadow-md transition-all group">
      <span class="material-symbols-outlined text-3xl text-secondary group-hover:scale-110 transition-transform">favorite</span>
      <span class="text-xs text-on-surface font-semibold">나의 찜 목록</span>
    </a>
    <a href="/order/lookup"
       class="flex flex-col items-center justify-center gap-2 p-5 bg-surface rounded-2xl border border-outline-variant/60 hover:border-primary hover:shadow-md transition-all group">
      <span class="material-symbols-outlined text-3xl text-primary group-hover:scale-110 transition-transform">local_shipping</span>
      <span class="text-xs text-on-surface font-semibold">배송 조회</span>
    </a>
  </div>

  <!-- 3. 메인 그리드: [좌] 알림 및 SNS 연동 센터 + [우] 회원 정보 & 배송지 수정 -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

    <!-- [좌측] 🔔 알림 및 SNS 연동 센터 (Kakao & Telegram) -->
    <div class="bg-surface rounded-2xl border border-outline-variant/80 p-6 md:p-7 shadow-sm flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-outline-variant/60">
          <h2 class="font-serif text-lg font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary">notifications_active</span>
            알림 및 SNS 연동 설정
          </h2>
          <span class="text-xs bg-secondary/10 text-secondary px-2.5 py-1 rounded-full font-medium">실시간 연동</span>
        </div>

        <form id="notifyForm" action="/mypage/notify" method="POST" class="flex flex-col gap-5">

          <!-- 1) 카카오톡 알림톡 연동 -->
          <div class="p-4 rounded-xl border border-yellow-200 bg-yellow-50/50 flex flex-col gap-2.5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#FEE500] text-[#191919] flex items-center justify-center font-bold text-xs shadow-sm">talk</span>
                <span class="text-xs font-bold text-gray-800">카카오톡 알림톡</span>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="notify_kakao" value="1" <?= ($user['notify_kakao'] ?? 1) ? 'checked' : '' ?> class="sr-only peer"/>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-400"></div>
              </label>
            </div>
            <p class="text-[11px] text-gray-600 leading-relaxed">
              도서 주문 확인, 입금 확인, 택배 송장 발송 알림을 등록된 휴대전화번호(<strong><?= htmlspecialchars($user['phone'] ?: '미등록') ?></strong>)의 카카오톡으로 즉시 발송합니다.
            </p>
          </div>

          <!-- 2) 텔레그램 알림 연동 (개인 ID / Chat ID 입력) -->
          <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/50 flex flex-col gap-2.5">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#229ED9] text-white flex items-center justify-center font-bold text-xs shadow-sm">TG</span>
                <span class="text-xs font-bold text-gray-800">텔레그램 봇 개인 알림</span>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="notify_telegram" value="1" <?= ($user['notify_telegram'] ?? 0) ? 'checked' : '' ?> class="sr-only peer"/>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-500"></div>
              </label>
            </div>

            <div>
              <label class="text-[11px] font-semibold text-gray-700 mb-1 block">텔레그램 사용자명 (@username) 또는 Chat ID</label>
              <input type="text" name="telegram_id"
                     value="<?= htmlspecialchars($user['telegram_id'] ?? '') ?>"
                     placeholder="예: @my_telegram_id 또는 12345678"
                     class="w-full bg-white border border-sky-300 rounded-lg px-3 py-2 text-xs text-gray-900 focus:ring-1 focus:ring-sky-500 outline-none"/>
              <p class="text-[11px] text-gray-500 mt-1">
                텔레그램 앱에서 <strong>@DaejangganBot</strong> 검색 후 대화 시작 후 본인 ID를 입력하시면 실시간 주문/배송 메시지를 전송받을 수 있습니다.
              </p>
            </div>
          </div>

          <!-- 3) SMS & 이메일 알림 -->
          <div class="flex items-center justify-between p-3 rounded-xl border border-outline-variant/60 bg-surface-container-low text-xs text-gray-700">
            <label class="flex items-center gap-2 cursor-pointer font-medium">
              <input type="checkbox" name="notify_sms" value="1" <?= ($user['notify_sms'] ?? 1) ? 'checked' : '' ?> class="rounded text-secondary focus:ring-secondary"/>
              <span>📱 SMS 문자 알림</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer font-medium">
              <input type="checkbox" name="notify_email" value="1" <?= ($user['notify_email'] ?? 1) ? 'checked' : '' ?> class="rounded text-secondary focus:ring-secondary"/>
              <span>📧 이메일 신간 소식</span>
            </label>
          </div>

          <button type="submit"
                  class="w-full py-3 bg-[#07131e] hover:bg-[#1c2833] text-white rounded-xl text-xs font-bold transition-all shadow-sm">
            알림 및 연동 설정 저장하기
          </button>
        </form>
      </div>
    </div>

    <!-- [우측] ✏️ 회원 정보 & 기본 배송지 주소 수정 -->
    <div class="bg-surface rounded-2xl border border-outline-variant/80 p-6 md:p-7 shadow-sm">
      <div class="flex items-center justify-between pb-4 mb-5 border-b border-outline-variant/60">
        <h2 class="font-serif text-lg font-bold text-primary flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary">manage_accounts</span>
          회원 정보 및 배송지 수정
        </h2>
        <span class="text-xs text-gray-500 font-mono">ID: <?= htmlspecialchars($user['username']) ?></span>
      </div>

      <form action="/mypage/profile" method="POST" class="flex flex-col gap-4">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-semibold text-gray-700 mb-1 block">이름 (실명) *</label>
            <input type="text" name="name" required
                   value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                   class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-700 mb-1 block">닉네임</label>
            <input type="text" name="nickname"
                   value="<?= htmlspecialchars($user['nickname'] ?? '') ?>"
                   placeholder="닉네임 입력"
                   class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-semibold text-gray-700 mb-1 block">휴대전화번호</label>
            <input type="tel" name="phone"
                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                   placeholder="010-0000-0000"
                   class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-700 mb-1 block">이메일 주소 *</label>
            <input type="email" name="email" required
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                   class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
          </div>
        </div>

        <!-- 기본 배송지 주소 -->
        <div class="flex flex-col gap-2 pt-2 border-t border-outline-variant/40">
          <label class="text-xs font-semibold text-gray-700 block">기본 배송지 주소</label>
          <div class="flex gap-2">
            <input type="text" id="my_zipcode" name="zipcode" readonly
                   value="<?= htmlspecialchars($user['zipcode'] ?? '') ?>"
                   placeholder="우편번호"
                   class="w-28 bg-gray-50 border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface outline-none cursor-pointer"
                   onclick="execDaumPostcodeMypage()"/>
            <button type="button" onclick="execDaumPostcodeMypage()"
                    class="px-3.5 py-2 bg-gray-800 text-white rounded-lg text-xs font-medium hover:bg-gray-700 transition-colors">
              우편번호 검색
            </button>
          </div>

          <input type="text" id="my_address1" name="address1" readonly
                 value="<?= htmlspecialchars($user['address1'] ?? '') ?>"
                 placeholder="기본 주소"
                 class="w-full bg-gray-50 border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface outline-none cursor-pointer"
                 onclick="execDaumPostcodeMypage()"/>

          <input type="text" id="my_address2" name="address2"
                 value="<?= htmlspecialchars($user['address2'] ?? '') ?>"
                 placeholder="상세 주소 입력 (동/호수)"
                 class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>

        <!-- 비밀번호 변경 (선택) -->
        <div class="pt-2 border-t border-outline-variant/40">
          <label class="text-xs font-semibold text-gray-700 mb-1 block">새 비밀번호 변경 (변경 시에만 입력)</label>
          <input type="password" name="new_password" placeholder="8자 이상 새 비밀번호"
                 class="w-full border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>

        <button type="submit"
                class="w-full py-3 bg-secondary hover:bg-secondary/90 text-white rounded-xl text-xs font-bold transition-all shadow-sm mt-2">
          회원 정보 수정 완료
        </button>
      </form>
    </div>
  </div>

  <!-- 4. 최근 주문 내역 -->
  <section class="bg-surface rounded-2xl border border-outline-variant/80 p-6 md:p-7 shadow-sm mb-8">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-outline-variant/60">
      <h2 class="font-serif text-lg font-bold text-primary flex items-center gap-2">
        <span class="material-symbols-outlined text-secondary">receipt_long</span>
        최근 주문 내역
      </h2>
      <a href="/mypage/orders" class="text-xs text-secondary font-medium hover:underline">전체 주문 보기 →</a>
    </div>

    <?php if (empty($recentOrders)): ?>
      <div class="text-center py-12 text-on-surface-variant text-xs">
        <span class="material-symbols-outlined text-3xl mb-2 block opacity-30">receipt_long</span>
        최근 진행된 주문 내역이 없습니다.
      </div>
    <?php else: ?>
      <div class="flex flex-col gap-3">
        <?php foreach ($recentOrders as $order):
          $payLabels = ['WAITING'=>'입금 대기','PAID'=>'결제 완료','CANCELLED'=>'취소','REFUNDED'=>'환불'];
          $delLabels = ['PREPARING'=>'배송 준비','SHIPPING'=>'배송중','DELIVERED'=>'배송 완료'];
          $payColors = ['WAITING'=>'bg-tertiary-container text-on-tertiary-container','PAID'=>'bg-green-100 text-green-700',
                        'CANCELLED'=>'bg-error-container text-error','REFUNDED'=>'bg-surface-variant text-on-surface-variant'];
        ?>
          <div class="flex items-center justify-between p-4 bg-surface-container-low rounded-xl border border-outline-variant/60 hover:shadow-sm transition-shadow">
            <div>
              <p class="font-mono text-xs font-bold text-gray-800"><?= htmlspecialchars($order['order_no']) ?></p>
              <p class="font-bold text-primary text-sm mt-0.5"><?= number_format((int)$order['total_pay_price']) ?>원</p>
              <p class="text-[11px] text-gray-500 mt-0.5"><?= date('Y.m.d H:i', strtotime($order['created_at'])) ?></p>
            </div>
            <div class="text-right flex flex-col gap-1">
              <span class="inline-block text-xs px-2.5 py-0.5 rounded-full font-semibold <?= $payColors[$order['pay_status']] ?? 'bg-gray-100' ?>">
                <?= $payLabels[$order['pay_status']] ?? $order['pay_status'] ?>
              </span>
              <span class="text-xs text-gray-600 font-medium"><?= $delLabels[$order['delivery_status']] ?? '' ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- 로그아웃 -->
  <div class="text-center">
    <a href="/logout" class="inline-flex items-center gap-1 text-xs text-error font-medium hover:underline p-2">
      <span class="material-symbols-outlined text-sm">logout</span> 로그아웃
    </a>
  </div>
</main>

<script>
function execDaumPostcodeMypage() {
  new daum.Postcode({
    oncomplete: function(data) {
      let addr = (data.userSelectedType === 'R') ? data.roadAddress : data.jibunAddress;
      let extraAddr = '';
      if(data.userSelectedType === 'R'){
        if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){ extraAddr += data.bname; }
        if(data.buildingName !== '' && data.apartment === 'Y'){ extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName); }
        if(extraAddr !== ''){ extraAddr = ' (' + extraAddr + ')'; }
      }
      document.getElementById('my_zipcode').value = data.zonecode;
      document.getElementById('my_address1').value = addr + extraAddr;
      document.getElementById('my_address2').focus();
    }
  }).open();
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
