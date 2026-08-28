<?php
/**
 * 관리자 배너 등록 / 수정 폼 (위치별 1,2,3,4,6,7번 선택 및 권장 해상도 가이드)
 */
$isEdit = isset($banner);
$pageTitle = $isEdit ? '배너 수정' : '새 배너 등록';
$activeMenu = 'banners';
include APP_ROOT . '/views/layouts/admin_layout.php';

$curType = $banner['banner_type'] ?? 'HERO_MAIN';
if ($curType === 'HERO') $curType = 'HERO_MAIN';
if ($curType === 'POSTER' || $curType === 'EVENT') $curType = 'EVENT_GRID';
?>

<div class="max-w-3xl bg-white rounded-xl border border-gray-200 p-6 shadow-sm" x-data="{
  pos: '<?= htmlspecialchars($curType) ?>',
  guideMap: {
    'HERO_MAIN': { size: '1280 x 440px (또는 920 x 420px)', desc: '메인 상단 중앙의 1280px 풀와이드 대표 슬라이더 배너입니다 (자동 리사이징 최적화 지원).' },
    'FLOAT_LEFT': { size: '130 x 280px (고화질 원본 260x560 이상 권장)', desc: '1번 위치: 화면 좌측 외곽에서 스크롤 시 따라다니는 플로팅 배너입니다 (업로드 시 비율 유지 자동 리사이징).' },
    'FLOAT_RIGHT_TOP': { size: '130 x 280px (고화질 원본 260x560 이상 권장)', desc: '3번 위치: 화면 우측 상단에서 스크롤 시 따라다니는 플로팅 배너입니다 (업로드 시 비율 유지 자동 리사이징).' },
    'FLOAT_RIGHT_BOTTOM': { size: '130 x 280px (고화질 원본 260x560 이상 권장)', desc: '4번 위치: 화면 우측 하단에서 스크롤 시 따라다니는 플로팅 배너입니다 (업로드 시 비율 유지 자동 리사이징).' },
    'EVENT_GRID': { size: '580 x 160px', desc: '6번 위치: 신간 도서 아래 2열(좌/우)로 나란히 배치되는 기획전 배너입니다.' },
    'MIDDLE_WIDE': { size: '1200 x 260px', desc: '7번 위치: 추천 도서 아래 전면을 가로지르는 풀와이드 연재/알림 배너입니다.' }
  }
}">
  <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200">
    <div>
      <h2 class="font-bold text-gray-800 text-lg"><?= $pageTitle ?></h2>
      <p class="text-xs text-gray-500 mt-0.5">배너가 노출될 위치를 선택하고 이미지를 업로드하세요 (비율이 맞으면 자동으로 최적 크기로 줄여서 저장됩니다).</p>
    </div>
    <a href="/admin/banners" class="text-xs text-gray-500 hover:text-gray-800">← 목록으로 돌아가기</a>
  </div>

  <form action="<?= $isEdit ? '/admin/banners/' . (int)$banner['id'] . '/edit' : '/admin/banners/create' ?>"
        method="POST" enctype="multipart/form-data" class="flex flex-col gap-5">

    <!-- 1. 노출 위치 선택 -->
    <div>
      <label class="text-xs text-gray-700 font-bold mb-1.5 block">배너 노출 위치 *</label>
      <select name="banner_type" x-model="pos" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-xs outline-none focus:border-blue-500 bg-white font-medium">
        <option value="HERO_MAIN">2번: 중앙 메인 슬라이더 (1280 x 440px 풀와이드)</option>
        <option value="FLOAT_LEFT">1번: 좌측 스크롤 추적 플로팅 배너 (130 x 280px)</option>
        <option value="FLOAT_RIGHT_TOP">3번: 우측 상단 플로팅 배너 (130 x 280px)</option>
        <option value="FLOAT_RIGHT_BOTTOM">4번: 우측 하단 플로팅 배너 (130 x 280px)</option>
        <option value="EVENT_GRID">6번: 이벤트 / 기획전 2열 배너 (580 x 160px)</option>
        <option value="MIDDLE_WIDE">7번: 출판사 연재 / 알림 와이드 배너 (1200 x 260px)</option>
      </select>

      <!-- 위치별 권장 규격 가이드 박스 -->
      <div class="mt-2.5 p-3.5 bg-blue-50/70 border border-blue-200 rounded-xl flex items-start gap-2.5 text-xs text-blue-900">
        <span class="material-symbols-outlined text-blue-600 text-base mt-0.5">aspect_ratio</span>
        <div>
          <p class="font-bold">권장 이미지 해상도: <span class="text-blue-700 font-mono" x-text="guideMap[pos]?.size"></span></p>
          <p class="text-blue-700/80 text-[11px] mt-0.5" x-text="guideMap[pos]?.desc"></p>
        </div>
      </div>
      <input type="hidden" name="size_memo" :value="guideMap[pos]?.size"/>
    </div>

    <!-- 2. 배너 제목 & 서브카피 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="text-xs text-gray-700 font-bold mb-1 block">배너 메인 제목 *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($banner['title'] ?? '') ?>" required placeholder="예: 평화로의 초대 — 평화 관련 도서 30% 특별 할인"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500"/>
      </div>

      <div class="md:col-span-2">
        <label class="text-xs text-gray-700 font-bold mb-1 block">서브 카피 문구</label>
        <input type="text" name="subtitle" value="<?= htmlspecialchars($banner['subtitle'] ?? '') ?>" placeholder="예: 주제별/장르별 평화 관련 도서 30% 할인 (신간 제외)"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500"/>
      </div>

      <div>
        <label class="text-xs text-gray-700 font-bold mb-1 block">배너 뱃지 태그</label>
        <input type="text" name="badge_text" value="<?= htmlspecialchars($banner['badge_text'] ?? '') ?>" placeholder="예: SPECIAL, 이벤트, 연재, 기획전"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500"/>
      </div>

      <div>
        <label class="text-xs text-gray-700 font-bold mb-1 block">정렬 순서 (낮을수록 앞순위)</label>
        <input type="number" name="sort_order" value="<?= (int)($banner['sort_order'] ?? 10) ?>"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500"/>
      </div>
    </div>

    <!-- 3. 연결 링크 URL & 퀵 프리셋 -->
    <div>
      <label class="text-xs text-gray-700 font-bold mb-1 block">클릭 시 이동할 연결 링크 (URL) *</label>
      <input type="text" name="link_url" id="linkInput" value="<?= htmlspecialchars($banner['link_url'] ?? '#') ?>" required
             placeholder="예: /book/1513089518 또는 /category/104010 또는 /community/notice"
             class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono outline-none focus:border-blue-500"/>

      <!-- 퀵 프리셋 버튼 -->
      <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px] text-gray-500">
        <span>빠른 링크 입력:</span>
        <button type="button" @click="document.getElementById('linkInput').value = '/books'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded border">전체 도서</button>
        <button type="button" @click="document.getElementById('linkInput').value = '/category/1050'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded border">도서출판 비공</button>
        <button type="button" @click="document.getElementById('linkInput').value = '/category/1060'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded border">NICS</button>
        <button type="button" @click="document.getElementById('linkInput').value = '/authors'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded border">저자별 보기</button>
        <button type="button" @click="document.getElementById('linkInput').value = '/community/notice'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded border">공지사항/투고</button>
        <button type="button" @click="document.getElementById('linkInput').value = '/community/gallery'" class="px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded border">연재 갤러리</button>
      </div>
    </div>

    <!-- 4. 배너 이미지 업로드 -->
    <div>
      <label class="text-xs text-gray-700 font-bold mb-1 block">배너 이미지 파일 <?= $isEdit ? '(변경 시에만 선택)' : '*' ?></label>
      <input type="file" name="image" accept="image/*" <?= $isEdit ? '' : 'required' ?>
             class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none bg-white"/>

      <?php if ($isEdit && !empty($banner['image_url'])): ?>
        <div class="mt-2.5 flex items-center gap-3 p-3 bg-gray-50 rounded-xl border">
          <img src="<?= htmlspecialchars($banner['image_url']) ?>" class="w-28 h-16 object-cover rounded-lg border bg-white" onerror="this.src='/assets/images/default_book.png'"/>
          <div class="text-xs text-gray-600">
            <p class="font-medium">현재 등록된 이미지</p>
            <p class="text-[11px] text-gray-400 font-mono truncate max-w-xs"><?= htmlspecialchars($banner['image_url']) ?></p>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- 5. 노출 상태 토글 -->
    <div class="pt-2">
      <label class="flex items-center gap-2 text-xs text-gray-800 font-bold cursor-pointer">
        <input type="checkbox" name="is_active" value="1" <?= (!isset($banner) || $banner['is_active']) ? 'checked' : '' ?> class="w-4 h-4 rounded text-blue-600"/>
        <span>이 배너를 쇼핑몰에 즉시 노출하기 (Active)</span>
      </label>
    </div>

    <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
      <a href="/admin/banners" class="px-5 py-2.5 border border-gray-300 rounded-lg text-xs text-gray-600 hover:bg-gray-50">취소</a>
      <button type="submit" class="px-6 py-2.5 bg-[#07131e] text-white rounded-lg text-xs font-semibold hover:bg-[#1c2833] shadow-sm">
        <?= $isEdit ? '수정 내용 저장하기' : '배너 등록하기' ?>
      </button>
    </div>
  </form>
</div>

  </main>
</div>
</body>
</html>
