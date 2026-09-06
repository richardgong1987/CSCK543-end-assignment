<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['slug', 'name'])]
class DietaryTag extends Model
{
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_dietary_tag');
    }
}
