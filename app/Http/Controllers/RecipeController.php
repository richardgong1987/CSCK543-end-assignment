<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Contracts\View\View;

class RecipeController extends Controller
{
    /**
     * A plain listing of every recipe. Search and sorting are added on top of this
     * by the search workstream; the query below is deliberately unfiltered.
     */
    public function index(): View
    {
        $recipes = Recipe::query()
            ->with(['categories', 'dietaryTags', 'prepTimeBand', 'cookTimeBand'])
            ->withAvg('ratings as average_rating', 'overall')
            ->withCount('ratings')
            ->orderBy('title')
            ->paginate(12);

        return view('recipes.index', ['recipes' => $recipes]);
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

        return view('recipes.show', [
            'recipe' => $recipe,
            // Lines that sit outside any named section, in their own listed order.
            'looseIngredients' => $recipe->ingredients()
                ->whereNull('section_id')
                ->with(['ingredient', 'unit'])
                ->get(),
            'averageRating' => $recipe->ratings()->avg('overall'),
            'ratingCount' => $recipe->ratings()->count(),
        ]);
    }
}
