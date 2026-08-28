<?php
$pageTitle = '로그인';
include APP_ROOT . '/views/layouts/header.php';
$errors = $_SESSION['_flash_error'] ?? null;
unset($_SESSION['_flash_error']);
?>

<main class="max-w-md mx-auto px-4 py-12 pb-28 md:pb-12">
  <div class="text-center mb-8">
    <a href="/" class="font-serif text-3xl font-bold text-primary">Daejanggan</a>
    <p class="text-sm text-on-surface-variant mt-2">도서출판 대장간 온라인 서점</p>
  </div>

  <?php if ($errors): ?>
    <div class="mb-4 p-3 bg-error-container text-error rounded-lg text-sm text-center">
      <?= htmlspecialchars($errors) ?>
    </div>
  <?php endif; ?>

  <form action="/login" method="POST"
    class="bg-surface rounded-2xl border border-outline-variant p-6 flex flex-col gap-4">

    <h1 class="font-serif text-xl font-bold text-primary text-center">로그인</h1>

    <div>
      <label class="text-xs text-on-surface-variant mb-1 block">아이디</label>
      <input type="text" name="username" required autofocus
        class="w-full border border-outline-variant rounded-lg px-4 py-3 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
    </div>

    <div>
      <label class="text-xs text-on-surface-variant mb-1 block">비밀번호</label>
      <input type="password" name="password" required
        class="w-full border border-outline-variant rounded-lg px-4 py-3 text-sm text-on-surface focus:ring-1 focus:ring-primary outline-none"/>
    </div>

    <button type="submit"
      class="py-3 bg-primary text-on-primary rounded-lg font-semibold text-sm hover:bg-primary-container transition-colors shadow-md">
      로그인
    </button>

    <div class="text-center text-xs text-on-surface-variant">
      계정이 없으신가요?
      <a href="/register" class="text-secondary hover:underline font-medium">회원가입</a>
    </div>
  </form>

  <div class="mt-4 text-center text-xs text-on-surface-variant">
    <a href="/order/lookup" class="hover:text-secondary transition-colors">비회원 주문 조회 →</a>
  </div>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
