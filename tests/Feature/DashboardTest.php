<?php

use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
});

it('shows the user\'s name and email on the account page', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('ada@example.com');
});

it('shows saved recipes as cards that link to the recipe', function () {
    $user = User::factory()->create();
    $recipe = Recipe::orderBy('id')->first();
    $user->favouriteRecipes()->attach($recipe);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee(route('recipes.show', $recipe))
        ->assertSee('Prep:')
        ->assertDontSee('You haven\'t saved any recipes yet.', escape: false);
});

it('points a user with no saved recipes towards the recipe listing', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertSee('You haven\'t saved any recipes yet.', escape: false)
        ->assertSee(route('recipes.index'));
});

it('lists the recipes the user has rated with the scores they gave', function () {
    $user = User::factory()->create();
    $recipe = Recipe::orderBy('id')->first();
    $user->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 4, 'taste' => 5]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSeeInOrder([$recipe->title, 'Overall:', '4 out of 5', 'Taste:', '5 out of 5'], escape: false)
        // Facets the user skipped are left out rather than shown empty.
        ->assertDontSee('Difficulty:')
        ->assertDontSee('Appearance:');
});

it('does not show ratings that belong to another user', function () {
    [$user, $otherUser] = User::factory()->count(2)->create();
    $recipe = Recipe::orderBy('id')->first();
    $otherUser->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 1]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee('You haven\'t rated any recipes yet.', escape: false)
        ->assertDontSee('Overall:');
});
