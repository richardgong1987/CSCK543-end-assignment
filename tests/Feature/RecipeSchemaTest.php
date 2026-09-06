<?php

use App\Models\Favourite;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeIngredientSection;
use App\Models\RecipeStep;
use App\Models\User;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
});

it('seeds the eight recipes named in the brief', function () {
    expect(Recipe::count())->toBe(8);

    expect(Recipe::pluck('slug')->all())->toContain(
        'spaghetti-bolognese-with-mushrooms-and-sun-dried-tomatoes',
        'vegan-pancakes',
        'healthy-pizza',
        'easy-lamb-biryani',
        'couscous-salad',
        'plum-clafoutis',
        'mango-pie',
        'mushroom-doner',
    );
});

it('gives every recipe at least one category, one ingredient and one step', function () {
    $recipes = Recipe::with(['categories', 'ingredients', 'steps'])->get();

    foreach ($recipes as $recipe) {
        expect($recipe->categories)->not->toBeEmpty("{$recipe->title} has no category");
        expect($recipe->ingredients)->not->toBeEmpty("{$recipe->title} has no ingredients");
        expect($recipe->steps)->not->toBeEmpty("{$recipe->title} has no steps");
    }
});

it('records a duration for every step', function () {
    $recipes = Recipe::with('steps')->get();

    foreach ($recipes as $recipe) {
        foreach ($recipe->steps as $step) {
            expect($step->duration_minutes)->toBeGreaterThan(0, "{$recipe->title} step {$step->step_number}");
        }
    }
});

it('numbers steps from one with no gaps', function () {
    foreach (Recipe::with('steps')->get() as $recipe) {
        expect($recipe->steps->pluck('step_number')->all())
            ->toBe(range(1, $recipe->steps->count()));
    }
});

it('stores every ingredient once and reuses it across recipes', function () {
    // Olive oil appears in three of the eight recipes but is one row in `ingredients`.
    $oliveOil = Ingredient::where('slug', 'olive-oil')->sole();

    expect($oliveOil->recipeIngredients()->distinct('recipe_id')->count('recipe_id'))
        ->toBeGreaterThan(1);
});

it('refuses to save the same recipe twice for one user', function () {
    $user = User::factory()->create();
    $recipe = Recipe::first();

    Favourite::create(['user_id' => $user->id, 'recipe_id' => $recipe->id]);

    expect(fn () => Favourite::create(['user_id' => $user->id, 'recipe_id' => $recipe->id]))
        ->toThrow(QueryException::class);
});

it('removes a recipe\'s ingredients and steps when the recipe is deleted', function () {
    $recipe = Recipe::where('slug', 'healthy-pizza')->sole();
    $recipeId = $recipe->id;

    $recipe->delete();

    expect(RecipeIngredient::where('recipe_id', $recipeId)->count())->toBe(0);
    expect(RecipeStep::where('recipe_id', $recipeId)->count())->toBe(0);
    expect(RecipeIngredientSection::where('recipe_id', $recipeId)->count())->toBe(0);
    // The ingredients themselves survive, because other recipes use them.
    expect(Ingredient::where('slug', 'olive-oil')->exists())->toBeTrue();
});
