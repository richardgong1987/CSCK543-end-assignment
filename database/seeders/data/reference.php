<?php

/**
 * Fixed vocabularies the recipe data refers to. These change only when the
 * application's own vocabulary changes, so they are kept apart from the recipes.
 */
return [
    /**
     * Timing bands, ordered from quickest to slowest. `max_minutes` is NULL where the
     * band is open-ended. Two bands can share bounds, so the order is stated, not derived.
     */
    'time_bands' => [
        ['name' => 'no cooking required', 'min_minutes' => 0, 'max_minutes' => 0, 'sort_order' => 1],
        ['name' => 'less than 10 mins', 'min_minutes' => 0, 'max_minutes' => 10, 'sort_order' => 2],
        ['name' => 'less than 30 mins', 'min_minutes' => 0, 'max_minutes' => 30, 'sort_order' => 3],
        ['name' => '10 to 30 mins', 'min_minutes' => 10, 'max_minutes' => 30, 'sort_order' => 4],
        ['name' => '30 mins to 1 hour', 'min_minutes' => 30, 'max_minutes' => 60, 'sort_order' => 5],
        ['name' => '1 to 2 hours', 'min_minutes' => 60, 'max_minutes' => 120, 'sort_order' => 6],
        ['name' => 'over 2 hours', 'min_minutes' => 120, 'max_minutes' => null, 'sort_order' => 7],
        ['name' => 'overnight', 'min_minutes' => 480, 'max_minutes' => null, 'sort_order' => 8],
    ],

    /**
     * `requires_space` is false for metric symbols written against the number ("500g")
     * and true for words ("2 tbsp"). `plural_name` is NULL where the plural is written
     * the same way.
     */
    'units' => [
        ['name' => 'g', 'plural_name' => null, 'requires_space' => false],
        ['name' => 'kg', 'plural_name' => null, 'requires_space' => false],
        ['name' => 'ml', 'plural_name' => null, 'requires_space' => false],
        ['name' => 'tbsp', 'plural_name' => null, 'requires_space' => true],
        ['name' => 'tsp', 'plural_name' => null, 'requires_space' => true],
        ['name' => 'clove', 'plural_name' => 'cloves', 'requires_space' => true],
        ['name' => 'rasher', 'plural_name' => 'rashers', 'requires_space' => true],
        ['name' => 'glass', 'plural_name' => 'glasses', 'requires_space' => true],
        ['name' => 'tin', 'plural_name' => 'tins', 'requires_space' => true],
        ['name' => 'jar', 'plural_name' => 'jars', 'requires_space' => true],
        ['name' => 'pinch', 'plural_name' => 'pinches', 'requires_space' => true],
        ['name' => 'drop', 'plural_name' => 'drops', 'requires_space' => true],
        ['name' => 'handful', 'plural_name' => 'handfuls', 'requires_space' => true],
    ],

    /**
     * Course categories. A recipe belongs to one or more of these; dietary
     * classifications are held separately in `dietary_tags`.
     */
    'categories' => [
        ['name' => 'Starter', 'sort_order' => 1],
        ['name' => 'Brunch', 'sort_order' => 2],
        ['name' => 'Light meals and snacks', 'sort_order' => 3],
        ['name' => 'Main course', 'sort_order' => 4],
        ['name' => 'Side dish', 'sort_order' => 5],
        ['name' => 'Dessert', 'sort_order' => 6],
    ],

    /**
     * Plural forms for ingredients that are counted rather than measured. Every other
     * ingredient is stored with a NULL plural and is never pluralised on screen.
     */
    'ingredient_plurals' => [
        'Bay leaf' => 'bay leaves',
        'Courgette' => 'courgettes',
        'Egg' => 'eggs',
        'Green chilli' => 'green chillies',
        'Lime' => 'limes',
        'Onion' => 'onions',
        'Pepper' => 'peppers',
        'Pickled chilli' => 'pickled chillies',
        'Pitta bread' => 'pitta breads',
        'Preserved lemon' => 'preserved lemons',
        'Red onion' => 'red onions',
        'Sun-dried tomato' => 'sun-dried tomatoes',
        'Tomato' => 'tomatoes',
    ],
];
