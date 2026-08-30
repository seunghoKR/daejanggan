<?php
/**
 * 관리자 도서 등록 / 수정 폼 (로컬 AI 스마트 파서 & 다중 이미지 드래그 업로더 탑재)
 */
$isEdit = isset($book);
$pageTitle = $isEdit ? '도서 수정' : '도서 등록';
$activeMenu = 'books';

$existingImages = [];
if (!empty($book['detail_images'])) {
    $dec = json_decode($book['detail_images'], true);
    if (is_array($dec)) {
        $existingImages = $dec;
    }
}
if (empty($existingImages) && !empty($book['cover_image']) && $book['cover_image'] !== DEFAULT_BOOK_IMG) {
    $existingImages = [$book['cover_image']];
}

include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="max-w-5xl mx-auto flex flex-col gap-6" x-data="bookFormManager(<?= htmlspecialchars(json_encode($existingImages, JSON_UNESCAPED_SLASHES)) ?>)">

  <!-- 상단 네비게이션 헤더 -->
  <div class="flex items-center justify-between bg-white rounded-2xl border border-gray-200 px-6 py-4 shadow-sm">
    <div>
      <h2 class="font-bold text-gray-900 text-xl flex items-center gap-2">
        <span class="material-symbols-outlined text-[#07131e]">menu_book</span>
        <span><?= $pageTitle ?></span>
      </h2>
      <p class="text-xs text-gray-500 mt-0.5">로컬 AI로 원고 텍스트를 자동 분석하고 다중 이미지를 드래그하여 등록하세요.</p>
    </div>
    <a href="/admin/books" class="flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200 transition-colors">
      <span class="material-symbols-outlined text-sm">arrow_back</span>
      <span>목록으로</span>
    </a>
  </div>

  <!-- ============================================================ -->
  <!-- 🤖 1. 로컬 AI 스마트 원고 텍스트 자동 분석 패널 -->
  <!-- ============================================================ -->
  <div class="bg-gradient-to-br from-[#07131e] via-[#1c2833] to-[#2c3e50] text-white rounded-2xl p-6 shadow-xl border border-gray-700 relative overflow-hidden">
    <!-- 배경 데코레이션 -->
    <div class="absolute -right-8 -bottom-8 w-60 h-60 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

    <div class="flex items-center justify-between mb-3 relative z-10">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-500/20 border border-blue-400/40 flex items-center justify-center text-blue-400">
          <span class="material-symbols-outlined text-xl">psychology</span>
        </div>
        <div>
          <h3 class="font-bold text-base text-white flex items-center gap-2">
            <span>로컬 AI 스마트 도서 정보 자동 입력</span>
            <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border flex items-center gap-1.5 transition-colors"
                  :class="aiOnline === false ? 'bg-amber-500/20 text-amber-300 border-amber-400/40' : 'bg-blue-500/30 text-blue-300 border-blue-400/30'">
              <span class="w-1.5 h-1.5 rounded-full" :class="aiOnline === false ? 'bg-amber-400' : 'bg-emerald-400 animate-pulse'"></span>
              <span x-text="aiOnline === false ? 'AI 오프라인 (룰 엔진 가동 & 장애알림 연동)' : 'LM Studio 연동'"></span>
            </span>
          </h3>
          <p class="text-xs text-gray-300">원고 텍스트(제목, 부제, 지은이, 옮긴이, 감수, 출판사, ISBN, [책소개], [목차] 등)를 붙여넣으면 폼에 자동으로 채워집니다.</p>
        </div>
      </div>

      <button
        type="button"
        @click="showAiHelp = !showAiHelp"
        class="text-xs text-gray-400 hover:text-white flex items-center gap-1 bg-white/10 px-2.5 py-1 rounded-md transition-colors">
        <span class="material-symbols-outlined text-sm">help_outline</span>
        <span x-text="showAiHelp ? '도움말 접기' : '입력 형식 안내'"></span>
      </button>
    </div>

    <!-- 도움말 토글 박스 -->
    <div x-show="showAiHelp" x-cloak class="bg-black/30 rounded-xl p-3.5 mb-3 text-xs text-gray-300 border border-white/10 leading-relaxed font-mono">
      <p class="font-bold text-blue-300 mb-1">💡 권장 텍스트 형식 및 자동 인식 규칙:</p>
      <pre class="text-[11px] text-gray-300 whitespace-pre-wrap">
로마서 (첫째 줄: 특별한 명칭이 없으면 '도서명'으로 자동 인식)
신자들의 교회 성서주석 (둘째 줄: 특별한 명칭이 없으면 '부제목'으로 자동 인식)

지은이: 존 E. 토우즈 | 옮긴이: 황의무 | 감수: 이상억 | 출판사: 도서출판 대장간
발행일: 2026년 7월 15일 | ISBN: 978-89-7071-800-2 | 가격: 27,000원
* * * * * * *
[책소개] 도서에 대한 소개글...
* * * * * * *
[목차] 1장... 2장...
* * * * * * *
[지은이] 저자 소개... [옮긴이] 역자 소개...
      </pre>
    </div>

    <!-- 텍스트 입력 영역 -->
    <div class="relative z-10 flex flex-col gap-3">
      <textarea
        x-model="rawManuscript"
        rows="5"
        placeholder="도서 원고 정보 텍스트를 여기에 복사-붙여넣기(Ctrl+V) 하세요..."
        class="w-full bg-black/40 border border-white/20 rounded-xl p-3.5 text-xs text-white placeholder:text-gray-400 outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-all font-sans leading-relaxed"></textarea>

      <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2 text-xs">
          <!-- 텍스트 파일 불러오기 버튼 -->
          <label class="cursor-pointer bg-white/10 hover:bg-white/20 text-gray-200 px-3 py-1.5 rounded-lg border border-white/20 flex items-center gap-1.5 transition-colors">
            <span class="material-symbols-outlined text-sm">upload_file</span>
            <span>.txt 파일 불러오기</span>
            <input type="file" accept=".txt,.md" @change="loadTextFile($event)" class="hidden"/>
          </label>

          <button
            type="button"
            x-show="rawManuscript.length > 0"
            @click="rawManuscript = ''"
            class="text-gray-400 hover:text-white px-2 py-1 transition-colors">
            내용 비우기
          </button>
        </div>

        <button
          type="button"
          @click="parseWithAi()"
          :disabled="isParsing || !rawManuscript.trim()"
          class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-xl text-xs font-bold shadow-lg flex items-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer hover:shadow-blue-500/25">
          <template x-if="!isParsing">
            <span class="flex items-center gap-1.5">
              <span class="material-symbols-outlined text-base">auto_awesome</span>
              <span>로컬 AI로 분석 & 자동 입력</span>
            </span>
          </template>
          <template x-if="isParsing">
            <span class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
              <span>로컬 AI가 분석 중입니다...</span>
            </span>
          </template>
        </button>
      </div>

      <!-- AI 분석 성공 피드백 알림 -->
      <div x-show="parseResult" x-cloak x-transition class="bg-emerald-500/20 border border-emerald-400/40 text-emerald-200 text-xs px-4 py-2.5 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-emerald-400 text-base">check_circle</span>
          <span x-text="parseResult"></span>
        </div>
        <button type="button" @click="parseResult = ''" class="text-emerald-400 hover:text-white">&times;</button>
      </div>
    </div>
  </div>

  <!-- ============================================================ -->
  <!-- 📝 메인 도서 등록 / 수정 폼 -->
  <!-- ============================================================ -->
  <form
    id="bookForm"
    action="<?= $isEdit ? '/admin/books/' . (int)$book['id'] . '/edit' : '/admin/books/create' ?>"
    method="POST"
    enctype="multipart/form-data"
    class="flex flex-col gap-6">

    <!-- 히든 이미지 리스트 (JSON Array) -->
    <input type="hidden" name="image_list" :value="JSON.stringify(images)"/>

    <!-- ============================================================ -->
    <!-- 📸 2. 다중 이미지 업로드 & 드래그 앤 드롭 순서 변경 (Sortable UI) -->
    <!-- ============================================================ -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col gap-4">
      <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-secondary text-xl">collections</span>
          <h3 class="font-bold text-gray-900 text-base">도서 이미지 갤러리 관리</h3>
          <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium" x-text="images.length + '장 등록됨'"></span>
        </div>
        <div class="text-xs text-gray-500">
          <span class="text-secondary font-bold">1번째 사진</span>이 자동으로 <span class="underline font-semibold">대표 표지</span>가 됩니다. (마우스 드래그로 순서 변경 가능)
        </div>
      </div>

      <!-- 드래그 & 드롭 파일 업로드 영역 -->
      <div
        @dragover.prevent="isDragOverZone = true"
        @dragleave.prevent="isDragOverZone = false"
        @drop.prevent="handleFileDrop($event)"
        :class="isDragOverZone ? 'border-secondary bg-red-50/40 ring-2 ring-secondary/20' : 'border-gray-300 bg-gray-50/60 hover:bg-gray-50'"
        class="border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer relative">

        <input
          type="file"
          id="multiImageInput"
          multiple
          accept="image/*"
          @change="handleFileSelect($event)"
          class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"/>

        <div class="flex flex-col items-center justify-center gap-2 pointer-events-none">
          <div class="w-12 h-12 rounded-full bg-secondary/10 text-secondary flex items-center justify-center">
            <template x-if="!isUploading">
              <span class="material-symbols-outlined text-2xl">add_photo_alternate</span>
            </template>
            <template x-if="isUploading">
              <svg class="animate-spin h-6 w-6 text-secondary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
            </template>
          </div>
          <div>
            <p class="text-sm font-bold text-gray-800">
              <template x-if="!isUploading">
                <span>여러 장의 도서 이미지를 한 번에 끌어다 놓거나 클릭하세요</span>
              </template>
              <template x-if="isUploading">
                <span class="text-secondary">이미지를 최적화하여 업로드 중입니다...</span>
              </template>
            </p>
            <p class="text-xs text-gray-500 mt-1">표지, 속지, 본문 미리보기 등 여러 장을 동시에 올릴 수 있으며, 가로/세로 1600px로 자동 최적화 리사이징됩니다.</p>
          </div>
        </div>
      </div>

      <!-- 업로드된 이미지 썸네일 그리드 (드래그 앤 드롭 정렬) -->
      <div x-show="images.length > 0" class="flex flex-col gap-2">
        <p class="text-xs font-semibold text-gray-700 flex items-center gap-1">
          <span class="material-symbols-outlined text-sm text-gray-500">swap_horiz</span>
          <span>이미지를 마우스로 끌어서 순서를 변경하세요:</span>
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5 pt-1">
          <template x-for="(imgUrl, index) in images" :key="imgUrl">
            <div
              draggable="true"
              @dragstart="onDragStart(index, $event)"
              @dragover.prevent="onDragOver(index, $event)"
              @dragenter.prevent=""
              @drop="onDrop(index, $event)"
              :class="{
                'ring-2 ring-secondary shadow-lg scale-102': index === 0,
                'opacity-40 border-dashed border-secondary': dragOverIndex === index && dragSrcIndex !== index
              }"
              class="group relative bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col cursor-grab active:cursor-grabbing">

              <!-- 순서 및 뱃지 -->
              <div class="absolute top-1.5 left-1.5 z-10 flex items-center gap-1">
                <template x-if="index === 0">
                  <span class="bg-gradient-to-r from-secondary to-orange-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow">
                    ★ 대표 표지
                  </span>
                </template>
                <template x-if="index > 0">
                  <span class="bg-gray-900/75 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full" x-text="(index + 1) + '번'"></span>
                </template>
              </div>

              <!-- 이미지 미리보기 -->
              <div class="w-full h-36 bg-gray-50 flex items-center justify-center p-2">
                <img :src="imgUrl" alt="도서 이미지" class="w-full h-full object-contain pointer-events-none drop-shadow-sm"/>
              </div>

              <!-- 툴바 버튼 (순서 이동 & 삭제) -->
              <div class="p-1.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-gray-500">
                <div class="flex items-center gap-0.5">
                  <button
                    type="button"
                    @click="moveImage(index, -1)"
                    :disabled="index === 0"
                    title="왼쪽으로 이동"
                    class="p-1 hover:text-gray-900 hover:bg-gray-200 rounded disabled:opacity-20 disabled:hover:bg-transparent">
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                  </button>
                  <button
                    type="button"
                    @click="moveImage(index, 1)"
                    :disabled="index === images.length - 1"
                    title="오른쪽으로 이동"
                    class="p-1 hover:text-gray-900 hover:bg-gray-200 rounded disabled:opacity-20 disabled:hover:bg-transparent">
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                  </button>
                </div>

                <button
                  type="button"
                  @click="removeImage(index)"
                  title="이미지 삭제"
                  class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded">
                  <span class="material-symbols-outlined text-sm">delete</span>
                </button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- 📋 3. 도서 세부 정보 입력 폼 -->
    <!-- ============================================================ -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col gap-5">
      <h3 class="font-bold text-gray-900 text-base border-b border-gray-100 pb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-xl">edit_note</span>
        <span>도서 기본 정보</span>
      </h3>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">도서 식별코드 (book_code) *</label>
          <input
            type="text"
            name="book_code"
            id="field_book_code"
            value="<?= htmlspecialchars($book['book_code'] ?? 'BK' . date('ymdHis')) ?>"
            required
            <?= $isEdit ? 'readonly' : '' ?>
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary <?= $isEdit ? 'bg-gray-100 text-gray-500' : '' ?>"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">도서분류 *</label>
          <select
            name="category_id"
            id="field_category_id"
            required
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary bg-white font-mono text-xs">
            <?php foreach ($categories as $cat):
              $codeLen = strlen($cat['code'] ?? '');
              $prefix = '';
              if ($codeLen <= 4) {
                  $prefix = '■ ';
              } elseif ($codeLen <= 6) {
                  $prefix = '　├ ';
              } else {
                  $prefix = '　　└ ';
              }
            ?>
              <option value="<?= (int)$cat['id'] ?>" <?= (($book['category_id'] ?? 1) == $cat['id']) ? 'selected' : '' ?>>
                <?= $prefix . htmlspecialchars($cat['name']) ?> (<?= htmlspecialchars($cat['code']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">시리즈 (선택)</label>
          <select
            name="series_id"
            id="field_series_id"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary bg-white">
            <option value="">시리즈 없음 (일반)</option>
            <?php foreach ($seriesList as $ser): ?>
              <option value="<?= (int)$ser['id'] ?>" <?= (($book['series_id'] ?? '') == $ser['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ser['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">도서명 *</label>
          <input
            type="text"
            name="title"
            id="field_title"
            value="<?= htmlspecialchars($book['title'] ?? '') ?>"
            required
            placeholder="도서명을 입력하세요"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">부제</label>
          <input
            type="text"
            name="subtitle"
            id="field_subtitle"
            value="<?= htmlspecialchars($book['subtitle'] ?? '') ?>"
            placeholder="부제목이 있는 경우 입력"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">저자 (지은이) *</label>
          <input
            type="text"
            name="author"
            id="field_author"
            value="<?= htmlspecialchars($book['author'] ?? '') ?>"
            required
            placeholder="저자명"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">역자 (옮긴이/감수)</label>
          <input
            type="text"
            name="translator"
            id="field_translator"
            value="<?= htmlspecialchars($book['translator'] ?? '') ?>"
            placeholder="역자명 (감수자 포함)"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">출판사</label>
          <input
            type="text"
            name="publisher"
            id="field_publisher"
            value="<?= htmlspecialchars($book['publisher'] ?? '도서출판 대장간') ?>"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">발행일</label>
          <input
            type="date"
            name="publish_date"
            id="field_publish_date"
            value="<?= htmlspecialchars($book['publish_date'] ?? date('Y-m-d')) ?>"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">ISBN</label>
          <input
            type="text"
            name="isbn"
            id="field_isbn"
            value="<?= htmlspecialchars($book['isbn'] ?? '') ?>"
            placeholder="예: 978-89-7071-801-9"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">정가 (원) *</label>
          <input
            type="number"
            name="original_price"
            id="field_original_price"
            value="<?= (int)($book['original_price'] ?? 0) ?>"
            required
            @input="calcDiscountPrice($event.target.value)"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-xs font-semibold text-gray-600">판매가 (원) *</label>
            <span class="text-[11px] text-blue-600 font-medium cursor-pointer hover:underline" @click="applyDefaultDiscount()">10% 할인가 적용</span>
          </div>
          <input
            type="number"
            name="price"
            id="field_price"
            value="<?= (int)($book['price'] ?? 0) ?>"
            required
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-bold text-secondary outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">재고 수량 *</label>
          <input
            type="number"
            name="stock_qty"
            id="field_stock_qty"
            value="<?= (int)($book['stock_qty'] ?? 100) ?>"
            required
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary"/>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-600 mb-1.5 block">판매 상태</label>
          <select
            name="status"
            id="field_status"
            class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary bg-white">
            <option value="SALE" <?= (($book['status'] ?? 'SALE') === 'SALE') ? 'selected' : '' ?>>판매중</option>
            <option value="SOLDOUT" <?= (($book['status'] ?? '') === 'SOLDOUT') ? 'selected' : '' ?>>품절</option>
            <option value="HIDDEN" <?= (($book['status'] ?? '') === 'HIDDEN') ? 'selected' : '' ?>>숨김 (비공개)</option>
          </select>
        </div>
      </div>

      <!-- 한줄 요약 (인용구형 Hook) -->
      <div>
        <label class="text-xs font-semibold text-gray-600 mb-1.5 block">한줄 요약 (도서 상세 상단 및 카드에 노출되는 핵심 Hook)</label>
        <textarea
          name="summary"
          id="field_summary"
          rows="2"
          placeholder="독자의 시선을 사로잡는 1~2문장의 핵심 소개글"
          class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-primary leading-relaxed"><?= htmlspecialchars($book['summary'] ?? '') ?></textarea>
      </div>

      <!-- 상세 설명 (HTML WYSIWYG & 소스 편집 탭) -->
      <div x-data="{ descTab: 'source' }">
        <div class="flex items-center justify-between mb-1.5">
          <label class="text-xs font-semibold text-gray-600">도서 상세 설명 (HTML 포맷 / [책소개], [목차], [저자소개] 등)</label>
          <div class="flex items-center gap-1 bg-gray-100 p-0.5 rounded-lg text-xs">
            <button
              type="button"
              @click="descTab = 'source'"
              :class="descTab === 'source' ? 'bg-white font-bold text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'"
              class="px-2.5 py-1 rounded-md transition-all">
              HTML 코드
            </button>
            <button
              type="button"
              @click="descTab = 'preview'"
              :class="descTab === 'preview' ? 'bg-white font-bold text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'"
              class="px-2.5 py-1 rounded-md transition-all">
              미리보기
            </button>
          </div>
        </div>

        <div x-show="descTab === 'source'">
          <textarea
            name="description"
            id="field_description"
            x-model="bookDesc"
            rows="10"
            placeholder="도서 상세 설명 HTML"
            class="w-full border border-gray-300 rounded-xl p-3.5 text-xs font-mono text-gray-800 outline-none focus:border-primary leading-relaxed"></textarea>
        </div>

        <div x-show="descTab === 'preview'" x-cloak class="border border-gray-200 rounded-xl p-5 bg-gray-50/50 min-h-[200px] max-h-[400px] overflow-y-auto">
          <div class="prose text-xs text-gray-800" x-html="bookDesc || '<p class=\'text-gray-400\'>상세 설명 내용이 없습니다.</p>'"></div>
        </div>
      </div>

      <!-- 도서 뱃지 태그 -->
      <div class="flex items-center flex-wrap gap-6 pt-3 border-t border-gray-100">
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
          <input type="checkbox" name="is_new" value="1" <?= (!empty($book['is_new'])) ? 'checked' : '' ?> class="rounded text-blue-600 focus:ring-blue-500"/>
          <span>신간 도서 (NEW)</span>
        </label>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
          <input type="checkbox" name="is_best" value="1" <?= (!empty($book['is_best'])) ? 'checked' : '' ?> class="rounded text-secondary focus:ring-secondary"/>
          <span>베스트셀러 (BEST)</span>
        </label>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
          <input type="checkbox" name="is_recommend" value="1" <?= (!empty($book['is_recommend'])) ? 'checked' : '' ?> class="rounded text-emerald-600 focus:ring-emerald-500"/>
          <span>추천 도서</span>
        </label>
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
          <input type="checkbox" name="is_discount" value="1" <?= (!empty($book['is_discount'])) ? 'checked' : '' ?> class="rounded text-orange-600 focus:ring-orange-500"/>
          <span>할인 도서</span>
        </label>
      </div>

      <!-- 하단 액션 버튼 -->
      <div class="pt-4 border-t border-gray-200 flex justify-end gap-3">
        <a href="/admin/books" class="px-6 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">취소</a>
        <button
          type="submit"
          class="px-8 py-2.5 bg-[#07131e] hover:bg-[#1c2833] text-white rounded-xl text-sm font-bold shadow-lg hover:shadow-xl transition-all cursor-pointer flex items-center gap-2">
          <span class="material-symbols-outlined text-base">save</span>
          <span><?= $isEdit ? '수정 내용 저장' : '도서 등록 완료' ?></span>
        </button>
      </div>
    </div>
  </form>
</div>

<!-- ============================================================ -->
<!-- Alpine.js 컴포넌트 스크립트 -->
<!-- ============================================================ -->
<script>
function bookFormManager(initialImages) {
  return {
    rawManuscript: '',
    isParsing: false,
    parseResult: '',
    showAiHelp: false,
    aiOnline: null,
    isDragOverZone: false,
    isUploading: false,
    images: Array.isArray(initialImages) ? initialImages : [],
    dragSrcIndex: null,
    dragOverIndex: null,
    bookDesc: `<?= addslashes($book['description'] ?? '') ?>`,

    init() {
      this.checkAiStatus();
    },

    async checkAiStatus() {
      try {
        const res = await fetch('/admin/notify/check-ai');
        const data = await res.json();
        this.aiOnline = data.is_online;
      } catch (e) {
        this.aiOnline = false;
      }
    },

    // 1. .txt 텍스트 파일 불러오기
    loadTextFile(event) {
      const file = event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        this.rawManuscript = e.target.result;
      };
      reader.readAsText(file, 'utf-8');
    },

    // 2. 로컬 AI 원고 텍스트 파싱 요청
    async parseWithAi() {
      if (!this.rawManuscript.trim()) {
        alert('분석할 원고 텍스트를 입력해 주세요.');
        return;
      }

      this.isParsing = true;
      this.parseResult = '';

      try {
        const res = await fetch('/admin/books/ai-parse', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ raw_text: this.rawManuscript })
        });

        const json = await res.json();
        if (!json.success || !json.data) {
          alert(json.message || '텍스트 분석에 실패했습니다.');
          return;
        }

        const d = json.data;

        // 폼 필드 자동 매핑 (양끝 기호 깔끔 제거)
        if (d.title) this.setFieldValue('field_title', this.cleanTitleStr(d.title));
        if (d.subtitle) this.setFieldValue('field_subtitle', this.cleanTitleStr(d.subtitle));
        if (d.author) this.setFieldValue('field_author', d.author);
        if (d.translator) this.setFieldValue('field_translator', d.translator);
        if (d.publisher) this.setFieldValue('field_publisher', d.publisher);
        if (d.publish_date) this.setFieldValue('field_publish_date', d.publish_date);
        if (d.isbn) this.setFieldValue('field_isbn', d.isbn);
        if (d.original_price) {
          this.setFieldValue('field_original_price', d.original_price);
          this.calcDiscountPrice(d.original_price);
        }
        if (d.category_id) {
          const catSelect = document.getElementById('field_category_id');
          if (catSelect) {
            catSelect.value = d.category_id;
            this.flashElement(catSelect);
          }
        }
        if (d.series_id) {
          const serSelect = document.getElementById('field_series_id');
          if (serSelect) {
            serSelect.value = d.series_id;
            this.flashElement(serSelect);
          }
        }
        if (d.summary) this.setFieldValue('field_summary', d.summary);
        if (d.description) {
          this.bookDesc = d.description;
          this.setFieldValue('field_description', d.description);
        }

        this.parseResult = `🎉 "${d.title || '도서'}" 정보(저자, 출판일, 정가, 목차/소개 등)가 폼에 자동 입력되었습니다!`;
      } catch (err) {
        console.error(err);
        alert('AI 분석 중 통신 오류가 발생했습니다.');
      } finally {
        this.isParsing = false;
      }
    },

    cleanTitleStr(str) {
      if (!str) return '';
      let s = String(str).trim();
      const brackets = [
        [/^<(.+)>$/, 1], [/^〈(.+)〉$/, 1], [/^《(.+)》$/, 1], [/^«(.+)»$/, 1],
        [/^「(.+)」$/, 1], [/^『(.+)』$/, 1], [/^\[(.+)\]$/, 1], [/^{(.+)}$/, 1],
        [/^\((.+)\)$/, 1], [/^"(.+)"$/, 1], [/^'(.+)'$/, 1], [/^“(.+)”$/, 1], [/^‘(.+)’$/, 1]
      ];
      let changed = true;
      let count = 0;
      while (changed && count < 5) {
        const prev = s;
        for (const [re] of brackets) {
          s = s.replace(re, '$1').trim();
        }
        s = s.replace(/^[\s<〈《«「『\[{\("“‘]+|[\s>〉》»」』\]}\)"”’]+$/g, '').trim();
        changed = (prev !== s);
        count++;
      }
      return s;
    },

    setFieldValue(elemId, value) {
      const el = document.getElementById(elemId);
      if (el) {
        el.value = value;
        this.flashElement(el);
      }
    },

    flashElement(el) {
      el.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50/50');
      setTimeout(() => {
        el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50/50');
      }, 1500);
    },

    // 3. 가격 자동 계산
    calcDiscountPrice(origPrice) {
      const num = parseInt(origPrice, 10);
      if (!isNaN(num) && num > 0) {
        const disc = Math.round(num * 0.9);
        const priceEl = document.getElementById('field_price');
        if (priceEl && (!priceEl.value || priceEl.value === '0' || priceEl.value == num)) {
          priceEl.value = disc;
        }
      }
    },

    applyDefaultDiscount() {
      const origEl = document.getElementById('field_original_price');
      const num = parseInt(origEl?.value || '0', 10);
      if (num > 0) {
        const priceEl = document.getElementById('field_price');
        if (priceEl) priceEl.value = Math.round(num * 0.9);
      }
    },

    // 4. 다중 이미지 업로드 처리
    handleFileSelect(event) {
      const files = event.target.files;
      if (files && files.length > 0) {
        this.uploadMultipleFiles(files);
      }
    },

    handleFileDrop(event) {
      this.isDragOverZone = false;
      const files = event.dataTransfer.files;
      if (files && files.length > 0) {
        this.uploadMultipleFiles(files);
      }
    },

    async uploadMultipleFiles(files) {
      this.isUploading = true;

      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (!file.type.startsWith('image/')) continue;

        const formData = new FormData();
        formData.append('image', file);

        try {
          const res = await fetch('/admin/books/upload-image', {
            method: 'POST',
            body: formData
          });
          const json = await res.json();
          if (json.success && json.url) {
            this.images.push(json.url);
          } else {
            alert(`[${file.name}] 업로드 실패: ` + (json.message || '오류 발생'));
          }
        } catch (e) {
          console.error(e);
          alert(`[${file.name}] 업로드 중 통신 오류가 발생했습니다.`);
        }
      }

      this.isUploading = false;
      const fileInput = document.getElementById('multiImageInput');
      if (fileInput) fileInput.value = '';
    },

    // 5. 이미지 순서 변경 & 삭제
    removeImage(idx) {
      if (confirm('이 이미지를 목록에서 제외하시겠습니까?')) {
        this.images.splice(idx, 1);
      }
    },

    moveImage(idx, dir) {
      const targetIdx = idx + dir;
      if (targetIdx < 0 || targetIdx >= this.images.length) return;
      const item = this.images.splice(idx, 1)[0];
      this.images.splice(targetIdx, 0, item);
    },

    // 드래그 앤 드롭 정렬 핸들러
    onDragStart(idx, event) {
      this.dragSrcIndex = idx;
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', idx);
    },

    onDragOver(idx, event) {
      this.dragOverIndex = idx;
    },

    onDrop(targetIdx, event) {
      if (this.dragSrcIndex === null || this.dragSrcIndex === targetIdx) {
        this.dragSrcIndex = null;
        this.dragOverIndex = null;
        return;
      }
      const item = this.images.splice(this.dragSrcIndex, 1)[0];
      this.images.splice(targetIdx, 0, item);
      this.dragSrcIndex = null;
      this.dragOverIndex = null;
    }
  };
}
</script>

  </main>
</div>
</body>
</html>
