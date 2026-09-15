<?php

use App\Models\Rating;
use App\Models\Recipe;
use App\Models\User;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
});

it('saves a user\'s rating with every score', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();

    $response = $this->actingAs($user)
        ->from(route('recipes.show', $recipe))
        ->put(route('recipes.rating.update', $recipe), [
            'overall' => 4,
            'taste' => 5,
            'difficulty' => 2,
            'appearance' => 3,
        ]);

    $response->assertRedirect(route('recipes.show', $recipe));
    $response->assertSessionHas('status', 'Your rating has been saved.');

    expect($user->ratings()->sole())
        ->recipe_id->toBe($recipe->id)
        ->overall->toBe(4)
        ->taste->toBe(5)
        ->difficulty->toBe(2)
        ->appearance->toBe(3);
});

it('accepts a rating with only the overall score', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();

    $this->actingAs($user)->put(route('recipes.rating.update', $recipe), [
        'overall' => 3,
        'taste' => '',
    ]);

    expect($user->ratings()->sole())
        ->overall->toBe(3)
        ->taste->toBeNull()
        ->difficulty->toBeNull()
        ->appearance->toBeNull();
});

it('replaces the user\'s earlier rating instead of adding a second one', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();
    $user->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 2, 'taste' => 2]);

    $this->actingAs($user)->put(route('recipes.rating.update', $recipe), ['overall' => 5]);

    // The facet left out of the new rating is cleared, not kept from the old one.
    expect($user->ratings()->sole())
        ->overall->toBe(5)
        ->taste->toBeNull();
});

it('rejects scores the database would not accept', function (array $scores, string $field, string $expectedMessage) {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('recipes.rating.update', Recipe::first()), $scores);

    $response->assertInvalid([$field => $expectedMessage]);

    expect($user->ratings()->exists())->toBeFalse();
})->with([
    'missing overall score' => [['taste' => 4], 'overall', 'required'],
    'overall score below 1' => [['overall' => 0], 'overall', 'between 1 and 5'],
    'overall score above 5' => [['overall' => 6], 'overall', 'between 1 and 5'],
    'overall score that is not a number' => [['overall' => 'great'], 'overall', 'integer'],
    'overall score with a fraction' => [['overall' => 3.5], 'overall', 'integer'],
    'taste score above 5' => [['overall' => 4, 'taste' => 6], 'taste', 'between 1 and 5'],
    'difficulty score below 1' => [['overall' => 4, 'difficulty' => 0], 'difficulty', 'between 1 and 5'],
    'appearance score above 5' => [['overall' => 4, 'appearance' => 6], 'appearance', 'between 1 and 5'],
]);

it('sends guests to the login page instead of saving a rating', function () {
    $recipe = Recipe::first();
    $ratingsBefore = Rating::count();

    $this->put(route('recipes.rating.update', $recipe), ['overall' => 5])
        ->assertRedirect(route('login'));

    expect(Rating::count())->toBe($ratingsBefore);
});

it('asks guests to log in before they can rate a recipe', function () {
    $this->get(route('recipes.show', Recipe::first()))
        ->assertSee('Log in to rate this recipe')
        ->assertDontSee('Save rating');
});

it('offers an empty rating form to a user who has not rated the recipe', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('recipes.show', Recipe::first()))
        ->assertSee('Rate this recipe')
        ->assertSee('Save rating');
});

it('fills the form with the user\'s existing scores', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();
    $user->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 4]);

    $this->actingAs($user)
        ->get(route('recipes.show', $recipe))
        ->assertSee('Update rating')
        ->assertSee('name="overall" value="4" checked', escape: false);
});

it('includes a new rating in the recipe\'s average', function () {
    $recipe = Recipe::first();

    $this->actingAs(User::factory()->create())
        ->put(route('recipes.rating.update', $recipe), ['overall' => 4]);

    $this->get(route('recipes.show', $recipe))
        ->assertSee('4.0 out of 5')
        ->assertSee('1 rating');
});
