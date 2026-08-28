<?php
/**
 * 장바구니 & 주문서 (Stitch: 장바구니 및 결제 화면 기반)
 * @var array $items
 * @var int   $subtotal
 * @var int   $shipping
 * @var int   $total
 */
$pageTitle = '장바구니';
include APP_ROOT . '/views/layouts/header.php';
$site     = $GLOBALS['site'] ?? [];
$freeMin  = (int)($site['free_shipping_min'] ?? 30000);
?>

<main class="max-w-7xl mx-auto px-4 py-8 pb-28 md:pb-8 w-full">
  <h1 class="font-serif text-2xl font-bold text-primary mb-6">장바구니</h1>

  <?php if (empty($items)): ?>
    <!-- 빈 장바구니 -->
    <div class="flex flex-col items-center justify-center py-24 gap-4 text-on-surface-variant">
      <span class="material-symbols-outlined text-6xl opacity-30">shopping_bag</span>
      <p class="text-base font-medium">장바구니가 비어 있습니다.</p>
      <a href="/books" class="px-6 py-2.5 bg-primary text-on-primary rounded-lg text-sm font-semibold hover:bg-primary-container transition-colors">
        도서 둘러보기
      </a>
    </div>

  <?php else: ?>
  <div class="flex flex-col lg:flex-row gap-6">

    <!-- 장바구니 목록 -->
    <div class="flex-1">
      <!-- 전체 선택 -->
      <div class="flex items-center justify-between mb-3 px-1">
        <label class="flex items-center gap-2 text-sm text-on-surface-variant cursor-pointer">
          <input type="checkbox" id="selectAll" onchange="toggleAll(this)"
            class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary"/>
          전체 선택
        </label>
        <button onclick="removeSelected()" class="text-xs text-error hover:underline">선택 삭제</button>
      </div>

      <!-- 아이템 목록 -->
      <div id="cartItems" class="flex flex-col gap-3">
        <?php foreach ($items as $item): ?>
          <div class="flex gap-4 p-4 bg-surface rounded-xl border border-surface-variant items-start"
               data-book-id="<?= (int)$item['book_id'] ?>"
               data-price="<?= (int)$item['price'] ?>">

            <input type="checkbox" name="item_ids[]" value="<?= (int)$item['book_id'] ?>"
              class="mt-1 w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary cursor-pointer"/>

            <!-- 표지 -->
            <a href="/book/<?= htmlspecialchars($item['book_code'] ?? '') ?>" class="flex-shrink-0">
              <img
                src="<?= htmlspecialchars($item['cover_image'] ?? '/assets/images/default_book.png') ?>"
                alt="<?= htmlspecialchars($item['title']) ?>"
                class="w-16 h-22 object-cover rounded-lg border border-surface-variant"
                onerror="this.src='/assets/images/default_book.png'"
              />
            </a>

            <!-- 정보 -->
            <div class="flex-1 min-w-0 flex flex-col gap-1">
              <a href="/book/<?= htmlspecialchars($item['book_code'] ?? '') ?>"
                 class="font-serif font-semibold text-primary text-sm line-clamp-2 hover:text-secondary transition-colors">
                <?= htmlspecialchars($item['title']) ?>
              </a>
              <p class="text-xs text-on-surface-variant"><?= htmlspecialchars($item['author']) ?></p>

              <?php if (($item['status'] ?? '') === 'SOLDOUT'): ?>
                <span class="text-xs text-error font-medium">품절</span>
              <?php endif; ?>

              <!-- 수량 조절 & 가격 -->
              <div class="flex items-center justify-between mt-2">
                <div class="flex items-center border border-outline-variant rounded-lg overflow-hidden">
                  <button onclick="updateQty(<?= (int)$item['book_id'] ?>, -1)"
                    class="px-3 py-1.5 text-on-surface-variant hover:bg-surface-variant transition-colors text-sm">−</button>
                  <input type="number" value="<?= (int)$item['quantity'] ?>" min="1"
                    id="qty-<?= (int)$item['book_id'] ?>"
                    onchange="setQty(<?= (int)$item['book_id'] ?>, this.value)"
                    class="w-10 text-center border-none text-sm text-on-surface py-1.5 outline-none"/>
                  <button onclick="updateQty(<?= (int)$item['book_id'] ?>, 1)"
                    class="px-3 py-1.5 text-on-surface-variant hover:bg-surface-variant transition-colors text-sm">+</button>
                </div>

                <span class="font-bold text-primary text-sm" id="price-<?= (int)$item['book_id'] ?>">
                  <?= number_format((int)$item['price'] * (int)$item['quantity']) ?>원
                </span>
              </div>
            </div>

            <!-- 삭제 -->
            <button onclick="removeItem(<?= (int)$item['book_id'] ?>)"
              class="text-on-surface-variant hover:text-error transition-colors p-1">
              <span class="material-symbols-outlined text-xl">close</span>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 주문 요약 -->
    <aside class="lg:w-72 shrink-0">
      <div class="bg-surface-container-low rounded-xl p-5 border border-outline-variant sticky top-20">
        <h3 class="font-serif font-bold text-primary mb-4">결제 금액</h3>

        <div class="flex flex-col gap-2 text-sm">
          <div class="flex justify-between">
            <span class="text-on-surface-variant">상품 금액</span>
            <span id="subtotalDisplay" class="text-on-surface font-medium"><?= number_format($subtotal) ?>원</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">배송비</span>
            <span id="shippingDisplay" class="text-on-surface font-medium">
              <?= $shipping > 0 ? number_format($shipping) . '원' : '<span class="text-green-600 font-semibold">무료</span>' ?>
            </span>
          </div>
          <?php if ($shipping > 0): ?>
            <p class="text-xs text-on-surface-variant">
              <?= number_format($freeMin - $subtotal) ?>원 더 담으면 무료배송!
            </p>
          <?php endif; ?>
        </div>

        <div class="border-t border-outline-variant my-4"></div>

        <div class="flex justify-between items-center">
          <span class="font-semibold text-on-surface">총 결제 금액</span>
          <span id="totalDisplay" class="text-xl font-bold text-primary"><?= number_format($total) ?>원</span>
        </div>

        <!-- 무료 배송 진행 바 -->
        <?php if ($shipping > 0 && $freeMin > 0): ?>
          <div class="mt-3">
            <div class="h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
              <div class="h-full bg-secondary rounded-full transition-all duration-300"
                   style="width: <?= min(100, round($subtotal / $freeMin * 100)) ?>%"></div>
            </div>
          </div>
        <?php endif; ?>

        <!-- 주문하기 버튼 -->
        <?php if (Auth::check()): ?>
          <a href="/order/checkout"
             class="mt-4 w-full block py-3.5 bg-secondary text-on-secondary rounded-lg font-bold text-center text-sm hover:opacity-90 transition-opacity shadow-md">
            주문하기
          </a>
        <?php else: ?>
          <a href="/login?redirect=/order/checkout"
             class="mt-4 w-full block py-3.5 bg-secondary text-on-secondary rounded-lg font-bold text-center text-sm hover:opacity-90 transition-opacity shadow-md">
            로그인하고 주문하기
          </a>
          <p class="text-xs text-on-surface-variant text-center mt-2">비회원도 주문 가능합니다</p>
        <?php endif; ?>

        <a href="/books" class="mt-2 w-full block py-2.5 border border-outline-variant text-on-surface-variant rounded-lg text-center text-sm hover:bg-surface-variant transition-colors">
          쇼핑 계속하기
        </a>
      </div>
    </aside>
  </div>
  <?php endif; ?>
</main>

<!-- 모바일 하단 고정 주문 버튼 -->
<?php if (!empty($items)): ?>
<div class="fixed bottom-16 md:hidden w-full left-0 px-4 pb-3 bg-gradient-to-t from-surface via-surface pt-4 z-40">
  <div class="flex items-center justify-between mb-2 px-1">
    <span class="text-sm text-on-surface-variant">총 결제</span>
    <span class="font-bold text-primary" id="mTotalDisplay"><?= number_format($total) ?>원</span>
  </div>
  <?php if (Auth::check()): ?>
    <a href="/order/checkout" class="block w-full py-3.5 bg-secondary text-on-secondary rounded-lg font-bold text-center text-sm shadow-lg">
      주문하기
    </a>
  <?php else: ?>
    <a href="/login" class="block w-full py-3.5 bg-secondary text-on-secondary rounded-lg font-bold text-center text-sm shadow-lg">
      로그인하고 주문하기
    </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

<script>
function updateQty(bookId, delta) {
  const input = document.getElementById(`qty-${bookId}`);
  const newQty = Math.max(1, parseInt(input.value) + delta);
  input.value = newQty;
  setQty(bookId, newQty);
}

function setQty(bookId, qty) {
  qty = Math.max(1, parseInt(qty));
  fetch('/cart/update', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `book_id=${bookId}&qty=${qty}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // 단가 × 수량 업데이트
      const row   = document.querySelector(`[data-book-id="${bookId}"]`);
      const price = parseInt(row.dataset.price);
      document.getElementById(`price-${bookId}`).textContent = (price * qty).toLocaleString() + '원';
      // 합계 업데이트
      document.getElementById('subtotalDisplay').textContent = data.subtotal + '원';
      document.getElementById('shippingDisplay').textContent = data.shipping === '0' ? '<span class="text-green-600 font-semibold">무료</span>' : data.shipping + '원';
      document.getElementById('totalDisplay').textContent    = data.total + '원';
      const mTotal = document.getElementById('mTotalDisplay');
      if (mTotal) mTotal.textContent = data.total + '원';
    }
  });
}

function removeItem(bookId) {
  if (!confirm('장바구니에서 삭제하시겠습니까?')) return;
  fetch('/cart/remove', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: `book_id=${bookId}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.querySelector(`[data-book-id="${bookId}"]`).remove();
      if (!document.querySelector('[data-book-id]')) location.reload();
    }
  });
}

function toggleAll(cb) {
  document.querySelectorAll('input[name="item_ids[]"]').forEach(el => el.checked = cb.checked);
}

function removeSelected() {
  const selected = [...document.querySelectorAll('input[name="item_ids[]"]:checked')]
    .map(el => el.value);
  if (!selected.length) { alert('삭제할 도서를 선택해 주세요.'); return; }
  if (!confirm(`선택한 ${selected.length}권을 삭제하시겠습니까?`)) return;
  Promise.all(selected.map(id =>
    fetch('/cart/remove', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `book_id=${id}`
    }).then(r => r.json())
  )).then(() => location.reload());
}
</script>
