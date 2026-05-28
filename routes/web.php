<?php

use App\Http\Controllers\AarRecordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\Auth\AzureAuthController;
use App\Http\Controllers\ClientController;
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

    // 分析（全ロール）
    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');

    // マスター管理（全ロール）
    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    // 管理（admin のみ）
    Route::middleware('can:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        // 部署
        Route::post('/departments', [AdminController::class, 'storeDepartment'])->name('departments.store');
        Route::put('/departments/{department}', [AdminController::class, 'updateDepartment'])->name('departments.update');
        Route::delete('/departments/{department}', [AdminController::class, 'destroyDepartment'])->name('departments.destroy');
        // 社員
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });
});
