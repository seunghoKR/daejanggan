<?php
/**
 * ✉️ 관리자 출판문의 상세 보기 및 상태 변경 (Admin Inquiry Detail)
 */
$pageTitle = '출판 문의 상세 보기';
$activeMenu = 'inquiries';
include APP_ROOT . '/views/layouts/admin_layout.php';

$statusLabels = [
  'PENDING'   => '접수 대기',
  'REVIEWING' => '원고 검토중',
  'ACCEPTED'  => '출판 수락 (계약 진행)',
  'REJECTED'  => '출판 불가 (반려)',
  'COMPLETED' => '출판 완료',
];
?>

<div class="w-full pb-16">
  <div class="mb-4">
    <a href="/admin/inquiries" class="text-xs text-gray-500 hover:text-gray-800 flex items-center gap-1">
      <span class="material-symbols-outlined text-sm">arrow_back</span>
      출판 문의 목록으로 돌아가기
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- 좌측: 의뢰 내용 상세 (2열) -->
    <div class="md:col-span-2 space-y-6">
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
          <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">
            <?= htmlspecialchars($inquiry['book_type']) ?>
          </span>
          <span class="text-xs text-gray-400 font-mono">
            접수일시: <?= date('Y-m-d H:i:s', strtotime($inquiry['created_at'])) ?>
          </span>
        </div>

        <h2 class="text-lg font-bold text-gray-900 mb-4">
          <?= htmlspecialchars($inquiry['title']) ?>
        </h2>

        <!-- 의뢰자 정보 요약 그리드 -->
        <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 gap-3 text-xs text-gray-700 mb-6 border border-gray-100">
          <div>
            <span class="text-gray-400 block mb-0.5">의뢰자/단체명</span>
            <strong class="text-gray-900 text-sm"><?= htmlspecialchars($inquiry['name']) ?></strong>
          </div>
          <div>
            <span class="text-gray-400 block mb-0.5">연락처</span>
            <a href="tel:<?= htmlspecialchars($inquiry['phone']) ?>" class="text-blue-600 font-semibold hover:underline">
              <?= htmlspecialchars($inquiry['phone']) ?>
            </a>
          </div>
          <div>
            <span class="text-gray-400 block mb-0.5">이메일</span>
            <a href="mailto:<?= htmlspecialchars($inquiry['email']) ?>" class="text-blue-600 font-semibold hover:underline">
              <?= htmlspecialchars($inquiry['email']) ?>
            </a>
          </div>
          <div>
            <span class="text-gray-400 block mb-0.5">예상 원고량/쪽수</span>
            <strong class="text-gray-900"><?= htmlspecialchars($inquiry['page_count'] ?: '미기재') ?></strong>
          </div>
        </div>

        <!-- 기획 의도 및 내용 -->
        <div>
          <h3 class="text-xs font-bold text-gray-800 mb-2">기획 의도 및 내용 소개</h3>
          <div class="p-4 bg-gray-50/70 border border-gray-100 rounded-xl text-xs text-gray-800 leading-relaxed whitespace-pre-wrap">
            <?= htmlspecialchars($inquiry['content']) ?>
          </div>
        </div>

        <!-- 첨부파일 다운로드 -->
        <?php if (!empty($inquiry['file_path'])): ?>
          <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between bg-blue-50/50 p-3.5 rounded-xl border border-blue-100">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-blue-600">attachment</span>
              <div>
                <p class="text-xs font-bold text-gray-800"><?= htmlspecialchars($inquiry['file_name'] ?: basename($inquiry['file_path'])) ?></p>
                <p class="text-[11px] text-gray-400">첨부 원고 / 기획안 파일</p>
              </div>
            </div>
            <a href="<?= htmlspecialchars($inquiry['file_path']) ?>" download
               class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-colors flex items-center gap-1 shadow-sm">
              <span class="material-symbols-outlined text-sm">download</span>
              다운로드
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- 우측: 상태 및 관리자 메모 저장 (1열) -->
    <div class="space-y-6">
      <form action="/admin/inquiries/<?= (int)$inquiry['id'] ?>/update" method="POST"
            class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <h3 class="font-bold text-sm text-gray-800 pb-2 border-b border-gray-100">진행 상태 & 관리자 메모</h3>

        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1.5 block">처리 상태</label>
          <select name="status" class="w-full border border-gray-300 rounded-lg p-2.5 text-xs text-gray-800 outline-none focus:ring-1 focus:ring-blue-500">
            <?php foreach ($statusLabels as $sKey => $sVal): ?>
              <option value="<?= $sKey ?>" <?= ($inquiry['status'] === $sKey) ? 'selected' : '' ?>>
                <?= $sVal ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="text-xs font-semibold text-gray-700 mb-1.5 block">관리자 전용 내부 메모</label>
          <textarea name="admin_memo" rows="6" placeholder="심사 의견, 연락 이력, 전달 사항 등을 기록하세요 (사용자에게 노출되지 않음)"
                    class="w-full border border-gray-300 rounded-lg p-3 text-xs text-gray-800 outline-none focus:ring-1 focus:ring-blue-500 leading-relaxed"><?= htmlspecialchars($inquiry['admin_memo'] ?? '') ?></textarea>
        </div>

        <button type="submit"
                class="w-full py-2.5 bg-[#07131e] hover:bg-[#1c2833] text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
          상태 및 메모 저장하기
        </button>
      </form>

      <!-- 문의 삭제 폼 -->
      <form action="/admin/inquiries/<?= (int)$inquiry['id'] ?>/delete" method="POST"
            onsubmit="return confirm('정말 이 출판 문의를 삭제하시겠습니까?');"
            class="text-right">
        <button type="submit" class="text-xs text-red-500 hover:underline">
          출판 문의 삭제하기
        </button>
      </form>
    </div>
  </div>
</div>
