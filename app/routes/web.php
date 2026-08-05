<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantSwitcherController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CampaignLinkController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\GtmConfigController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\UserController;
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

        Route::resource('pages', PageController::class);
    });

    Route::middleware('can:donation.manage')->group(function () {
        Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
        Route::get('/donations/{donation}', [DonationController::class, 'show'])->name('donations.show');

        Route::resource('campaign-links', CampaignLinkController::class)->except(['show']);
    });

    Route::middleware('can:donation.process')->group(function () {
        Route::put('/donations/{donation}/status', [DonationController::class, 'updateStatus'])->name('donations.status');
    });

    Route::middleware('can:content.manage')->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::put('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    });

    Route::middleware('can:user.manage')->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::middleware('can:setting.manage')->group(function () {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('can:report.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('can:audit.view')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('can:backup.manage')->group(function () {
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('backups/{backup}', [BackupController::class, 'destroy'])->name('backups.destroy');
    });

    Route::middleware('can:tenant.view')->group(function () {
        Route::resource('tenants', TenantController::class);
        Route::put('/tenants/{tenant}/status', [TenantController::class, 'updateStatus'])->name('tenants.status');
    });

    Route::middleware('can:tenant.edit')->group(function () {
        Route::get('gtm', [GtmConfigController::class, 'index'])->name('gtm.index');
        Route::put('gtm', [GtmConfigController::class, 'update'])->name('gtm.update');
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

Route::middleware(['resolve.tenant'])->group(function () {
    Route::get('/sitemap.xml', \App\Http\Controllers\PublicSite\SitemapController::class)->name('sitemap');
});
