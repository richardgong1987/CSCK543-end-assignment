<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    /**
     * Create the user's rating for a recipe, or replace the one they gave before.
     */
    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        // The 1-5 range matches the CHECK constraints on the ratings table.
        $scores = $request->validate([
            'overall' => ['required', 'integer', 'between:1,5'],
            'taste' => ['nullable', 'integer', 'between:1,5'],
            'difficulty' => ['nullable', 'integer', 'between:1,5'],
            'appearance' => ['nullable', 'integer', 'between:1,5'],
        ]);

        // A facet left out means "not rated", so it must clear a score from an earlier rating.
        $scores += ['taste' => null, 'difficulty' => null, 'appearance' => null];

        $request->user()
            ->ratings()
            ->updateOrCreate(['recipe_id' => $recipe->id], $scores);

        return back()->with('status', 'Your rating has been saved.');
    }
}
