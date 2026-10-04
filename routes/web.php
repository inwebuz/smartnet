<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamController;
use App\Http\Middleware\EnsurePasswordChanged;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/ru');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
});

Route::prefix('studio')->name('studio.')->middleware(['auth', EnsurePasswordChanged::class])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/password', [AuthController::class, 'showPassword'])->name('password.edit');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::get('/settings', [ResourceController::class, 'settings'])->name('settings.edit');
    Route::put('/settings', [ResourceController::class, 'updateSettings'])->name('settings.update');
    Route::resource('team', TeamController::class)->except('show')->parameters(['team' => 'member']);
    Route::resource('roles', RoleController::class)->except('show');
    Route::post('/{resource}/{id}/publish', [ResourceController::class, 'publish'])->name('resources.publish');
    Route::get('/{resource}', [ResourceController::class, 'index'])->name('resources.index');
    Route::get('/{resource}/create', [ResourceController::class, 'create'])->name('resources.create');
    Route::post('/{resource}', [ResourceController::class, 'store'])->name('resources.store');
    Route::get('/{resource}/{id}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
    Route::put('/{resource}/{id}', [ResourceController::class, 'update'])->name('resources.update');
    Route::delete('/{resource}/{id}', [ResourceController::class, 'destroy'])->name('resources.destroy');
});

Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap'])->name('sitemap');

Route::prefix('{locale}')->where(['locale' => 'ru|uz|en'])->name('site.')->group(function (): void {
    Route::get('/', [PublicSiteController::class, 'home'])->name('home');
    Route::get('/services', [PublicSiteController::class, 'services'])->name('services');
    Route::get('/cases', [PublicSiteController::class, 'cases'])->name('cases');
    Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
    Route::post('/contact', [LeadController::class, 'storePublic'])->middleware('throttle:5,1')->name('contact.submit');
    Route::get('/{type}/{slug}', [PublicSiteController::class, 'detail'])->where(['type' => 'services|cases'])->name('detail');
});
