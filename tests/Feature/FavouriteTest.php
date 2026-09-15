<?php

use App\Models\Favourite;
use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
});

it('saves a recipe to the user\'s favourites', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();

    $response = $this->actingAs($user)
        ->from(route('recipes.show', $recipe))
        ->post(route('recipes.favourite.store', $recipe));

    $response->assertRedirect(route('recipes.show', $recipe));
    $response->assertSessionHas('status', 'Recipe saved to favourites.');

    expect($user->favouriteRecipes()->whereKey($recipe->id)->exists())->toBeTrue();
});

it('keeps a single favourite when the same recipe is saved twice', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();

    $this->actingAs($user)->post(route('recipes.favourite.store', $recipe));
    $this->actingAs($user)->post(route('recipes.favourite.store', $recipe));

    expect($user->favouriteRecipes()->count())->toBe(1);
});

it('removes a recipe from the user\'s favourites', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();
    $user->favouriteRecipes()->attach($recipe);

    $response = $this->actingAs($user)
        ->from(route('recipes.show', $recipe))
        ->delete(route('recipes.favourite.destroy', $recipe));

    $response->assertRedirect(route('recipes.show', $recipe));
    $response->assertSessionHas('status', 'Recipe removed from favourites.');

    expect($user->favouriteRecipes()->exists())->toBeFalse();
});

it('leaves other users\' favourites alone when one user removes theirs', function () {
    [$user, $otherUser] = User::factory()->count(2)->create();
    $recipe = Recipe::first();
    $user->favouriteRecipes()->attach($recipe);
    $otherUser->favouriteRecipes()->attach($recipe);

    $this->actingAs($user)->delete(route('recipes.favourite.destroy', $recipe));

    expect($otherUser->favouriteRecipes()->whereKey($recipe->id)->exists())->toBeTrue();
});

it('sends guests to the login page instead of changing favourites', function () {
    $recipe = Recipe::first();
    $favouritesBefore = Favourite::count();

    $this->post(route('recipes.favourite.store', $recipe))->assertRedirect(route('login'));
    $this->delete(route('recipes.favourite.destroy', $recipe))->assertRedirect(route('login'));

    expect(Favourite::count())->toBe($favouritesBefore);
});

it('offers to save a recipe the user has not saved yet', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('recipes.show', Recipe::first()))
        ->assertSee('Save favourite')
        ->assertDontSee('Remove favourite');
});

it('offers to remove a recipe the user has already saved', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();
    $user->favouriteRecipes()->attach($recipe);

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertSee('Remove favourite')
        ->assertDontSee('Save favourite');
});

it('asks guests to log in before they can save a recipe', function () {
    $this->get(route('recipes.show', Recipe::first()))
        ->assertSee('Log in to save this recipe')
        ->assertDontSee('Save favourite');
});

it('shows a confirmation after a recipe is saved', function () {
    $recipe = Recipe::first();

    $this->actingAs(User::factory()->create())
        ->from(route('recipes.show', $recipe))
        ->followingRedirects()
        ->post(route('recipes.favourite.store', $recipe))
        ->assertSee('Recipe saved to favourites.');
});

it('lists only the user\'s saved recipes on the dashboard', function () {
    $user = User::factory()->create();
    [$savedRecipe, $unsavedRecipe] = Recipe::orderBy('id')->take(2)->get();
    $user->favouriteRecipes()->attach($savedRecipe);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSee($savedRecipe->title, escape: false)
        ->assertDontSee($unsavedRecipe->title, escape: false);
});
