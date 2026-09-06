<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'recipe_id', 'ingredient_id', 'section_id', 'unit_id',
    'quantity', 'quantity_max', 'note', 'sort_order',
])]
class RecipeIngredient extends Model
{
    /**
     * Cooks write "½ tsp", not "0.5 tsp". Amounts are stored as decimals so that
     * they stay comparable, and are converted back to vulgar fractions for display.
     */
    private const FRACTIONS = [
        '0.125' => '⅛',
        '0.250' => '¼',
        '0.333' => '⅓',
        '0.500' => '½',
        '0.667' => '⅔',
        '0.750' => '¾',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'quantity_max' => 'decimal:3',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(RecipeIngredientSection::class, 'section_id');
    }

    /**
     * The whole line as a cook reads it, e.g. "3 garlic cloves, crushed" or
     * "125g self-raising flour, plus extra for dusting".
     */
    public function displayText(): string
    {
        $amount = $this->amountText();

        return implode(', ', array_filter([
            trim(($amount ?? '') . ' ' . $this->ingredientName()),
            $this->note,
        ]));
    }

    private function ingredientName(): string
    {
        // Ingredient names are stored capitalised because they head their own filter
        // lists; mid-line, after an amount, they read as ordinary words.
        if ($this->quantity === null) {
            return ucfirst($this->ingredient->name);
        }

        // Countable ingredients given without a unit take their plural: "2 onions".
        // Measured ones do not, because the unit already carries the number: "2 tbsp onion".
        $isCountedPlural = $this->unit_id === null
            && $this->ingredient->plural_name !== null
            && (float)($this->quantity_max ?? $this->quantity) > 1;

        return $isCountedPlural
            ? lcfirst($this->ingredient->plural_name)
            : lcfirst($this->ingredient->name);
    }

    /**
     * "2 tbsp", "125g", "3-4", or NULL where the source gives no amount at all.
     */
    public function amountText(): ?string
    {
        if ($this->quantity === null) {
            return null;
        }

        $quantity = $this->quantity_max === null
            ? $this->humanise($this->quantity)
            : $this->humanise($this->quantity) . '-' . $this->humanise($this->quantity_max);

        return $this->unit?->format($quantity) ?? $quantity;
    }

    private function humanise(string $value): string
    {
        $whole = (int)floor((float)$value);
        $remainder = number_format((float)$value - $whole, 3, '.', '');
        $fraction = self::FRACTIONS[$remainder] ?? null;

        if ($fraction === null) {
            return rtrim(rtrim(number_format((float)$value, 3, '.', ''), '0'), '.');
        }

        return $whole === 0 ? $fraction : $whole . $fraction;
    }
}
