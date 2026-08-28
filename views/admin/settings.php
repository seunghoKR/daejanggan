<?php
/**
 * 관리자 환경설정
 */
$pageTitle = '환경설정';
$activeMenu = 'settings';
include APP_ROOT . '/views/layouts/admin_layout.php';
?>

<form action="/admin/settings" method="POST" class="max-w-4xl flex flex-col gap-6">

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

  <!-- 2. KG이니시스 결제 설정 (영카트 연동) -->
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

  </main>
</div>
</body>
</html>
