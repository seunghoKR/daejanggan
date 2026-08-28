<?php
/**
 * 관리자 도서분류 관리
 */
$pageTitle = '도서분류 관리';
$activeMenu = 'categories';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div class="flex flex-col gap-6" x-data="{ editModal: false, editCat: {} }">

  <!-- 상단 안내 및 등록 폼 -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- 신규 도서분류 등록 폼 -->
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
      <h3 class="font-bold text-gray-800 text-sm mb-4 pb-2 border-b border-gray-100 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-base text-blue-600">add_circle</span>
        새 도서분류 등록
      </h3>

      <form action="/admin/categories/create" method="POST" class="flex flex-col gap-3">
        <div>
          <label class="text-xs text-gray-500 mb-1 block">분류 구분 (대분류 타입) *</label>
          <select name="type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500">
            <option value="SERIES">대장간 시리즈 (SERIES)</option>
            <option value="TOPIC" selected>주제별/장르별 (TOPIC)</option>
            <option value="BIGONG">도서출판비공 (BIGONG)</option>
            <option value="NICS">NICS (NICS)</option>
            <option value="GENERAL">일반/기타 (GENERAL)</option>
          </select>
        </div>

        <div>
          <label class="text-xs text-gray-500 mb-1 block">도서분류 코드 (영카트 ca_id 매핑) *</label>
          <input type="text" name="code" required placeholder="예: 103099 또는 104060"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono outline-none focus:border-blue-500"/>
          <p class="text-[11px] text-gray-400 mt-0.5">영카트 기존 코드 또는 신규 고유 코드</p>
        </div>

        <div>
          <label class="text-xs text-gray-500 mb-1 block">도서분류명 *</label>
          <input type="text" name="name" required placeholder="예: 평화학/갈등전환"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500"/>
        </div>

        <div>
          <label class="text-xs text-gray-500 mb-1 block">정렬 순서</label>
          <input type="number" name="sort_order" value="10"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none focus:border-blue-500"/>
        </div>

        <button type="submit" class="mt-2 w-full py-2.5 bg-[#07131e] text-white text-xs font-semibold rounded-lg hover:bg-[#1c2833] transition-colors">
          + 도서분류 추가하기
        </button>
      </form>
    </div>

    <!-- 도서분류 목록 테이블 -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-bold text-gray-800 text-sm">전체 도서분류 목록 (총 <?= count($categories) ?>개)</h3>
      </div>

      <div class="overflow-x-auto flex-1">
        <table class="w-full text-xs text-left">
          <thead class="bg-gray-50 text-gray-500 uppercase border-b border-gray-100">
            <tr>
              <th class="px-4 py-3">코드</th>
              <th class="px-4 py-3">타입</th>
              <th class="px-4 py-3">도서분류명</th>
              <th class="px-4 py-3 text-center">도서 수</th>
              <th class="px-4 py-3 text-center">정렬</th>
              <th class="px-4 py-3 text-center">관리</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($categories as $c):
              $typeLabels = ['SERIES'=>'시리즈', 'TOPIC'=>'주제별', 'BIGONG'=>'도서출판비공', 'NICS'=>'NICS', 'GENERAL'=>'일반'];
              $typeColors = ['SERIES'=>'bg-purple-100 text-purple-700', 'TOPIC'=>'bg-blue-100 text-blue-700', 'BIGONG'=>'bg-emerald-100 text-emerald-700', 'NICS'=>'bg-orange-100 text-orange-700', 'GENERAL'=>'bg-gray-100 text-gray-700'];
            ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-2.5 font-mono text-gray-600 font-semibold"><?= htmlspecialchars($c['code']) ?></td>
                <td class="px-4 py-2.5">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $typeColors[$c['type']] ?? 'bg-gray-100' ?>">
                    <?= $typeLabels[$c['type']] ?? $c['type'] ?>
                  </span>
                </td>
                <td class="px-4 py-2.5 font-medium text-gray-900">
                  <a href="/category/<?= htmlspecialchars($c['code']) ?>" target="_blank" class="hover:text-blue-600 hover:underline">
                    <?= htmlspecialchars($c['name']) ?>
                  </a>
                </td>
                <td class="px-4 py-2.5 text-center font-bold text-gray-700"><?= (int)$c['book_count'] ?>권</td>
                <td class="px-4 py-2.5 text-center text-gray-500"><?= (int)$c['sort_order'] ?></td>
                <td class="px-4 py-2.5 text-center whitespace-nowrap">
                  <button @click="editCat = <?= htmlspecialchars(json_encode($c)) ?>; editModal = true"
                          class="text-blue-600 hover:underline mr-2">수정</button>
                  <button onclick="deleteCat(<?= (int)$c['id'] ?>, '<?= htmlspecialchars($c['name']) ?>')"
                          class="text-red-600 hover:underline">삭제</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- 도서분류 수정 모달 -->
  <div x-show="editModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.away="editModal = false">
      <h3 class="font-bold text-gray-800 text-base mb-4">도서분류 수정</h3>

      <form :action="'/admin/categories/' + editCat.id + '/edit'" method="POST" class="flex flex-col gap-3">
        <div>
          <label class="text-xs text-gray-500 mb-1 block">분류 구분 *</label>
          <select name="type" x-model="editCat.type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none">
            <option value="SERIES">대장간 시리즈 (SERIES)</option>
            <option value="TOPIC">주제별/장르별 (TOPIC)</option>
            <option value="BIGONG">도서출판비공 (BIGONG)</option>
            <option value="NICS">NICS (NICS)</option>
            <option value="GENERAL">일반/기타 (GENERAL)</option>
          </select>
        </div>

        <div>
          <label class="text-xs text-gray-500 mb-1 block">도서분류 코드 *</label>
          <input type="text" name="code" x-model="editCat.code" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono outline-none"/>
        </div>

        <div>
          <label class="text-xs text-gray-500 mb-1 block">도서분류명 *</label>
          <input type="text" name="name" x-model="editCat.name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none"/>
        </div>

        <div>
          <label class="text-xs text-gray-500 mb-1 block">정렬 순서</label>
          <input type="number" name="sort_order" x-model="editCat.sort_order" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs outline-none"/>
        </div>

        <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-gray-100">
          <button type="button" @click="editModal = false" class="px-4 py-2 text-xs text-gray-500 hover:bg-gray-100 rounded-lg">취소</button>
          <button type="submit" class="px-5 py-2 text-xs bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700">수정 저장</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
function deleteCat(id, name) {
  if (!confirm(`'${name}' 도서분류를 정말 삭제하시겠습니까?`)) return;
  fetch(`/admin/categories/${id}/delete`, {method: 'POST'})
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        alert('도서분류가 삭제되었습니다.');
        location.reload();
      } else {
        alert(d.error || '삭제 중 오류가 발생했습니다.');
      }
    });
}
</script>

  </main>
</div>
</body>
</html>
