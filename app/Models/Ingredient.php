<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'plural_name'])]
class Ingredient extends Model
{
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }
}
