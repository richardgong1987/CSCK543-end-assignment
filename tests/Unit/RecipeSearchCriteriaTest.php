<?php

use App\Services\RecipeSearch;

/**
 * How raw query-string values are read. Nothing here touches the database: the point
 * of these tests is the allow-list that stands between a URL and the SQL it produces.
 */
it('keeps a sort key that is on the allow-list', function () {
    expect(RecipeSearch::fromQuery(['sort' => 'quickest'])->sort)->toBe('quickest');
});

it('falls back to the default sort when the key is not on the allow-list', function () {
    expect(RecipeSearch::fromQuery(['sort' => 'created_at desc'])->sort)->toBe('title');
    expect(RecipeSearch::fromQuery(['sort' => 'title; drop table recipes'])->sort)->toBe('title');
    expect(RecipeSearch::fromQuery(['sort' => ['title']])->sort)->toBe('title');
    expect(RecipeSearch::fromQuery([])->sort)->toBe('title');
});

it('ignores numeric filters that are not one of the offered options', function () {
    $search = RecipeSearch::fromQuery([
        'max_minutes' => '45',
        'min_servings' => '3',
        'min_rating' => '2',
    ]);

    expect($search->maxMinutes)->toBeNull();
    expect($search->minServings)->toBeNull();
    expect($search->minRating)->toBeNull();
});

it('accepts the numeric filters that are offered', function () {
    $search = RecipeSearch::fromQuery([
        'max_minutes' => '60',
        'min_servings' => '4',
        'min_rating' => '5',
    ]);

    expect($search->maxMinutes)->toBe(60);
    expect($search->minServings)->toBe(4);
    expect($search->minRating)->toBe(5);
});

it('reads repeated checkbox values and drops the empty ones', function () {
    $search = RecipeSearch::fromQuery(['diet' => ['vegan', '', 'nut-free', 'vegan']]);

    expect($search->diets)->toBe(['vegan', 'nut-free']);
});

it('reads a single checkbox value that arrives without brackets', function () {
    expect(RecipeSearch::fromQuery(['category' => 'dessert'])->categories)->toBe(['dessert']);
});

it('survives an array where a string was expected', function () {
    $search = RecipeSearch::fromQuery(['q' => ['spaghetti'], 'ingredient' => ['bacon']]);

    expect($search->keyword)->toBe('');
    expect($search->ingredient)->toBe('');
});

it('trims the keyword so that whitespace alone is not a search', function () {
    expect(RecipeSearch::fromQuery(['q' => '  pizza  '])->keyword)->toBe('pizza');
    expect(RecipeSearch::fromQuery(['q' => '   '])->isFiltered())->toBeFalse();
});

it('does not count sorting as filtering, because it hides nothing', function () {
    expect(RecipeSearch::fromQuery(['sort' => 'rating'])->isFiltered())->toBeFalse();
    expect(RecipeSearch::fromQuery(['q' => 'pizza'])->isFiltered())->toBeTrue();
    expect(RecipeSearch::fromQuery(['min_rating' => '4'])->isFiltered())->toBeTrue();
});
