<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Marketing\MarketingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Marketing Website Routes
Route::get('/', function () {
    return Inertia::render('Marketing/Landing');
})->name('marketing.landing');

Route::get('/marketing', function () {
    return Inertia::render('Marketing/Landing');
})->name('marketing.home');

Route::get('/features', function () {
    return Inertia::render('Marketing/Features');
})->name('marketing.features');

Route::get('/test', function () {
    return Inertia::render('Test');
})->name('test.page');

Route::get('/pricing', function () {
    return Inertia::render('Marketing/Pricing');
})->name('marketing.pricing');

Route::get('/demo', function () {
    return Inertia::render('Marketing/Demo');
})->name('marketing.demo');

Route::get('/about-us', function () {
    return Inertia::render('Marketing/AboutUs');
})->name('marketing.about-us');

Route::get('/careers', function () {
    $jobOpenings = \App\Models\CompanyJobOpening::active()
        ->byPriority()
        ->get()
        ->map(function ($job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'department' => $job->department,
                'location' => $job->location,
                'type' => $job->type,
                'experience_level' => $job->experience_level,
                'description' => $job->description,
                'requirements' => $job->requirements,
                'salary_range' => $job->salary_range,
                'posted_ago' => $job->posted_ago,
                'application_deadline' => $job->application_deadline?->format('M d, Y'),
                'contact_email' => $job->contact_email,
            ];
        });

    $departments = \App\Models\CompanyJobOpening::getDepartments()->values()->toArray();
    $locations = \App\Models\CompanyJobOpening::getLocations()->values()->toArray();

    return Inertia::render('Marketing/Careers', [
        'jobOpenings' => $jobOpenings,
        'departments' => $departments,
        'locations' => $locations,
    ]);
})->name('marketing.careers');

// Support Pages
Route::get('/help-center', function () {
    return Inertia::render('Marketing/HelpCenter');
})->name('marketing.help-center');

Route::get('/documentation', function () {
    return Inertia::render('Marketing/Documentation');
})->name('marketing.documentation');

Route::get('/privacy-policy', function () {
    return Inertia::render('Marketing/PrivacyPolicy');
})->name('marketing.privacy-policy');

Route::get('/terms-of-service', function () {
    return Inertia::render('Marketing/TermsOfService');
})->name('marketing.terms-of-service');

// Registration and Payment Routes
Route::get('/register', [MarketingController::class, 'showRegister'])->name('marketing.register');
Route::post('/register', [MarketingController::class, 'register'])->name('marketing.register.submit');
Route::get('/payment', [MarketingController::class, 'showPayment'])->name('marketing.payment');
Route::post('/payment/success', [MarketingController::class, 'paymentSuccess'])->name('marketing.payment.success');
Route::get('/success/{tenant}', [MarketingController::class, 'showSuccess'])->name('marketing.success');

// Application Dashboard (protected)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Test route for employee form
Route::get('/test-employee-form', function () {
    $controller = app()->make('\App\Http\Controllers\Admin\EmployeeCrudController');
    $controller->setup();
    
    // Manually set up the CRUD context
    $controller->crud->setOperation('create');
    
    try {
        $controller->setupCreateOperation();
        return "✅ Employee form setup works correctly!";
    } catch (Exception $e) {
        return "❌ Error: " . $e->getMessage() . "\nFile: " . $e->getFile() . ":" . $e->getLine();
    }
});
