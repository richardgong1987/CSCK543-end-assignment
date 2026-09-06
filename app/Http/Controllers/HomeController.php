<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DietaryTag;
use App\Models\Recipe;
use App\Services\RecipeSearch;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * The front page is a way in to the recipes rather than a page of its own: every
     * link on it is a search, so it is built from the same service the listing uses.
     */
    public function index(): View
    {
        return view('home', [
            'recipeCount' => Recipe::count(),
            'featured' => RecipeSearch::fromQuery(['sort' => 'rating'])->query()->take(3)->get(),
            // Counts let the page leave out a course or a diet nothing is filed under,
            // so no link on the front page leads to an empty result.
            'categories' => Category::withCount('recipes')->orderBy('sort_order')->get(),
            'dietaryTags' => DietaryTag::withCount('recipes')->orderBy('name')->get(),
        ]);
    }
}
