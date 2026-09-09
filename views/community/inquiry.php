<?php
/**
 * ✍️ 출판문의 및 안내 (도서출판 대장간)
 * https://daejanggan.org/content/press 출판의뢰 안내문 기반 + 캡챠 + 접수 폼 + 텔레그램 연동
 */
$pageTitle = '출판 문의';
$boardTitle = '출판 문의';
include APP_ROOT . '/views/layouts/header.php';
$captchaQuestion = Captcha::generate();
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">
  <!-- 상단 브레드크럼 & 헤더 -->
  <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-outline-variant/60">
    <div>
      <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-1">
        <a href="/" class="hover:text-primary">홈</a>
        <span>›</span>
        <span>커뮤니티</span>
        <span>›</span>
        <span class="text-primary font-medium">출판 문의</span>
      </div>
      <h1 class="font-serif text-2xl md:text-3xl font-bold text-primary flex items-center gap-2">
        <span>출판 의뢰 및 문의</span>
        <span class="text-xs font-sans px-2.5 py-0.5 rounded-full bg-secondary/10 text-secondary font-semibold">Publishing Inquiry</span>
      </h1>
      <p class="text-xs text-on-surface-variant mt-1">도서출판 대장간과 함께 참된 평화와 진리의 가치를 담은 책을 만들어갈 저자·번역가 분들을 모십니다.</p>
    </div>

    <?php if (Auth::isAdmin()): ?>
      <div>
        <a href="/admin/inquiries" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#07131e] text-white text-xs font-semibold rounded-xl hover:bg-[#1c2833] transition-colors shadow-sm">
          <span class="material-symbols-outlined text-sm">mark_email_unread</span>
          접수된 출판문의 관리하기
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- 1. 출판의뢰 요청 절차 및 기준 안내 배너 카드 -->
  <div class="mb-12 bg-surface rounded-3xl border border-outline-variant/80 p-6 md:p-10 shadow-sm">
    <div class="flex items-center gap-2 text-secondary font-bold text-sm mb-3">
      <span class="material-symbols-outlined text-xl">menu_book</span>
      <span>출판의뢰 요청 절차 및 안내</span>
    </div>
    <h2 class="font-serif text-xl md:text-2xl font-bold text-primary mb-4 leading-snug">
      대장간은 소중한 원고를 정성껏 검토하고 있습니다.
    </h2>
    <p class="text-xs md:text-sm text-on-surface-variant leading-relaxed mb-8">
      도서출판 대장간에 관심을 가져주시고 원고를 보내주시는 모든 분들께 감사드립니다.<br class="hidden sm:inline"/>
      특히 한국교회와 사회를 위한 귀한 통찰이 담긴 원고를 환영하며, 아래의 원고 접수 요령을 참고하시어 접수해 주시기 바랍니다.
    </p>

    <!-- 3단계 절차 그리드 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <!-- 1단계: 원고 접수 -->
      <div class="bg-surface-container-low rounded-2xl p-5 border border-outline-variant/60 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="w-7 h-7 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">1</span>
            <span class="text-[11px] font-semibold text-secondary">원고 준비</span>
          </div>
          <h3 class="font-bold text-sm text-primary mb-2">원고 및 기획서 접수</h3>
          <ul class="text-xs text-on-surface-variant space-y-1.5 leading-relaxed">
            <li>• 완성된 <strong>원고 1부</strong> 또는 <strong>기획서</strong></li>
            <li>• 전체 목차와 샘플 원고(2~3챕터 분량)</li>
            <li>• 저자/역자 약력 및 연락처 기재</li>
          </ul>
        </div>
        <div class="mt-4 pt-3 border-t border-outline-variant/40 text-[11px] text-gray-400">
          HWP, PDF, DOCX 파일 첨부 가능
        </div>
      </div>

      <!-- 2단계: 심사 및 검토 -->
      <div class="bg-surface-container-low rounded-2xl p-5 border border-outline-variant/60 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="w-7 h-7 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">2</span>
            <span class="text-[11px] font-semibold text-secondary">출판 심사</span>
          </div>
          <h3 class="font-bold text-sm text-primary mb-2">원고 심사 (약 2주 소요)</h3>
          <ul class="text-xs text-on-surface-variant space-y-1.5 leading-relaxed">
            <li>• 편집위원회 및 외부 전문위원 심사</li>
            <li>• 대장간의 출판 방향 및 기획 적합성 검토</li>
            <li>• 접수 후 2주 이내 이메일/전화로 결과 회신</li>
          </ul>
        </div>
        <div class="mt-4 pt-3 border-t border-outline-variant/40 text-[11px] text-gray-400">
          심사 진행 상황은 개별 안내
        </div>
      </div>

      <!-- 3단계: 계약 및 출간 -->
      <div class="bg-surface-container-low rounded-2xl p-5 border border-outline-variant/60 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="w-7 h-7 rounded-full bg-primary text-white text-xs font-bold flex items-center justify-center">3</span>
            <span class="text-[11px] font-semibold text-secondary">출판 계약</span>
          </div>
          <h3 class="font-bold text-sm text-primary mb-2">출판 계약 및 편집 진행</h3>
          <ul class="text-xs text-on-surface-variant space-y-1.5 leading-relaxed">
            <li>• 출판 계약서 체결 및 일정 협의</li>
            <li>• 전문 편집, 교정·교열, 표지/본문 디자인</li>
            <li>• 전국 온/오프라인 서점 배본 및 마케팅</li>
          </ul>
        </div>
        <div class="mt-4 pt-3 border-t border-outline-variant/40 text-[11px] text-gray-400">
          기획출판 / 번역출판 / 기관출판
        </div>
      </div>
    </div>

    <!-- 유의사항 박스 -->
    <div class="bg-[#faf6f0] border-l-4 border-secondary p-4 rounded-r-xl text-xs text-on-surface-variant leading-relaxed">
      <p class="font-semibold text-primary mb-1">📌 원고 접수 시 유의사항</p>
      <p>• 우편으로 보내주신 원고(인쇄본)는 반환되지 않으므로 반드시 <strong>사본</strong>을 보내주시기 바랍니다.</p>
      <p>• 문의가 폭주하거나 학술 원고의 경우 외부 심사 기간으로 인해 회신이 다소 늦어질 수 있습니다. (문의: 041-742-1424 / jlife@daejanggan.org)</p>
    </div>
  </div>

  <!-- 2. 온라인 출판의뢰 문의 접수 폼 -->
  <div class="bg-surface rounded-3xl border border-outline-variant/80 p-6 md:p-12 shadow-sm">
    <div class="max-w-3xl mx-auto">
      <div class="text-center mb-8">
        <span class="text-xs font-bold text-secondary tracking-wider uppercase">Online Submission</span>
        <h2 class="font-serif text-2xl md:text-3xl font-bold text-primary mt-1">출판 의뢰 온라인 접수</h2>
        <p class="text-xs text-on-surface-variant mt-2">아래 양식을 작성해 주시면 담당 편집자가 확인 후 신속하게 연락드리겠습니다.</p>
      </div>

      <form action="/community/inquiry" method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- 의뢰자/단체명 -->
          <div>
            <label class="block text-xs font-semibold text-primary mb-1.5">
              성명 또는 단체명 <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" required placeholder="예: 홍길동 (또는 한국OO학회)"
                   value="<?= htmlspecialchars(Auth::user()['name'] ?? '') ?>"
                   class="w-full bg-surface-container-low border border-outline-variant/80 rounded-xl px-4 py-3 text-xs text-on-surface focus:border-primary focus:bg-surface outline-none transition-all"/>
          </div>

          <!-- 연락처 -->
          <div>
            <label class="block text-xs font-semibold text-primary mb-1.5">
              연락처(휴대전화) <span class="text-red-500">*</span>
            </label>
            <input type="tel" name="phone" required placeholder="예: 010-1234-5678"
                   value="<?= htmlspecialchars(Auth::user()['phone'] ?? '') ?>"
                   class="w-full bg-surface-container-low border border-outline-variant/80 rounded-xl px-4 py-3 text-xs text-on-surface focus:border-primary focus:bg-surface outline-none transition-all"/>
          </div>

          <!-- 이메일 -->
          <div>
            <label class="block text-xs font-semibold text-primary mb-1.5">
              이메일 주소 <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" required placeholder="예: author@example.com"
                   value="<?= htmlspecialchars(Auth::user()['email'] ?? '') ?>"
                   class="w-full bg-surface-container-low border border-outline-variant/80 rounded-xl px-4 py-3 text-xs text-on-surface focus:border-primary focus:bg-surface outline-none transition-all"/>
          </div>

          <!-- 출판 형태 -->
          <div>
            <label class="block text-xs font-semibold text-primary mb-1.5">
              출판 형태 <span class="text-red-500">*</span>
            </label>
            <select name="book_type" required
                    class="w-full bg-surface-container-low border border-outline-variant/80 rounded-xl px-4 py-3 text-xs text-on-surface focus:border-primary focus:bg-surface outline-none transition-all">
              <option value="단행본(국내저작)">단행본 (국내 저작/집필)</option>
              <option value="기획번역(외서)">기획 번역 (해외 원서 번역)</option>
              <option value="학술/기관도서">학술도서 / 연구소·기관 출판</option>
              <option value="전집/시리즈">전집 및 시리즈 기획</option>
              <option value="기타출판">기타 출판 의뢰</option>
            </select>
          </div>
        </div>

        <!-- 도서명(가제) -->
        <div>
          <label class="block text-xs font-semibold text-primary mb-1.5">
            도서명 (가제) 또는 기획 주제 <span class="text-red-500">*</span>
          </label>
          <input type="text" name="title" required placeholder="예: 참된 평화와 화해의 신학 (가제)"
                 class="w-full bg-surface-container-low border border-outline-variant/80 rounded-xl px-4 py-3 text-xs text-on-surface focus:border-primary focus:bg-surface outline-none transition-all"/>
        </div>

        <!-- 예상 원고량/쪽수 -->
        <div>
          <label class="block text-xs font-semibold text-primary mb-1.5">
            예상 원고량 또는 쪽수
          </label>
          <input type="text" name="page_count" placeholder="예: 200자 원고지 800매 (A4 80쪽 / 신국판 약 250쪽 예상)"
                 class="w-full bg-surface-container-low border border-outline-variant/80 rounded-xl px-4 py-3 text-xs text-on-surface focus:border-primary focus:bg-surface outline-none transition-all"/>
        </div>

        <!-- 기획 의도 및 내용 소개 -->
        <div>
          <label class="block text-xs font-semibold text-primary mb-1.5">
            기획 의도, 목차 및 저자/역자 소개 <span class="text-red-500">*</span>
          </label>
          <textarea name="content" rows="6" required
                    placeholder="도서의 기획 의도, 대상 독자층, 주요 목차 및 간략한 저자/역자 소개를 자유롭게 적어주세요."
                    class="w-full bg-surface-container-low border border-outline-variant/80 rounded-xl p-4 text-xs text-on-surface focus:border-primary focus:bg-surface outline-none transition-all leading-relaxed"></textarea>
        </div>

        <!-- 파일 첨부 -->
        <div>
          <label class="block text-xs font-semibold text-primary mb-1.5">
            원고 또는 기획안 첨부파일
          </label>
          <div class="border-2 border-dashed border-outline-variant/80 hover:border-primary rounded-2xl p-6 text-center transition-colors bg-surface-container-low/50">
            <input type="file" name="attachment" id="inquiryFile" class="hidden"
                   accept=".hwp,.hwpx,.pdf,.doc,.docx,.zip,.txt,.epub,.ppt,.pptx"
                   onchange="updateFileName(this)"/>
            <label for="inquiryFile" class="cursor-pointer flex flex-col items-center gap-2">
              <span class="material-symbols-outlined text-3xl text-secondary">cloud_upload</span>
              <span class="text-xs font-semibold text-primary" id="fileLabelText">파일을 선택하거나 여기로 드래그하세요</span>
              <span class="text-[11px] text-gray-400">지원 형식: HWP, HWPX, PDF, DOCX, ZIP 등 (최대 30MB)</span>
            </label>
          </div>
        </div>

        <!-- 캡챠 (스팸 방지) -->
        <div class="bg-surface-container-low rounded-2xl p-4 border border-outline-variant/60 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-xl text-secondary">security</span>
            <div>
              <p class="text-xs font-bold text-primary">스팸 방지 보안 퀴즈</p>
              <p class="text-[11px] text-on-surface-variant">자동 등록 방지를 위해 정답을 숫자로 입력해 주세요.</p>
            </div>
          </div>
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <span class="font-mono font-bold text-sm bg-surface px-3 py-2 rounded-lg border border-outline-variant text-secondary whitespace-nowrap">
              <?= htmlspecialchars($captchaQuestion) ?>
            </span>
            <input type="number" name="captcha" required placeholder="정답 입력"
                   class="w-24 bg-surface border border-outline-variant rounded-lg px-3 py-2 text-xs font-bold text-center text-primary focus:border-primary outline-none"/>
          </div>
        </div>

        <!-- 개인정보 수집 동의 -->
        <div class="flex items-center gap-2 text-xs text-on-surface-variant">
          <input type="checkbox" id="agreePrivacy" required class="rounded text-primary"/>
          <label for="agreePrivacy" class="cursor-pointer">
            출판 상담 및 원고 검토를 위한 개인정보 수집 및 이용에 동의합니다.
          </label>
        </div>

        <!-- 제출 버튼 -->
        <div class="pt-4">
          <button type="submit"
                  class="w-full py-4 bg-[#07131e] text-white font-bold text-sm rounded-2xl hover:bg-[#1c2833] transition-all shadow-md flex items-center justify-center gap-2 group">
            <span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">send</span>
            <span>출판 의뢰 문의 접수하기</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
function updateFileName(input) {
  const label = document.getElementById('fileLabelText');
  if (input.files && input.files[0]) {
    label.innerText = `선택된 파일: ${input.files[0].name} (${(input.files[0].size / 1024 / 1024).toFixed(2)} MB)`;
    label.classList.add('text-secondary', 'font-bold');
  } else {
    label.innerText = '파일을 선택하거나 여기로 드래그하세요';
    label.classList.remove('text-secondary', 'font-bold');
  }
}
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
