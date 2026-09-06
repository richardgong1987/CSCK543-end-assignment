<?php

use App\Models\User;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\SampleUserSeeder;

beforeEach(function () {
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class, SampleUserSeeder::class]);
});

it('greets a visitor with the application name rather than the framework name', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(config('app.name'))
        ->assertDontSee('Laravel');
});

it('says how many recipes there are to search', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('8 recipes, searchable by title, ingredient, course, diet,');
});

it('offers a search box that submits to the recipe listing', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('action="'.route('recipes.index').'"', escape: false)
        ->assertSee('name="q"', escape: false);
});

it('links to each course and dietary label as a ready-made search', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(route('recipes.index', ['category' => ['dessert']]), escape: false)
        ->assertSee(route('recipes.index', ['diet' => ['vegan']]), escape: false);
});

it('shows the highest rated recipes', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Highest rated right now');
});

it('invites a guest to register, and stops once they have', function () {
    $this->get(route('home'))->assertSee('Keep the ones you like');

    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertDontSee('Keep the ones you like');
});

/**
 * Every shortcut on the front page promises results, so none of them may lead to an
 * empty listing. This is the test that catches a limit chosen without checking the data.
 */
it('offers no shortcut that leads nowhere', function () {
    $shortcuts = [
        ['max_minutes' => 60],
        ['sort' => 'rating'],
        ['sort' => 'steps'],
        ['min_servings' => 8],
    ];

    foreach ($shortcuts as $query) {
        $this->get(route('recipes.index', $query))
            ->assertOk()
            ->assertDontSee('No recipes match your search.');
    }
});
