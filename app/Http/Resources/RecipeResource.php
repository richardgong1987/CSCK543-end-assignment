<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One recipe in the JSON search results. It exposes the recipe and its aggregate rating
 * only, never who rated or saved it.
 *
 * Expects the recipe to come from RecipeSearch, which loads the relationships and counts
 * read here.
 *
 * @mixin \App\Models\Recipe
 */
class RecipeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'url' => route('recipes.show', $this->resource),
            'image_url' => $this->image_path ? asset($this->image_path) : null,
            'prep_time' => $this->prepTimeBand->name,
            'cook_time' => $this->cookTimeBand->name,
            'servings' => $this->servingsText(),
            'categories' => $this->categories->pluck('name'),
            'dietary_tags' => $this->dietaryTags->pluck('name'),
            'average_rating' => $this->average_rating === null ? null : round((float) $this->average_rating, 1),
            'ratings_count' => $this->ratings_count,
            'steps_count' => $this->steps_count,
        ];
    }
}
