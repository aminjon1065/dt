<?php

use App\Http\Controllers\Cms\AuditLogController;
use App\Http\Controllers\Cms\DashboardController;
use App\Http\Controllers\Cms\DocumentController;
use App\Http\Controllers\Cms\GrmSubmissionController;
use App\Http\Controllers\Cms\MenuController;
use App\Http\Controllers\Cms\NewsController;
use App\Http\Controllers\Cms\PageController;
use App\Http\Controllers\Cms\ProcurementController;
use App\Http\Controllers\Cms\SettingsController;
use App\Http\Controllers\Cms\StaffMemberController;
use App\Http\Controllers\Cms\SubscriptionController;
use App\Http\Controllers\PublicDocumentController;
use App\Http\Controllers\PublicGrmController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicProcurementController;
use App\Http\Controllers\PublicSearchController;
use App\Http\Controllers\PublicStaffController;
use App\Http\Controllers\PublicSubscriptionController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'redirectToDefaultLocale'])
    ->name('home');

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('cms/audit-logs', [AuditLogController::class, 'index'])
        ->name('cms.audit-logs.index');

    Route::resource('cms/pages', PageController::class)
        ->except('show')
        ->names('cms.pages');
    Route::post('cms/pages/{page}/workflow', [PageController::class, 'workflow'])
        ->name('cms.pages.workflow');

    Route::get('cms/settings', [SettingsController::class, 'edit'])
        ->name('cms.settings.edit');
    Route::put('cms/settings', [SettingsController::class, 'update'])
        ->name('cms.settings.update');

    Route::resource('cms/menus', MenuController::class)
        ->except('show')
        ->names('cms.menus');

    Route::resource('cms/news', NewsController::class)
        ->except('show')
        ->names('cms.news');
    Route::post('cms/news/{news}/workflow', [NewsController::class, 'workflow'])
        ->name('cms.news.workflow');

    Route::resource('cms/documents', DocumentController::class)
        ->except('show')
        ->names('cms.documents');
    Route::post('cms/documents/{document}/workflow', [DocumentController::class, 'workflow'])
        ->name('cms.documents.workflow');

    Route::resource('cms/grm-submissions', GrmSubmissionController::class)
        ->except('show')
        ->names('cms.grm-submissions');
    Route::post('cms/grm-submissions/{grm_submission}/workflow', [GrmSubmissionController::class, 'workflow'])
        ->name('cms.grm-submissions.workflow');

    Route::resource('cms/procurements', ProcurementController::class)
        ->except('show')
        ->names('cms.procurements');
    Route::post('cms/procurements/{procurement}/workflow', [ProcurementController::class, 'workflow'])
        ->name('cms.procurements.workflow');

    Route::resource('cms/staff-members', StaffMemberController::class)
        ->except('show')
        ->names('cms.staff-members');

    Route::resource('cms/subscriptions', SubscriptionController::class)
        ->only(['index', 'edit', 'update', 'destroy'])
        ->names('cms.subscriptions');
    Route::post('cms/subscriptions/{subscription}/workflow', [SubscriptionController::class, 'workflow'])
        ->name('cms.subscriptions.workflow');
});

require __DIR__.'/settings.php';

Route::get('/{locale}', [PublicPageController::class, 'home'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.home');

Route::get('/{locale}/news', [PublicNewsController::class, 'index'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.news.index');

Route::get('/{locale}/news/{slug}', [PublicNewsController::class, 'show'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.news.show');

Route::get('/{locale}/documents', [PublicDocumentController::class, 'index'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.documents.index');

Route::get('/{locale}/documents/{slug}', [PublicDocumentController::class, 'show'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.documents.show');

Route::get('/{locale}/procurements', [PublicProcurementController::class, 'index'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.procurements.index');

Route::get('/{locale}/procurements/{slug}', [PublicProcurementController::class, 'show'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.procurements.show');

Route::get('/{locale}/grm', [PublicGrmController::class, 'create'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.grm.create');

Route::post('/{locale}/grm', [PublicGrmController::class, 'store'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.grm.store');

Route::get('/{locale}/grm/thank-you', [PublicGrmController::class, 'thankYou'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.grm.thank-you');

Route::get('/{locale}/staff', [PublicStaffController::class, 'index'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.staff.index');

Route::get('/{locale}/staff/{slug}', [PublicStaffController::class, 'show'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.staff.show');

Route::get('/{locale}/subscribe', [PublicSubscriptionController::class, 'create'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.subscriptions.create');

Route::post('/{locale}/subscribe', [PublicSubscriptionController::class, 'store'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.subscriptions.store');

Route::get('/{locale}/subscribe/thank-you', [PublicSubscriptionController::class, 'thankYou'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.subscriptions.thank-you');

Route::get('/{locale}/unsubscribe', [PublicSubscriptionController::class, 'unsubscribe'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.subscriptions.unsubscribe');

Route::post('/{locale}/unsubscribe', [PublicSubscriptionController::class, 'unsubscribeStore'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.subscriptions.unsubscribe.store');

Route::get('/{locale}/unsubscribe/thank-you', [PublicSubscriptionController::class, 'unsubscribeThankYou'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.subscriptions.unsubscribe-thank-you');

Route::get('/{locale}/search', PublicSearchController::class)
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.search');

Route::get('/{locale}/{slug}', [PublicPageController::class, 'show'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.pages.show');
