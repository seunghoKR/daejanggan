<?php
/**
 * 🏢 회사소개 (도서출판 대장간)
 * site_settings의 company_intro_html을 동적으로 표시하고 감각적인 브랜드 레이아웃 제공
 */
$pageTitle = '회사소개';
$boardTitle = '회사소개';
include APP_ROOT . '/views/layouts/header.php';
$companyIntro = $companyIntro ?? ($GLOBALS['site']['company_intro_html'] ?? '');
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">
  <!-- 상단 브레드크럼 & 헤더 -->
  <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-outline-variant/60">
    <div>
      <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-1">
        <a href="/" class="hover:text-primary">홈</a>
        <span>›</span>
        <span>커뮤니티</span>
        <span>›</span>
        <span class="text-primary font-medium">회사소개</span>
      </div>
      <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary flex items-center gap-2">
        <span>도서출판 대장간 소개</span>
        <span class="text-xs font-sans px-2.5 py-0.5 rounded-full bg-secondary/10 text-secondary font-semibold">About Us</span>
      </h1>
      <p class="text-xs text-on-surface-variant mt-1">진리를 향한 깊은 물음, 삶을 변화시키는 평화와 생명의 책을 만듭니다.</p>
    </div>

    <?php if (Auth::isAdmin()): ?>
      <div>
        <a href="/admin/company" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#07131e] text-white text-xs font-semibold rounded-xl hover:bg-[#1c2833] transition-colors shadow-sm">
          <span class="material-symbols-outlined text-sm">edit</span>
          회사소개 내용 수정하기
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- 브랜드 비전 배너 -->
  <div class="mb-10 bg-gradient-to-r from-[#07131e] to-[#1c2833] text-white rounded-3xl p-8 md:p-12 shadow-md relative overflow-hidden">
    <div class="relative z-10 max-w-2xl">
      <span class="inline-block px-3 py-1 bg-white/10 rounded-full text-[11px] font-semibold tracking-wider text-secondary-container mb-4">DAEJANGGAN PUBLISHING</span>
      <h2 class="font-serif text-2xl md:text-3xl lg:text-4xl font-bold leading-snug mb-4">
        시대의 아픔을 보듬고<br/>
        참된 평화와 제자도를 벼려내는 대장간
      </h2>
      <p class="text-xs md:text-sm text-white/80 leading-relaxed font-light">
        대장간은 쇠를 달구어 쓸모 있는 도구를 만드는 곳입니다. 도서출판 대장간은 세상의 거친 풍파 속에서 복음과 정의, 평화와 생명의 가치를 책으로 벼려내어 한국 사회와 교회에 올곧은 길을 제시하고자 합니다.
      </p>
    </div>
    <div class="absolute right-4 bottom-4 opacity-10 pointer-events-none text-9xl">
      <span class="material-symbols-outlined" style="font-size: 180px;">auto_stories</span>
    </div>
  </div>

  <!-- 본문 콘텐츠 카드 -->
  <article class="bg-surface rounded-3xl border border-outline-variant/80 p-6 md:p-12 shadow-sm mb-12">
    <?php if (!empty($companyIntro)): ?>
      <div class="prose max-w-none text-on-surface leading-relaxed text-sm md:text-base">
        <?= $companyIntro ?>
      </div>
    <?php else: ?>
      <div class="py-12 text-center text-gray-500 text-sm">
        등록된 회사소개 내용이 없습니다. 관리자 화면에서 소개글을 입력해 주세요.
      </div>
    <?php endif; ?>
  </article>

  <!-- 출판 철학 & 오시는 길 3열 그리드 -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- 1. 핵심 가치 -->
    <div class="bg-surface rounded-2xl border border-outline-variant/60 p-6 shadow-sm flex flex-col justify-between">
      <div>
        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4">
          <span class="material-symbols-outlined">balance</span>
        </div>
        <h3 class="font-serif font-bold text-base text-primary mb-2">평화와 회복적 정의</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed">
          갈등과 폭력이 넘치는 세상에서 비폭력 평화주의와 예수 그리스도의 화해의 길을 조명합니다.
        </p>
      </div>
      <div class="mt-4 pt-4 border-t border-outline-variant/40 text-[11px] text-secondary font-semibold">
        #평화 #아나뱁티스트 #회복적정의
      </div>
    </div>

    <!-- 2. 신학과 학술 -->
    <div class="bg-surface rounded-2xl border border-outline-variant/60 p-6 shadow-sm flex flex-col justify-between">
      <div>
        <div class="w-10 h-10 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center mb-4">
          <span class="material-symbols-outlined">menu_book</span>
        </div>
        <h3 class="font-serif font-bold text-base text-primary mb-2">깊이 있는 인문·신학</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed">
          시대와 호흡하는 사회과학, 기독교 사상, 생태와 노동, 공동체를 아우르는 양서를 기획·번역합니다.
        </p>
      </div>
      <div class="mt-4 pt-4 border-t border-outline-variant/40 text-[11px] text-secondary font-semibold">
        #자본론 #NICS #비공출판사
      </div>
    </div>

    <!-- 3. 고객 및 출판 안내 -->
    <div class="bg-surface rounded-2xl border border-outline-variant/60 p-6 shadow-sm flex flex-col justify-between">
      <div>
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
          <span class="material-symbols-outlined">campaign</span>
        </div>
        <h3 class="font-serif font-bold text-base text-primary mb-2">출판 의뢰 및 문의</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed">
          대장간과 함께 뜻깊은 책을 출간하고자 하시는 저자, 번역가, 연구자 분들의 소중한 원고를 기다립니다.
        </p>
      </div>
      <div class="mt-4 pt-4 border-t border-outline-variant/40">
        <a href="/community/inquiry" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:text-secondary">
          출판 문의 바로가기 →
        </a>
      </div>
    </div>
  </div>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
