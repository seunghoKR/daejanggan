<?php

declare(strict_types=1);

/**
 * Front Controller — 진입점 (index.php)
 */

// ============================================================
// 부트스트랩
// ============================================================
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Cart.php';
require_once __DIR__ . '/core/Captcha.php';
require_once __DIR__ . '/core/FileUploader.php';

// ============================================================
// 컨트롤러 로드
// ============================================================
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/BookController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/AdminController.php';

// ============================================================
// 사이트 설정 전역 로드
// ============================================================
try {
    $GLOBALS['site'] = array_column(
        Database::fetchAll("SELECT key_name, key_value FROM site_settings"),
        'key_value', 'key_name'
    );
} catch (\Throwable $e) {
    $GLOBALS['site'] = [];
}

// ============================================================
// 라우팅 정의
// ============================================================

// --- 홈 & 검색 ---
Router::get('/',                      [HomeController::class, 'index']);
Router::get('/search',                [HomeController::class, 'search']);

// --- 도서 ---
Router::get('/books',                 [BookController::class, 'index']);
Router::get('/book/:code',            [BookController::class, 'detail']);
Router::get('/category/:code',        [BookController::class, 'category']);
Router::get('/series/:id',            [BookController::class, 'series']);
Router::get('/authors',               [BookController::class, 'authors']);
Router::get('/author/:name',          [BookController::class, 'authors']);
Router::post('/book/:id/review',      [BookController::class, 'addReview']);

// --- 장바구니 & 주문 ---
Router::get('/cart',                  [OrderController::class, 'cart']);
Router::post('/cart/add',             [OrderController::class, 'addToCart']);
Router::post('/cart/update',          [OrderController::class, 'updateCart']);
Router::post('/cart/remove',          [OrderController::class, 'removeFromCart']);
Router::get('/order/checkout',        [OrderController::class, 'checkout']);
Router::post('/order/place',          [OrderController::class, 'place']);
Router::post('/order/inicis/return',  [OrderController::class, 'inicisReturn']);
Router::get('/order/inicis/close',    [OrderController::class, 'inicisClose']);
Router::get('/order/complete/:no',    [OrderController::class, 'complete']);
Router::get('/order/lookup',          [OrderController::class, 'lookup']);
Router::post('/order/lookup',         [OrderController::class, 'doLookup']);

// --- 회원 ---
Router::get('/login',                 [UserController::class, 'login']);
Router::post('/login',                [UserController::class, 'doLogin']);
Router::get('/logout',                [UserController::class, 'logout']);
Router::get('/register',              [UserController::class, 'register']);
Router::post('/register',             [UserController::class, 'doRegister']);
Router::get('/mypage',                [UserController::class, 'mypage']);
Router::post('/mypage/profile',       [UserController::class, 'updateProfile']);
Router::post('/mypage/notify',        [UserController::class, 'updateNotify']);
Router::get('/mypage/orders',         [UserController::class, 'orders']);
Router::get('/mypage/wishlist',       [UserController::class, 'wishlist']);
Router::post('/mypage/wishlist/add',  [UserController::class, 'addWishlist']);

// --- 게시판 ---
Router::get('/community/:type',       [HomeController::class, 'board']);
Router::get('/community/:type/:id',   [HomeController::class, 'boardDetail']);

// --- 관리자 ---
Router::get('/admin',                 [AdminController::class, 'dashboard']);
Router::get('/admin/books',           [AdminController::class, 'books']);
Router::get('/admin/books/create',    [AdminController::class, 'bookCreate']);
Router::post('/admin/books/create',   [AdminController::class, 'bookStore']);
Router::post('/admin/books/ai-parse',     [AdminController::class, 'bookAiParse']);
Router::post('/admin/books/upload-image', [AdminController::class, 'uploadBookImage']);
Router::get('/admin/books/:id/edit',  [AdminController::class, 'bookEdit']);
Router::post('/admin/books/:id/edit', [AdminController::class, 'bookUpdate']);
Router::post('/admin/books/:id/delete',[AdminController::class, 'bookDelete']);
Router::post('/admin/books/batch',    [AdminController::class, 'bookBatch']);
Router::get('/admin/orders',          [AdminController::class, 'orders']);
Router::post('/admin/orders/:id/status',[AdminController::class, 'orderStatus']);
Router::get('/admin/categories',      [AdminController::class, 'categories']);
Router::post('/admin/categories/create',[AdminController::class, 'categoryStore']);
Router::post('/admin/categories/:id/edit',[AdminController::class, 'categoryUpdate']);
Router::post('/admin/categories/:id/delete',[AdminController::class, 'categoryDelete']);
Router::get('/admin/banners',         [AdminController::class, 'banners']);
Router::get('/admin/banners/archive', [AdminController::class, 'bannerArchive']);
Router::get('/admin/banners/create',  [AdminController::class, 'bannerCreate']);
Router::post('/admin/banners/create', [AdminController::class, 'bannerStore']);
Router::get('/admin/banners/:id/edit',[AdminController::class, 'bannerEdit']);
Router::post('/admin/banners/:id/edit',[AdminController::class, 'bannerUpdate']);
Router::post('/admin/banners/:id/delete',[AdminController::class, 'bannerDelete']);
Router::post('/admin/banners/:id/toggle',[AdminController::class, 'bannerToggle']);
Router::get('/admin/members',         [AdminController::class, 'members']);
Router::post('/admin/members/:id/points',[AdminController::class, 'adjustPoints']);
Router::get('/admin/settings',        [AdminController::class, 'settings']);
Router::post('/admin/settings',       [AdminController::class, 'saveSettings']);

// ============================================================
// 요청 디스패치
// ============================================================
Router::dispatch();
