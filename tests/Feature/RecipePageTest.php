<?php

use App\Models\Recipe;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class]);
});

it('lists every seeded recipe', function () {
    $response = $this->get(route('recipes.index'));

    $response->assertOk();

    foreach (Recipe::pluck('title') as $title) {
        $response->assertSee($title, escape: false);
    }
});

it('lets a guest browse recipes without an account', function () {
    $this->get(route('recipes.index'))->assertOk();
    $this->get(route('recipes.show', Recipe::first()))->assertOk();
});

it('shows ingredients, steps and step timings on the detail page', function () {
    $recipe = Recipe::where('slug', 'healthy-pizza')->sole();

    $response = $this->get(route('recipes.show', $recipe));

    $response->assertOk()
        ->assertSee($recipe->title)
        ->assertSee('125g wholemeal flour, self-raising, plus extra for dusting')
        ->assertSee('For the topping')
        ->assertSee('Preheat the oven to 220C/200C Fan/Gas 7.')
        // Every step carries a time, which the brief requires.
        ->assertSee('Step 1 · 10 mins', escape: false)
        ->assertSee('Serves 2');
});

it('shows the source attribution for recipes taken from BBC Food', function () {
    $recipe = Recipe::where('slug', 'mango-pie')->sole();

    $this->get(route('recipes.show', $recipe))
        ->assertOk()
        ->assertSee('BBC Food')
        ->assertSee($recipe->source_url, escape: false);
});

it('returns 404 for an unknown recipe', function () {
    $this->get('/recipes/does-not-exist')->assertNotFound();
});

it('resolves a recipe by its slug rather than its id', function () {
    $recipe = Recipe::first();

    expect(route('recipes.show', $recipe))->toContain($recipe->slug);
});
