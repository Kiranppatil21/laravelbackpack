<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Token-based API login pages (SPA pages that interact with /api/* endpoints)
Route::get('/api-login', function () {
    return Inertia::render('Api/Login');
})->name('api.login');

Route::get('/api-dashboard', function () {
    return Inertia::render('Api/Dashboard');
})->name('api.dashboard');

Route::get('/api-register', function () {
    return Inertia::render('Api/Register');
})->name('api.register');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Public webhook endpoints (no CSRF) for external billing providers.
// Tests and external services post to these paths: /stripe/webhook and /razorpay/webhook
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\SignupController;

Route::post('/stripe/webhook', [BillingController::class, 'webhook'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

Route::post('/razorpay/webhook', [RazorpayController::class, 'webhook'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// Public signup pages
Route::get('/signup', [SignupController::class, 'show'])->name('signup.show');
Route::post('/signup', [SignupController::class, 'store'])->name('signup.store');
Route::get('/signup/success', [SignupController::class, 'success'])->name('signup.success');

// Admin routes: tenant activation (Backpack admin prefix)
Route::match(['get', 'post'], config('backpack.base.route_prefix').'/tenant/{tenant}/activate', [\App\Http\Controllers\Admin\TenantCrudController::class, 'activate'])
    ->name('admin.tenant.activate')
    ->middleware(['web', 'admin']);
