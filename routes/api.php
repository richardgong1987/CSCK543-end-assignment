<?php

use App\Http\Controllers\Api\RecipeSearchController;
use Illuminate\Support\Facades\Route;

// Read-only and public, like the listing it mirrors. The "recipe-api" limiter is defined in
// AppServiceProvider so a load test can raise it without touching this file.
Route::get('recipes', RecipeSearchController::class)
    ->middleware('throttle:recipe-api')
    ->name('api.recipes.index');
