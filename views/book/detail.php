<?php
/**
 * 도서 상세 페이지 (Stitch: 도서상세정보 기반)
 * @var array $book
 * @var array $reviews
 * @var float $avgRating
 * @var array $relatedBooks
 * @var bool  $isWishlisted
 * @var int   $cartCount
 */
$pageTitle = $book['title'] ?? '도서 상세';
$ogImage   = !empty($book['cover_image']) ? 'http://ndaejanggan.iwinv.net' . $book['cover_image'] : 'http://ndaejanggan.iwinv.net/assets/images/logo.png';
$ogDescription = !empty($book['summary']) ? mb_substr(strip_tags($book['summary']), 0, 120) . '...' : (!empty($book['description']) ? mb_substr(strip_tags($book['description']), 0, 120) . '...' : '도서출판 대장간 도서 안내');
include APP_ROOT . '/views/layouts/header.php';

$site       = $GLOBALS['site'] ?? [];
$freeMin    = (int)($site['free_shipping_min'] ?? 30000);
$shippingFee= (int)($site['shipping_fee'] ?? 3000);
$pointRate  = (int)($site['point_rate'] ?? 5);
$earnPoint  = (int)($book['price'] * $pointRate / 100);
$isLogin    = Auth::check();
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">

<?php
$galleryImages = [];
if (!empty($book['detail_images'])) {
    $decoded = json_decode($book['detail_images'], true);
    if (is_array($decoded) && count($decoded) > 0) {
        $galleryImages = $decoded;
    }
}
if (empty($galleryImages)) {
    $galleryImages = [$book['cover_image']];
}
$mainCover = $book['cover_image'] ?: $galleryImages[0];
?>

  <!-- ===== 도서 기본 정보 (1280px 와이드 그리드 완벽 정렬) ===== -->
  <div class="flex flex-col lg:flex-row gap-10 lg:gap-14" x-data="{ currentImg: '<?= htmlspecialchars($mainCover) ?>' }">

    <!-- 표지 및 속지 갤러리 이미지 영역 (좌측 고정 프레임) -->
    <div class="w-full lg:w-[400px] shrink-0 flex flex-col items-center">
      <!-- 메인 뷰어: 고정 높이 480px 프레임으로 어떤 이미지 비율이든 레이아웃 불변 유지 -->
      <div class="w-full max-w-[380px] h-[440px] md:h-[480px] flex items-center justify-center rounded-2xl overflow-hidden border border-outline-variant/60 bg-surface-container-low p-4 shadow-sm relative">
        <img
          :src="currentImg"
          src="<?= htmlspecialchars($mainCover) ?>"
          alt="<?= htmlspecialchars($book['title']) ?>"
          class="w-full h-full object-contain transition-all duration-200 drop-shadow-md select-none"
          onerror="this.src='/assets/images/default_book.png'"
        />
      </div>

      <!-- 속지/추가 이미지 썸네일 갤러리 (2장 이상일 때 노출) -->
      <?php if (count($galleryImages) > 1): ?>
        <div class="flex flex-wrap items-center justify-center gap-2 mt-4 max-w-[380px]">
          <?php foreach ($galleryImages as $idx => $gImg): ?>
            <button
              type="button"
              @click="currentImg = '<?= htmlspecialchars($gImg) ?>'"
              :class="currentImg === '<?= htmlspecialchars($gImg) ?>' ? 'ring-2 ring-secondary shadow-md scale-105 border-secondary' : 'opacity-70 hover:opacity-100 border-outline-variant/60'"
              class="w-14 h-18 rounded-lg overflow-hidden border bg-surface p-1 transition-all cursor-pointer flex items-center justify-center">
              <img src="<?= htmlspecialchars($gImg) ?>" alt="미리보기 <?= $idx + 1 ?>" class="w-full h-full object-contain rounded" />
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- 위시리스트 버튼 -->
      <?php if ($isLogin): ?>
        <button
          id="wishBtn"
          onclick="toggleWishlist(<?= (int)$book['id'] ?>)"
          class="mt-3 flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-secondary transition-colors">
          <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' <?= $isWishlisted ? '1' : '0' ?>">favorite</span>
          <span><?= $isWishlisted ? '찜 취소' : '찜하기' ?></span>
        </button>
      <?php endif; ?>
    </div>

    <!-- 도서 정보 -->
    <div class="flex-1 flex flex-col gap-3">
      <!-- 카테고리/시리즈 태그 -->
      <div class="flex flex-wrap gap-2">
        <?php if ($book['category_name'] ?? ''): ?>
          <span class="text-xs bg-surface-container text-on-surface-variant px-3 py-1 rounded-full border border-outline-variant">
            <?= htmlspecialchars($book['category_name']) ?>
          </span>
        <?php endif; ?>
        <?php if ($book['series_name'] ?? ''): ?>
          <a href="/series/<?= (int)$book['series_id'] ?>"
             class="text-xs bg-tertiary-container text-on-tertiary-container px-3 py-1 rounded-full hover:opacity-80 transition-opacity">
            <?= htmlspecialchars($book['series_name']) ?> 시리즈
          </a>
        <?php endif; ?>
        <?php if ($book['is_new']): ?>
          <span class="text-xs bg-secondary text-white px-3 py-1 rounded-full font-semibold">NEW</span>
        <?php endif; ?>
      </div>

      <!-- 제목 -->
      <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary leading-tight">
        <?= htmlspecialchars($book['title']) ?>
      </h1>
      <?php if ($book['subtitle']): ?>
        <p class="text-on-surface-variant text-base"><?= htmlspecialchars($book['subtitle']) ?></p>
      <?php endif; ?>

      <!-- 인용구 스타일 한줄 소개 -->
      <?php if (!empty($book['summary'])): ?>
        <div class="border-l-2 border-secondary pl-4 py-1 my-1">
          <p class="font-serif text-base text-primary italic leading-relaxed line-clamp-3">
            "<?= htmlspecialchars(mb_substr(strip_tags($book['summary']), 0, 120)) ?>"
          </p>
        </div>
      <?php endif; ?>

      <!-- 저자/출판 정보 -->
      <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-sm border-t border-outline-variant pt-3 mt-1">
        <div class="text-on-surface-variant">저자</div>
        <div class="text-on-surface font-medium">
          <a href="/author/<?= urlencode($book['author']) ?>" class="text-primary hover:text-secondary hover:underline transition-colors">
            <?= htmlspecialchars($book['author']) ?>
          </a>
        </div>

        <?php if ($book['translator']): ?>
          <div class="text-on-surface-variant">역자</div>
          <div class="text-on-surface"><?= htmlspecialchars($book['translator']) ?></div>
        <?php endif; ?>

        <div class="text-on-surface-variant">출판사</div>
        <div class="text-on-surface"><?= htmlspecialchars($book['publisher'] ?? '대장간') ?></div>

        <?php if ($book['publish_date']): ?>
          <div class="text-on-surface-variant">발행일</div>
          <div class="text-on-surface"><?= htmlspecialchars(date('Y년 m월 d일', strtotime($book['publish_date']))) ?></div>
        <?php endif; ?>

        <?php if ($book['isbn']): ?>
          <div class="text-on-surface-variant">ISBN</div>
          <div class="text-on-surface text-xs"><?= htmlspecialchars($book['isbn']) ?></div>
        <?php endif; ?>
      </div>

      <!-- 가격 -->
      <div class="flex items-baseline gap-3 mt-2">
        <span class="text-3xl font-bold text-primary"><?= number_format((int)$book['price']) ?>원</span>
        <?php if ($book['original_price'] > $book['price']): ?>
          <span class="text-base text-on-surface-variant line-through"><?= number_format((int)$book['original_price']) ?>원</span>
          <span class="text-sm text-secondary font-semibold">
            <?= round((1 - $book['price'] / $book['original_price']) * 100) ?>% 할인
          </span>
        <?php endif; ?>
      </div>

      <!-- 배송/적립 안내 -->
      <div class="flex flex-col gap-1 text-xs text-on-surface-variant">
        <div class="flex items-center gap-1">
          <span class="material-symbols-outlined text-sm text-primary">local_shipping</span>
          배송비 <?= number_format($shippingFee) ?>원
          (<?= number_format($freeMin) ?>원 이상 무료)
        </div>
        <?php if ($earnPoint > 0): ?>
          <div class="flex items-center gap-1">
            <span class="material-symbols-outlined text-sm text-tertiary">savings</span>
            구매 시 <?= number_format($earnPoint) ?>p 적립
          </div>
        <?php endif; ?>
      </div>

      <!-- 품절 여부 -->
      <?php if ($book['status'] === 'SOLDOUT'): ?>
        <div class="p-3 bg-error-container text-error text-sm rounded-lg font-medium text-center">품절</div>
      <?php else: ?>
        <!-- 구매 버튼 -->
        <div class="flex gap-3 mt-3">
          <button
            onclick="addToCart(<?= (int)$book['id'] ?>)"
            class="flex-1 py-3.5 bg-primary text-on-primary rounded-xl font-bold text-sm hover:bg-primary-container transition-colors shadow-md">
            장바구니 담기
          </button>
          <a href="/cart"
             onclick="addToCartRedirect(<?= (int)$book['id'] ?>); return false;"
             class="flex-1 py-3.5 bg-secondary text-on-secondary rounded-xl font-bold text-sm hover:opacity-90 transition-opacity text-center shadow-md">
            바로 구매
          </a>
        </div>
      <?php endif; ?>

      <!-- 📤 도서 SNS 퍼가기 & 공유하기 바 -->
      <div class="mt-4 pt-4 border-t border-outline-variant/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <span class="text-xs text-gray-600 font-semibold flex items-center gap-1.5 shrink-0">
          <span class="material-symbols-outlined text-base text-secondary">share</span>
          이 책 공유하기
        </span>
        <div class="flex flex-wrap items-center gap-2">
          <!-- 카카오톡 (공식 노란색 말풍선) -->
          <button type="button" onclick="shareSNS('kakao', '<?= addslashes($book['title']) ?>', null, '<?= $ogImage ?>', '<?= addslashes($ogDescription) ?>')"
                  class="w-8 h-8 rounded-full bg-[#FEE500] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="카카오톡 공유">
            <svg class="w-4 h-4 text-[#191919] fill-current" viewBox="0 0 24 24">
              <path d="M12 3c-5.523 0-10 3.582-10 8 0 2.85 1.867 5.347 4.688 6.72-.206.76-.745 2.756-.853 3.18-.135.534.195.526.41.383.17-.113 2.705-1.84 3.79-2.58.643.093 1.303.143 1.965.143 5.523 0 10-3.582 10-8s-4.477-8-10-8z"/>
            </svg>
          </button>

          <!-- 페이스북 (공식 F 로고) -->
          <button type="button" onclick="shareSNS('facebook', '<?= addslashes($book['title']) ?>')"
                  class="w-8 h-8 rounded-full bg-[#1877F2] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="페이스북 공유">
            <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </button>

          <!-- X (공식 𝕏 로고) -->
          <button type="button" onclick="shareSNS('x', '<?= addslashes($book['title']) ?> - 도서출판 대장간')"
                  class="w-8 h-8 rounded-full bg-[#000000] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="X (트위터) 공유">
            <svg class="w-3.5 h-3.5 text-white fill-current" viewBox="0 0 24 24">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
          </button>

          <!-- 네이버 블로그/카페 (공식 N 로고) -->
          <button type="button" onclick="shareSNS('naver', '<?= addslashes($book['title']) ?> - 도서출판 대장간')"
                  class="w-8 h-8 rounded-full bg-[#03C75A] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="네이버 공유">
            <svg class="w-3.5 h-3.5 text-white fill-current" viewBox="0 0 24 24">
              <path d="M16.273 12.845L7.376 0H0v24h7.727V11.155L16.624 24H24V0h-7.727z"/>
            </svg>
          </button>

          <!-- 네이버 밴드 (공식 밴드 로고) -->
          <button type="button" onclick="shareSNS('band', '<?= addslashes($book['title']) ?> - 도서출판 대장간')"
                  class="w-8 h-8 rounded-full bg-[#00D362] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="네이버 밴드 공유">
            <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2.5c-.28 0-.5.22-.5.5V12h3l-.5 3h-2v6.8c4.56-.93 8-4.96 8-9.8 0-5.52-4.48-10-10-10z"/>
            </svg>
          </button>

          <!-- 텔레그램 (공식 종이비행기 로고) -->
          <button type="button" onclick="shareSNS('telegram', '<?= addslashes($book['title']) ?> - 도서출판 대장간')"
                  class="w-8 h-8 rounded-full bg-[#24A1DE] flex items-center justify-center shadow-sm hover:scale-110 transition-transform" title="텔레그램 공유">
            <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24">
              <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
          </button>

          <!-- 링크 복사 -->
          <button type="button" onclick="shareSNS('copy')"
                  class="h-8 px-3 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center gap-1 text-xs font-semibold shadow-sm hover:scale-105 transition-transform" title="도서 링크 복사">
            <span class="material-symbols-outlined text-sm">link</span>
            <span>복사</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== 탭 콘텐츠 ===== -->
  <div x-data="{ tab: 'intro' }" class="mt-10">

    <!-- 탭 헤더 -->
    <div class="flex border-b border-outline-variant overflow-x-auto hide-scrollbar">
      <?php
      $tabs = [
        'intro'   => '책 소개 &amp; 목차',
        'author'  => '저자/역자 소개',
        'review'  => '독자 리뷰 (' . count($reviews) . ')',
        'return'  => '교환/반품',
      ];
      foreach ($tabs as $key => $label): ?>
        <button
          @click="tab = '<?= $key ?>'"
          :class="tab === '<?= $key ?>' ? 'border-b-2 border-secondary text-secondary font-semibold' : 'text-on-surface-variant'"
          class="px-5 py-3 text-sm whitespace-nowrap transition-colors hover:text-primary">
          <?= $label ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- 책 소개 -->
    <div x-show="tab === 'intro'" class="py-6">
      <?php if (!empty($book['description'])): ?>
        <div class="prose prose-sm max-w-none text-on-surface leading-relaxed">
          <?= $book['description'] /* HTML이므로 escape 안 함 */ ?>
        </div>
      <?php elseif (!empty($book['summary'])): ?>
        <p class="text-on-surface leading-relaxed whitespace-pre-line">
          <?= htmlspecialchars($book['summary']) ?>
        </p>
      <?php else: ?>
        <p class="text-on-surface-variant text-sm">도서 소개 정보가 없습니다.</p>
      <?php endif; ?>
    </div>

    <!-- 저자/역자 소개 -->
    <div x-show="tab === 'author'" class="py-6">
      <div class="bg-surface-container-low rounded-xl p-6">
        <h3 class="font-serif text-lg font-bold text-primary mb-2">저자: <?= htmlspecialchars($book['author']) ?></h3>
        <p class="text-on-surface-variant text-sm">저자 소개 정보를 준비 중입니다.</p>
      </div>
    </div>

    <!-- 독자 리뷰 -->
    <div x-show="tab === 'review'" class="py-6">
      <!-- 평점 요약 -->
      <?php if (!empty($reviews)): ?>
        <div class="flex items-center gap-4 mb-6 p-4 bg-surface-container rounded-xl">
          <div class="text-center">
            <div class="text-4xl font-bold text-primary"><?= number_format($avgRating, 1) ?></div>
            <div class="flex gap-0.5 mt-1 justify-center">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="material-symbols-outlined text-sm <?= $i <= round($avgRating) ? 'text-tertiary' : 'text-outline-variant' ?>"
                      style="font-variation-settings:'FILL' <?= $i <= round($avgRating) ? '1' : '0' ?>">star</span>
              <?php endfor; ?>
            </div>
            <div class="text-xs text-on-surface-variant mt-0.5"><?= count($reviews) ?>개 리뷰</div>
          </div>
        </div>

        <!-- 리뷰 목록 -->
        <div class="flex flex-col gap-4">
          <?php foreach ($reviews as $rev): ?>
            <div class="border-b border-outline-variant pb-4">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <span class="font-medium text-sm text-on-surface"><?= htmlspecialchars($rev['reviewer_name']) ?></span>
                  <div class="flex gap-0.5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <span class="material-symbols-outlined text-xs <?= $i <= $rev['rating'] ? 'text-tertiary' : 'text-outline-variant' ?>"
                            style="font-variation-settings:'FILL' <?= $i <= $rev['rating'] ? '1' : '0' ?>">star</span>
                    <?php endfor; ?>
                  </div>
                </div>
                <span class="text-xs text-on-surface-variant"><?= date('Y.m.d', strtotime($rev['created_at'])) ?></span>
              </div>
              <h4 class="font-serif font-semibold text-primary text-sm mb-1"><?= htmlspecialchars($rev['title']) ?></h4>
              <p class="text-sm text-on-surface leading-relaxed"><?= nl2br(htmlspecialchars($rev['content'])) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-on-surface-variant text-sm py-4">아직 리뷰가 없습니다. 첫 번째 리뷰를 남겨보세요!</p>
      <?php endif; ?>

      <!-- 리뷰 작성 폼 -->
      <?php if ($isLogin): ?>
        <div class="mt-6 p-5 bg-surface-container-low rounded-xl">
          <h4 class="font-serif font-bold text-primary mb-4">서평 작성</h4>
          <form action="/book/<?= (int)$book['id'] ?>/review" method="POST" class="flex flex-col gap-3">
            <!-- 별점 -->
            <div class="flex items-center gap-2">
              <label class="text-sm text-on-surface-variant">별점</label>
              <div class="flex gap-1" x-data="{ rating: 5 }">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <button type="button" @click="rating = <?= $i ?>"
                          class="material-symbols-outlined text-xl transition-colors"
                          :style="`font-variation-settings:'FILL' ${rating >= <?= $i ?> ? 1 : 0}; color: ${rating >= <?= $i ?> ? '#735c00' : '#c4c6cb'}`">star</button>
                <?php endfor; ?>
                <input type="hidden" name="rating" x-bind:value="rating"/>
              </div>
            </div>
            <input type="text" name="title" required placeholder="리뷰 제목"
              class="border border-outline-variant rounded-lg px-4 py-2.5 text-sm bg-surface focus:ring-1 focus:ring-primary outline-none"/>
            <textarea name="content" required rows="4" placeholder="도서에 대한 솔직한 리뷰를 남겨주세요."
              class="border border-outline-variant rounded-lg px-4 py-2.5 text-sm bg-surface focus:ring-1 focus:ring-primary outline-none resize-none"></textarea>
            <button type="submit" class="py-2.5 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
              서평 등록
            </button>
          </form>
        </div>
      <?php else: ?>
        <div class="mt-4 text-center py-4">
          <a href="/login" class="text-sm text-secondary hover:underline">로그인 후 서평을 남길 수 있습니다.</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- 교환/반품 -->
    <div x-show="tab === 'return'" class="py-6">
      <div class="prose prose-sm text-on-surface-variant">
        <h4 class="text-on-surface font-semibold mb-3">교환 및 반품 안내</h4>
        <ul class="space-y-2 text-sm">
          <li>도서 수령 후 7일 이내 교환/반품 신청 가능합니다.</li>
          <li>파손, 오류, 오배송의 경우 배송비 무료로 교환 처리됩니다.</li>
          <li>단순 변심의 경우 왕복 배송비는 고객 부담입니다.</li>
          <li>고객센터: <?= htmlspecialchars($site['cs_phone'] ?? '041-742-1424') ?> / <?= htmlspecialchars($site['email'] ?? 'jlife@daejanggan.org') ?></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- ===== 같은 시리즈 도서 ===== -->
  <?php if (!empty($relatedBooks)): ?>
  <section class="mt-12">
    <h3 class="font-serif text-xl font-bold text-primary mb-4">같은 시리즈 도서</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <?php foreach ($relatedBooks as $rb): ?>
        <a href="/book/<?= htmlspecialchars($rb['book_code']) ?>"
           class="flex flex-col bg-surface rounded-xl border border-surface-variant overflow-hidden hover:shadow-md transition-shadow">
          <div class="aspect-[3/4] bg-surface-container overflow-hidden">
            <img src="<?= htmlspecialchars($rb['cover_image']) ?>"
                 alt="<?= htmlspecialchars($rb['title']) ?>"
                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                 onerror="this.src='/assets/images/default_book.png'"/>
          </div>
          <div class="p-3">
            <h4 class="font-serif text-sm font-semibold text-primary line-clamp-2"><?= htmlspecialchars($rb['title']) ?></h4>
            <p class="text-sm font-bold text-primary mt-1"><?= number_format((int)$rb['price']) ?>원</p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</main>

<!-- 모바일 하단 고정 구매 버튼 -->
<?php if ($book['status'] !== 'SOLDOUT'): ?>
<div class="fixed bottom-16 md:hidden w-full left-0 px-4 pb-3 bg-gradient-to-t from-surface via-surface pt-4 z-40">
  <div class="flex gap-3">
    <button onclick="addToCart(<?= (int)$book['id'] ?>)"
      class="flex-1 py-3.5 bg-primary text-on-primary rounded-lg font-semibold text-sm shadow-lg">
      장바구니
    </button>
    <a href="/cart" onclick="addToCartRedirect(<?= (int)$book['id'] ?>); return false;"
      class="flex-1 py-3.5 bg-secondary text-on-secondary rounded-lg font-semibold text-sm text-center shadow-lg">
      바로 구매
    </a>
  </div>
</div>
<?php endif; ?>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

<script>
function addToCart(bookId, redirect = false) {
  fetch('/cart/add', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `book_id=${bookId}&qty=1`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      if (redirect) {
        window.location.href = '/cart';
      } else {
        alert('장바구니에 담았습니다!');
      }
    } else {
      alert(data.error || '오류가 발생했습니다.');
    }
  });
}

function addToCartRedirect(bookId) {
  addToCart(bookId, true);
}

function toggleWishlist(bookId) {
  fetch('/mypage/wishlist/add', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `book_id=${bookId}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.error === 'login_required') {
      window.location.href = '/login';
      return;
    }
    const btn  = document.getElementById('wishBtn');
    const icon = btn.querySelector('.material-symbols-outlined');
    const span = btn.querySelector('span:not(.material-symbols-outlined)');
    if (data.action === 'added') {
      icon.style.fontVariationSettings = "'FILL' 1";
      span.textContent = '찜 취소';
    } else {
      icon.style.fontVariationSettings = "'FILL' 0";
      span.textContent = '찜하기';
    }
  });
}
</script>
