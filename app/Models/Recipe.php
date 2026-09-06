<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug', 'title', 'description', 'tips', 'chef_id', 'cuisine_id',
    'prep_time_band_id', 'cook_time_band_id', 'servings_min', 'servings_max',
    'image_path', 'source_url',
])]
class Recipe extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function chef(): BelongsTo
    {
        return $this->belongsTo(Chef::class);
    }

    public function cuisine(): BelongsTo
    {
        return $this->belongsTo(Cuisine::class);
    }

    public function prepTimeBand(): BelongsTo
    {
        return $this->belongsTo(TimeBand::class, 'prep_time_band_id');
    }

    public function cookTimeBand(): BelongsTo
    {
        return $this->belongsTo(TimeBand::class, 'cook_time_band_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'recipe_category')->orderBy('sort_order');
    }

    public function dietaryTags(): BelongsToMany
    {
        return $this->belongsToMany(DietaryTag::class, 'recipe_dietary_tag')->orderBy('name');
    }

    public function ingredientSections(): HasMany
    {
        return $this->hasMany(RecipeIngredientSection::class)->orderBy('sort_order');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function favouritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favourites')->withTimestamps();
    }

    /**
     * "Serves 6" or "Serves 6-8", rebuilt from the stored bounds rather than held
     * as a second copy of the same fact.
     */
    public function servingsText(): string
    {
        return $this->servings_min === $this->servings_max
            ? "Serves {$this->servings_min}"
            : "Serves {$this->servings_min}-{$this->servings_max}";
    }

    /**
     * Time the individual steps add up to. This is a different figure from the prep
     * and cook bands, which come from the source recipe, and is shown alongside the
     * step list rather than stored.
     */
    public function totalStepMinutes(): int
    {
        return (int) $this->steps->sum('duration_minutes');
    }

    /**
     * Longest plausible time from starting to serving, used for "under an hour"
     * style filtering. NULL when either band is open-ended, such as "overnight".
     */
    public function maximumTotalMinutes(): ?int
    {
        $prep = $this->prepTimeBand->max_minutes;
        $cook = $this->cookTimeBand->max_minutes;

        return $prep === null || $cook === null ? null : $prep + $cook;
    }
}
