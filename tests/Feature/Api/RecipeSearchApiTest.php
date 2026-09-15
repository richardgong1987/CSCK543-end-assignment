<?php

use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeSearch;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
});

it('returns every recipe as JSON when there is no search', function () {
    $response = $this->getJson(route('api.recipes.index'));

    $response->assertOk()
        ->assertJsonCount(Recipe::count(), 'data')
        ->assertJsonStructure([
            'data' => [[
                'slug', 'title', 'description', 'url', 'image_url', 'prep_time', 'cook_time',
                'servings', 'categories', 'dietary_tags', 'average_rating', 'ratings_count', 'steps_count',
            ]],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ])
        ->assertJsonPath('meta.per_page', 12);
});

it('returns the same recipes in the same order as the listing\'s search service', function (array $query) {
    $expectedSlugs = RecipeSearch::fromQuery($query)->query()->pluck('slug')->all();

    $response = $this->getJson(route('api.recipes.index', $query));

    $response->assertOk();
    expect(array_column($response->json('data'), 'slug'))->toBe($expectedSlugs);
})->with([
    'keyword' => [['q' => 'pizza']],
    'sorted by title, Z to A' => [['sort' => 'title_desc']],
    'quickest first' => [['sort' => 'quickest']],
    'ready within an hour' => [['max_minutes' => 60]],
    'keyword and sort together' => [['q' => 'salad', 'sort' => 'rating']],
]);

it('describes a recipe with its link, times and labels', function () {
    $recipe = Recipe::where('slug', 'healthy-pizza')->sole();

    $response = $this->getJson(route('api.recipes.index', ['q' => 'healthy pizza']));

    $response->assertJsonPath('data.0.slug', 'healthy-pizza')
        ->assertJsonPath('data.0.title', $recipe->title)
        ->assertJsonPath('data.0.url', route('recipes.show', $recipe))
        ->assertJsonPath('data.0.servings', $recipe->servingsText());
});

it('reports the average rating rounded to one decimal place', function () {
    $recipe = Recipe::where('slug', 'healthy-pizza')->sole();
    [$ada, $ben, $chen] = User::factory()->count(3)->create();
    $ada->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 5]);
    $ben->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 4]);
    $chen->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 4]);

    $this->getJson(route('api.recipes.index', ['q' => 'healthy pizza']))
        ->assertJsonPath('data.0.average_rating', 4.3)
        ->assertJsonPath('data.0.ratings_count', 3);
});

it('never exposes who rated a recipe', function () {
    $recipe = Recipe::first();
    $user = User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    $user->ratings()->create(['recipe_id' => $recipe->id, 'overall' => 5]);

    $body = $this->getJson(route('api.recipes.index'))->getContent();

    expect($body)->not->toContain('ada@example.com')
        ->and($body)->not->toContain('Ada Lovelace')
        ->and($body)->not->toContain('"user_id"');
});

it('ignores unrecognised input instead of failing, like the listing page', function () {
    $this->getJson(route('api.recipes.index', ['sort' => 'title; DROP TABLE recipes', 'max_minutes' => 'soon']))
        ->assertOk()
        ->assertJsonCount(Recipe::count(), 'data');
});

it('answers an unknown API address with a JSON 404', function () {
    $this->getJson('/api/no-such-endpoint')
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});

it('limits each client to the configured number of requests a minute', function () {
    config(['api.recipe_search_per_minute' => 3]);

    foreach (range(1, 3) as $attempt) {
        $this->getJson(route('api.recipes.index'))->assertOk();
    }

    $this->getJson(route('api.recipes.index'))->assertTooManyRequests();
});
