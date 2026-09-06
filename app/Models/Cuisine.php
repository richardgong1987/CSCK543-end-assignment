<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name'])]
class Cuisine extends Model
{
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
