<?php

declare(strict_types=1);

/**
 * Front Controller — 모든 HTTP 요청의 진입점
 * public/index.php
 */

// ============================================================
// 부트스트랩
// ============================================================
require_once dirname(__DIR__) . '/config/settings.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/core/Router.php';
require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/core/Cart.php';
require_once dirname(__DIR__) . '/core/Captcha.php';
require_once dirname(__DIR__) . '/core/FileUploader.php';

// ============================================================
// 컨트롤러 로드
// ============================================================
require_once dirname(__DIR__) . '/controllers/HomeController.php';
require_once dirname(__DIR__) . '/controllers/BookController.php';
require_once dirname(__DIR__) . '/controllers/OrderController.php';
require_once dirname(__DIR__) . '/controllers/UserController.php';
require_once dirname(__DIR__) . '/controllers/AdminController.php';

// ============================================================
// 사이트 설정 전역 로드
// ============================================================
$GLOBALS['site'] = array_column(
    Database::fetchAll("SELECT key_name, key_value FROM site_settings"),
    'key_value', 'key_name'
);

// ============================================================
// 라우팅 정의
// ============================================================

// --- 홈 ---
Router::get('/',              [HomeController::class, 'index']);
Router::get('/search',        [HomeController::class, 'search']);

// --- 도서 ---
Router::get('/books',                 [BookController::class, 'index']);
Router::get('/book/:code',            [BookController::class, 'detail']);
Router::get('/category/:code',        [BookController::class, 'category']);
Router::get('/series/:id',            [BookController::class, 'series']);
Router::post('/book/:id/review',      [BookController::class, 'addReview']);

// --- 장바구니 & 주문 ---
Router::post('/cart/add',             [OrderController::class, 'addToCart']);
Router::post('/cart/update',          [OrderController::class, 'updateCart']);
Router::post('/cart/remove',          [OrderController::class, 'removeFromCart']);
Router::get('/cart',                  [OrderController::class, 'cart']);
Router::get('/order/checkout',        [OrderController::class, 'checkout']);
Router::post('/order/place',          [OrderController::class, 'place']);
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
Router::get('/admin/books/:id/edit',  [AdminController::class, 'bookEdit']);
Router::post('/admin/books/:id/edit', [AdminController::class, 'bookUpdate']);
Router::post('/admin/books/:id/delete',[AdminController::class, 'bookDelete']);
Router::post('/admin/books/batch',    [AdminController::class, 'bookBatch']);
Router::get('/admin/orders',          [AdminController::class, 'orders']);
Router::post('/admin/orders/:id/status',[AdminController::class, 'orderStatus']);
Router::get('/admin/members',         [AdminController::class, 'members']);
Router::post('/admin/members/:id/points',[AdminController::class, 'adjustPoints']);
Router::get('/admin/settings',        [AdminController::class, 'settings']);
Router::post('/admin/settings',       [AdminController::class, 'saveSettings']);

// ============================================================
// 요청 디스패치
// ============================================================
Router::dispatch();
