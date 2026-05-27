<?php

use App\Http\Controllers\AarRecordController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Auth\AzureAuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── ログイン画面 ─────────────────────────────────────────────
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login')->middleware('guest');

// ── Microsoft SSO ────────────────────────────────────────────
Route::get('/auth/azure/redirect', [AzureAuthController::class, 'redirect'])->name('azure.redirect');
Route::get('/auth/azure/callback', [AzureAuthController::class, 'callback'])->name('azure.callback');
Route::post('/logout', [AzureAuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── 認証済みルート ────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ダッシュボード
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AAR入力
    Route::get('/aar/create', [AarRecordController::class, 'create'])->name('aar.create');
    Route::post('/aar', [AarRecordController::class, 'store'])->name('aar.store');
    Route::get('/aar/{aarRecord}', [AarRecordController::class, 'show'])->name('aar.show');
    Route::delete('/aar/{aarRecord}', [AarRecordController::class, 'destroy'])->name('aar.destroy');

    // マイ記録
    Route::get('/my-records', [AarRecordController::class, 'myRecords'])->name('my-records.index');

    // チームDB
    Route::get('/team-db', [AarRecordController::class, 'teamDb'])->name('team-db.index');

    // 分析（マネージャー以上 or viewer）
    Route::get('/analysis', [AnalysisController::class, 'index'])
        ->name('analysis.index')
        ->middleware('can:view-analysis');

    // 管理（admin のみ）
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', fn() => Inertia::render('Admin/Index'))->name('index');
    });
});
