<?php
/**
 * 관리자 회원 관리
 * 회원 등급/그룹 분류, 상세 정보 수정, 스팸 계정 개별 및 일괄 삭제 지원
 */
$pageTitle = '회원 관리';
$activeMenu = 'members';
include APP_ROOT . '/views/layouts/admin_layout.php';

$q           = htmlspecialchars($_GET['q'] ?? '');
$curGroup    = htmlspecialchars($_GET['group'] ?? '');
$curStatus   = htmlspecialchars($_GET['status'] ?? '');
?>

<div x-data="memberManager()" class="flex flex-col gap-5 pb-16">

  <!-- 1. 상단 타이틀 & 통계 & 일괄 관리 바 -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
        <span class="material-symbols-outlined text-xl">group</span>
      </div>
      <div>
        <h1 class="text-base font-bold text-gray-900">회원 및 그룹 관리</h1>
        <p class="text-xs text-gray-500 mt-0.5">총 <span class="font-bold text-blue-600"><?= number_format($total) ?></span>명의 회원이 등록되어 있습니다.</p>
      </div>
    </div>

    <!-- 일괄 액션 버튼 (선택 시 활성화) -->
    <div class="flex items-center gap-2">
      <template x-if="selectedIds.length > 0">
        <div class="flex items-center gap-2 bg-red-50 border border-red-200 px-3 py-1.5 rounded-xl">
          <span class="text-xs font-semibold text-red-700" x-text="`${selectedIds.length}명 선택됨`"></span>
          <button type="button" @click="deleteSelected()"
                  class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">delete_sweep</span>
            선택 회원 일괄 삭제 (스팸 정리)
          </button>
        </div>
      </template>
    </div>
  </div>

  <!-- 2. 검색 & 등급/상태 필터 바 -->
  <form method="GET" action="/admin/members" class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-wrap items-center gap-3">
    <!-- 검색창 -->
    <div class="flex-1 min-w-[220px] relative">
      <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
      <input type="text" name="q" value="<?= $q ?>" placeholder="아이디, 실명, 닉네임, 이메일, 전화번호 검색"
             class="w-full pl-9 pr-3 py-2 text-xs border border-gray-300 rounded-lg outline-none focus:border-blue-500 bg-gray-50/50 focus:bg-white transition-all"/>
    </div>

    <!-- 회원 등급/그룹 필터 -->
    <div class="w-40">
      <select name="group" class="w-full py-2 px-3 text-xs border border-gray-300 rounded-lg outline-none focus:border-blue-500 bg-white">
        <option value="">전체 등급/그룹</option>
        <?php foreach ($allGroups as $grp): ?>
          <option value="<?= htmlspecialchars($grp) ?>" <?= ($curGroup === $grp) ? 'selected' : '' ?>>
            <?= htmlspecialchars($grp) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- 상태 필터 -->
    <div class="w-32">
      <select name="status" class="w-full py-2 px-3 text-xs border border-gray-300 rounded-lg outline-none focus:border-blue-500 bg-white">
        <option value="">전체 상태</option>
        <option value="ACTIVE" <?= ($curStatus === 'ACTIVE') ? 'selected' : '' ?>>정상 회원</option>
        <option value="BLOCKED" <?= ($curStatus === 'BLOCKED') ? 'selected' : '' ?>>차단/스팸</option>
        <option value="WITHDRAWN" <?= ($curStatus === 'WITHDRAWN') ? 'selected' : '' ?>>탈퇴 회원</option>
      </select>
    </div>

    <button type="submit" class="px-4 py-2 bg-[#07131e] hover:bg-[#1c2833] text-white rounded-lg text-xs font-semibold transition-colors">
      검색/필터 적용
    </button>

    <?php if ($q !== '' || $curGroup !== '' || $curStatus !== ''): ?>
      <a href="/admin/members" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs transition-colors">
        필터 초기화
      </a>
    <?php endif; ?>
  </form>

  <!-- 3. 회원 목록 테이블 -->
  <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-200">
          <tr>
            <th class="px-4 py-3 text-center w-10">
              <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected()" class="rounded text-blue-600"/>
            </th>
            <th class="px-4 py-3">아이디 / 계정</th>
            <th class="px-4 py-3">실명 (닉네임)</th>
            <th class="px-4 py-3 text-center">회원 등급/그룹</th>
            <th class="px-4 py-3">이메일</th>
            <th class="px-4 py-3">연락처</th>
            <th class="px-4 py-3 text-center">상태</th>
            <th class="px-4 py-3 text-right">보유 적립금</th>
            <th class="px-4 py-3 text-center">가입일시</th>
            <th class="px-4 py-3 text-center">관리 / 액션</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-xs">
          <?php if (empty($members)): ?>
            <tr>
              <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                <span class="material-symbols-outlined text-3xl mb-1 text-gray-300 block">search_off</span>
                조건에 일치하는 회원이 없습니다.
              </td>
            </tr>
          <?php endif; ?>

          <?php foreach ($members as $m): ?>
            <?php
              $mId = (int)$m['id'];
              $grp = $m['member_group'] ?? '일반회원';
              $sts = $m['status'] ?? 'ACTIVE';
              $isAdmin = ($m['role'] ?? 'USER') === 'ADMIN';

              // 그룹별 배지 색상
              $grpBadgeClass = match($grp) {
                '특별회원' => 'bg-purple-100 text-purple-800 border-purple-200',
                '우수회원' => 'bg-amber-100 text-amber-800 border-amber-200',
                '저자/필진' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                '도서관/기관' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                default => 'bg-gray-100 text-gray-700 border-gray-200',
              };
            ?>
            <tr class="hover:bg-blue-50/30 transition-colors <?= ($sts === 'BLOCKED') ? 'bg-red-50/30 text-gray-400' : '' ?>">
              <!-- 선택 체크박스 -->
              <td class="px-4 py-3 text-center">
                <?php if (!$isAdmin): ?>
                  <input type="checkbox" value="<?= $mId ?>" :checked="selectedIds.includes(<?= $mId ?>)" @change="toggleSelect(<?= $mId ?>)" class="rounded text-blue-600"/>
                <?php endif; ?>
              </td>

              <!-- 아이디 -->
              <td class="px-4 py-3 font-mono font-bold text-gray-800">
                <div class="flex items-center gap-1.5">
                  <span><?= htmlspecialchars($m['username']) ?></span>
                  <?php if ($isAdmin): ?>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white">관리자</span>
                  <?php endif; ?>
                </div>
              </td>

              <!-- 실명 (닉네임) -->
              <td class="px-4 py-3 font-medium text-gray-900">
                <span><?= htmlspecialchars($m['name']) ?></span>
                <?php if (!empty($m['nickname']) && $m['nickname'] !== $m['name']): ?>
                  <span class="text-gray-400 block font-normal">(<?= htmlspecialchars($m['nickname']) ?>)</span>
                <?php endif; ?>
              </td>

              <!-- 회원 등급/그룹 -->
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold border <?= $grpBadgeClass ?>">
                  <?= htmlspecialchars($grp) ?>
                </span>
              </td>

              <!-- 이메일 -->
              <td class="px-4 py-3 text-gray-600 font-mono">
                <?= htmlspecialchars($m['email']) ?>
              </td>

              <!-- 연락처 -->
              <td class="px-4 py-3 text-gray-600 font-mono">
                <?= htmlspecialchars($m['phone'] ?? '-') ?>
              </td>

              <!-- 상태 -->
              <td class="px-4 py-3 text-center">
                <?php if ($sts === 'ACTIVE'): ?>
                  <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 정상
                  </span>
                <?php elseif ($sts === 'BLOCKED'): ?>
                  <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded-md border border-red-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> 차단됨
                  </span>
                <?php else: ?>
                  <span class="text-gray-400 text-[11px]">탈퇴</span>
                <?php endif; ?>
              </td>

              <!-- 보유 적립금 -->
              <td class="px-4 py-3 text-right font-bold text-gray-800 font-mono">
                <?= number_format((int)$m['points']) ?> P
              </td>

              <!-- 가입일시 -->
              <td class="px-4 py-3 text-center text-gray-400 font-mono text-[11px]">
                <?= date('Y.m.d', strtotime($m['created_at'])) ?>
              </td>

              <!-- 관리 액션 -->
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- 수정/관리 모달 열기 -->
                  <button type="button" @click="openEditModal(<?= $mId ?>)"
                          class="px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg font-semibold border border-blue-200 transition-colors flex items-center gap-1" title="회원 정보 및 그룹 수정">
                    <span class="material-symbols-outlined text-xs">edit_note</span>
                    관리
                  </button>

                  <!-- 단일 삭제 (스팸 계정 정리) -->
                  <?php if (!$isAdmin): ?>
                    <button type="button" @click="deleteMember(<?= $mId ?>, '<?= htmlspecialchars($m['username']) ?>')"
                            class="p-1 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg border border-red-200 transition-colors" title="스팸 회원 삭제">
                      <span class="material-symbols-outlined text-sm">delete</span>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 4. 페이지네이션 -->
  <?php if ($totalPages > 1):
    $window = 7;
    $startPage = max(1, $page - (int)floor($window / 2));
    $endPage = min($totalPages, $startPage + $window - 1);
    if ($endPage - $startPage + 1 < $window) {
        $startPage = max(1, $endPage - $window + 1);
    }
    $queryStr = http_build_query(array_filter([
      'q'      => $_GET['q'] ?? '',
      'group'  => $_GET['group'] ?? '',
      'status' => $_GET['status'] ?? '',
    ]));
    $prefix = '/admin/members?' . ($queryStr ? $queryStr . '&' : '');
  ?>
    <nav class="flex items-center justify-center gap-1.5 mt-4" aria-label="Pagination">
      <?php if ($page > 1): ?>
        <a href="<?= $prefix ?>page=1" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold">«</a>
        <a href="<?= $prefix ?>page=<?= $page - 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold">‹</a>
      <?php endif; ?>

      <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <a href="<?= $prefix ?>page=<?= $i ?>"
           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                  <?= $i === $page ? 'bg-[#07131e] text-white font-bold shadow-sm' : 'border border-gray-300 text-gray-700 hover:bg-gray-100' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="<?= $prefix ?>page=<?= $page + 1 ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold">›</a>
        <a href="<?= $prefix ?>page=<?= $totalPages ?>" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 text-xs font-bold">»</a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>

  <!-- 5. 회원 상세 정보 수정 / 그룹 변경 모달 (Alpine.js) -->
  <div x-show="isEditModalOpen" style="display: none;"
       class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-sm"
       @keydown.escape.window="isEditModalOpen = false">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-200 relative flex flex-col gap-4"
         @click.away="isEditModalOpen = false">
      
      <!-- 모달 헤더 -->
      <div class="flex items-center justify-between pb-3 border-b border-gray-100">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-600 text-xl">manage_accounts</span>
          <h2 class="font-bold text-gray-900 text-base">회원 정보 및 그룹 관리</h2>
        </div>
        <button type="button" @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
          <span class="material-symbols-outlined text-lg">close</span>
        </button>
      </div>

      <!-- 모달 폼 -->
      <template x-if="editingUser">
        <div class="flex flex-col gap-4 text-xs">
          <!-- 계정 아이디 & 역할 -->
          <div class="grid grid-cols-2 gap-3 bg-gray-50 p-3 rounded-xl border border-gray-200">
            <div>
              <span class="text-gray-500 block mb-0.5 font-medium">아이디</span>
              <span class="font-mono font-bold text-gray-900 text-sm" x-text="editingUser.username"></span>
            </div>
            <div>
              <span class="text-gray-500 block mb-0.5 font-medium">관리 권한</span>
              <select x-model="editingUser.role" class="w-full border border-gray-300 rounded px-2 py-1 bg-white font-medium">
                <option value="USER">일반 사용자 (USER)</option>
                <option value="ADMIN">관리자 (ADMIN)</option>
              </select>
            </div>
          </div>

          <!-- 회원 등급/그룹 & 상태 -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-bold text-gray-700 block mb-1">🏷️ 회원 그룹 / 등급 *</label>
              <div class="flex flex-col gap-1.5">
                <select x-model="editingUser.member_group" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white text-xs font-semibold text-blue-700">
                  <option value="일반회원">일반회원</option>
                  <option value="특별회원">특별회원 (우대)</option>
                  <option value="우수회원">우수회원 (VIP)</option>
                  <option value="저자/필진">저자/필진</option>
                  <option value="도서관/기관">도서관/기관</option>
                  <option value="custom">직접 입력 (새 그룹명)</option>
                </select>
                <template x-if="editingUser.member_group === 'custom' || !['일반회원','특별회원','우수회원','저자/필진','도서관/기관'].includes(editingUser.member_group)">
                  <input type="text" x-model="editingUser.custom_group" placeholder="새로운 그룹명 입력"
                         class="w-full border border-blue-300 rounded-lg px-3 py-1.5 bg-blue-50 text-xs font-semibold"/>
                </template>
              </div>
            </div>

            <div>
              <label class="font-bold text-gray-700 block mb-1">🛡️ 계정 상태 *</label>
              <select x-model="editingUser.status" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white text-xs font-semibold"
                      :class="editingUser.status === 'BLOCKED' ? 'text-red-600 bg-red-50' : 'text-gray-800'">
                <option value="ACTIVE">✅ 정상 이용</option>
                <option value="BLOCKED">🚫 계정 차단 (스팸 차단)</option>
                <option value="WITHDRAWN">🚪 탈퇴 처리</option>
              </select>
            </div>
          </div>

          <!-- 기본 정보: 실명, 닉네임, 연락처, 이메일 -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-medium text-gray-600 block mb-1">실명 (이름) *</label>
              <input type="text" x-model="editingUser.name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs"/>
            </div>
            <div>
              <label class="font-medium text-gray-600 block mb-1">닉네임</label>
              <input type="text" x-model="editingUser.nickname" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs"/>
            </div>
            <div>
              <label class="font-medium text-gray-600 block mb-1">휴대전화번호</label>
              <input type="text" x-model="editingUser.phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs"/>
            </div>
            <div>
              <label class="font-medium text-gray-600 block mb-1">이메일 주소</label>
              <input type="email" x-model="editingUser.email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs"/>
            </div>
          </div>

          <!-- 적립금 & 비밀번호 재설정 -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-medium text-gray-600 block mb-1">보유 적립금 (Point)</label>
              <input type="number" x-model="editingUser.points" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono font-bold"/>
            </div>
            <div>
              <label class="font-medium text-gray-600 block mb-1">비밀번호 변경 (선택)</label>
              <input type="password" x-model="editingUser.password" placeholder="변경 시에만 입력" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs"/>
            </div>
          </div>

          <!-- 관리자 메모 -->
          <div>
            <label class="font-medium text-gray-600 block mb-1">📝 관리자 전용 메모</label>
            <textarea x-model="editingUser.admin_memo" rows="2" placeholder="특별회원 혜택 적용 내역, 상담 메모 등 (회원에게 보이지 않음)"
                      class="w-full border border-gray-300 rounded-lg p-2.5 text-xs outline-none focus:border-blue-500"></textarea>
          </div>

          <!-- 에러/알림 피드백 -->
          <template x-if="modalMsg">
            <div class="p-2.5 rounded-lg text-xs" :class="modalSuccess ? 'bg-emerald-50 text-emerald-800' : 'bg-red-50 text-red-800'" x-text="modalMsg"></div>
          </template>

          <!-- 버튼 바 -->
          <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <button type="button" @click="isEditModalOpen = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
              닫기
            </button>
            <button type="button" @click="saveMember()" :disabled="isSaving"
                    class="px-5 py-2 bg-[#07131e] text-white rounded-lg font-bold hover:bg-[#1c2833] transition-colors shadow-sm disabled:opacity-50">
              <span x-text="isSaving ? '저장 중...' : '회원 정보 저장하기'"></span>
            </button>
          </div>
        </div>
      </template>
    </div>
  </div>

</div>

<script>
function memberManager() {
  return {
    selectedIds: [],
    isEditModalOpen: false,
    editingUser: null,
    isSaving: false,
    modalMsg: '',
    modalSuccess: false,
    allMemberIds: <?= json_encode(array_map(fn($m) => (int)$m['id'], array_filter($members, fn($m) => ($m['role'] ?? 'USER') !== 'ADMIN'))) ?>,

    toggleSelect(id) {
      const idx = this.selectedIds.indexOf(id);
      if (idx > -1) {
        this.selectedIds.splice(idx, 1);
      } else {
        this.selectedIds.push(id);
      }
    },

    toggleSelectAll(e) {
      if (e.target.checked) {
        this.selectedIds = [...this.allMemberIds];
      } else {
        this.selectedIds = [];
      }
    },

    isAllSelected() {
      return this.allMemberIds.length > 0 && this.selectedIds.length === this.allMemberIds.length;
    },

    async openEditModal(userId) {
      this.modalMsg = '';
      try {
        const res = await fetch(`/admin/members/${userId}`);
        const data = await res.json();
        if (data.success) {
          this.editingUser = data.data;
          this.editingUser.password = '';
          this.isEditModalOpen = true;
        } else {
          alert(data.message || '회원 정보를 불러오지 못했습니다.');
        }
      } catch (err) {
        alert('통신 오류: ' + err.message);
      }
    },

    async saveMember() {
      if (!this.editingUser) return;
      this.isSaving = true;
      this.modalMsg = '';

      // 커스텀 그룹명 처리
      let groupName = this.editingUser.member_group;
      if (groupName === 'custom' && this.editingUser.custom_group) {
        groupName = this.editingUser.custom_group.trim();
      }

      const payload = {
        ...this.editingUser,
        member_group: groupName,
      };

      try {
        const res = await fetch(`/admin/members/${this.editingUser.id}/update`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
          alert('회원 정보가 성공적으로 저장되었습니다.');
          this.isEditModalOpen = false;
          location.reload();
        } else {
          this.modalMsg = data.message || '저장 실패';
          this.modalSuccess = false;
        }
      } catch (err) {
        this.modalMsg = '통신 오류: ' + err.message;
        this.modalSuccess = false;
      } finally {
        this.isSaving = false;
      }
    },

    async deleteMember(userId, username) {
      if (!confirm(`[${username}] 회원을 정말로 삭제하시겠습니까?\n\n(스팸 계정은 삭제 시 모든 데이터가 완전히 정리됩니다)`)) {
        return;
      }

      try {
        const res = await fetch(`/admin/members/${userId}/delete`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
          alert(data.message || '회원이 삭제되었습니다.');
          location.reload();
        } else {
          alert(data.message || '삭제에 실패했습니다.');
        }
      } catch (err) {
        alert('통신 오류: ' + err.message);
      }
    },

    async deleteSelected() {
      if (this.selectedIds.length === 0) return;
      if (!confirm(`선택한 ${this.selectedIds.length}명의 회원을 일괄 삭제하시겠습니까?\n\n(스팸 봇 계정들을 한 번에 깨끗이 정리할 수 있습니다)`)) {
        return;
      }

      try {
        const res = await fetch(`/admin/members/batch-delete`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ids: this.selectedIds })
        });
        const data = await res.json();
        if (data.success) {
          alert(data.message || '선택한 회원이 모두 삭제되었습니다.');
          location.reload();
        } else {
          alert(data.message || '일괄 삭제 중 오류가 발생했습니다.');
        }
      } catch (err) {
        alert('통신 오류: ' + err.message);
      }
    }
  };
}
</script>

  </main>
</div>
</body>
</html>
