<?php

use App\Http\Controllers\Account\OrderHistoryController;
use App\Http\Controllers\Account\ProfileController as AccountProfileController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\HomeBannerController as AdminHomeBannerController;
use App\Http\Controllers\Admin\HomeProductCarouselController as AdminHomeProductCarouselController;
use App\Http\Controllers\Admin\HomeSecondaryBannerController as AdminHomeSecondaryBannerController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\SiteSettingController as AdminSiteSettingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PolicyPageController;
use App\Http\Controllers\WebhookController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = request()->user();
    $forceStorefront = request()->boolean('storefront');

    if ($user && $user->role !== 'customer' && ! $forceStorefront) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('storefront.home');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn () => backoffice_dashboard_view())->name('dashboard');
});

Route::prefix('tienda')->name('storefront.')->group(function () {
    Route::get('/', fn () => redirect()->route('storefront.home'));
    Route::get('/home', fn () => storefront_view('home'))->name('home');
    Route::get('/catalogo', fn () => storefront_view('catalogo'))->name('catalogo');
    Route::get('/contacto', fn () => storefront_view('contacto'))->name('contacto');
    Route::get('/favoritos', fn () => storefront_view('favoritos'))->name('favoritos');
    Route::get('/carrito', fn () => storefront_view('carrito'))->name('carrito');
    Route::get('/checkout', fn () => storefront_view('checkout'))->name('checkout');
    Route::get('/producto/{product}', function (Product $product) {
        return storefront_view('producto', $product->id);
    })->name('product.show');
});

Route::get('/api/home-products', [CatalogController::class, 'featured']);
Route::get('/api/home-banners', [CatalogController::class, 'banners']);
Route::get('/api/home-main-banner', [CatalogController::class, 'mainBanner']);
Route::get('/api/home-secondary-banners', [CatalogController::class, 'secondaryBanners']);
Route::get('/api/home-product-carousels', [CatalogController::class, 'homeProductCarousels']);
Route::get('/api/home-promotion-banner', [CatalogController::class, 'promotionBanner']);
Route::post('/api/contact-messages', [ContactController::class, 'store']);
Route::get('/api/categories', [CatalogController::class, 'categories']);
Route::get('/api/brands', [CatalogController::class, 'brands']);
Route::get('/api/catalog', [CatalogController::class, 'index']);
Route::get('/api/products/{product}', [CatalogController::class, 'show']);
Route::get('/api/checkout/coupon', [CheckoutController::class, 'validateCoupon']);
Route::post('/api/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::post('/api/checkout/prepare', [CheckoutController::class, 'prepare']);
Route::get('/api/orders/{order}/summary', [CheckoutController::class, 'summary']);
Route::get('/politicas/{slug}', [PolicyPageController::class, 'show'])->name('storefront.policy.show');

Route::middleware('auth')->group(function () {
    Route::get('/api/favorites', [FavoriteController::class, 'index']);
    Route::get('/api/favorites/ids', [FavoriteController::class, 'ids']);
    Route::post('/api/favorites/toggle', [FavoriteController::class, 'toggle']);
});

Route::post('/api/payments/stripe/checkout-session', [PaymentController::class, 'createStripeCheckoutSession']);
Route::post('/api/payments/paypal/order', [PaymentController::class, 'createPaypalOrder']);

Route::get('/checkout/stripe/success/{order}', [PaymentController::class, 'stripeSuccess'])->name('checkout.stripe.success');
Route::get('/checkout/paypal/return/{order}', [PaymentController::class, 'paypalReturn'])->name('checkout.paypal.return');
Route::get('/checkout/cancel/{order}', [PaymentController::class, 'cancel'])->name('checkout.cancel');

Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/mi-cuenta', [AccountProfileController::class, 'edit'])->name('account.profile.edit');
    Route::put('/mi-cuenta', [AccountProfileController::class, 'update'])->name('account.profile.update');
    Route::get('/mi-cuenta/pedidos/exportar', [OrderHistoryController::class, 'export'])->name('account.orders.export');
    Route::get('/mi-cuenta/pedidos/{order}/pdf', [OrderHistoryController::class, 'pdf'])->name('account.orders.pdf');
    Route::get('/mi-cuenta/pedidos', [OrderHistoryController::class, 'index'])->name('account.orders.index');
    Route::get('/mi-cuenta/pedidos/{order}', [OrderHistoryController::class, 'show'])->name('account.orders.show');
});

Route::middleware(['auth', 'permission:view_admin_orders'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/exportar', [AdminOrderController::class, 'export'])->name('orders.export');
    Route::get('/orders/{order}/pdf', [AdminOrderController::class, 'pdf'])->name('orders.pdf');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
});

Route::middleware(['auth', 'permission:manage_categories'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
});

Route::middleware(['auth', 'permission:manage_products'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/promotions/search-products', [AdminPromotionController::class, 'searchProducts'])->name('promotions.search-products');
    Route::get('/home-product-carousels/search-products', [AdminHomeProductCarouselController::class, 'searchProducts'])->name('home-product-carousels.search-products');
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('promotions', AdminPromotionController::class)->except(['show']);
    Route::resource('coupons', AdminCouponController::class)->except(['show']);
    Route::resource('home-banners', AdminHomeBannerController::class)->except(['show']);
    Route::resource('home-secondary-banners', AdminHomeSecondaryBannerController::class)
        ->parameters(['home-secondary-banners' => 'homeSecondaryBanner'])
        ->except(['show']);
    Route::resource('home-product-carousels', AdminHomeProductCarouselController::class)
        ->parameters(['home-product-carousels' => 'homeProductCarousel'])
        ->except(['show']);
    Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class)->except(['show']);
});

Route::middleware(['auth', 'permission:manage_site_settings'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/site-settings', [AdminSiteSettingController::class, 'edit'])->name('site-settings.edit');
    Route::put('/site-settings', [AdminSiteSettingController::class, 'update'])->name('site-settings.update');
    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::delete('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    Route::get('/newsletter-subscribers', [AdminNewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index');
    Route::get('/newsletter-subscribers/exportar', [AdminNewsletterSubscriberController::class, 'export'])->name('newsletter-subscribers.export');
    Route::patch('/newsletter-subscribers/{newsletterSubscriber}/toggle', [AdminNewsletterSubscriberController::class, 'toggle'])->name('newsletter-subscribers.toggle');
    Route::delete('/newsletter-subscribers/{newsletterSubscriber}', [AdminNewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy');
});

Route::middleware(['auth', 'permission:manage_users'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
});
