<?php
/**
 * 관리자 배너/기획전 관리 (위치별 1,2,3,4,6,7번 분류 및 권장 해상도 가이드)
 */
$pageTitle = '배너 / 기획전 관리';
$activeMenu = 'banners';
include APP_ROOT . '/views/layouts/admin_layout.php';

$posLabels = [
  'HERO_MAIN'          => ['name' => '2번: 메인 슬라이더', 'badge' => 'bg-purple-100 text-purple-700', 'size' => '1280 x 440px (또는 920 x 420px)'],
  'FLOAT_LEFT'         => ['name' => '1번: 좌측 플로팅 배너', 'badge' => 'bg-blue-100 text-blue-700', 'size' => '130 x 280px (스크롤 추적, 자동 리사이즈)'],
  'FLOAT_RIGHT_TOP'    => ['name' => '3번: 우측 상단 플로팅 배너', 'badge' => 'bg-emerald-100 text-emerald-700', 'size' => '130 x 280px (스크롤 추적, 자동 리사이즈)'],
  'FLOAT_RIGHT_BOTTOM' => ['name' => '4번: 우측 하단 플로팅 배너', 'badge' => 'bg-teal-100 text-teal-700', 'size' => '130 x 280px (스크롤 추적, 자동 리사이즈)'],
  'EVENT_GRID'         => ['name' => '6번: 이벤트/기획전 2열 배너', 'badge' => 'bg-amber-100 text-amber-700', 'size' => '580 x 160px'],
  'MIDDLE_WIDE'        => ['name' => '7번: 연재/알림 와이드 배너', 'badge' => 'bg-rose-100 text-rose-700', 'size' => '1200 x 260px'],
  'HERO'               => ['name' => '메인 슬라이더 (레거시)', 'badge' => 'bg-purple-100 text-purple-700', 'size' => '1280 x 440px'],
  'POSTER'             => ['name' => '포스터 배너 (레거시)', 'badge' => 'bg-amber-100 text-amber-700', 'size' => '580 x 160px'],
];
?>

<div class="flex flex-col gap-6" x-data="{ currentFilter: 'ALL' }">

  <!-- 상단 액션 & 위치 가이드 카드 -->
  <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
      <h2 class="font-bold text-gray-900 text-base">쇼핑몰 배너 & 기획전 위치별 관리</h2>
      <p class="text-xs text-gray-500 mt-1">
        중앙 메인 슬라이더(2번), 좌우 스크롤 추적 플로팅 배너(1,3,4번 - 130x280px, 업로드 시 자동 리사이징), 이벤트 기획전(6번), 연재 와이드 배너(7번)를 위치별로 관리합니다.
      </p>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <a href="/admin/banners/archive" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 flex items-center gap-1.5 shadow-sm">
        <span class="material-symbols-outlined text-base text-purple-600">collections_bookmark</span>
        이전 배너 보관함
      </a>
      <a href="/admin/banners/create" class="px-4 py-2.5 bg-[#07131e] text-white text-xs font-semibold rounded-lg hover:bg-[#1c2833] flex items-center gap-1.5 shadow-sm">
        <span class="material-symbols-outlined text-base">add_photo_alternate</span>
        + 새 배너 등록
      </a>
    </div>
  </div>

  <!-- 위치별 탭 필터 -->
  <div class="flex items-center gap-2 overflow-x-auto pb-1 hide-scrollbar">
    <button @click="currentFilter = 'ALL'"
            :class="currentFilter === 'ALL' ? 'bg-[#07131e] text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
            class="px-4 py-2 rounded-lg text-xs transition-colors shrink-0 shadow-sm">
      전체 배너 (<?= count($banners) ?>)
    </button>
    <button @click="currentFilter = 'HERO_MAIN'"
            :class="currentFilter === 'HERO_MAIN' ? 'bg-purple-600 text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
            class="px-4 py-2 rounded-lg text-xs transition-colors shrink-0 shadow-sm">
      2번: 메인 슬라이더 (1280x440)
    </button>
    <button @click="currentFilter = 'FLOATING'"
            :class="currentFilter === 'FLOATING' ? 'bg-blue-600 text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
            class="px-4 py-2 rounded-lg text-xs transition-colors shrink-0 shadow-sm">
      1,3,4번: 좌우 플로팅 (130x280)
    </button>
    <button @click="currentFilter = 'EVENT_GRID'"
            :class="currentFilter === 'EVENT_GRID' ? 'bg-amber-600 text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
            class="px-4 py-2 rounded-lg text-xs transition-colors shrink-0 shadow-sm">
      6번: 이벤트 기획전 (580x160)
    </button>
    <button @click="currentFilter = 'MIDDLE_WIDE'"
            :class="currentFilter === 'MIDDLE_WIDE' ? 'bg-rose-600 text-white font-bold' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
            class="px-4 py-2 rounded-lg text-xs transition-colors shrink-0 shadow-sm">
      7번: 연재 와이드 (1200x260)
    </button>
  </div>

  <!-- 배너 목록 테이블 -->
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left">
        <thead class="bg-gray-50 text-gray-500 uppercase border-b border-gray-100">
          <tr>
            <th class="px-4 py-3">배너 미리보기</th>
            <th class="px-4 py-3">노출 위치</th>
            <th class="px-4 py-3">제목 / 서브타이틀</th>
            <th class="px-4 py-3">연결 링크</th>
            <th class="px-4 py-3">권장 규격</th>
            <th class="px-4 py-3 text-center">정렬순서</th>
            <th class="px-4 py-3 text-center">노출상태</th>
            <th class="px-4 py-3 text-center">관리</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (empty($banners)): ?>
            <tr>
              <td colspan="8" class="py-12 text-center text-gray-400">등록된 배너가 없습니다. 새 배너를 등록해 보세요.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($banners as $b):
              $type = $b['banner_type'];
              $info = $posLabels[$type] ?? ['name' => $type, 'badge' => 'bg-gray-100 text-gray-700', 'size' => '자유'];
              $isFloating = in_array($type, ['FLOAT_LEFT', 'FLOAT_RIGHT_TOP', 'FLOAT_RIGHT_BOTTOM']);
            ?>
              <tr class="hover:bg-gray-50"
                  x-show="currentFilter === 'ALL' ||
                          (currentFilter === 'HERO_MAIN' && ('<?= $type ?>' === 'HERO_MAIN' || '<?= $type ?>' === 'HERO')) ||
                          (currentFilter === 'FLOATING' && <?= $isFloating ? 'true' : 'false' ?>) ||
                          (currentFilter === 'EVENT_GRID' && ('<?= $type ?>' === 'EVENT_GRID' || '<?= $type ?>' === 'POSTER' || '<?= $type ?>' === 'EVENT')) ||
                          (currentFilter === 'MIDDLE_WIDE' && '<?= $type ?>' === 'MIDDLE_WIDE')">
                <td class="px-4 py-3">
                  <img src="<?= htmlspecialchars($b['image_url']) ?>" alt="<?= htmlspecialchars($b['title']) ?>"
                       class="w-24 h-14 object-cover rounded-lg border border-gray-200 bg-gray-100"
                       onerror="this.src='/assets/images/default_book.png'"/>
                </td>
                <td class="px-4 py-3">
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $info['badge'] ?>">
                    <?= $info['name'] ?>
                  </span>
                  <?php if (!empty($b['badge_text'])): ?>
                    <span class="block text-[10px] text-gray-400 mt-1">태그: <?= htmlspecialchars($b['badge_text']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3 max-w-xs">
                  <div class="font-bold text-gray-900 text-sm line-clamp-1"><?= htmlspecialchars($b['title']) ?></div>
                  <?php if (!empty($b['subtitle'])): ?>
                    <div class="text-gray-500 text-xs line-clamp-1 mt-0.5"><?= htmlspecialchars($b['subtitle']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                  <a href="<?= htmlspecialchars($b['link_url']) ?>" target="_blank" class="text-blue-600 hover:underline max-w-[160px] truncate block font-mono text-[11px]">
                    <?= htmlspecialchars($b['link_url']) ?>
                  </a>
                </td>
                <td class="px-4 py-3 text-gray-500 text-[11px] font-mono">
                  <?= htmlspecialchars($b['size_memo'] ?: $info['size']) ?>
                </td>
                <td class="px-4 py-3 text-center font-bold text-gray-700"><?= (int)$b['sort_order'] ?></td>
                <td class="px-4 py-3 text-center">
                  <button onclick="toggleBanner(<?= (int)$b['id'] ?>)"
                          class="px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors
                                 <?= $b['is_active'] ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>">
                    <?= $b['is_active'] ? '● 노출중' : '○ 숨김' ?>
                  </button>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                  <a href="/admin/banners/<?= (int)$b['id'] ?>/edit" class="text-blue-600 hover:underline mr-2">수정</a>
                  <button onclick="deleteBanner(<?= (int)$b['id'] ?>)" class="text-red-600 hover:underline">삭제</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
function toggleBanner(id) {
  fetch(`/admin/banners/${id}/toggle`, {method: 'POST'})
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); });
}

function deleteBanner(id) {
  if (!confirm('정말 이 배너를 삭제하시겠습니까?')) return;
  fetch(`/admin/banners/${id}/delete`, {method: 'POST'})
    .then(r => r.json())
    .then(d => {
      if (d.success) location.reload();
      else alert('삭제 중 오류가 발생했습니다.');
    });
}
</script>

  </main>
</div>
</body>
</html>
