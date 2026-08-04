<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/admin/dashboard', DashboardController::class)->name('admin.dashboard');

    Route::get('/two-factor/setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');
    Route::post('/two-factor/setup', [TwoFactorSetupController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('/two-factor/disable', [TwoFactorSetupController::class, 'disable'])->name('two-factor.disable');
});

Route::middleware('resolve.tenant')->group(function () {
    Route::get('/', function () {
        return response(app(\App\Support\TenantContext::class)->id() ?: 'no tenant');
    })->name('home');

    Route::get('/programs', [\App\Http\Controllers\PublicSite\ProgramController::class, 'index'])->name('public.programs');
    Route::get('/program/{slug}', [\App\Http\Controllers\PublicSite\ProgramController::class, 'show'])->name('public.program');

    Route::get('/campaigns', [\App\Http\Controllers\PublicSite\CampaignController::class, 'index'])->name('public.campaigns');
    Route::get('/campaign/{slug}', [\App\Http\Controllers\PublicSite\CampaignController::class, 'show'])->name('public.campaign');

    Route::get('/articles', [\App\Http\Controllers\PublicSite\ArticleController::class, 'index'])->name('public.articles');
    Route::get('/article/{slug}', [\App\Http\Controllers\PublicSite\ArticleController::class, 'show'])->name('public.article');

    Route::get('/albums', [\App\Http\Controllers\PublicSite\AlbumController::class, 'index'])->name('public.albums');
    Route::get('/album/{slug}', [\App\Http\Controllers\PublicSite\AlbumController::class, 'show'])->name('public.album');
});
