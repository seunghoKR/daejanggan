<?php
/**
 * 공통 푸터 레이아웃
 */
$site = $GLOBALS['site'] ?? [];
?>

<!-- ============ FOOTER ============ -->
<footer class="bg-surface-container-low border-t border-outline-variant mt-16">
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
          <a href="#"                    class="text-sm text-on-surface-variant hover:text-secondary transition-colors">이용약관</a>
          <a href="#" class="text-sm text-on-surface-variant hover:text-secondary transition-colors font-semibold">개인정보처리방침</a>
        </nav>
      </div>
    </div>

    <!-- 사업자 정보 -->
    <div class="mt-8 pt-6 border-t border-outline-variant">
      <p class="text-xs text-on-surface-variant leading-relaxed">
        <strong class="text-on-surface"><?= htmlspecialchars($site['site_name'] ?? '도서출판 대장간') ?></strong>
        &nbsp;|&nbsp; 대표자: <?= htmlspecialchars($site['ceo_name'] ?? '배용하') ?>
        &nbsp;|&nbsp; 사업자등록번호: <?= htmlspecialchars($site['biz_number'] ?? '305-92-42157') ?>
        <br/>
        <?= htmlspecialchars($site['address'] ?? '충남 논산시 가야곡면 매죽헌로1176번길 8-54 101호') ?>
      </p>
      <p class="text-xs text-on-surface-variant mt-2 flex items-center justify-between flex-wrap gap-2">
        <span>© <?= date('Y') ?> Daejanggan Publishing House. All rights reserved.</span>
        <span class="text-[11px] text-on-surface-variant/60 font-mono"><?= defined('APP_VERSION') ? APP_VERSION : 'v1.2.0' ?></span>
      </p>
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
