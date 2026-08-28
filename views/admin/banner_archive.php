<?php
/**
 * 관리자 — 이전 영카트 배너 / 기획전 아카이브 (보관함)
 */
$pageTitle = '이전 배너 / 기획전 아카이브';
$activeMenu = 'banners';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="flex flex-col gap-6" x-data="{ selectedTab: 'slider' }">

  <!-- 상단 네비게이션 & 안내 -->
  <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <a href="/admin/banners" class="text-xs text-gray-500 hover:text-gray-800">← 현재 운영 배너 관리</a>
        <span class="text-xs text-gray-300">/</span>
        <span class="text-xs font-bold text-blue-600">이전 배너 아카이브 (보관함)</span>
      </div>
      <h2 class="font-bold text-gray-900 text-lg">영카트 이전 배너 & 기획전 보관함</h2>
      <p class="text-xs text-gray-500 mt-1">
        기존 사이트에서 사용되었던 메인 슬라이더, 이벤트 포스터, 갤러리 이미지들을 한눈에 모아보고 신규 쇼핑몰 레이아웃에 맞춰 바로 등록하거나 재활용할 수 있습니다.
      </p>
    </div>

    <div class="flex gap-2">
      <button @click="selectedTab = 'slider'"
              :class="selectedTab === 'slider' ? 'bg-[#07131e] text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="px-4 py-2 rounded-lg text-xs transition-colors">
        이윰 슬라이더 (<?= count($legacySliders) ?>개)
      </button>
      <button @click="selectedTab = 'gallery'"
              :class="selectedTab === 'gallery' ? 'bg-[#07131e] text-white font-bold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="px-4 py-2 rounded-lg text-xs transition-colors">
        갤러리/이벤트 이미지 (<?= count($legacyGallery) ?>개)
      </button>
    </div>
  </div>

  <!-- 1. 이윰 메인 슬라이더 아카이브 -->
  <div x-show="selectedTab === 'slider'" class="flex flex-col gap-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php if (empty($legacySliders)): ?>
        <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-xl border">
          이전 슬라이더 배너 데이터가 없습니다.
        </div>
      <?php else: ?>
        <?php foreach ($legacySliders as $s):
          $imgUrl = str_starts_with($s['ei_img'], 'http') ? $s['ei_img'] : 'https://daejanggan.org/data/slider/' . $s['ei_img'];
        ?>
          <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div>
              <div class="relative aspect-video bg-gray-100 border-b border-gray-100 overflow-hidden group">
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($s['ei_title']) ?>"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     onerror="this.src='/assets/images/default_book.png'"/>
                <span class="absolute top-2 right-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded font-mono">
                  #<?= $s['ei_no'] ?>
                </span>
              </div>

              <div class="p-4 flex flex-col gap-1.5">
                <h4 class="font-bold text-gray-900 text-sm line-clamp-1"><?= htmlspecialchars($s['ei_title'] ?: '제목 없음') ?></h4>
                <?php if (!empty($s['ei_subtitle'])): ?>
                  <p class="text-xs text-gray-600 line-clamp-1"><?= htmlspecialchars($s['ei_subtitle']) ?></p>
                <?php endif; ?>
                <?php if (!empty($s['ei_text'])): ?>
                  <p class="text-[11px] text-gray-400 line-clamp-2 mt-0.5 leading-relaxed"><?= htmlspecialchars($s['ei_text']) ?></p>
                <?php endif; ?>

                <div class="mt-2 pt-2 border-t border-gray-100 text-[11px] text-gray-500 font-mono truncate">
                  링크: <a href="<?= htmlspecialchars($s['ei_link'] ?: '#') ?>" target="_blank" class="text-blue-600 hover:underline"><?= htmlspecialchars($s['ei_link'] ?: '없음') ?></a>
                </div>
              </div>
            </div>

            <div class="p-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
              <span class="text-[10px] text-gray-400 font-mono"><?= substr($s['ei_regdt'], 0, 10) ?></span>
              <a href="/admin/banners/create" class="px-3 py-1 bg-white border border-gray-300 rounded text-xs font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                새 배너로 사용하기 →
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- 2. 갤러리/이벤트 이미지 아카이브 -->
  <div x-show="selectedTab === 'gallery'" x-cloak class="flex flex-col gap-4">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <?php if (empty($legacyGallery)): ?>
        <div class="col-span-full py-12 text-center text-gray-400 bg-white rounded-xl border">
          게시판 이미지 데이터가 없습니다.
        </div>
      <?php else: ?>
        <?php foreach ($legacyGallery as $g):
          $imgUrl = 'https://daejanggan.org/data/file/' . $g['bo_table'] . '/' . $g['bf_file'];
        ?>
          <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="relative aspect-square bg-gray-100 overflow-hidden">
              <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($g['title']) ?>"
                   class="w-full h-full object-cover"
                   onerror="this.src='/assets/images/default_book.png'"/>
            </div>
            <div class="p-2.5">
              <span class="text-[10px] font-bold text-blue-600 uppercase"><?= htmlspecialchars($g['bo_table']) ?></span>
              <p class="text-xs font-medium text-gray-800 line-clamp-1 mt-0.5"><?= htmlspecialchars($g['title']) ?></p>
              <p class="text-[10px] text-gray-400 mt-1 font-mono truncate"><?= htmlspecialchars($g['bf_source']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

  </main>
</div>
</body>
</html>
