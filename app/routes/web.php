<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantSwitcherController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\TenantController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorSetupController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/two-factor/disable', [TwoFactorSetupController::class, 'disable'])->name('two-factor.disable');
});

Route::middleware(['auth', 'admin.tenant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/switch-tenant/{tenant}', TenantSwitcherController::class)->name('switch-tenant');

    Route::middleware('can:content.manage')->group(function () {
        Route::resource('programs', ProgramController::class);
        Route::resource('campaigns', CampaignController::class);
        Route::resource('articles', ArticleController::class);

        Route::resource('albums', AlbumController::class);
        Route::post('/albums/{album}/gallery', [AlbumController::class, 'addGallery'])->name('albums.gallery');
        Route::delete('/galleries/{gallery}', [AlbumController::class, 'removeGallery'])->name('galleries.destroy');

        Route::resource('members', MemberController::class);
    });

    Route::middleware('can:tenant.view')->group(function () {
        Route::resource('tenants', TenantController::class);
        Route::put('/tenants/{tenant}/status', [TenantController::class, 'updateStatus'])->name('tenants.status');
    });

    Route::middleware('can:media.manage')->group(function () {
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::get('/media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
        Route::put('/media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    });
});

Route::middleware(['resolve.tenant', 'capture.utm', 'capture.visit'])->group(function () {
    Route::get('/', [\App\Http\Controllers\PublicSite\HomeController::class, 'index'])->name('home');

    Route::get('/programs', [\App\Http\Controllers\PublicSite\ProgramController::class, 'index'])->name('public.programs');
    Route::get('/program/{slug}', [\App\Http\Controllers\PublicSite\ProgramController::class, 'show'])->name('public.program');

    Route::get('/campaigns', [\App\Http\Controllers\PublicSite\CampaignController::class, 'index'])->name('public.campaigns');
    Route::get('/campaign/{slug}', [\App\Http\Controllers\PublicSite\CampaignController::class, 'show'])->name('public.campaign');

    Route::get('/donasi/{slug}', [\App\Http\Controllers\PublicSite\DonationController::class, 'show'])->name('public.donation');
    Route::post('/donasi/{slug}', [\App\Http\Controllers\PublicSite\DonationController::class, 'store']);
    Route::get('/donasi/{slug}/status/{orderId}', [\App\Http\Controllers\PublicSite\DonationController::class, 'status'])->name('public.donation.status');

    Route::get('/articles', [\App\Http\Controllers\PublicSite\ArticleController::class, 'index'])->name('public.articles');
    Route::get('/article/{slug}', [\App\Http\Controllers\PublicSite\ArticleController::class, 'show'])->name('public.article');

    Route::get('/albums', [\App\Http\Controllers\PublicSite\AlbumController::class, 'index'])->name('public.albums');
    Route::get('/album/{slug}', [\App\Http\Controllers\PublicSite\AlbumController::class, 'show'])->name('public.album');

    Route::get('/pengurus', [\App\Http\Controllers\PublicSite\MemberController::class, 'index'])->name('public.members');

    Route::get('/kontak', [\App\Http\Controllers\PublicSite\LeadController::class, 'show'])->name('public.contact');
    Route::post('/kontak', [\App\Http\Controllers\PublicSite\LeadController::class, 'store']);

    Route::get('/page/{slug}', [\App\Http\Controllers\PublicSite\PageController::class, 'show'])->name('public.page');
});
