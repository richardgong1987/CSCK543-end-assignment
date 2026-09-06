<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['slug', 'name', 'sort_order'])]
class Category extends Model
{
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_category');
    }
}
