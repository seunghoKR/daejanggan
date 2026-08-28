<?php
/**
 * 시리즈별 도서 목록
 */
$pageTitle = $series['name'] . ' 시리즈';
include APP_ROOT . '/views/layouts/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8">
  <div class="mb-8 p-6 bg-surface-container rounded-2xl border border-surface-variant">
    <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Series</span>
    <h1 class="font-serif text-3xl font-bold text-primary mt-1 mb-3"><?= htmlspecialchars($series['name']) ?></h1>
    <?php if (!empty($series['description'])): ?>
      <p class="text-sm text-on-surface-variant leading-relaxed max-w-3xl"><?= nl2br(htmlspecialchars($series['description'])) ?></p>
    <?php endif; ?>
  </div>

  <?php if (empty($books)): ?>
    <div class="py-16 text-center text-on-surface-variant">해당 시리즈에 등록된 도서가 없습니다.</div>
  <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <?php foreach ($books as $book): ?>
        <a href="/book/<?= htmlspecialchars($book['book_code']) ?>"
           class="book-card flex flex-col bg-surface rounded-xl border border-surface-variant overflow-hidden hover:shadow-lg transition-all duration-200">
          <div class="relative aspect-[3/4] bg-surface-container overflow-hidden">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>"
                 alt="<?= htmlspecialchars($book['title']) ?>"
                 class="book-cover w-full h-full object-cover"
                 onerror="this.src='/assets/images/default_book.png'"/>
          </div>
          <div class="p-3 flex flex-col gap-1">
            <h3 class="font-serif text-sm font-semibold text-primary line-clamp-2 leading-snug"><?= htmlspecialchars($book['title']) ?></h3>
            <p class="text-xs text-on-surface-variant"><?= htmlspecialchars($book['author']) ?></p>
            <p class="text-sm font-bold text-primary mt-1"><?= number_format((int)$book['price']) ?>원</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
