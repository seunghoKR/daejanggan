<?php
/**
 * 메인 홈 페이지 (300 x 650px 스크롤 추적 사이드 플로팅 배너 + 1280px 메인 슬라이더)
 * @var array $newBooks
 * @var array $recommendBooks
 * @var array $bestBooks
 * @var array $seriesList
 * @var array $heroBanners
 * @var array $floatLeftBanners
 * @var array $floatRightTopBanners
 * @var array $floatRightBottomBanners
 * @var array $eventGridBanners
 * @var array $middleWideBanners
 * @var int   $cartCount
 */
$pageTitle = '홈';
include APP_ROOT . '/views/layouts/header.php';
?>

<!-- ===================== [스크롤 추적] 좌/우 사이드 플로팅 배너 (130 x 280px, 순수 배너 이미지 단독 노출) ===================== -->

<!-- 1번: 좌측 플로팅 배너 (130 x 280px, 화면 좌측 외곽 고정 스크롤 추적) -->
<?php if (!empty($floatLeftBanners)): $fl = $floatLeftBanners[0]; ?>
  <aside class="hidden lg:block fixed top-32 z-30 transition-all pointer-events-auto"
         style="left: max(10px, calc(50% - 640px - 142px)); width: 130px;">
    <a href="<?= htmlspecialchars($fl['link_url'] ?: '#') ?>"
       class="group block w-[130px] rounded-xl overflow-hidden shadow-lg border border-outline-variant/80 bg-surface hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
      <img src="<?= htmlspecialchars($fl['image_url']) ?>" alt="<?= htmlspecialchars($fl['title']) ?>"
           class="w-full h-auto min-h-[280px] max-h-[570px] object-cover group-hover:scale-102 transition-transform duration-500 block"
           onerror="this.src='/assets/images/default_book.png'"/>
    </a>
  </aside>
<?php endif; ?>

<!-- 3번 & 4번: 우측 플로팅 배너 (130 x 280px 2개, 화면 우측 외곽 고정 스크롤 추적) -->
<?php if (!empty($floatRightTopBanners) || !empty($floatRightBottomBanners)): ?>
  <aside class="hidden lg:block fixed top-32 z-30 transition-all pointer-events-auto flex flex-col gap-2.5"
         style="right: max(10px, calc(50% - 640px - 142px)); width: 130px;">
    <?php if (!empty($floatRightTopBanners)): $frt = $floatRightTopBanners[0]; ?>
      <a href="<?= htmlspecialchars($frt['link_url'] ?: '/category/1050') ?>"
         class="group block w-[130px] h-[280px] rounded-xl overflow-hidden shadow-lg border border-outline-variant/80 bg-surface hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
        <img src="<?= htmlspecialchars($frt['image_url']) ?>" alt="<?= htmlspecialchars($frt['title']) ?>"
             class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500 block"
             onerror="this.src='/assets/images/default_book.png'"/>
      </a>
    <?php endif; ?>

    <?php if (!empty($floatRightBottomBanners)): $frb = $floatRightBottomBanners[0]; ?>
      <a href="<?= htmlspecialchars($frb['link_url'] ?: '/category/1060') ?>"
         class="group block w-[130px] h-[280px] rounded-xl overflow-hidden shadow-lg border border-outline-variant/80 bg-surface hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
        <img src="<?= htmlspecialchars($frb['image_url']) ?>" alt="<?= htmlspecialchars($frb['title']) ?>"
             class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-500 block"
             onerror="this.src='/assets/images/default_book.png'"/>
      </a>
    <?php endif; ?>
  </aside>
<?php endif; ?>

<main class="flex flex-col gap-12 md:gap-16 pb-16 relative">

  <!-- ===================== 2. 중앙 메인 슬라이더 (1280px 풀와이드 Hero Carousel) ===================== -->
  <section class="px-4 pt-4 max-w-7xl mx-auto w-full">
    <div class="w-full" x-data="{ currentSlide: 0, total: <?= count($heroBanners) ?> }">
      <?php if (!empty($heroBanners)): ?>
        <div class="relative bg-gradient-to-r from-[#07131e] via-[#1c2833] to-[#2c3e50] text-white rounded-3xl p-8 md:p-12 lg:p-16 overflow-hidden shadow-2xl min-h-[380px] md:min-h-[440px] flex items-center">
          <!-- 배경 데코 패턴 -->
          <div class="absolute -right-10 -bottom-10 w-96 h-96 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

          <!-- 슬라이드 아이템들 -->
          <?php foreach ($heroBanners as $idx => $banner): ?>
            <div x-show="currentSlide === <?= $idx ?>" x-cloak class="flex flex-col md:flex-row items-center justify-between gap-8 w-full z-10 transition-all duration-300">
              <div class="flex-1 text-center md:text-left">
                <?php if (!empty($banner['badge_text'])): ?>
                  <span class="inline-block bg-secondary text-white text-xs font-bold px-3.5 py-1.5 rounded-full mb-4 shadow-sm">
                    <?= htmlspecialchars($banner['badge_text']) ?>
                  </span>
                <?php endif; ?>
                <h1 class="font-serif text-2xl md:text-4xl lg:text-5xl font-bold leading-tight mb-4">
                  <?= htmlspecialchars($banner['title']) ?>
                </h1>
                <?php if (!empty($banner['subtitle'])): ?>
                  <p class="text-white/80 text-xs md:text-sm max-w-xl mb-8 leading-relaxed">
                    <?= htmlspecialchars($banner['subtitle']) ?>
                  </p>
                <?php endif; ?>
                <div class="flex items-center justify-center md:justify-start gap-3.5">
                  <a href="<?= htmlspecialchars($banner['link_url'] ?: '/books') ?>"
                     class="px-6 py-3 bg-secondary hover:bg-secondary/90 text-white rounded-xl text-xs font-semibold transition-all shadow-md">
                    자세히 보기 →
                  </a>
                  <a href="/books"
                     class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-medium border border-white/20 transition-all">
                    도서 전체보기
                  </a>
                </div>
              </div>

              <!-- 배너 이미지 (있는 경우) -->
              <?php if (!empty($banner['image_url']) && $banner['image_url'] !== DEFAULT_BOOK_IMG): ?>
                <div class="w-48 md:w-64 lg:w-80 shrink-0 shadow-2xl rounded-2xl overflow-hidden border border-white/20">
                  <img src="<?= htmlspecialchars($banner['image_url']) ?>" alt="<?= htmlspecialchars($banner['title']) ?>"
                       class="w-full h-auto max-h-80 object-cover"
                       onerror="this.src='/assets/images/default_book.png'"/>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <!-- 슬라이더 이전/다음 화살표 버튼 -->
          <?php if (count($heroBanners) > 1): ?>
            <button @click="currentSlide = (currentSlide === 0 ? total - 1 : currentSlide - 1)"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-all z-20">
              <span class="material-symbols-outlined text-base">chevron_left</span>
            </button>
            <button @click="currentSlide = (currentSlide === total - 1 ? 0 : currentSlide + 1)"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center transition-all z-20">
              <span class="material-symbols-outlined text-base">chevron_right</span>
            </button>

            <!-- 슬라이더 도트 인디케이터 -->
            <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2 z-20">
              <?php foreach ($heroBanners as $idx => $b): ?>
                <button @click="currentSlide = <?= $idx ?>"
                        :class="currentSlide === <?= $idx ?> ? 'bg-secondary w-6' : 'bg-white/40 w-2'"
                        class="h-2 rounded-full transition-all"></button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ===================== 3. 신간 도서 섹션 ===================== -->
  <?php if (!empty($newBooks)): ?>
  <section class="px-4 max-w-7xl mx-auto w-full">
    <div class="flex items-center justify-between mb-6 pb-2 border-b border-outline-variant/50">
      <div>
        <span class="text-xs font-semibold text-secondary uppercase tracking-wider">New Releases</span>
        <h2 class="font-serif text-2xl font-bold text-primary mt-0.5">신간 도서</h2>
      </div>
      <a href="/books?sort=new" class="text-xs text-secondary hover:underline flex items-center gap-0.5">
        전체보기 <span class="material-symbols-outlined text-sm">chevron_right</span>
      </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <?php foreach ($newBooks as $book): ?>
        <a href="/book/<?= htmlspecialchars($book['book_code']) ?>" class="book-card flex flex-col bg-surface rounded-xl border border-surface-variant overflow-hidden hover:shadow-lg transition-all group">
          <div class="relative aspect-[3/4] bg-surface-container overflow-hidden">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>"
                 class="book-cover w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 onerror="this.src='/assets/images/default_book.png'"/>
            <span class="absolute top-2 left-2 bg-secondary text-white text-[10px] px-2 py-0.5 rounded font-semibold shadow-sm">NEW</span>
          </div>
          <div class="p-3 flex flex-col gap-1">
            <h3 class="font-serif text-xs font-semibold text-primary line-clamp-2 leading-snug group-hover:text-secondary transition-colors"><?= htmlspecialchars($book['title']) ?></h3>
            <p class="text-[11px] text-on-surface-variant"><?= htmlspecialchars($book['author']) ?></p>
            <p class="text-xs font-bold text-primary mt-1"><?= number_format((int)$book['price']) ?>원</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== 6번: 이벤트 / 기획전 2열 분할 가로 배너 ===================== -->
  <?php if (!empty($eventGridBanners)): ?>
    <section class="px-4 max-w-7xl mx-auto w-full">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($eventGridBanners as $eb): ?>
          <a href="<?= htmlspecialchars($eb['link_url'] ?: '#') ?>"
             class="group relative rounded-2xl overflow-hidden p-6 md:p-7 flex items-center justify-between bg-surface-container border border-outline-variant/60 hover:shadow-lg transition-all">
            <div class="flex-1 pr-4">
              <?php if (!empty($eb['badge_text'])): ?>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-secondary text-white mb-2 shadow-sm">
                  <?= htmlspecialchars($eb['badge_text']) ?>
                </span>
              <?php endif; ?>
              <h3 class="font-serif text-lg md:text-xl font-bold text-primary group-hover:text-secondary transition-colors">
                <?= htmlspecialchars($eb['title']) ?>
              </h3>
              <?php if (!empty($eb['subtitle'])): ?>
                <p class="text-xs text-on-surface-variant mt-1.5 line-clamp-2 leading-relaxed"><?= htmlspecialchars($eb['subtitle']) ?></p>
              <?php endif; ?>
              <span class="inline-flex items-center gap-1 text-xs font-semibold text-secondary mt-3 group-hover:translate-x-1 transition-transform">
                기획전 바로가기 <span class="material-symbols-outlined text-sm">arrow_forward</span>
              </span>
            </div>
            <?php if (!empty($eb['image_url']) && $eb['image_url'] !== DEFAULT_BOOK_IMG): ?>
              <img src="<?= htmlspecialchars($eb['image_url']) ?>" alt="<?= htmlspecialchars($eb['title']) ?>"
                   class="w-24 h-24 md:w-28 md:h-28 object-cover rounded-xl shrink-0 shadow-md"
                   onerror="this.src='/assets/images/default_book.png'"/>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- ===================== 4. 대장간 추천 도서 ===================== -->
  <?php if (!empty($recommendBooks)): ?>
  <section class="px-4 max-w-7xl mx-auto w-full">
    <div class="flex items-center justify-between mb-6 pb-2 border-b border-outline-variant/50">
      <div>
        <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Curation</span>
        <h2 class="font-serif text-2xl font-bold text-primary mt-0.5">대장간 추천 도서</h2>
      </div>
      <a href="/books?sort=popular" class="text-xs text-secondary hover:underline">전체보기 →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <?php foreach ($recommendBooks as $book): ?>
        <a href="/book/<?= htmlspecialchars($book['book_code']) ?>" class="book-card flex flex-col bg-surface rounded-xl border border-surface-variant overflow-hidden hover:shadow-lg transition-all group">
          <div class="relative aspect-[3/4] bg-surface-container overflow-hidden">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>"
                 class="book-cover w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 onerror="this.src='/assets/images/default_book.png'"/>
          </div>
          <div class="p-3 flex flex-col gap-1">
            <h3 class="font-serif text-xs font-semibold text-primary line-clamp-2 leading-snug group-hover:text-secondary transition-colors"><?= htmlspecialchars($book['title']) ?></h3>
            <p class="text-[11px] text-on-surface-variant"><?= htmlspecialchars($book['author']) ?></p>
            <p class="text-xs font-bold text-primary mt-1"><?= number_format((int)$book['price']) ?>원</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== 7번: 출판사 연재 / 알림 와이드 배너 (글 먹는 시간 등) ===================== -->
  <?php if (!empty($middleWideBanners)): $wb = $middleWideBanners[0]; ?>
    <section class="px-4 max-w-7xl mx-auto w-full">
      <a href="<?= htmlspecialchars($wb['link_url'] ?: '#') ?>"
         class="group relative block rounded-2xl overflow-hidden shadow-lg border border-outline-variant/60 bg-[#1c2833] text-white">
        <?php if (!empty($wb['image_url']) && $wb['image_url'] !== DEFAULT_BOOK_IMG): ?>
          <img src="<?= htmlspecialchars($wb['image_url']) ?>" alt="<?= htmlspecialchars($wb['title']) ?>"
               class="w-full h-44 md:h-60 object-cover group-hover:scale-102 transition-transform duration-500 opacity-80"
               onerror="this.style.display='none'"/>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent p-6 md:p-10 flex flex-col justify-center">
          <?php if (!empty($wb['badge_text'])): ?>
            <span class="inline-block bg-amber-500 text-black text-xs font-bold px-3 py-1 rounded-full mb-2 w-max shadow-sm">
              <?= htmlspecialchars($wb['badge_text']) ?>
            </span>
          <?php endif; ?>
          <h2 class="font-serif text-xl md:text-3xl font-bold leading-tight group-hover:text-secondary transition-colors">
            <?= htmlspecialchars($wb['title']) ?>
          </h2>
          <?php if (!empty($wb['subtitle'])): ?>
            <p class="text-xs md:text-sm text-gray-200 mt-2 max-w-lg leading-relaxed"><?= htmlspecialchars($wb['subtitle']) ?></p>
          <?php endif; ?>
          <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-300 mt-4 group-hover:underline">
            연재글 보러가기 →
          </span>
        </div>
      </a>
    </section>
  <?php endif; ?>

  <!-- ===================== 5. 대장간 대표 시리즈 ===================== -->
  <?php if (!empty($seriesList)): ?>
  <section class="px-4 max-w-7xl mx-auto w-full">
    <div class="flex items-center justify-between mb-6 pb-2 border-b border-outline-variant/50">
      <div>
        <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Series</span>
        <h2 class="font-serif text-2xl font-bold text-primary mt-0.5">대장간 대표 시리즈</h2>
      </div>
      <a href="/category/1030" class="text-xs text-secondary hover:underline flex items-center gap-0.5">
        시리즈 전체보기 <span class="material-symbols-outlined text-sm">chevron_right</span>
      </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <?php foreach ($seriesList as $series): ?>
        <a href="/series/<?= (int)$series['id'] ?>"
           class="group bg-surface rounded-xl p-5 border border-surface-variant hover:border-outline-variant hover:shadow-md transition-all flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs text-tertiary font-semibold uppercase tracking-wider">총서</span>
              <span class="text-xs bg-surface-container text-on-surface-variant px-2 py-0.5 rounded-full font-medium">
                <?= (int)$series['book_count'] ?>권
              </span>
            </div>
            <h3 class="font-serif text-base font-bold text-primary group-hover:text-secondary transition-colors">
              <?= htmlspecialchars($series['name']) ?>
            </h3>
            <?php if ($series['description']): ?>
              <p class="text-xs text-on-surface-variant mt-1.5 line-clamp-2 leading-relaxed">
                <?= htmlspecialchars($series['description']) ?>
              </p>
            <?php endif; ?>
          </div>
          <span class="text-xs font-semibold text-primary group-hover:text-secondary mt-4 flex items-center gap-1">
            시리즈 도서 보기 →
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== 6. 베스트셀러 ===================== -->
  <?php if (!empty($bestBooks)): ?>
  <section class="px-4 max-w-7xl mx-auto w-full">
    <div class="flex items-center justify-between mb-6 pb-2 border-b border-outline-variant/50">
      <div>
        <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Bestseller</span>
        <h2 class="font-serif text-2xl font-bold text-primary mt-0.5">베스트셀러</h2>
      </div>
      <a href="/books?sort=popular" class="text-xs text-secondary hover:underline">더 보기 →</a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
      <?php foreach ($bestBooks as $i => $book): ?>
        <div class="flex gap-3 items-start bg-surface rounded-xl p-4 border border-surface-variant hover:shadow-md transition-shadow">
          <span class="text-3xl font-bold text-outline-variant shrink-0"><?= $i + 1 ?></span>
          <a href="/book/<?= htmlspecialchars($book['book_code']) ?>" class="flex gap-3 flex-1 min-w-0">
            <img src="<?= htmlspecialchars($book['cover_image']) ?>"
                 alt="<?= htmlspecialchars($book['title']) ?>"
                 class="w-14 h-20 object-cover rounded flex-shrink-0 shadow-sm"
                 onerror="this.src='/assets/images/default_book.png'"/>
            <div class="min-w-0">
              <h4 class="font-serif text-xs font-semibold text-primary line-clamp-2 leading-snug">
                <?= htmlspecialchars($book['title']) ?>
              </h4>
              <p class="text-[11px] text-on-surface-variant mt-1"><?= htmlspecialchars($book['author']) ?></p>
              <p class="text-xs font-bold text-primary mt-1"><?= number_format((int)$book['price']) ?>원</p>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
