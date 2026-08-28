<?php
/**
 * 마이페이지 위시리스트
 */
$pageTitle = '위시리스트';
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">
  <div class="flex items-center justify-between mb-6">
    <h1 class="font-serif text-2xl font-bold text-primary">나의 찜 목록</h1>
    <a href="/mypage" class="text-xs text-on-surface-variant hover:text-primary">← 마이페이지로 돌아가기</a>
  </div>

  <?php if (empty($wishlist)): ?>
    <div class="text-center py-20 bg-surface rounded-2xl border border-surface-variant text-on-surface-variant">
      <span class="material-symbols-outlined text-6xl mb-3 opacity-30">favorite_border</span>
      <p class="text-base font-medium">찜한 도서가 없습니다.</p>
      <a href="/books" class="mt-4 inline-block px-6 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-semibold">도서 보러가기</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <?php foreach ($wishlist as $book): ?>
        <div class="bg-surface rounded-xl border border-surface-variant overflow-hidden flex flex-col p-3">
          <a href="/book/<?= htmlspecialchars($book['book_code']) ?>" class="aspect-[3/4] bg-surface-container rounded-lg overflow-hidden mb-2">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" class="w-full h-full object-cover" onerror="this.src='/assets/images/default_book.png'"/>
          </a>
          <h3 class="font-serif text-xs font-semibold text-primary line-clamp-2"><?= htmlspecialchars($book['title']) ?></h3>
          <p class="text-xs font-bold text-primary mt-1"><?= number_format((int)$book['price']) ?>원</p>
          <button onclick="addToCart(<?= (int)$book['id'] ?>)" class="mt-2 w-full py-1.5 bg-primary text-on-primary text-xs rounded font-medium hover:bg-primary-container">장바구니</button>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
