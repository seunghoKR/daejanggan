<?php
/**
 * ✍️ 관리자 커뮤니티 게시판 작성/수정 (Admin Board Form)
 */
$typeNames = [
  'notice'  => '공지사항',
  'event'   => '대장간이벤트',
  'gallery' => '글 먹는 시간',
  'archive' => '자료실',
  'press'   => '언론보도',
];
$boardName = $typeNames[$type] ?? '게시판';
$isEdit    = !empty($post['id']);
$pageTitle = $boardName . ($isEdit ? ' 수정' : ' 새 글 작성');
$activeMenu = 'board_' . $type;
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="w-full pb-16">
  <div class="mb-4">
    <a href="/admin/board/<?= htmlspecialchars($type) ?>" class="text-xs text-gray-500 hover:text-gray-800 flex items-center gap-1">
      <span class="material-symbols-outlined text-sm">arrow_back</span>
      <?= htmlspecialchars($boardName) ?> 목록으로 돌아가기
    </a>
  </div>

  <form action="<?= $isEdit ? "/admin/board/{$type}/{$post['id']}/edit" : "/admin/board/{$type}/create" ?>"
        method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
      <div class="border-b border-gray-100 pb-4 flex items-center justify-between">
        <h2 class="font-bold text-base text-gray-800">
          <?= htmlspecialchars($boardName) ?> <?= $isEdit ? '게시글 수정' : '신규 글 작성' ?>
        </h2>
        <span class="text-xs text-gray-400 font-mono">type: <?= htmlspecialchars($type) ?></span>
      </div>

      <!-- 상단 옵션: 공지 고정 & 작성자 -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1.5 block">작성자명</label>
          <input type="text" name="author_name" required
                 value="<?= htmlspecialchars($post['author_name'] ?? (Auth::user()['name'] ?? '도서출판 대장간')) ?>"
                 class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs text-gray-800 outline-none focus:ring-1 focus:ring-blue-500"/>
        </div>

        <div class="flex items-center gap-4 sm:pt-6">
          <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
            <input type="checkbox" name="is_notice" value="1" <?= !empty($post['is_notice']) ? 'checked' : '' ?>
                   class="rounded text-blue-600"/>
            <span class="font-semibold text-amber-800">📌 상단 공지로 고정</span>
          </label>
        </div>
      </div>

      <!-- 제목 -->
      <div>
        <label class="text-xs font-semibold text-gray-700 mb-1.5 block">
          게시글 제목 <span class="text-red-500">*</span>
        </label>
        <input type="text" name="title" required placeholder="제목을 입력하세요"
               value="<?= htmlspecialchars($post['title'] ?? '') ?>"
               class="w-full border border-gray-300 rounded-lg px-3.5 py-2 text-xs text-gray-800 outline-none focus:ring-1 focus:ring-blue-500"/>
      </div>

      <!-- 대표 이미지 또는 첨부파일 -->
      <div>
        <label class="text-xs font-semibold text-gray-700 mb-1.5 block">
          <?= ($type === 'gallery' || $type === 'event') ? '대표 이미지 (카드뉴스/포스터)' : '첨부파일 (이미지 또는 문서)' ?>
        </label>
        <?php if (!empty($post['file_path'])): ?>
          <div class="mb-2 p-2 bg-gray-50 border rounded-lg flex items-center justify-between text-xs">
            <span class="text-gray-600 truncate max-w-xs">현재 등록 파일: <?= htmlspecialchars($post['file_path']) ?></span>
            <a href="<?= htmlspecialchars($post['file_path']) ?>" target="_blank" class="text-blue-600 font-semibold hover:underline">보기</a>
          </div>
        <?php endif; ?>
        <input type="file" name="attachment"
               accept="<?= ($type === 'gallery') ? 'image/*' : '*/*' ?>"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-700 bg-gray-50 file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
        <p class="text-[11px] text-gray-400 mt-1">새 파일을 선택하면 기존 파일이 교체됩니다.</p>
      </div>

      <!-- 본문 내용 (위지윅 에디터) -->
      <div>
        <label class="text-xs font-semibold text-gray-700 mb-1.5 block">
          게시글 본문 내용 <span class="text-red-500">*</span>
        </label>
        <textarea name="content" id="boardContent" rows="16" required placeholder="내용을 작성하세요"
                  class="w-full border border-gray-300 rounded-lg p-3.5 text-xs font-mono text-gray-800"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
      </div>

      <!-- 저장 버튼 -->
      <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
        <a href="/admin/board/<?= htmlspecialchars($type) ?>" class="text-xs text-gray-500 hover:underline">취소</a>
        <button type="submit"
                class="px-6 py-2.5 bg-[#07131e] hover:bg-[#1c2833] text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
          <?= $isEdit ? '게시글 수정 완료' : '게시글 등록하기' ?>
        </button>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof CKEDITOR !== 'undefined' && document.getElementById('boardContent')) {
    CKEDITOR.replace('boardContent', {
      height: 450,
      language: 'ko',
      font_names: 'Noto Sans KR/Noto Sans KR, sans-serif;' +
                  '맑은 고딕/Malgun Gothic, sans-serif;' +
                  '돋움/Dotum, sans-serif;' +
                  '굴림/Gulim, sans-serif;' +
                  '바탕/Batang, serif;' +
                  '궁서/Gungsuh, serif;' +
                  'Arial/Arial, Helvetica, sans-serif;' +
                  'Times New Roman/Times New Roman, Times, serif;',
      fontSize_sizes: '8/8px;9/9px;10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;24/24px;28/28px;36/36px;48/48px;',
      allowedContent: true,
      extraAllowedContent: '*(*)[*]{*};',
      versionCheck: false
    });
  }

  // 폼 제출 전 동기화
  document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function() {
      if (typeof CKEDITOR !== 'undefined') {
        for (var instance in CKEDITOR.instances) {
          CKEDITOR.instances[instance].updateElement();
        }
      }
    });
  });
});
</script>
