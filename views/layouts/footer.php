<?php
/**
 * 공통 푸터 레이아웃
 */
$site = $GLOBALS['site'] ?? [];
?>

<!-- ============ FOOTER ============ -->
<footer class="bg-surface-container-low border-t border-outline-variant mt-16" x-data="{ showTerms: false, showPrivacy: false }">
  <div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

      <!-- 브랜드 -->
      <div>
        <div class="mb-3">
          <img src="/assets/images/logo.png" alt="도서출판 대장간" class="h-8 w-auto object-contain"/>
        </div>
        <p class="text-sm text-on-surface-variant leading-relaxed">
          진리를 향한 깊은 물음,<br/>
          삶을 변화시키는 책을 만듭니다.
        </p>
      </div>

      <!-- 고객센터 -->
      <div>
        <h4 class="font-medium text-on-surface mb-3 text-sm tracking-wide uppercase">고객센터</h4>
        <div class="space-y-1.5 text-sm text-on-surface-variant">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-primary">call</span>
            <a href="tel:<?= htmlspecialchars($site['cs_phone'] ?? '041-742-1424') ?>"
               class="hover:text-primary transition-colors">
              <?= htmlspecialchars($site['cs_phone'] ?? '041-742-1424') ?>
            </a>
          </div>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-primary">schedule</span>
            <span><?= htmlspecialchars($site['cs_hours'] ?? '평일 09:30 ~ 17:30') ?></span>
          </div>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-base text-primary">mail</span>
            <a href="mailto:<?= htmlspecialchars($site['email'] ?? 'jlife@daejanggan.org') ?>"
               class="hover:text-primary transition-colors">
              <?= htmlspecialchars($site['email'] ?? 'jlife@daejanggan.org') ?>
            </a>
          </div>
        </div>

        <!-- 무통장 계좌 -->
        <div class="mt-4 p-3 bg-surface rounded-lg border border-outline-variant">
          <p class="text-xs font-semibold text-on-surface mb-1 uppercase tracking-wide">무통장 입금</p>
          <p class="text-sm text-on-surface font-medium">
            <?= htmlspecialchars($site['bank_account'] ?? '우체국 311639-02-150821 배용하') ?>
          </p>
        </div>
      </div>

      <!-- 링크 -->
      <div>
        <h4 class="font-medium text-on-surface mb-3 text-sm tracking-wide uppercase">쇼핑몰 안내</h4>
        <nav class="flex flex-col gap-2">
          <a href="/community/notice"    class="text-sm text-on-surface-variant hover:text-secondary transition-colors">공지사항</a>
          <a href="/community/archive"   class="text-sm text-on-surface-variant hover:text-secondary transition-colors">자료실</a>
          <a href="/order/lookup"        class="text-sm text-on-surface-variant hover:text-secondary transition-colors">주문/배송 조회</a>
          <button type="button" @click="showTerms = true" class="text-left text-sm text-on-surface-variant hover:text-secondary transition-colors">이용약관</button>
          <button type="button" @click="showPrivacy = true" class="text-left text-sm text-on-surface-variant hover:text-secondary transition-colors font-semibold">개인정보처리방침</button>
        </nav>
      </div>
    </div>

    <!-- 사업자 정보 & 전자상거래 법적 고지 -->
    <div class="mt-8 pt-6 border-t border-outline-variant space-y-3">
      <!-- 1행: 상호, 대표자, 사업자등록번호(공정위 링크), 통신판매업신고 -->
      <div class="text-xs text-on-surface-variant leading-relaxed flex flex-wrap items-center gap-x-3 gap-y-1">
        <span><strong class="text-on-surface font-bold"><?= htmlspecialchars($site['site_name'] ?? '도서출판 대장간') ?></strong></span>
        <span class="text-gray-300">|</span>
        <span>대표자: <strong class="text-on-surface"><?= htmlspecialchars($site['ceo_name'] ?? '배용하') ?></strong></span>
        <span class="text-gray-300">|</span>
        <span>사업자등록번호: <?= htmlspecialchars($site['biz_number'] ?? '305-92-42157') ?>
          <a href="https://www.ftc.go.kr/bizCommPop.do?wrkr_no=<?= preg_replace('/[^0-9]/', '', $site['biz_number'] ?? '3059242157') ?>"
             target="_blank" rel="noopener noreferrer"
             class="underline hover:text-primary text-[11px] text-gray-500 ml-0.5 font-medium">[사업자정보확인]</a>
        </span>
        <span class="text-gray-300">|</span>
        <span>통신판매업신고: <?= htmlspecialchars($site['mail_order_number'] ?? '제2011-충남논산-0016호') ?></span>
      </div>

      <!-- 2행: 사업장 주소, 대표전화, 대표이메일, 개인정보보호책임자, 호스팅제공자 -->
      <div class="text-xs text-on-surface-variant leading-relaxed flex flex-wrap items-center gap-x-3 gap-y-1">
        <span>사업장 소재지: <?= htmlspecialchars($site['address'] ?? '충남 논산시 가야곡면 매죽헌로1176번길 8-54 101호') ?></span>
        <span class="text-gray-300">|</span>
        <span>대표전화: <?= htmlspecialchars($site['cs_phone'] ?? '041-742-1424') ?></span>
        <span class="text-gray-300">|</span>
        <span>이메일: <?= htmlspecialchars($site['email'] ?? 'jlife@daejanggan.org') ?></span>
        <span class="text-gray-300">|</span>
        <span>개인정보보호책임자: <?= htmlspecialchars($site['privacy_officer'] ?? '배용하') ?></span>
        <span class="text-gray-300">|</span>
        <span>호스팅 서비스 제공자: <strong class="text-on-surface"><?= htmlspecialchars($site['hosting_company'] ?? '(주)스마일서브 (iwinv.com)') ?></strong></span>
      </div>

      <!-- 3행: 에스크로 구매안전서비스 안내 -->
      <div class="p-3 bg-surface rounded-lg border border-outline-variant/60 text-[11px] text-on-surface-variant leading-relaxed flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-base text-emerald-600">verified_user</span>
          <span>
            <strong>KG이니시스 구매안전(에스크로) 서비스:</strong> 고객님의 안전거래를 위해 현금 등으로 결제 시 저희 쇼핑몰에서 가입한 KG이니시스 전자보증 구매안전서비스를 이용하실 수 있습니다.
          </span>
        </div>
        <a href="https://mark.inicis.com/mark/escrow_popup.php?mid=<?= htmlspecialchars($site['inicis_mid'] ?? 'INIBillTst') ?>"
           target="_blank" rel="noopener noreferrer"
           class="text-[11px] font-semibold text-blue-600 hover:underline shrink-0 flex items-center gap-0.5">
          <span>서비스 가입사실 확인</span>
          <span class="material-symbols-outlined text-xs">open_in_new</span>
        </a>
      </div>

      <!-- 4행: 저작권 & 버전 -->
      <div class="pt-2 text-xs text-on-surface-variant flex items-center justify-between flex-wrap gap-2">
        <span>© <?= date('Y') ?> Daejanggan Publishing House. All rights reserved.</span>
        <span class="text-[11px] text-on-surface-variant/60 font-mono"><?= defined('APP_VERSION') ? APP_VERSION : 'v1.5.0' ?></span>
      </div>
    </div>
  </div>

  <!-- 이용약관 모달 -->
  <div x-show="showTerms" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div @click.away="showTerms = false" class="bg-white rounded-2xl max-w-2xl w-full max-h-[80vh] flex flex-col shadow-2xl border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
        <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">description</span>
          <span>도서출판 대장간 이용약관</span>
        </h3>
        <button type="button" @click="showTerms = false" class="text-gray-400 hover:text-gray-700 text-xl font-bold">&times;</button>
      </div>
      <div class="p-6 overflow-y-auto text-xs text-gray-700 leading-relaxed space-y-4">
        <h4 class="font-bold text-gray-900 text-sm">제1조 (목적)</h4>
        <p>본 약관은 도서출판 대장간(이하 "몰")이 운영하는 인터넷 사이버 몰에서 제공하는 전자상거래 관련 서비스(이하 "서비스")를 이용함에 있어 몰과 이용자의 권리, 의무 및 책임사항을 규정함을 목적으로 합니다.</p>
        <h4 class="font-bold text-gray-900 text-sm">제2조 (정의)</h4>
        <p>"몰"이란 도서출판 대장간이 재화 또는 용역을 이용자에게 제공하기 위하여 컴퓨터 등 정보통신설비를 이용하여 재화 등을 거래할 수 있도록 설정한 가상의 영업장을 말합니다.</p>
        <h4 class="font-bold text-gray-900 text-sm">제3조 (회원가입 및 주문)</h4>
        <p>이용자는 몰이 정한 가입 양식에 따라 회원정보를 기입한 후 본 약관에 동의한다는 의사표시를 함으로서 회원가입을 신청합니다. 몰은 이용자의 주문 접수 후 신속하고 안전하게 상품을 배송합니다.</p>
        <h4 class="font-bold text-gray-900 text-sm">제4조 (청약철회 및 환불)</h4>
        <p>몰과 재화 등의 구매에 관한 계약을 체결한 이용자는 「전자상거래 등에서의 소비자보호에 관한 법률」 제17조에 따라 상품을 수령한 날부터 7일 이내에 청약의 철회를 할 수 있습니다.</p>
      </div>
      <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end">
        <button type="button" @click="showTerms = false" class="px-4 py-2 bg-[#07131e] text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition-colors">확인</button>
      </div>
    </div>
  </div>

  <!-- 개인정보처리방침 모달 -->
  <div x-show="showPrivacy" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div @click.away="showPrivacy = false" class="bg-white rounded-2xl max-w-2xl w-full max-h-[80vh] flex flex-col shadow-2xl border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
        <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">security</span>
          <span>도서출판 대장간 개인정보처리방침</span>
        </h3>
        <button type="button" @click="showPrivacy = false" class="text-gray-400 hover:text-gray-700 text-xl font-bold">&times;</button>
      </div>
      <div class="p-6 overflow-y-auto text-xs text-gray-700 leading-relaxed space-y-4">
        <p class="text-gray-500">도서출판 대장간은 「개인정보 보호법」 제30조에 따라 정보주체의 개인정보를 보호하고 이와 관련한 고충을 신속하고 원활하게 처리할 수 있도록 다음과 같이 개인정보 처리방침을 수립·공개합니다.</p>
        <h4 class="font-bold text-gray-900 text-sm">1. 개인정보의 수집 및 이용 목적</h4>
        <p>몰은 회원관리, 본인확인, 주문 및 결제 처리, 상품 배송, 출판 의뢰 상담 등을 위해 필요한 최소한의 개인정보를 수집하여 처리합니다.</p>
        <h4 class="font-bold text-gray-900 text-sm">2. 수집하는 개인정보의 항목</h4>
        <p>- 필수항목: 성명, 아이디, 비밀번호, 휴대전화번호, 배송지 주소, 결제정보<br/>- 선택항목: 이메일, 텔레그램 ID</p>
        <h4 class="font-bold text-gray-900 text-sm">3. 개인정보의 보유 및 이용 기간</h4>
        <p>이용자의 개인정보는 원칙적으로 개인정보의 처리 목적이 달성되면 지체 없이 파기합니다. 단, 전자상거래법 등 관계 법령에 의하여 보존할 필요가 있는 경우 관련 법령이 정한 기간 동안 안전하게 보관합니다.</p>
        <h4 class="font-bold text-gray-900 text-sm">4. 개인정보 보호책임자</h4>
        <p>성명: <?= htmlspecialchars($site['privacy_officer'] ?? '배용하') ?><br/>연락처: <?= htmlspecialchars($site['cs_phone'] ?? '041-742-1424') ?> (<?= htmlspecialchars($site['email'] ?? 'jlife@daejanggan.org') ?>)</p>
      </div>
      <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 flex justify-end">
        <button type="button" @click="showPrivacy = false" class="px-4 py-2 bg-[#07131e] text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition-colors">확인</button>
      </div>
    </div>
  </div>
</footer>

<!-- 모바일 하단 네비게이션 -->
<nav class="fixed bottom-0 w-full md:hidden flex justify-around items-center px-4 py-2 bg-surface border-t border-outline-variant shadow-lg z-50">
  <a href="/" class="flex flex-col items-center text-secondary gap-0.5 w-16">
    <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1">home</span>
    <span class="text-xs font-label-sm">홈</span>
  </a>
  <a href="/search" class="flex flex-col items-center text-on-surface-variant hover:text-primary gap-0.5 w-16">
    <span class="material-symbols-outlined">search</span>
    <span class="text-xs font-label-sm">검색</span>
  </a>
  <?php if (Auth::check()): ?>
    <a href="/mypage/wishlist" class="flex flex-col items-center text-on-surface-variant hover:text-primary gap-0.5 w-16">
      <span class="material-symbols-outlined">favorite</span>
      <span class="text-xs font-label-sm">위시</span>
    </a>
    <a href="/mypage" class="flex flex-col items-center text-on-surface-variant hover:text-primary gap-0.5 w-16">
      <span class="material-symbols-outlined">person</span>
      <span class="text-xs font-label-sm">마이</span>
    </a>
  <?php else: ?>
    <a href="/login" class="flex flex-col items-center text-on-surface-variant hover:text-primary gap-0.5 w-16">
      <span class="material-symbols-outlined">login</span>
      <span class="text-xs font-label-sm">로그인</span>
    </a>
    <a href="/register" class="flex flex-col items-center text-on-surface-variant hover:text-primary gap-0.5 w-16">
      <span class="material-symbols-outlined">person_add</span>
      <span class="text-xs font-label-sm">가입</span>
    </a>
  <?php endif; ?>
  <a href="/cart" class="flex flex-col items-center text-on-surface-variant hover:text-primary gap-0.5 w-16 relative">
    <span class="material-symbols-outlined">shopping_bag</span>
    <?php if (($cartCount ?? 0) > 0): ?>
      <span class="absolute -top-1 right-1 bg-secondary text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">
        <?= min(9, $cartCount ?? 0) ?>
      </span>
    <?php endif; ?>
    <span class="text-xs font-label-sm">장바구니</span>
  </a>
</nav>

<!-- 모바일 하단 여백 (하단 nav 가리지 않게) -->
<div class="h-20 md:hidden"></div>

</body>
</html>
