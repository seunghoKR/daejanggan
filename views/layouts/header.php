<?php
/**
 * 공통 헤더 레이아웃
 * @var string $pageTitle
 * @var int    $cartCount
 * @var array  $GLOBALS['site']
 */
$site      = $GLOBALS['site'] ?? [];
$siteName  = htmlspecialchars($site['site_name'] ?? '도서출판 대장간');
$pageTitle = htmlspecialchars($pageTitle ?? $siteName);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= $pageTitle ?> — <?= $siteName ?></title>
<meta name="description" content="도서출판 대장간 온라인 서점 — 신학, 평화, 정의, 아나뱁티스트, 인문사회 도서"/>
<link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico"/>
<link rel="shortcut icon" type="image/x-icon" href="/assets/images/favicon.ico"/>

<!-- OpenGraph & Twitter Card 메타 태그 (SNS 미리보기 최적화) -->
<meta property="og:type" content="website"/>
<meta property="og:site_name" content="<?= $siteName ?>"/>
<meta property="og:title" content="<?= $pageTitle ?> — <?= $siteName ?>"/>
<meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? '도서출판 대장간 온라인 서점 — 신학, 평화, 정의, 아나뱁티스트 도서') ?>"/>
<meta property="og:image" content="<?= htmlspecialchars($ogImage ?? 'http://ndaejanggan.iwinv.net/assets/images/logo.png') ?>"/>
<meta property="og:url" content="http://ndaejanggan.iwinv.net<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?>"/>
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:title" content="<?= $pageTitle ?>"/>
<meta name="twitter:image" content="<?= htmlspecialchars($ogImage ?? 'http://ndaejanggan.iwinv.net/assets/images/logo.png') ?>"/>

<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>

<!-- 폰트: Noto Serif KR / Noto Sans KR -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;600;700&family=Noto+Sans+KR:wght@300;400;500;600;700&family=Hanken+Grotesk:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<!-- Alpine.js -->
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Kakao JS SDK -->
<script src="https://t1.kakaocdn.net/kakao_js_sdk/2.7.2/kakao.min.js"></script>

<!-- Global SNS Share Helper -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (window.Kakao && !window.Kakao.isInitialized()) {
    // Kakao JavaScript 키 초기화
    try {
      window.Kakao.init('99042bbf083908f929311cb93df49c4f');
    } catch(e) {}
  }
});

window.shareSNS = function(platform, customTitle, customUrl, customImage, customDesc) {
  const currentUrl = customUrl || window.location.href;
  const rawTitle   = customTitle || document.title;
  const url        = encodeURIComponent(currentUrl);
  const title      = encodeURIComponent(rawTitle);
  const imageUrl   = customImage || 'http://ndaejanggan.iwinv.net/assets/images/logo.png';
  const desc       = customDesc || '도서출판 대장간';

  let shareUrl = '';

  switch(platform) {
    case 'kakao':
    case 'kakaotalk':
      if (window.Kakao && window.Kakao.isInitialized()) {
        window.Kakao.Share.sendDefault({
          objectType: 'feed',
          content: {
            title: rawTitle,
            description: desc,
            imageUrl: imageUrl,
            link: {
              mobileWebUrl: currentUrl,
              webUrl: currentUrl,
            },
          },
          buttons: [
            {
              title: '도서 보러가기',
              link: {
                mobileWebUrl: currentUrl,
                webUrl: currentUrl,
              },
            },
          ],
        });
        return;
      }
      // 폴백: 모바일 네이티브 공유 또는 클립보드 복사
      if (navigator.share) {
        navigator.share({ title: rawTitle, url: currentUrl }).catch(() => {});
        return;
      }
      break;

    case 'facebook':
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
      break;

    case 'twitter':
    case 'x':
      shareUrl = `https://twitter.com/intent/tweet?text=${title}&url=${url}`;
      break;

    case 'naver':
      shareUrl = `https://share.naver.com/web/shareView?url=${url}&title=${title}`;
      break;

    case 'band':
      shareUrl = `https://band.us/plugin/share?body=${title}%0A${url}&route=${url}`;
      break;

    case 'telegram':
      shareUrl = `https://t.me/share/url?url=${url}&text=${title}`;
      break;

    case 'copy':
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(currentUrl).then(() => {
          showShareToast('✨ 링크가 복사되었습니다! 원하는 곳에 붙여넣기(Ctrl+V)하세요.');
        }).catch(() => {
          prompt('이 링크를 복사하세요 (Ctrl+C):', currentUrl);
        });
      } else {
        prompt('이 링크를 복사하세요 (Ctrl+C):', currentUrl);
      }
      return;
  }

  if (shareUrl) {
    const w = 600, h = 600;
    const left = (screen.width/2)-(w/2), top = (screen.height/2)-(h/2);
    window.open(shareUrl, 'share_popup', `width=${w},height=${h},top=${top},left=${left},toolbar=no,menubar=no,scrollbars=yes`);
  }
};

function showShareToast(msg) {
  let toast = document.getElementById('shareToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'shareToast';
    toast.className = 'fixed bottom-8 left-1/2 -translate-x-1/2 bg-[#07131e] text-white px-5 py-3 rounded-full text-xs font-semibold shadow-2xl z-50 transition-opacity duration-300 opacity-0 pointer-events-none flex items-center gap-2';
    document.body.appendChild(toast);
  }
  toast.innerHTML = `<span class="material-symbols-outlined text-base text-secondary-container">link</span><span>${msg}</span>`;
  toast.style.opacity = '1';
  setTimeout(() => { toast.style.opacity = '0'; }, 2800);
}
</script>

<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "primary":                 "#07131e",
        "primary-container":       "#1c2833",
        "on-primary":              "#ffffff",
        "on-primary-container":    "#838f9d",
        "secondary":               "#b02d21",
        "secondary-container":     "#fc6451",
        "on-secondary":            "#ffffff",
        "on-secondary-container":  "#650001",
        "tertiary":                "#735c00",
        "tertiary-container":      "#cea701",
        "on-tertiary":             "#ffffff",
        "surface":                 "#faf9f7",
        "surface-dim":             "#dadad8",
        "surface-bright":          "#faf9f7",
        "surface-container":       "#efeeec",
        "surface-container-low":   "#f4f3f1",
        "surface-container-high":  "#e9e8e6",
        "surface-container-highest":"#e3e2e0",
        "surface-variant":         "#e3e2e0",
        "on-surface":              "#1a1c1b",
        "on-surface-variant":      "#44474b",
        "outline":                 "#74777c",
        "outline-variant":         "#c4c6cb",
        "inverse-surface":         "#2f3130",
        "inverse-on-surface":      "#f1f1ef",
        "error":                   "#ba1a1a",
        "error-container":         "#ffdad6",
        "background":              "#faf9f7",
        "on-background":           "#1a1c1b",
      },
      fontFamily: {
        "serif":  ['"Noto Serif KR"', '"Noto Serif"', 'serif'],
        "sans":   ['"Noto Sans KR"', '"Hanken Grotesk"', 'sans-serif'],
        "headline-md-mobile": ['"Noto Serif KR"', '"Noto Serif"', "serif"],
        "headline-md":        ['"Noto Serif KR"', '"Noto Serif"', "serif"],
      },
    },
  },
}
</script>

<style>
  body { background-color: #faf9f7; color: #1a1c1b; font-family: "Noto Sans KR", sans-serif; }
  .hide-scrollbar::-webkit-scrollbar { display: none; }
  .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  .book-card:hover .book-cover { transform: scale(1.03); }
  .book-cover { transition: transform 0.3s ease; }
  
  /* 드롭다운 호버 메뉴 스타일 */
  .nav-item-dropdown:hover .nav-dropdown-menu {
    display: block;
    animation: fadeIn 0.15s ease-out forwards;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>
</head>
<body class="min-h-screen antialiased flex flex-col justify-between" x-data="{ mobileMenuOpen: false }">

<!-- ============ TOP NAV BAR ============ -->
<header class="sticky top-0 z-50 bg-surface border-b border-outline-variant shadow-sm">
  <!-- 상단 로고 & 검색 & 사용자 메뉴 -->
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

    <!-- 로고 (고해상도 로고에 한글명/심볼 포함) -->
    <a href="/" class="flex items-center shrink-0 hover:opacity-90 transition-opacity">
      <img src="/assets/images/logo.png" alt="도서출판 대장간" class="h-8 md:h-10 w-auto object-contain"/>
    </a>

    <!-- 검색창 (데스크탑) -->
    <form action="/search" method="GET" class="hidden md:flex flex-1 max-w-lg mx-4">
      <div class="relative w-full">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">search</span>
        <input
          type="text" name="q"
          value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
          placeholder="도서명, 저자, 시리즈 검색"
          class="w-full bg-surface-container-high border border-outline-variant/60 rounded-full py-2 pl-10 pr-4 text-sm text-on-surface placeholder:text-on-surface-variant focus:ring-1 focus:ring-primary focus:border-primary outline-none"
        />
      </div>
    </form>

    <!-- 우측 사용자 메뉴 -->
    <div class="flex items-center gap-3">
      <?php if (Auth::check()): ?>
        <a href="/mypage" class="hidden md:flex items-center gap-1.5 text-on-surface-variant hover:text-primary text-sm px-3 py-1.5 rounded-full hover:bg-surface-variant transition-colors">
          <span class="material-symbols-outlined text-lg">person</span>
          <span class="text-xs font-medium"><?= htmlspecialchars(Auth::user()['name'] ?? '') ?></span>
        </a>
        <?php if (Auth::isAdmin()): ?>
          <a href="/admin" class="hidden md:inline-flex text-xs px-2.5 py-1 bg-primary text-white rounded-md hover:bg-primary-container font-medium">관리자</a>
        <?php endif; ?>
        <a href="/logout" class="hidden md:inline-flex text-xs text-on-surface-variant hover:text-secondary transition-colors">로그아웃</a>
      <?php else: ?>
        <a href="/login" class="hidden md:flex items-center gap-1 text-on-surface-variant hover:text-primary text-sm px-3 py-1.5 rounded-full hover:bg-surface-variant transition-colors">
          <span class="material-symbols-outlined text-lg">login</span>
          <span class="text-xs font-medium">로그인</span>
        </a>
        <a href="/register" class="hidden md:inline-flex text-xs text-on-surface-variant hover:text-secondary transition-colors">회원가입</a>
      <?php endif; ?>

      <!-- 찜 목록 (위시리스트) -->
      <a href="<?= Auth::check() ? '/mypage/wishlist' : '/login' ?>" class="p-2 text-on-surface-variant hover:text-secondary hover:bg-surface-variant rounded-full transition-colors hidden sm:inline-flex" title="위시리스트">
        <span class="material-symbols-outlined text-xl">favorite</span>
      </a>

      <!-- 장바구니 뱃지 -->
      <a href="/cart" class="relative p-2 text-primary hover:bg-surface-variant rounded-full transition-colors" title="장바구니">
        <span class="material-symbols-outlined text-2xl">shopping_bag</span>
        <?php if (($cartCount ?? 0) > 0): ?>
          <span class="absolute -top-0.5 -right-0.5 bg-secondary text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-bold">
            <?= min(99, $cartCount) ?>
          </span>
        <?php endif; ?>
      </a>

      <!-- 모바일 메뉴 버튼 -->
      <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-primary hover:bg-surface-variant rounded-full">
        <span class="material-symbols-outlined" x-text="mobileMenuOpen ? 'close' : 'menu'">menu</span>
      </button>
    </div>
  </div>

  <!-- ============ 대장간 공식 카테고리 GNB (데스크탑) ============ -->
  <nav class="hidden md:block border-t border-outline-variant bg-[#1c2833] text-white">
    <div class="max-w-7xl mx-auto px-4 flex items-center gap-1">

      <!-- 1. 도서 전체보기 -->
      <a href="/books" class="px-4 py-3 text-sm font-medium hover:bg-white/10 transition-colors flex items-center gap-1">
        <span class="material-symbols-outlined text-base text-secondary">menu_book</span>
        도서전체보기
      </a>

      <!-- 2. 시리즈 (드롭다운) -->
      <div class="relative nav-item-dropdown">
        <a href="/category/1030" class="px-4 py-3 text-sm font-medium hover:bg-white/10 transition-colors flex items-center gap-1 cursor-pointer">
          시리즈
          <span class="material-symbols-outlined text-xs opacity-70">expand_more</span>
        </a>
        <div class="nav-dropdown-menu hidden absolute top-full left-0 w-64 bg-white text-gray-800 rounded-b-xl shadow-xl border border-gray-200 py-2 z-50">
          <a href="/category/103010" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 정의와 평화 실천
          </a>
          <a href="/category/103020" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 회복적 정의/회복적 서클
          </a>
          <a href="/category/103030" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 아나뱁티스트/메노나이트
          </a>
          <a href="/category/103040" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 성경과 제국
          </a>
          <a href="/category/103050" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 느헤미야/이슈북
          </a>
          <a href="/category/103060" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 신자들의 교회 성서주석
          </a>
          <a href="/category/103070" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 생명과 평화의 눈으로 읽는 성서
          </a>
          <a href="/category/103080" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 누림신학
          </a>
          <a href="/category/103090" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 어린이 대장장이
          </a>
          <a href="/category/1030a0" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 대장간 문고
          </a>
        </div>
      </div>

      <!-- 2.5. 저자별 (드롭다운) -->
      <div class="relative nav-item-dropdown">
        <a href="/authors" class="px-4 py-3 text-sm font-medium hover:bg-white/10 transition-colors flex items-center gap-1 cursor-pointer">
          저자별
          <span class="material-symbols-outlined text-xs opacity-70">expand_more</span>
        </a>
        <div class="nav-dropdown-menu hidden absolute top-full left-0 w-60 bg-white text-gray-800 rounded-b-xl shadow-xl border border-gray-200 py-2 z-50">
          <div class="px-4 py-1.5 text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 mb-1">
            대표 저자 바로가기
          </div>
          <a href="/author/자끄 엘륄" class="flex items-center justify-between px-4 py-1.5 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span>자끄 엘륄</span><span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded-full">41권</span>
          </a>
          <a href="/author/팔머 베커" class="flex items-center justify-between px-4 py-1.5 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span>팔머 베커</span><span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded-full">18권</span>
          </a>
          <a href="/author/존 하워드 요더" class="flex items-center justify-between px-4 py-1.5 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span>존 하워드 요더</span><span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded-full">14권</span>
          </a>
          <a href="/author/김경호" class="flex items-center justify-between px-4 py-1.5 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span>김경호</span><span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded-full">10권</span>
          </a>
          <a href="/author/곽연근" class="flex items-center justify-between px-4 py-1.5 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span>곽연근</span><span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.2 rounded-full">10권</span>
          </a>
          <div class="border-t border-gray-100 mt-1 pt-1">
            <a href="/authors" class="flex items-center justify-center gap-1 px-4 py-2 text-xs font-semibold text-secondary hover:bg-gray-50">
              전체 저자 목록 보기 →
            </a>
          </div>
        </div>
      </div>

      <!-- 3. 주제별/장르별 (드롭다운) -->
      <div class="relative nav-item-dropdown">
        <a href="/category/1040" class="px-4 py-3 text-sm font-medium hover:bg-white/10 transition-colors flex items-center gap-1 cursor-pointer">
          주제별/장르별
          <span class="material-symbols-outlined text-xs opacity-70">expand_more</span>
        </a>
        <div class="nav-dropdown-menu hidden absolute top-full left-0 w-80 bg-white text-gray-800 rounded-b-xl shadow-xl border border-gray-200 p-3 z-50">
          <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 pb-1 border-b">주제별 도서 탐색</div>
          <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-xs">
            <a href="/category/104010" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 평화</a>
            <a href="/category/104015" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 하나님나라</a>
            <a href="/category/104020" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 교회</a>
            <a href="/category/104025" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 공동체</a>
            <a href="/category/104030" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 회복적정의/서클</a>
            <a href="/category/104035" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 아나뱁티스트</a>
            <a href="/category/104040" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 예수따름/제자도</a>
            <a href="/category/104045" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 사회선교</a>
            <a href="/category/104050" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 용서/화해</a>
            <a href="/category/104055" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 초대 교회</a>
            <a href="/category/104060" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 생태/환경</a>
            <a href="/category/104065" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 구약</a>
            <a href="/category/104070" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 신약</a>
            <a href="/category/104075" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 기독교신학</a>
            <a href="/category/104080" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 기독교윤리</a>
            <a href="/category/104085" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 교회사</a>
            <a href="/category/104090" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 선교/전도</a>
            <a href="/category/104095" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 성서일반</a>
            <a href="/category/1040a0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 신앙일반</a>
            <a href="/category/1040a5" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 사이비 이단</a>
            <a href="/category/1040b0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 어린이/청소년</a>
            <a href="/category/1040b5" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 교회생활</a>
            <a href="/category/1040c0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 인문사회과학</a>
            <a href="/category/1040c5" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 문학/예술</a>
            <a href="/category/1040d0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 기독교 아나키즘</a>
            <a href="/category/1040d5" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 기타</a>
          </div>
        </div>
      </div>

      <!-- 4. 비공출판사 (도서출판비공) (드롭다운) -->
      <div class="relative nav-item-dropdown">
        <a href="/category/1050" class="px-4 py-3 text-sm font-medium hover:bg-white/10 transition-colors flex items-center gap-1 cursor-pointer">
          비공출판사
          <span class="material-symbols-outlined text-xs opacity-70">expand_more</span>
        </a>
        <div class="nav-dropdown-menu hidden absolute top-full left-0 w-72 bg-white text-gray-800 rounded-b-xl shadow-xl border border-gray-200 p-3 z-50">
          <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2 pb-1 border-b">비공출판사 도서</div>
          <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-xs">
            <a href="/category/105010" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 문학</a>
            <a href="/category/105020" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 사회과학</a>
            <a href="/category/105030" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 정치</a>
            <a href="/category/105040" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 교육</a>
            <a href="/category/105050" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 생태/환경/기후</a>
            <a href="/category/105060" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 농촌</a>
            <a href="/category/105070" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 공동체/협동조합</a>
            <a href="/category/105080" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 반세계화</a>
            <a href="/category/105090" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 페미니즘/여성</a>
            <a href="/category/1050a0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 노동</a>
            <a href="/category/1050b0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 취미</a>
            <a href="/category/1050c0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 어린이/청소년</a>
            <a href="/category/1050d0" class="flex items-center gap-1 px-2 py-1.5 hover:bg-gray-100 hover:text-secondary rounded">› 종교</a>
          </div>
        </div>
      </div>

      <!-- 5. NICS (드롭다운) -->
      <div class="relative nav-item-dropdown">
        <a href="/category/1060" class="px-4 py-3 text-sm font-medium hover:bg-white/10 transition-colors flex items-center gap-1 cursor-pointer">
          NICS
          <span class="material-symbols-outlined text-xs opacity-70">expand_more</span>
        </a>
        <div class="nav-dropdown-menu hidden absolute top-full left-0 w-48 bg-white text-gray-800 rounded-b-xl shadow-xl border border-gray-200 py-2 z-50">
          <a href="/category/106010" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 느헤미야 렉처
          </a>
          <a href="/category/106020" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 교회
          </a>
          <a href="/category/106030" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 신학
          </a>
          <a href="/category/106040" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="material-symbols-outlined text-xs text-secondary">arrow_right</span> 신앙
          </a>
        </div>
      </div>

      <!-- 6. 커뮤니티 (드롭다운: 회사소개, 출판 문의, 대장간이벤트, 저자 소개, 글 먹는 시간, 자료실) -->
      <div class="relative nav-item-dropdown">
        <a href="/community/company" class="px-4 py-3 text-sm font-medium hover:bg-white/10 transition-colors flex items-center gap-1 cursor-pointer">
          커뮤니티
          <span class="material-symbols-outlined text-xs opacity-70">expand_more</span>
        </a>
        <div class="nav-dropdown-menu hidden absolute top-full left-0 w-48 bg-white text-gray-800 rounded-b-xl shadow-xl border border-gray-200 py-2 z-50">
          <a href="/community/company" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="text-[10px] text-secondary font-bold">▶</span> 회사소개
          </a>
          <a href="/community/inquiry" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="text-[10px] text-secondary font-bold">▶</span> 출판 문의
          </a>
          <a href="/community/event" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="text-[10px] text-secondary font-bold">▶</span> 대장간이벤트
          </a>
          <a href="/authors" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="text-[10px] text-secondary font-bold">▶</span> 저자 소개
          </a>
          <a href="/community/gallery" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="text-[10px] text-secondary font-bold">▶</span> 글 먹는 시간
          </a>
          <a href="/community/archive" class="flex items-center gap-2 px-4 py-2 text-xs hover:bg-gray-100 hover:text-secondary transition-colors">
            <span class="text-[10px] text-secondary font-bold">▶</span> 자료실
          </a>
        </div>
      </div>

      <!-- 우측 빠른 주문조회 링크 -->
      <div class="ml-auto">
        <a href="/order/lookup" class="px-3 py-1.5 bg-secondary/80 hover:bg-secondary text-white text-xs font-semibold rounded-md transition-colors flex items-center gap-1">
          <span class="material-symbols-outlined text-sm">local_shipping</span>
          주문/배송조회
        </a>
      </div>

    </div>
  </nav>

  <!-- ============ 모바일 메뉴 아코디언 ============ -->
  <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-surface border-t border-outline-variant pb-6 max-h-[85vh] overflow-y-auto">
    <!-- 모바일 검색 -->
    <form action="/search" method="GET" class="px-4 pt-4 pb-3">
      <div class="relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl">search</span>
        <input type="text" name="q" placeholder="도서명, 저자 검색"
          class="w-full bg-surface-container border border-outline-variant/60 rounded-full py-2.5 pl-10 pr-4 text-sm outline-none"/>
      </div>
    </form>

    <div class="px-4 flex flex-col gap-2 divide-y divide-outline-variant/40" x-data="{ openSec: 'series' }">
      <!-- 1. 도서 전체 -->
      <div class="pt-2">
        <a href="/books" class="py-2 text-sm font-semibold text-primary block flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-lg">menu_book</span> 도서전체보기
        </a>
      </div>

      <!-- 2. 시리즈 아코디언 -->
      <div class="pt-2">
        <button @click="openSec = (openSec === 'series' ? '' : 'series')" class="w-full py-2 text-sm font-semibold text-primary flex items-center justify-between">
          <span>📚 대장간 시리즈</span>
          <span class="material-symbols-outlined text-sm text-on-surface-variant" x-text="openSec === 'series' ? 'expand_less' : 'expand_more'"></span>
        </button>
        <div x-show="openSec === 'series'" class="pl-4 pb-2 flex flex-col gap-1.5 text-xs text-on-surface-variant">
          <a href="/category/103010" class="py-1 hover:text-secondary">› 정의와 평화 실천</a>
          <a href="/category/103030" class="py-1 hover:text-secondary">› 아나뱁티스트/메노나이트</a>
          <a href="/category/103040" class="py-1 hover:text-secondary">› 성경과 제국</a>
          <a href="/category/103050" class="py-1 hover:text-secondary">› 느헤미야/이슈북</a>
          <a href="/category/103060" class="py-1 hover:text-secondary">› 신자들의 교회 성서주석</a>
          <a href="/category/103070" class="py-1 hover:text-secondary">› 생명과 평화의 눈으로 읽는 성서</a>
          <a href="/category/103080" class="py-1 hover:text-secondary">› 누림신학</a>
          <a href="/category/103090" class="py-1 hover:text-secondary">› 어린이 대장장이</a>
          <a href="/category/1030a0" class="py-1 hover:text-secondary">› 대장간 문고</a>
        </div>
      </div>

      <!-- 2.5. 저자별 아코디언 -->
      <div class="pt-2">
        <button @click="openSec = (openSec === 'author' ? '' : 'author')" class="w-full py-2 text-sm font-semibold text-primary flex items-center justify-between">
          <span>✍️ 저자별 도서</span>
          <span class="material-symbols-outlined text-sm text-on-surface-variant" x-text="openSec === 'author' ? 'expand_less' : 'expand_more'"></span>
        </button>
        <div x-show="openSec === 'author'" class="pl-4 pb-2 flex flex-col gap-1.5 text-xs text-on-surface-variant">
          <a href="/authors" class="py-1 font-bold text-secondary">› 전체 저자 모아보기 →</a>
          <a href="/author/자끄 엘륄" class="py-1 hover:text-secondary">› 자끄 엘륄</a>
          <a href="/author/팔머 베커" class="py-1 hover:text-secondary">› 팔머 베커</a>
          <a href="/author/존 하워드 요더" class="py-1 hover:text-secondary">› 존 하워드 요더</a>
          <a href="/author/김경호" class="py-1 hover:text-secondary">› 김경호</a>
          <a href="/author/곽연근" class="py-1 hover:text-secondary">› 곽연근</a>
        </div>
      </div>

      <!-- 3. 주제별/장르별 아코디언 -->
      <div class="pt-2">
        <button @click="openSec = (openSec === 'topic' ? '' : 'topic')" class="w-full py-2 text-sm font-semibold text-primary flex items-center justify-between">
          <span>📖 주제별 / 장르별</span>
          <span class="material-symbols-outlined text-sm text-on-surface-variant" x-text="openSec === 'topic' ? 'expand_less' : 'expand_more'"></span>
        </button>
        <div x-show="openSec === 'topic'" class="pl-4 pb-2 flex flex-col gap-1.5 text-xs text-on-surface-variant">
          <a href="/category/104010" class="py-1 hover:text-secondary">› 신학/성서학</a>
          <a href="/category/104020" class="py-1 hover:text-secondary">› 기독교 사상/철학</a>
          <a href="/category/104030" class="py-1 hover:text-secondary">› 교회와 목회</a>
          <a href="/category/104040" class="py-1 hover:text-secondary">› 영성과 삶</a>
          <a href="/category/104050" class="py-1 hover:text-secondary">› 사회/윤리/평화</a>
        </div>
      </div>

      <!-- 4. 도서출판비공 아코디언 -->
      <div class="pt-2">
        <button @click="openSec = (openSec === 'bigong' ? '' : 'bigong')" class="w-full py-2 text-sm font-semibold text-primary flex items-center justify-between">
          <span>🌱 도서출판비공</span>
          <span class="material-symbols-outlined text-sm text-on-surface-variant" x-text="openSec === 'bigong' ? 'expand_less' : 'expand_more'"></span>
        </button>
        <div x-show="openSec === 'bigong'" class="pl-4 pb-2 grid grid-cols-2 gap-1.5 text-xs text-on-surface-variant">
          <a href="/category/105010" class="py-1 hover:text-secondary">› 문학</a>
          <a href="/category/105020" class="py-1 hover:text-secondary">› 사회과학</a>
          <a href="/category/105030" class="py-1 hover:text-secondary">› 정치</a>
          <a href="/category/105040" class="py-1 hover:text-secondary">› 교육</a>
          <a href="/category/105050" class="py-1 hover:text-secondary">› 생태/환경/기후</a>
          <a href="/category/105060" class="py-1 hover:text-secondary">› 농촌</a>
          <a href="/category/105070" class="py-1 hover:text-secondary">› 공동체/협동조합</a>
          <a href="/category/105080" class="py-1 hover:text-secondary">› 반세계화</a>
          <a href="/category/105090" class="py-1 hover:text-secondary">› 페미니즘/여성</a>
          <a href="/category/1050a0" class="py-1 hover:text-secondary">› 노동</a>
          <a href="/category/1050b0" class="py-1 hover:text-secondary">› 취미</a>
          <a href="/category/1050c0" class="py-1 hover:text-secondary">› 어린이/청소년</a>
        </div>
      </div>

      <!-- 5. NICS 아코디언 -->
      <div class="pt-2">
        <button @click="openSec = (openSec === 'nics' ? '' : 'nics')" class="w-full py-2 text-sm font-semibold text-primary flex items-center justify-between">
          <span>🏛️ NICS</span>
          <span class="material-symbols-outlined text-sm text-on-surface-variant" x-text="openSec === 'nics' ? 'expand_less' : 'expand_more'"></span>
        </button>
        <div x-show="openSec === 'nics'" class="pl-4 pb-2 flex flex-col gap-1.5 text-xs text-on-surface-variant">
          <a href="/category/106010" class="py-1 hover:text-secondary">› 느헤미야 렉처</a>
          <a href="/category/106020" class="py-1 hover:text-secondary">› 교회</a>
          <a href="/category/106030" class="py-1 hover:text-secondary">› 신학</a>
          <a href="/category/106040" class="py-1 hover:text-secondary">› 신앙</a>
        </div>
      </div>

      <!-- 6. 계정 및 주문 -->
      <div class="pt-3 flex flex-col gap-1 text-sm font-medium">
        <a href="/order/lookup" class="py-1.5 text-secondary flex items-center gap-1.5">
          <span class="material-symbols-outlined text-base">local_shipping</span> 비회원 주문/배송조회
        </a>
        <a href="/community/notice" class="py-1.5 text-on-surface-variant hover:text-primary">공지사항 및 자료실</a>
        <?php if (Auth::check()): ?>
          <a href="/mypage" class="py-1.5 text-on-surface-variant hover:text-primary">마이페이지 (적립금/주문)</a>
          <a href="/logout" class="py-1.5 text-error">로그아웃</a>
        <?php else: ?>
          <div class="flex gap-2 mt-2">
            <a href="/login" class="flex-1 py-2 text-center bg-primary text-white rounded-lg text-xs font-semibold">로그인</a>
            <a href="/register" class="flex-1 py-2 text-center border border-outline-variant text-on-surface rounded-lg text-xs font-semibold">회원가입</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<!-- Flash 메시지 -->
<?php if (!empty($_SESSION['_flash_error'])): ?>
  <div class="bg-error-container text-error px-4 py-3 text-sm text-center" role="alert">
    <?= htmlspecialchars($_SESSION['_flash_error']) ?>
  </div>
  <?php unset($_SESSION['_flash_error']); ?>
<?php endif; ?>
<?php if (!empty($_SESSION['_flash_success'])): ?>
  <div class="bg-green-50 text-green-800 px-4 py-3 text-sm text-center border-b border-green-200" role="alert">
    <?= htmlspecialchars($_SESSION['_flash_success']) ?>
  </div>
  <?php unset($_SESSION['_flash_success']); ?>
<?php endif; ?>
