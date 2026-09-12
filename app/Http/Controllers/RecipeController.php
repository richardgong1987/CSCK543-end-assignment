<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Services\RecipeSearch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    /**
     * The recipe listing, which is also the search results page: with no query string
     * the same view shows every recipe in alphabetical order.
     */
    public function index(Request $request): View
    {
        $search = RecipeSearch::fromQuery($request->query());

        return view('recipes.index', [
            'search' => $search,
            // withQueryString keeps the filters attached to the page links, so that
            // page 2 of a search is still that search.
            'recipes' => $search->query()->paginate(12)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->get(),
            'dietaryTags' => DietaryTag::orderBy('name')->get(),
            'cuisines' => Cuisine::orderBy('name')->get(),
            'ingredientNames' => Ingredient::orderBy('name')->pluck('name'),

        ]);
    }

    public function show(Recipe $recipe): View
    {
        $recipe->load([
            'chef',
            'cuisine',
            'prepTimeBand',
            'cookTimeBand',
            'categories',
            'dietaryTags',
            'ingredientSections.ingredients.ingredient',
            'ingredientSections.ingredients.unit',
            'steps',
        ]);

        $isFavourite = auth()->check()
            && auth()->user()->favouriteRecipes()->whereKey($recipe->id)->exists();

        return view('recipes.show', [
            'recipe' => $recipe,
            // Lines that sit outside any named section, in their own listed order.
            'looseIngredients' => $recipe->ingredients()
                ->whereNull('section_id')
                ->with(['ingredient', 'unit'])
                ->get(),
            'averageRating' => $recipe->ratings()->avg('overall'),
            'ratingCount' => $recipe->ratings()->count(),
            'isFavourite' => $isFavourite,
        ]);
    }
}
