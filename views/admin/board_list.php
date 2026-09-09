<?php
/**
 * 📋 관리자 커뮤니티 게시판 목록 (Admin Board List)
 */
$typeNames = [
  'notice'  => '공지사항',
  'event'   => '대장간이벤트',
  'gallery' => '글 먹는 시간',
  'archive' => '자료실',
  'press'   => '언론보도',
];
$boardName = $typeNames[$type] ?? '게시판';
$pageTitle = $boardName . ' 관리';
$activeMenu = 'board_' . $type;
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="max-w-7xl pb-16">
  <!-- 상단 액션 바 -->
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <span><?= htmlspecialchars($boardName) ?> 관리</span>
        <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-semibold">총 <?= number_format($total ?? 0) ?>건</span>
      </h2>
      <p class="text-xs text-gray-500 mt-1">쇼핑몰 커뮤니티의 <?= htmlspecialchars($boardName) ?> 게시물을 등록, 수정 및 관리합니다.</p>
    </div>

    <div class="flex items-center gap-3">
      <a href="/community/<?= htmlspecialchars($type) ?>" target="_blank"
         class="inline-flex items-center gap-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors">
        <span class="material-symbols-outlined text-sm">open_in_new</span>
        사용자 게시판 보기
      </a>
      <a href="/admin/board/<?= htmlspecialchars($type) ?>/create"
         class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#07131e] hover:bg-[#1c2833] text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
        <span class="material-symbols-outlined text-sm">edit_note</span>
        새 글 작성
      </a>
    </div>
  </div>

  <!-- 게시글 테이블 -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold">
          <tr>
            <th class="py-3 px-4 w-16 text-center">번호</th>
            <?php if ($type === 'gallery'): ?>
              <th class="py-3 px-4 w-20 text-center">미리보기</th>
            <?php endif; ?>
            <th class="py-3 px-4">제목</th>
            <th class="py-3 px-4 w-28">작성자</th>
            <th class="py-3 px-4 w-20 text-center">조회수</th>
            <th class="py-3 px-4 w-28 text-center">등록일</th>
            <th class="py-3 px-4 w-28 text-center">관리</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
          <?php if (empty($posts)): ?>
            <tr>
              <td colspan="<?= ($type === 'gallery') ? 7 : 6 ?>" class="py-12 text-center text-gray-400 text-xs">
                등록된 게시글이 없습니다. '새 글 작성' 버튼을 눌러 첫 글을 등록해 보세요!
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($posts as $post): ?>
              <tr class="hover:bg-gray-50/80 transition-colors <?= !empty($post['is_notice']) ? 'bg-amber-50/30' : '' ?>">
                <td class="py-3.5 px-4 text-center text-gray-400 font-mono">
                  <?php if (!empty($post['is_notice'])): ?>
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">공지</span>
                  <?php else: ?>
                    <?= (int)$post['id'] ?>
                  <?php endif; ?>
                </td>

                <?php if ($type === 'gallery'): ?>
                  <td class="py-2 px-4 text-center">
                    <?php if (!empty($post['file_path'])): ?>
                      <img src="<?= htmlspecialchars($post['file_path']) ?>" class="w-12 h-9 object-cover rounded mx-auto border"/>
                    <?php else: ?>
                      <div class="w-12 h-9 bg-gray-100 rounded mx-auto flex items-center justify-center text-gray-400 text-[10px]">No img</div>
                    <?php endif; ?>
                  </td>
                <?php endif; ?>

                <td class="py-3.5 px-4">
                  <a href="/admin/board/<?= htmlspecialchars($type) ?>/<?= (int)$post['id'] ?>/edit"
                     class="font-semibold text-gray-900 hover:text-blue-600 line-clamp-1 block">
                    <?= htmlspecialchars($post['title']) ?>
                  </a>
                </td>

                <td class="py-3.5 px-4 text-gray-600">
                  <?= htmlspecialchars($post['author_name'] ?? '관리자') ?>
                </td>

                <td class="py-3.5 px-4 text-center text-gray-500 font-mono">
                  <?= number_format((int)$post['view_count']) ?>
                </td>

                <td class="py-3.5 px-4 text-center text-[11px] text-gray-400 font-mono">
                  <?= date('Y-m-d', strtotime($post['created_at'])) ?>
                </td>

                <td class="py-3.5 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <a href="/admin/board/<?= htmlspecialchars($type) ?>/<?= (int)$post['id'] ?>/edit"
                       class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-xs font-semibold transition-colors">
                      수정
                    </a>
                    <form action="/admin/board/<?= htmlspecialchars($type) ?>/<?= (int)$post['id'] ?>/delete" method="POST"
                          onsubmit="return confirm('정말 이 게시글을 삭제하시겠습니까?');" class="inline">
                      <button type="submit" class="px-2 py-1 text-red-500 hover:bg-red-50 rounded text-xs font-semibold transition-colors">
                        삭제
                      </button>
                    </form>
                  </div>
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
          <a href="/admin/board/<?= htmlspecialchars($type) ?>?page=<?= $p ?>"
             class="w-7 h-7 flex items-center justify-center rounded text-xs font-semibold <?= $page === $p ? 'bg-[#07131e] text-white' : 'bg-white border text-gray-700 hover:bg-gray-100' ?>">
            <?= $p ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
