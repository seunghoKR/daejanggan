<?php
/**
 * 관리자 레이아웃
 * @var string $pageTitle
 * @var string $activeMenu
 */
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>관리자 — <?= htmlspecialchars($pageTitle ?? '대장간') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
  body { font-family: 'Noto Sans KR', sans-serif; background: #f4f3f1; }
  .admin-sidebar a.active { background: #1c2833; color: #fff; }
</style>
</head>
<body class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">

<!-- ======= 사이드바 ======= -->
<aside class="hidden md:flex flex-col w-56 bg-[#07131e] text-white shrink-0"
       :class="sidebarOpen ? 'w-56' : 'w-14'">
  <div class="px-4 py-5 border-b border-white/10 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined text-lg">storefront</span>
      <span x-show="sidebarOpen" class="font-bold text-sm">대장간 관리자</span>
    </div>
    <span x-show="sidebarOpen" class="text-[10px] bg-white/15 text-white/80 px-1.5 py-0.5 rounded-full font-mono"><?= defined('APP_VERSION') ? APP_VERSION : 'v1.2.0' ?></span>
  </div>

  <nav class="flex-1 py-4 flex flex-col gap-1 px-2 overflow-y-auto admin-sidebar">
    <?php
    $menus = [
      ['dashboard',       '/admin',            'dashboard',    '대시보드'],
      ['books',           '/admin/books',       'menu_book',    '도서 관리'],
      ['categories',      '/admin/categories',  'category',     '도서분류 관리'],
      ['banners',         '/admin/banners',     'view_carousel','배너/기획전 관리'],
      ['orders',          '/admin/orders',      'receipt_long', '주문 관리'],
      ['members',         '/admin/members',     'group',        '회원 관리'],
      ['settings',        '/admin/settings',    'settings',     '환경설정'],
    ];
    foreach ($menus as [$key, $url, $icon, $label]):
      $isActive = ($activeMenu ?? '') === $key;
    ?>
      <a href="<?= $url ?>"
         class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                <?= $isActive ? 'bg-white/15 text-white font-semibold' : 'text-white/70 hover:bg-white/10 hover:text-white' ?>">
        <span class="material-symbols-outlined text-xl"><?= $icon ?></span>
        <span x-show="sidebarOpen"><?= $label ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="px-4 py-3 border-t border-white/10">
    <a href="/" class="flex items-center gap-2 text-xs text-white/50 hover:text-white transition-colors">
      <span class="material-symbols-outlined text-base">open_in_new</span>
      <span x-show="sidebarOpen">쇼핑몰 보기</span>
    </a>
  </div>
</aside>

<!-- ======= 메인 콘텐츠 ======= -->
<div class="flex-1 flex flex-col overflow-hidden">

  <!-- 상단 바 -->
  <header class="bg-white border-b border-gray-200 px-4 md:px-6 py-3 flex items-center gap-4 shrink-0">
    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
      <span class="material-symbols-outlined">menu</span>
    </button>
    <h1 class="font-semibold text-gray-800 text-base"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
    <div class="ml-auto flex items-center gap-2 text-sm text-gray-500">
      <span class="material-symbols-outlined text-lg">person</span>
      <span><?= htmlspecialchars(Auth::user()['name'] ?? '') ?></span>
      <a href="/logout" class="ml-2 text-xs text-red-500 hover:underline">로그아웃</a>
    </div>
  </header>

  <!-- 페이지 콘텐츠 -->
  <main class="flex-1 overflow-y-auto p-4 md:p-6">

    <!-- Flash 메시지 -->
    <?php if (!empty($_SESSION['_flash_error'])): ?>
      <div class="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
        <?= htmlspecialchars($_SESSION['_flash_error']) ?>
      </div>
      <?php unset($_SESSION['_flash_error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['_flash_success'])): ?>
      <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200">
        <?= htmlspecialchars($_SESSION['_flash_success']) ?>
      </div>
      <?php unset($_SESSION['_flash_success']); ?>
    <?php endif; ?>
