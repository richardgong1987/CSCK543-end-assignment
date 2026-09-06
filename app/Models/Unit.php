<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'name', 'plural_name', 'requires_space'])]
class Unit extends Model
{
    protected function casts(): array
    {
        return ['requires_space' => 'boolean'];
    }

    /**
     * "500g", "2 tbsp", "3 cloves" -- the spacing and plural rules live with the
     * unit so that views do not have to special-case metric symbols.
     */
    public function format(string $quantity): string
    {
        $label = $quantity === '1' || $this->plural_name === null
            ? $this->name
            : $this->plural_name;

        return $this->requires_space ? "{$quantity} {$label}" : "{$quantity}{$label}";
    }
}
