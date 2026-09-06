<?php

use App\Models\Ingredient;
use App\Models\RecipeIngredient;
use App\Models\Unit;

/**
 * The stored parts of an ingredient line -- amount, unit, ingredient, note -- have to
 * come back together as something a cook can read.
 */
function line(?float $quantity, ?Unit $unit, Ingredient $ingredient, ?string $note = null, ?float $max = null): RecipeIngredient
{
    $line = new RecipeIngredient([
        'quantity' => $quantity,
        'quantity_max' => $max,
        'note' => $note,
    ]);

    $line->unit_id = $unit?->id;
    $line->setRelation('unit', $unit);
    $line->setRelation('ingredient', $ingredient);

    return $line;
}

function unit(string $name, ?string $plural, bool $requiresSpace): Unit
{
    $unit = new Unit(['name' => $name, 'plural_name' => $plural, 'requires_space' => $requiresSpace]);
    $unit->id = 1;

    return $unit;
}

it('writes metric symbols against the number and words after a space', function () {
    $flour = new Ingredient(['name' => 'Self-raising flour']);

    expect(line(125, unit('g', null, false), $flour)->displayText())
        ->toBe('125g self-raising flour');

    expect(line(2, unit('tbsp', null, true), new Ingredient(['name' => 'Caster sugar']))->displayText())
        ->toBe('2 tbsp caster sugar');
});

it('pluralises units above one', function () {
    $garlic = new Ingredient(['name' => 'Garlic']);
    $clove = unit('clove', 'cloves', true);

    expect(line(1, $clove, $garlic)->displayText())->toBe('1 clove garlic');
    expect(line(3, $clove, $garlic, 'crushed')->displayText())->toBe('3 cloves garlic, crushed');
});

it('pluralises counted ingredients that carry no unit', function () {
    $onion = new Ingredient(['name' => 'Onion', 'plural_name' => 'onions']);

    expect(line(1, null, $onion, 'finely sliced')->displayText())->toBe('1 onion, finely sliced');
    expect(line(2, null, $onion, 'finely sliced')->displayText())->toBe('2 onions, finely sliced');
});

it('does not pluralise an ingredient that is measured rather than counted', function () {
    // "200g yoghurt", never "200g yoghurts".
    $yoghurt = new Ingredient(['name' => 'Yoghurt']);

    expect(line(200, unit('g', null, false), $yoghurt, 'Greek or natural')->displayText())
        ->toBe('200g yoghurt, Greek or natural');
});

it('shows amounts as fractions rather than decimals', function () {
    $tsp = unit('tsp', null, true);

    expect(line(0.25, $tsp, new Ingredient(['name' => 'Vanilla extract']))->displayText())
        ->toBe('¼ tsp vanilla extract');

    expect(line(1.5, unit('tbsp', null, true), new Ingredient(['name' => 'Milk']))->displayText())
        ->toBe('1½ tbsp milk');
});

it('shows a range when the source gives one', function () {
    expect(line(3, null, new Ingredient(['name' => 'Green chilli', 'plural_name' => 'green chillies']), 'finely chopped', 4)->displayText())
        ->toBe('3-4 green chillies, finely chopped');

    expect(line(800, unit('g', null, false), new Ingredient(['name' => 'Spaghetti']), 'dried', 1000)->displayText())
        ->toBe('800-1000g spaghetti, dried');
});

it('starts the line with the ingredient when the source states no amount', function () {
    expect(line(null, null, new Ingredient(['name' => 'Icing sugar']), 'for dusting')->displayText())
        ->toBe('Icing sugar, for dusting');
});
