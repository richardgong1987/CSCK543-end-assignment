<?php

namespace App\Models;

use App\Support\Duration;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['recipe_id', 'step_number', 'instruction', 'duration_minutes'])]
class RecipeStep extends Model
{
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function durationText(): string
    {
        return Duration::format($this->duration_minutes);
    }
}
