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

it('serves the recipe photo as WebP copies sized for the screen', function () {
    $recipe = Recipe::where('slug', 'healthy-pizza')->sole();

    $this->get(route('recipes.show', $recipe))
        ->assertSee(
            'srcset="'.asset('images/recipes/healthy-pizza-416.webp').' 416w, '
                .asset('images/recipes/healthy-pizza-640.webp').' 640w, '
                .asset('images/recipes/healthy-pizza-832.webp').' 832w"',
            escape: false,
        );
});

it('has a WebP copy in every width for every recipe photo', function () {
    foreach (Recipe::pluck('image_path') as $imagePath) {
        foreach ([416, 640, 832] as $width) {
            expect(public_path(str_replace('.jpg', "-{$width}.webp", $imagePath)))->toBeFile();
        }
    }
});

it('loads only the first photo of the listing straight away', function () {
    $html = $this->get(route('recipes.index'))->getContent();

    expect(substr_count($html, 'fetchpriority="high"'))->toBe(1)
        ->and(substr_count($html, 'loading="lazy"'))->toBe(7);
});
