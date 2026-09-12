<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function store(Request $request, Recipe $recipe): RedirectResponse
    {
        $request->user()
            ->favouriteRecipes()
            ->syncWithoutDetaching([$recipe->id]);

        return back()->with('status', 'Recipe saved to favourites.');
    }

    public function destroy(Request $request, Recipe $recipe): RedirectResponse
    {
        $request->user()
            ->favouriteRecipes()
            ->detach($recipe->id);

        return back()->with('status', 'Recipe removed from favourites.');
    }
}
