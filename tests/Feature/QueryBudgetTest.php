<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

// Proposal §7.2: database query counts. N+1 queries are caught everywhere by
// Model::preventLazyLoading() in AppServiceProvider; these budgets catch a page that
// quietly starts running more queries than it needs.
//
// Each budget is the count measured on 15 September 2026 plus two queries of headroom.
// None of them grows with the number of recipes, because the listings eager load. If a
// change needs a higher budget, raise it here and say why in the commit.

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('keeps each page within its query budget', function (string $path, bool $asSampleUser, int $budget) {
    if ($asSampleUser) {
        $this->actingAs(User::where('email', 'amelia@example.test')->sole());
    }

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    $this->get($path)->assertOk();

    expect($queryCount)->toBeLessThanOrEqual($budget, "{$path} ran {$queryCount} queries; its budget is {$budget}.");
})->with([
    'home page' => ['/', false, 10],
    'recipe listing' => ['/recipes', false, 12],
    'search with every filter' => ['/recipes?q=a&category[]=main&diet[]=vegetarian&max_minutes=120&min_servings=2&min_rating=3&sort=rating', false, 7],
    'recipe page, guest' => ['/recipes/healthy-pizza', false, 16],
    'recipe page, signed in' => ['/recipes/healthy-pizza', true, 18],
    'account page' => ['/dashboard', true, 9],
    'account settings' => ['/account', true, 2],
    'JSON search API' => ['/api/recipes', false, 8],
]);
