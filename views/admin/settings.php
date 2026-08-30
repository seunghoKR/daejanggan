<?php
/**
 * 관리자 환경설정
 */
$pageTitle = '환경설정';
$activeMenu = 'settings';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<div x-data="settingsManager()" class="max-w-4xl pb-16">
  <form action="/admin/settings" method="POST" class="flex flex-col gap-6">

    <!-- 1. 쇼핑몰 기본 정보 -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
        <span class="material-symbols-outlined text-gray-700 text-lg">store</span>
        <h2 class="font-bold text-gray-800 text-sm">쇼핑몰 기본 및 사업자 정보</h2>
      </div>
      <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php
        $baseFields = [
          ['site_name',        '쇼핑몰 상호명',      'text'],
          ['ceo_name',         '대표자명',            'text'],
          ['biz_number',       '사업자등록번호',       'text'],
          ['cs_phone',         '고객센터 전화번호',    'text'],
          ['cs_hours',         '고객센터 운영시간',    'text'],
          ['email',            '대표 이메일',          'email'],
          ['bank_account',     '무통장 입금 계좌',     'text'],
          ['address',          '사업장 주소',          'text'],
          ['shipping_fee',     '기본 배송비 (원)',      'number'],
          ['free_shipping_min','무료배송 최소 금액 (원)','number'],
          ['point_rate',       '구매 적립률 (%)',       'number'],
          ['kakao_map_key',    '카카오 주소검색 API 키', 'text'],
        ];
        foreach ($baseFields as [$key, $label, $type]):
          $val = htmlspecialchars($settings[$key]['key_value'] ?? '');
        ?>
          <div>
            <label class="text-xs text-gray-500 mb-1 block"><?= $label ?></label>
            <input type="<?= $type ?>" name="<?= $key ?>" value="<?= $val ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-800 focus:ring-1 focus:ring-blue-500 outline-none"/>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 2. 실시간 알림 서비스 & 텔레그램 봇 설정 (AI 장애 경보 / 주문 알림) -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50/70 to-indigo-50/70 flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-600 text-lg">notifications_active</span>
          <h2 class="font-bold text-gray-800 text-sm">실시간 알림 서비스 & 텔레그램 봇 설정</h2>
        </div>
        <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-semibold">AI 장애 / 주문 / 회원 연동</span>
      </div>

      <div class="p-5 flex flex-col gap-5">
        <!-- 텔레그램 봇 토큰 및 수신 Chat ID -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-xs text-gray-700 font-medium mb-1 block">텔레그램 봇 토큰 (Bot Token)</label>
            <input type="text" name="telegram_bot_token" x-model="botToken"
              value="<?= htmlspecialchars($settings['telegram_bot_token']['key_value'] ?? '') ?>"
              placeholder="예: 7123456789:AAHfkjwe..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 focus:ring-1 focus:ring-blue-500 outline-none"/>
            <p class="text-[11px] text-gray-400 mt-1">@BotFather를 통해 발급받은 봇 토큰을 입력하세요.</p>
          </div>

          <div>
            <label class="text-xs text-gray-700 font-medium mb-1 block">관리자/개발자 수신 Chat ID</label>
            <input type="text" name="telegram_admin_chat_id" x-model="chatId"
              value="<?= htmlspecialchars($settings['telegram_admin_chat_id']['key_value'] ?? '') ?>"
              placeholder="예: 123456789, 987654321 (쉼표 구분)"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 focus:ring-1 focus:ring-blue-500 outline-none"/>
            <p class="text-[11px] text-gray-400 mt-1">알림을 수신할 개인 또는 그룹 Chat ID (쉼표로 다중 지정 가능)</p>
          </div>
        </div>

        <!-- 알림 수신 항목 토글 -->
        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
          <p class="text-xs font-bold text-gray-700 mb-3">📢 실시간 텔레그램 자동 발송 항목</p>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer bg-white p-2.5 rounded-lg border border-gray-200 hover:border-blue-300">
              <input type="checkbox" name="telegram_notify_ai" value="1" <?= (($settings['telegram_notify_ai']['key_value'] ?? '1') === '1') ? 'checked' : '' ?> class="rounded text-blue-600"/>
              <div>
                <span class="font-semibold block text-red-600">🤖 로컬 AI 연동 장애</span>
                <span class="text-[10px] text-gray-400">응답 끊김 시 즉시 긴급 경보</span>
              </div>
            </label>

            <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer bg-white p-2.5 rounded-lg border border-gray-200 hover:border-blue-300">
              <input type="checkbox" name="telegram_notify_order" value="1" <?= (($settings['telegram_notify_order']['key_value'] ?? '1') === '1') ? 'checked' : '' ?> class="rounded text-blue-600"/>
              <div>
                <span class="font-semibold block text-blue-600">🛒 신규 주문 접수</span>
                <span class="text-[10px] text-gray-400">주문서 발생 시 실시간 알림</span>
              </div>
            </label>

            <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer bg-white p-2.5 rounded-lg border border-gray-200 hover:border-blue-300">
              <input type="checkbox" name="telegram_notify_member" value="1" <?= (($settings['telegram_notify_member']['key_value'] ?? '1') === '1') ? 'checked' : '' ?> class="rounded text-blue-600"/>
              <div>
                <span class="font-semibold block text-emerald-600">👤 신규 회원 가입</span>
                <span class="text-[10px] text-gray-400">신규 회원 등록 시 알림</span>
              </div>
            </label>
          </div>
        </div>

        <!-- 실시간 상태 점검 및 테스트 발송 액션 바 -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
          <!-- 1) 로컬 AI 상태 점검 버튼 -->
          <div class="flex items-center gap-2">
            <button type="button" @click="checkAiHealth()" :disabled="isCheckingAi"
              class="px-3.5 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors disabled:opacity-50">
              <span class="material-symbols-outlined text-base" :class="isCheckingAi ? 'animate-spin' : ''">memory</span>
              <span x-text="isCheckingAi ? 'AI 상태 점검 중...' : '🤖 로컬 AI 연결 실시간 점검'"></span>
            </button>
            <template x-if="aiStatus">
              <span class="text-xs px-2.5 py-1 rounded-full font-mono flex items-center gap-1"
                    :class="aiStatus.is_online ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'">
                <span class="w-2 h-2 rounded-full" :class="aiStatus.is_online ? 'bg-emerald-500' : 'bg-red-500'"></span>
                <span x-text="aiStatus.is_online ? `정상 (${aiStatus.latency_ms}ms)` : '연결 끊김 (장애)'"></span>
              </span>
            </template>
          </div>

          <!-- 2) 텔레그램 테스트 메시지 발송 -->
          <div class="flex items-center gap-2">
            <button type="button" @click="sendTestTelegram()" :disabled="isTestingTelegram"
              class="px-3.5 py-2 bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors disabled:opacity-50">
              <span class="material-symbols-outlined text-base">send</span>
              <span x-text="isTestingTelegram ? '테스트 발송 중...' : '✈️ 텔레그램 테스트 알림 전송'"></span>
            </button>
          </div>
        </div>

        <!-- 피드백 메시지 표시 -->
        <template x-if="testResultMsg">
          <div class="p-3 rounded-lg text-xs font-medium border"
               :class="testResultSuccess ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'"
               x-text="testResultMsg">
          </div>
        </template>
      </div>
    </div>

    <!-- 3. 카카오 알림톡 & SMS 설정 (확장 준비 영역) -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-yellow-600 text-lg">chat</span>
          <h2 class="font-bold text-gray-800 text-sm">카카오 알림톡 & SMS 비즈니스 메시지 연동 (준비)</h2>
        </div>
        <span class="text-xs px-2.5 py-0.5 rounded-full bg-yellow-100 text-yellow-800 font-semibold">카카오 비즈메시지</span>
      </div>
      <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-xs text-gray-600 mb-1 block font-medium">카카오 REST API 키</label>
          <input type="text" name="kakao_rest_key" value="<?= htmlspecialchars($settings['kakao_rest_key']['key_value'] ?? '') ?>"
            placeholder="카카오 디벨로퍼스 REST API 키"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 outline-none focus:border-blue-500"/>
        </div>
        <div>
          <label class="text-xs text-gray-600 mb-1 block font-medium">관리자 비상 수신 휴대폰번호</label>
          <input type="text" name="kakao_admin_phone" value="<?= htmlspecialchars($settings['kakao_admin_phone']['key_value'] ?? '010-0000-0000') ?>"
            placeholder="010-0000-0000"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-800 outline-none focus:border-blue-500"/>
        </div>
      </div>
    </div>

    <!-- 4. KG이니시스 결제 설정 (영카트 연동) -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-blue-600 text-lg">credit_card</span>
          <h2 class="font-bold text-gray-800 text-sm">KG이니시스 전자결제 (PG) 설정</h2>
        </div>
        <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-semibold">영카트 이니시스 호환</span>
      </div>

      <div class="p-5 flex flex-col gap-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-xs text-gray-600 mb-1 block font-medium">KG이니시스 상점아이디 (MID) *</label>
            <input type="text" name="inicis_mid" value="<?= htmlspecialchars($settings['inicis_mid']['key_value'] ?? 'SIRdjgnbks') ?>"
              placeholder="SIRdjgnbks"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 outline-none focus:border-blue-500"/>
            <p class="text-[11px] text-gray-400 mt-0.5">영카트 발급 MID (예: SIRdjgnbks)</p>
          </div>

          <div>
            <label class="text-xs text-gray-600 mb-1 block font-medium">KG이니시스 키패스워드</label>
            <input type="text" name="inicis_keypass" value="<?= htmlspecialchars($settings['inicis_keypass']['key_value'] ?? '1111') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 outline-none focus:border-blue-500"/>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs text-gray-600 mb-1 block font-medium">KG이니시스 웹결제 사인키 (SignKey) *</label>
            <input type="text" name="inicis_signkey" value="<?= htmlspecialchars($settings['inicis_signkey']['key_value'] ?? 'NHRLWnM0bGFXTIRnbU1uRENmL29vdz0=') ?>"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 outline-none focus:border-blue-500"/>
            <p class="text-[11px] text-gray-400 mt-0.5">KG이니시스 상점관리자 > 가맹점정보 > 계약정보의 웹결제 SignKey</p>
          </div>
        </div>

        <!-- 결제 모드 및 수단 사용 여부 -->
        <div class="border-t border-gray-100 pt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <label class="text-xs text-gray-600 mb-1 block font-medium">결제 모드</label>
            <select name="inicis_test" class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-xs outline-none">
              <option value="0" <?= (($settings['inicis_test']['key_value'] ?? '0') === '0') ? 'selected' : '' ?>>실결제 모드 (운영)</option>
              <option value="1" <?= (($settings['inicis_test']['key_value'] ?? '0') === '1') ? 'selected' : '' ?>>테스트 모드 (가상결제)</option>
            </select>
          </div>

          <div class="flex items-center pt-4">
            <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
              <input type="checkbox" name="inicis_card_use" value="1" <?= (($settings['inicis_card_use']['key_value'] ?? '1') === '1') ? 'checked' : '' ?> class="rounded text-blue-600"/>
              신용카드 결제 사용
            </label>
          </div>

          <div class="flex items-center pt-4">
            <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
              <input type="checkbox" name="inicis_vbank_use" value="1" <?= (($settings['inicis_vbank_use']['key_value'] ?? '1') === '1') ? 'checked' : '' ?> class="rounded text-blue-600"/>
              가상계좌 사용
            </label>
          </div>

          <div class="flex items-center pt-4">
            <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
              <input type="checkbox" name="inicis_kakaopay" value="1" <?= (($settings['inicis_kakaopay']['key_value'] ?? '1') === '1') ? 'checked' : '' ?> class="rounded text-blue-600"/>
              카카오페이 사용
            </label>
          </div>
        </div>
      </div>

      <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
        <button type="submit"
          class="px-6 py-2.5 bg-[#07131e] text-white rounded-lg font-semibold text-xs hover:bg-[#1c2833] transition-colors shadow-sm">
          전체 환경설정 저장하기
        </button>
      </div>
    </div>

  </form>
</div>

<script>
function settingsManager() {
  return {
    botToken: '<?= addslashes($settings['telegram_bot_token']['key_value'] ?? '') ?>',
    chatId: '<?= addslashes($settings['telegram_admin_chat_id']['key_value'] ?? '') ?>',
    isCheckingAi: false,
    aiStatus: null,
    isTestingTelegram: false,
    testResultMsg: '',
    testResultSuccess: false,

    async checkAiHealth() {
      this.isCheckingAi = true;
      this.testResultMsg = '';
      try {
        const res = await fetch('/admin/notify/check-ai');
        const data = await res.json();
        this.aiStatus = data;
        if (data.is_online) {
          this.testResultMsg = `✅ 로컬 LM Studio AI 서버 응답 정상 (지연시간: ${data.latency_ms}ms, 모델: ${data.model || 'google/gemma-4-e2b'})`;
          this.testResultSuccess = true;
        } else {
          this.testResultMsg = `⚠️ 로컬 AI 서버 연결 실패 (${data.error || '응답 없음'}). 텔레그램 장애 알림 수신 대상입니다.`;
          this.testResultSuccess = false;
        }
      } catch (err) {
        this.aiStatus = { is_online: false, latency_ms: 0, error: err.message };
        this.testResultMsg = '통신 오류: ' + err.message;
        this.testResultSuccess = false;
      } finally {
        this.isCheckingAi = false;
      }
    },

    async sendTestTelegram() {
      if (!this.botToken || !this.chatId) {
        alert('텔레그램 봇 토큰과 수신 Chat ID를 먼저 입력해 주세요.');
        return;
      }
      this.isTestingTelegram = true;
      this.testResultMsg = '';
      try {
        const res = await fetch('/admin/notify/test-telegram', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            bot_token: this.botToken,
            chat_id: this.chatId
          })
        });
        const data = await res.json();
        if (data.success) {
          this.testResultMsg = '🎉 텔레그램 테스트 알림이 성공적으로 전송되었습니다! 텔레그램 앱을 확인해 보세요.';
          this.testResultSuccess = true;
        } else {
          this.testResultMsg = '❌ 텔레그램 발송 실패: ' + (data.message || '토큰이나 Chat ID를 다시 확인해 주세요.');
          this.testResultSuccess = false;
        }
      } catch (err) {
        this.testResultMsg = '통신 오류: ' + err.message;
        this.testResultSuccess = false;
      } finally {
        this.isTestingTelegram = false;
      }
    }
  };
}
</script>

  </main>
</div>
</body>
</html>
