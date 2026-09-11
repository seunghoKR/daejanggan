<?php
/**
 * ✉️ 관리자 출판문의 목록 (Admin Inquiries List)
 */
$pageTitle = '출판 의뢰 문의 관리';
$activeMenu = 'inquiries';
include APP_ROOT . '/views/layouts/admin_layout.php';

$statusLabels = [
  'PENDING'   => ['대기',   'bg-amber-100 text-amber-800 border-amber-200'],
  'REVIEWING' => ['검토중', 'bg-blue-100 text-blue-800 border-blue-200'],
  'ACCEPTED'  => ['수락(진행)', 'bg-emerald-100 text-emerald-800 border-emerald-200'],
  'REJECTED'  => ['반려',   'bg-gray-100 text-gray-700 border-gray-200'],
  'COMPLETED' => ['완료',   'bg-purple-100 text-purple-800 border-purple-200'],
];
?>

<div class="w-full pb-16">
  <!-- 상단 액션 바 -->
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <span>출판 의뢰 문의 목록</span>
        <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-semibold">총 <?= number_format($total ?? 0) ?>건</span>
      </h2>
      <p class="text-xs text-gray-500 mt-1">사용자가 접수한 출판 기획 및 원고 의뢰 내역을 확인하고 처리 상태를 관리합니다.</p>
    </div>

    <!-- 상태 필터 -->
    <div class="flex flex-wrap items-center gap-2">
      <a href="/admin/inquiries"
         class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors <?= empty($status) ? 'bg-[#07131e] text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
        전체 보기
      </a>
      <?php foreach ($statusLabels as $stKey => [$stName, $stClass]): ?>
        <a href="/admin/inquiries?status=<?= $stKey ?>"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors <?= ($status === $stKey) ? 'bg-[#07131e] text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
          <?= $stName ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 문의 목록 테이블 -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold">
          <tr>
            <th class="py-3 px-4 w-14 text-center">번호</th>
            <th class="py-3 px-4 w-28 text-center">상태</th>
            <th class="py-3 px-4 w-28">출판형태</th>
            <th class="py-3 px-4">도서명 (가제) / 기획주제</th>
            <th class="py-3 px-4 w-32">의뢰자 / 연락처</th>
            <th class="py-3 px-4 w-20 text-center">첨부파일</th>
            <th class="py-3 px-4 w-28 text-center">접수일시</th>
            <th class="py-3 px-4 w-20 text-center">관리</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
          <?php if (empty($inquiries)): ?>
            <tr>
              <td colspan="8" class="py-12 text-center text-gray-400 text-xs">접수된 출판 의뢰 문의가 없습니다.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($inquiries as $inq):
              $st = $statusLabels[$inq['status']] ?? ['대기', 'bg-gray-100 text-gray-700'];
            ?>
              <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="py-3.5 px-4 text-center text-gray-400 font-mono"><?= (int)$inq['id'] ?></td>
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $st[1] ?>">
                    <?= $st[0] ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 font-medium text-gray-800">
                  <?= htmlspecialchars($inq['book_type']) ?>
                </td>
                <td class="py-3.5 px-4">
                  <a href="/admin/inquiries/<?= (int)$inq['id'] ?>" class="font-semibold text-gray-900 hover:text-blue-600 block line-clamp-1">
                    <?= htmlspecialchars($inq['title']) ?>
                  </a>
                  <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">
                    <?= htmlspecialchars(mb_substr(strip_tags($inq['content']), 0, 80)) ?>
                  </p>
                </td>
                <td class="py-3.5 px-4">
                  <span class="font-semibold text-gray-900 block"><?= htmlspecialchars($inq['name']) ?></span>
                  <span class="text-[11px] text-gray-500 font-mono"><?= htmlspecialchars($inq['phone']) ?></span>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <?php if (!empty($inq['file_path'])): ?>
                    <a href="<?= htmlspecialchars($inq['file_path']) ?>" target="_blank"
                       class="inline-flex items-center text-blue-600 hover:text-blue-800 text-xs gap-0.5" title="첨부파일 다운로드">
                      <span class="material-symbols-outlined text-base">download</span>
                    </a>
                  <?php else: ?>
                    <span class="text-gray-300">-</span>
                  <?php endif; ?>
                </td>
                <td class="py-3.5 px-4 text-center text-[11px] text-gray-500 font-mono">
                  <?= date('Y-m-d H:i', strtotime($inq['created_at'])) ?>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <a href="/admin/inquiries/<?= (int)$inq['id'] ?>"
                     class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs font-semibold transition-colors">
                    상세보기
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- 페이징 -->
    <?php if ($totalPages > 1): ?>
      <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-center gap-1">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <a href="/admin/inquiries?page=<?= $p ?>&status=<?= urlencode($status ?? '') ?>"
             class="w-7 h-7 flex items-center justify-center rounded text-xs font-semibold <?= $page === $p ? 'bg-[#07131e] text-white' : 'bg-white border text-gray-700 hover:bg-gray-100' ?>">
            <?= $p ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
