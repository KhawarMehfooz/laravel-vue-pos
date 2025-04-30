<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Settings\AppSettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function(){
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/search', [CategoryController::class, 'search']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}',[CategoryController::class,'update']);
    Route::delete('/categories/{category}',[CategoryController::class,'destroy']);

    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/search', [CompanyController::class, 'search']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::put('/companies/{company}',[CompanyController::class,'update']);
    Route::delete('/companies/{company}',[CompanyController::class,'destroy']);

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}',[ProductController::class,'update']);
    Route::delete('/products/{product}',[ProductController::class,'destroy']);

    Route::get('/settings/app', [AppSettingsController::class, 'index'])->name('app-settings.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
