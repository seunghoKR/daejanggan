<?php
$pageTitle = '회원가입';
include APP_ROOT . '/views/layouts/header.php';
$regErrors = $_SESSION['_reg_errors'] ?? [];
$regInput  = $_SESSION['_reg_input']  ?? [];
unset($_SESSION['_reg_errors'], $_SESSION['_reg_input']);
?>

<!-- 다음 우편번호 검색 서비스 SDK -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<main class="max-w-xl mx-auto px-4 py-10 pb-28 md:pb-12">
  <div class="text-center mb-8">
    <a href="/" class="inline-block hover:opacity-90 transition-opacity">
      <img src="/assets/images/logo.png" alt="도서출판 대장간" class="h-10 mx-auto object-contain"/>
    </a>
    <h1 class="font-serif text-2xl font-bold text-primary mt-4">도서출판 대장간 회원가입</h1>
    <p class="text-xs text-on-surface-variant mt-1">대장간 회원이 되시면 신간 소식과 다양한 혜택을 가장 먼저 받아보실 수 있습니다.</p>
  </div>

  <?php if (!empty($regErrors)): ?>
    <div class="mb-6 p-4 bg-error-container text-error rounded-xl text-xs flex flex-col gap-1 border border-error/20">
      <?php foreach ($regErrors as $err): ?>
        <p class="flex items-center gap-1.5"><span class="material-symbols-outlined text-sm">error</span> <?= htmlspecialchars($err) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form action="/register" method="POST"
        class="bg-surface rounded-2xl border border-outline-variant/80 p-6 md:p-8 flex flex-col gap-6 shadow-sm">

    <!-- 1. 기본 계정 정보 -->
    <div class="flex flex-col gap-4">
      <h2 class="text-sm font-bold text-primary pb-2 border-b border-outline-variant/60 flex items-center gap-2">
        <span class="material-symbols-outlined text-base text-secondary">account_circle</span>
        기본 계정 정보
      </h2>

      <div>
        <label class="text-xs font-semibold text-gray-700 mb-1 block">아이디 *</label>
        <input type="text" name="username" required
               value="<?= htmlspecialchars($regInput['username'] ?? '') ?>"
               placeholder="영문/숫자 4자 이상"
               class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1 block">비밀번호 *</label>
          <input type="password" name="password" required placeholder="8자 이상"
                 class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>
        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1 block">비밀번호 확인 *</label>
          <input type="password" name="password_confirm" required placeholder="비밀번호 재입력"
                 class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>
      </div>
    </div>

    <!-- 2. 회원 정보 (실명, 닉네임, 연락처, 이메일) -->
    <div class="flex flex-col gap-4">
      <h2 class="text-sm font-bold text-primary pb-2 border-b border-outline-variant/60 flex items-center gap-2">
        <span class="material-symbols-outlined text-base text-secondary">badge</span>
        개인 정보
      </h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1 block">실명 (이름) *</label>
          <input type="text" name="name" required
                 value="<?= htmlspecialchars($regInput['name'] ?? '') ?>"
                 placeholder="홍길동"
                 class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>
        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1 block">닉네임</label>
          <input type="text" name="nickname"
                 value="<?= htmlspecialchars($regInput['nickname'] ?? '') ?>"
                 placeholder="미입력 시 이름으로 자동 설정"
                 class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1 block">휴대전화번호 *</label>
          <input type="tel" name="phone" required
                 value="<?= htmlspecialchars($regInput['phone'] ?? '') ?>"
                 placeholder="010-0000-0000"
                 class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>
        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1 block">이메일 주소 *</label>
          <input type="email" name="email" required
                 value="<?= htmlspecialchars($regInput['email'] ?? '') ?>"
                 placeholder="example@domain.com"
                 class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        </div>
      </div>
    </div>

    <!-- 3. 기본 배송지 주소 (다음 우편번호 검색 연동) -->
    <div class="flex flex-col gap-3">
      <h2 class="text-sm font-bold text-primary pb-2 border-b border-outline-variant/60 flex items-center justify-between">
        <span class="flex items-center gap-2">
          <span class="material-symbols-outlined text-base text-secondary">home_pin</span>
          기본 배송지 주소 (선택)
        </span>
      </h2>

      <div class="flex gap-2">
        <input type="text" id="reg_zipcode" name="zipcode" readonly
               value="<?= htmlspecialchars($regInput['zipcode'] ?? '') ?>"
               placeholder="우편번호"
               class="w-32 bg-gray-50 border border-outline-variant rounded-lg px-4 py-2 text-xs text-on-surface outline-none cursor-pointer"
               onclick="execDaumPostcode()"/>
        <button type="button" onclick="execDaumPostcode()"
                class="px-4 py-2 bg-gray-800 text-white rounded-lg text-xs font-medium hover:bg-gray-700 transition-colors">
          우편번호 검색
        </button>
      </div>

      <input type="text" id="reg_address1" name="address1" readonly
             value="<?= htmlspecialchars($regInput['address1'] ?? '') ?>"
             placeholder="기본 주소 (검색 버튼을 클릭하세요)"
             class="w-full bg-gray-50 border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface outline-none cursor-pointer"
             onclick="execDaumPostcode()"/>

      <input type="text" id="reg_address2" name="address2"
             value="<?= htmlspecialchars($regInput['address2'] ?? '') ?>"
             placeholder="상세 주소 (동/호수, 층수 등 입력)"
             class="w-full border border-outline-variant rounded-lg px-4 py-2.5 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
    </div>

    <!-- 4. 알림 및 메신저 연동 설정 -->
    <div class="flex flex-col gap-3 p-4 bg-surface-container-low rounded-xl border border-outline-variant/60">
      <h2 class="text-xs font-bold text-primary flex items-center gap-1.5">
        <span class="material-symbols-outlined text-base text-secondary">notifications_active</span>
        주문/배송 & 소식 알림 수신 설정
      </h2>

      <!-- 카카오톡 알림 -->
      <label class="flex items-center gap-2.5 cursor-pointer text-xs text-gray-700">
        <input type="checkbox" name="notify_kakao" value="1" checked
               class="w-4 h-4 rounded text-secondary focus:ring-secondary border-gray-300"/>
        <span class="font-medium">💬 카카오톡 알림톡 수신 (주문/입금/배송 알림)</span>
      </label>

      <!-- 텔레그램 연동 ID -->
      <div class="flex flex-col gap-1.5 mt-1 pt-2 border-t border-outline-variant/40">
        <label class="text-xs text-gray-700 font-medium flex items-center gap-1">
          <span>✈️ 텔레그램 개인 ID / Chat ID (선택)</span>
          <span class="text-[11px] text-gray-400 font-normal">(@username 또는 숫자 Chat ID)</span>
        </label>
        <input type="text" name="telegram_id"
               value="<?= htmlspecialchars($regInput['telegramId'] ?? '') ?>"
               placeholder="예: @username 또는 123456789"
               class="w-full bg-white border border-outline-variant rounded-lg px-3 py-2 text-xs text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
        <p class="text-[11px] text-gray-500">입력 시 텔레그램 봇을 통해 도서 발송 및 주문 상태를 실시간으로 전송해 드립니다.</p>
      </div>

      <!-- SMS / 이메일 -->
      <div class="flex items-center gap-4 pt-2 border-t border-outline-variant/40 text-xs text-gray-600">
        <label class="flex items-center gap-1.5 cursor-pointer">
          <input type="checkbox" name="notify_sms" value="1" checked class="rounded text-secondary focus:ring-secondary"/>
          <span>📱 SMS 문자 알림</span>
        </label>
        <label class="flex items-center gap-1.5 cursor-pointer">
          <input type="checkbox" name="notify_email" value="1" checked class="rounded text-secondary focus:ring-secondary"/>
          <span>📧 이메일 신간 소식</span>
        </label>
      </div>
    </div>

    <!-- 제출 버튼 -->
    <button type="submit"
            class="w-full py-3.5 bg-[#07131e] text-white rounded-xl font-bold text-sm hover:bg-[#1c2833] transition-all shadow-md mt-2">
      가입 완료하기
    </button>

    <div class="text-center text-xs text-on-surface-variant">
      이미 대장간 계정이 있으신가요? <a href="/login" class="text-secondary hover:underline font-semibold ml-1">로그인하기</a>
    </div>
  </form>
</main>

<script>
function execDaumPostcode() {
  new daum.Postcode({
    oncomplete: function(data) {
      let addr = (data.userSelectedType === 'R') ? data.roadAddress : data.jibunAddress;
      let extraAddr = '';
      if(data.userSelectedType === 'R'){
        if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){ extraAddr += data.bname; }
        if(data.buildingName !== '' && data.apartment === 'Y'){ extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName); }
        if(extraAddr !== ''){ extraAddr = ' (' + extraAddr + ')'; }
      }
      document.getElementById('reg_zipcode').value = data.zonecode;
      document.getElementById('reg_address1').value = addr + extraAddr;
      document.getElementById('reg_address2').focus();
    }
  }).open();
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
