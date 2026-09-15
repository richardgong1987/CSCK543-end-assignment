<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function store(Request $request, Recipe $recipe): RedirectResponse|JsonResponse
    {
        $request->user()
            ->favouriteRecipes()
            ->syncWithoutDetaching([$recipe->id]);

        return $this->respond($request, isFavourite: true, message: 'Recipe saved to favourites.');
    }

    public function destroy(Request $request, Recipe $recipe): RedirectResponse|JsonResponse
    {
        $request->user()
            ->favouriteRecipes()
            ->detach($recipe->id);

        return $this->respond($request, isFavourite: false, message: 'Recipe removed from favourites.');
    }

    /**
     * The recipe page's script asks for JSON so it can update the button in place;
     * a plain form submission, with JavaScript off, gets the usual redirect back.
     */
    private function respond(Request $request, bool $isFavourite, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['is_favourite' => $isFavourite, 'message' => $message]);
        }

        return back()->with('status', $message);
    }
}
