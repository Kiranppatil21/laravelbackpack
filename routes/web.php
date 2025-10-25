<?php

use Illuminate\Support\Facades\Route;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as Backpack;

Route::get('/', function () {
    return view('welcome');
});
 
 // The admin route group has been removed as Backpack routes are defined in routes/backpack/custom.php

// Stripe webhook endpoint (receives events from Stripe and updates tenant/subscription status)
Route::post('stripe/webhook', [\App\Http\Controllers\Admin\BillingController::class, 'webhook']);

// Public signup and checkout
Route::get('signup', [\App\Http\Controllers\SignupController::class, 'show'])->name('signup.show');
Route::post('signup', [\App\Http\Controllers\SignupController::class, 'store'])->name('signup.store');
Route::get('signup/success', [\App\Http\Controllers\SignupController::class, 'success'])->name('signup.success');

// Razorpay webhook endpoint
Route::post('razorpay/webhook', [\App\Http\Controllers\RazorpayController::class, 'webhook']);

