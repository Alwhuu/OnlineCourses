<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseVideoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscribeTransactionController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// Route untuk halaman depan
Route::get('/', [FrontController::class, 'index'])->name('front.index');

Route::get('/category/{category:slug}', [FrontController::class, 'category'])->name('front.category');

Route::get('/details/{course:slug}', [FrontController::class, 'details'])->name('front.details');

Route::get('/pricing', [FrontController::class, 'pricing'])
->name('front.pricing');

// Route dengan middleware auth
Route::middleware('auth')->group(function () {
    // Route untuk profile pengguna
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route untuk checkout
    Route::get('/checkout', [FrontController::class, 'checkout'])
        ->name('front.checkout')
        ->middleware('role:student');

    Route::post('/checkout/store', [FrontController::class, 'checkout_store'])
        ->name('front.checkout.store')
        ->middleware('role:student');

    // Route untuk proses learning
    Route::get('/learning/{course}/{courseVideoId}', [FrontController::class, 'learning'])
        ->middleware('role:student|teacher|owner')
        ->name('front.learning');

    // Route admin dengan prefix 'admin' dan nama route 'admin.'
    Route::prefix('admin')->name('admin.')->group(function () {
        // Route resource untuk kategori
        Route::resource('categories', CategoryController::class)
            ->middleware('role:owner');

        // Route resource untuk teacher
        Route::resource('teachers', TeacherController::class)
            ->middleware('role:owner');

        // Route resource untuk course
        Route::resource('courses', CourseController::class)
            ->middleware('role:owner|teacher');

        // Route resource untuk subscribe_transactions
        Route::resource('subscribe_transactions', SubscribeTransactionController::class)
            ->middleware('role:owner');

        // Route untuk menambah video pada course
        Route::get('/add/video/{course:id}', [CourseVideoController::class, 'create'])
            ->middleware('role:teacher|owner')
            ->name('course.add_video');

        Route::post('/add/video/save/{course:id}', [CourseVideoController::class, 'store'])
            ->middleware('role:teacher|owner')
            ->name('course.add_video.save');

        // Route resource untuk course videos
        Route::resource('course_videos', CourseVideoController::class)
            ->middleware('role:owner|teacher');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});

// Authentication routes
require __DIR__.'/auth.php';
