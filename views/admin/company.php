<?php
/**
 * 🏢 관리자 회사소개 편집기 (Admin Company Editor)
 */
$pageTitle = '회사소개 편집';
$activeMenu = 'company';
include APP_ROOT . '/views/layouts/admin_layout.php';
$companyIntro = $companyIntro ?? ($settings['company_intro_html']['key_value'] ?? '');
?>

<div class="max-w-5xl pb-16">
  <div class="mb-6 flex items-center justify-between">
    <div>
      <h2 class="text-lg font-bold text-gray-800">회사소개 콘텐츠 관리</h2>
      <p class="text-xs text-gray-500 mt-1">쇼핑몰 '커뮤니티 > 회사소개' 페이지에 노출되는 소개글 및 연혁, 안내 HTML을 직접 수정합니다.</p>
    </div>
    <a href="/community/company" target="_blank"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors">
      <span class="material-symbols-outlined text-sm">open_in_new</span>
      사용자 페이지 바로 확인
    </a>
  </div>

  <form action="/admin/company" method="POST" class="flex flex-col gap-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-gray-700 text-lg">edit_note</span>
          <h3 class="font-bold text-gray-800 text-sm">회사소개 본문 HTML 편집</h3>
        </div>
        <span class="text-xs text-gray-400">HTML 태그 및 서식 지원</span>
      </div>

      <div class="p-5">
        <label class="text-xs font-semibold text-gray-700 mb-2 block">소개글 내용 편집 (위지윅 에디터)</label>
        <textarea name="company_intro_html" id="companyIntroHtml" rows="18"
                  class="w-full border border-gray-300 rounded-xl p-4 font-mono text-xs text-gray-800"><?= htmlspecialchars($companyIntro) ?></textarea>
        <p class="text-[11px] text-gray-400 mt-2">
          💡 워드나 한글처럼 자유롭게 글꼴, 크기, 색상, 이미지, 표를 편집하실 수 있습니다. [소스] 버튼을 누르면 원본 HTML도 수정 가능합니다.
        </p>
      </div>

      <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
        <span class="text-xs text-gray-500">저장 즉시 쇼핑몰 사용자단에 반영됩니다.</span>
        <button type="submit"
                class="px-6 py-2.5 bg-[#07131e] text-white rounded-lg font-semibold text-xs hover:bg-[#1c2833] transition-colors shadow-sm flex items-center gap-1.5">
          <span class="material-symbols-outlined text-sm">save</span>
          <span>회사소개 저장하기</span>
        </button>
      </div>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof CKEDITOR !== 'undefined' && document.getElementById('companyIntroHtml')) {
    CKEDITOR.replace('companyIntroHtml', {
      height: 480,
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
      extraAllowedContent: '*(*)[*]{*};'
    });
  }

  // 폼 제출 전 내용 자동 동기화
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
