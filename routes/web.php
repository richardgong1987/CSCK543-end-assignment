<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountPasswordController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Browsing recipes does not require an account; saving and rating them will.
Route::get('recipes', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('recipes/{recipe}', [RecipeController::class, 'show'])->name('recipes.show');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('account', [AccountController::class, 'update'])->name('account.update');
    Route::delete('account', [AccountController::class, 'destroy'])->name('account.destroy');
    Route::put('account/password', [AccountPasswordController::class, 'update'])->name('account.password.update');

    Route::post('recipes/{recipe}/favourite', [FavouriteController::class, 'store'])
        ->name('recipes.favourite.store');

    Route::delete('recipes/{recipe}/favourite', [FavouriteController::class, 'destroy'])
        ->name('recipes.favourite.destroy');

    // PUT rather than POST: a user has at most one rating per recipe, and rating again replaces it.
    Route::put('recipes/{recipe}/rating', [RatingController::class, 'update'])
        ->name('recipes.rating.update');
});
