<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Services\RecipeSearch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeSearchController extends Controller
{
    /**
     * The JSON twin of the recipe listing. It takes the same query string and runs the same
     * RecipeSearch, so a search here always returns what /recipes shows for it.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $recipes = RecipeSearch::fromQuery($request->query())
            ->query()
            ->paginate(12)
            ->withQueryString();

        return RecipeResource::collection($recipes);
    }
}
