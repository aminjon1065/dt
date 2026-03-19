<?php

use App\Http\Controllers\Cms\NewsController;
use App\Http\Controllers\Cms\PageController;
use App\Http\Controllers\Cms\DocumentController;
use App\Http\Controllers\Cms\GrmSubmissionController;
use App\Http\Controllers\Cms\MenuController;
use App\Http\Controllers\Cms\ProcurementController;
use App\Http\Controllers\Cms\SettingsController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\PublicDocumentController;
use App\Http\Controllers\PublicGrmController;
use App\Http\Controllers\PublicProcurementController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', [PublicPageController::class, 'redirectToDefaultLocale'])
    ->name('home');

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('cms/pages', PageController::class)
        ->except('show')
        ->names('cms.pages');

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

    Route::resource('cms/documents', DocumentController::class)
        ->except('show')
        ->names('cms.documents');

    Route::resource('cms/grm-submissions', GrmSubmissionController::class)
        ->except('show')
        ->names('cms.grm-submissions');

    Route::resource('cms/procurements', ProcurementController::class)
        ->except('show')
        ->names('cms.procurements');
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

Route::get('/{locale}/{slug}', [PublicPageController::class, 'show'])
    ->whereIn('locale', ['en', 'tj', 'ru'])
    ->name('public.pages.show');
