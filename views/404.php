<?php
$pageTitle = '페이지를 찾을 수 없습니다';
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-2xl mx-auto px-4 py-20 text-center">
  <div class="text-8xl font-bold text-surface-dim mb-4 font-serif">404</div>
  <h1 class="font-serif text-2xl font-bold text-primary mb-3">페이지를 찾을 수 없습니다</h1>
  <p class="text-on-surface-variant text-sm mb-8">
    요청하신 페이지가 존재하지 않거나 이동되었습니다.
  </p>
  <div class="flex gap-4 justify-center">
    <a href="/" class="px-6 py-3 bg-primary text-on-primary rounded-lg font-semibold text-sm hover:bg-primary-container transition-colors">
      홈으로
    </a>
    <a href="javascript:history.back()" class="px-6 py-3 border border-outline-variant text-on-surface-variant rounded-lg font-semibold text-sm hover:bg-surface-variant transition-colors">
      이전 페이지
    </a>
  </div>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
