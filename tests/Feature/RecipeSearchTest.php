<?php

use App\Services\RecipeSearch;
use Database\Seeders\RecipeSeeder;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\SampleUserSeeder;

beforeEach(function () {
    // The sample users carry the ratings that the rating filter and sort depend on.
    $this->seed([ReferenceDataSeeder::class, RecipeSeeder::class, SampleUserSeeder::class]);
});

/**
 * @param  array<string, mixed>  $query
 * @return list<string>
 */
function searchTitles(array $query): array
{
    return RecipeSearch::fromQuery($query)->query()->pluck('title')->all();
}

describe('keyword search', function () {
    it('matches a recipe title', function () {
        expect(searchTitles(['q' => 'biryani']))->toBe(['Easy lamb biryani']);
    });

    it('matches an ingredient the title never mentions', function () {
        expect(searchTitles(['q' => 'bacon']))
            ->toBe(['Spaghetti bolognese with mushrooms and sun-dried tomatoes']);
    });

    it('matches the chef who wrote the recipe', function () {
        expect(searchTitles(['q' => 'Jo Pratt']))
            ->toBe(['Spaghetti bolognese with mushrooms and sun-dried tomatoes']);
    });

    it('matches a cuisine no other field mentions', function () {
        expect(searchTitles(['q' => 'North African']))->toBe(['Couscous salad']);
    });

    it('gathers matches from every field it searches', function () {
        // "Indian" is the biryani's cuisine and appears in the mango pie's description.
        expect(searchTitles(['q' => 'Indian']))->toBe(['Easy lamb biryani', 'Mango pie']);
    });

    it('matches a dietary label', function () {
        expect(searchTitles(['q' => 'Dairy-free']))->toBe(['Vegan pancakes']);
    });

    it('returns nothing when no recipe mentions the word', function () {
        expect(searchTitles(['q' => 'sauerkraut']))->toBe([]);
    });

    it('ignores case', function () {
        expect(searchTitles(['q' => 'BIRYANI']))->toBe(['Easy lamb biryani']);
    });
});

describe('filters', function () {
    it('narrows to a course', function () {
        expect(searchTitles(['category' => ['dessert']]))
            ->toBe(['Mango pie', 'Plum clafoutis', 'Vegan pancakes']);
    });

    it('returns recipes in any of several courses', function () {
        expect(searchTitles(['category' => ['dessert', 'brunch']]))
            ->toBe(['Mango pie', 'Plum clafoutis', 'Vegan pancakes']);
    });

    it('requires every dietary label, because each one narrows what may be eaten', function () {
        expect(searchTitles(['diet' => ['vegan']]))->toBe(['Couscous salad', 'Vegan pancakes']);
        expect(searchTitles(['diet' => ['vegan', 'dairy-free']]))->toBe(['Vegan pancakes']);
    });

    it('narrows to a cuisine', function () {
        expect(searchTitles(['cuisine' => ['indian']]))->toBe(['Easy lamb biryani']);
    });

    it('finds every recipe using one ingredient', function () {
        expect(searchTitles(['ingredient' => 'olive oil']))
            ->toBe(['Couscous salad', 'Healthy pizza', 'Spaghetti bolognese with mushrooms and sun-dried tomatoes']);
    });

    it('keeps only recipes that fit inside a time limit', function () {
        // Prep and cooking bands together: couscous 40 mins, the other three 60.
        expect(searchTitles(['max_minutes' => 60]))
            ->toBe(['Couscous salad', 'Healthy pizza', 'Mushroom doner', 'Vegan pancakes']);
    });

    it('excludes a recipe whose timing has no upper bound', function () {
        // Lamb biryani marinates overnight, so it fits no "ready in" limit.
        expect(searchTitles(['max_minutes' => 120]))->not->toContain('Easy lamb biryani');
    });

    it('keeps only recipes that serve enough people', function () {
        expect(searchTitles(['min_servings' => 8]))->toBe([
            'Easy lamb biryani',
            'Mango pie',
            'Spaghetti bolognese with mushrooms and sun-dried tomatoes',
        ]);
    });

    it('keeps only recipes averaging at least the chosen rating', function () {
        expect(searchTitles(['min_rating' => 5]))
            ->toBe(['Couscous salad', 'Mushroom doner', 'Vegan pancakes']);
    });

    it('combines filters, so that each one narrows the last', function () {
        expect(searchTitles([
            'diet' => ['vegetarian'],
            'max_minutes' => 60,
            'category' => ['main-course'],
        ]))->toBe(['Couscous salad', 'Healthy pizza', 'Mushroom doner']);
    });

    it('returns an empty result rather than an error when nothing matches', function () {
        expect(searchTitles(['diet' => ['vegan', 'nut-free']]))->toBe([]);
    });
});

describe('sorting', function () {
    it('sorts alphabetically by default', function () {
        expect(searchTitles([]))->toBe(searchTitles(['sort' => 'title']));
        expect(searchTitles([])[0])->toBe('Couscous salad');
    });

    it('reverses the alphabetical order', function () {
        expect(searchTitles(['sort' => 'title_desc']))
            ->toBe(array_reverse(searchTitles(['sort' => 'title'])));
    });

    it('puts the quickest recipe first and the open-ended one last', function () {
        $titles = searchTitles(['sort' => 'quickest']);

        expect($titles[0])->toBe('Couscous salad');
        expect(end($titles))->toBe('Easy lamb biryani');
    });

    it('puts the open-ended recipe first when the longest is wanted', function () {
        expect(searchTitles(['sort' => 'slowest'])[0])->toBe('Easy lamb biryani');
    });

    it('puts the best rated recipe first', function () {
        $best = RecipeSearch::fromQuery(['sort' => 'rating'])->query()->first();

        expect((float) $best->average_rating)->toBe(5.0);
    });

    it('puts the recipe with fewest steps first', function () {
        expect(searchTitles(['sort' => 'steps'])[0])->toBe('Couscous salad');
    });

    it('sorts and filters together', function () {
        expect(searchTitles(['diet' => ['vegetarian'], 'sort' => 'title_desc']))
            ->toBe(['Vegan pancakes', 'Plum clafoutis', 'Mushroom doner', 'Healthy pizza', 'Couscous salad']);
    });

    it('answers every sort the menu offers', function () {
        foreach (array_keys(RecipeSearch::SORTS) as $sort) {
            expect(searchTitles(['sort' => $sort]))->toHaveCount(8);
        }
    });
});

describe('the search page', function () {
    it('lists every recipe when nothing is searched for', function () {
        $this->get(route('recipes.index'))
            ->assertOk()
            ->assertSee('8 recipes.')
            ->assertSee('Sorted by title (A-Z).');
    });

    it('says which order the results are in', function () {
        $this->get(route('recipes.index', ['sort' => 'quickest']))
            ->assertOk()
            ->assertSee('Sorted by total time: shortest first.');
    });

    it('shows only the matching recipes', function () {
        $this->get(route('recipes.index', ['q' => 'biryani']))
            ->assertOk()
            ->assertSee('One recipe matches your search.')
            ->assertSee('Easy lamb biryani')
            ->assertDontSee('Healthy pizza');
    });

    it('says so plainly when a search matches nothing', function () {
        $this->get(route('recipes.index', ['q' => 'sauerkraut']))
            ->assertOk()
            ->assertSee('No recipes match your search.')
            ->assertSee('Nothing here matches every filter you chose.');
    });

    it('puts the search back into the form so it can be adjusted', function () {
        $this->get(route('recipes.index', ['q' => 'pizza', 'category' => ['main-course']]))
            ->assertOk()
            ->assertSee('value="pizza"', escape: false)
            ->assertSee('Clear all filters');
    });

    it('lets a guest search without an account', function () {
        $this->assertGuest();

        $this->get(route('recipes.index', ['q' => 'pizza']))->assertOk();
    });

    it('ignores a sort key that is not offered rather than failing', function () {
        $response = $this->get(route('recipes.index', ['sort' => 'recipes.id; drop table recipes']));

        $response->assertOk();
        expect(searchTitles(['sort' => 'recipes.id; drop table recipes']))
            ->toBe(searchTitles([]));
    });
});
