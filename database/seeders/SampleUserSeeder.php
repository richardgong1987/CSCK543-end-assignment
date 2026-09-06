<?php

namespace Database\Seeders;

use App\Models\Favourite;
use App\Models\Rating;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Fictional accounts with saved recipes and ratings, so that the account page and
 * rating-based sorting have something to show. No real personal data is used.
 *
 * Scores are derived from the user and recipe positions rather than drawn at random,
 * so every developer and every test run sees the same numbers.
 */
class SampleUserSeeder extends Seeder
{
    private const USERS = [
        ['name' => 'Amelia Carter', 'email' => 'amelia@example.test'],
        ['name' => 'Ben Okafor', 'email' => 'ben@example.test'],
        ['name' => 'Chen Wei', 'email' => 'chen@example.test'],
        ['name' => 'Dara Novak', 'email' => 'dara@example.test'],
    ];

    public function run(): void
    {
        $recipes = Recipe::orderBy('id')->get();

        foreach (array_values(self::USERS) as $userIndex => $attributes) {
            $user = User::updateOrCreate(
                ['email' => $attributes['email']],
                ['name' => $attributes['name'], 'password' => Hash::make('password')],
            );

            foreach ($recipes as $recipeIndex => $recipe) {
                // Each user rates most, but not all, recipes.
                if (($userIndex + $recipeIndex) % 4 === 3) {
                    continue;
                }

                Rating::updateOrCreate(
                    ['user_id' => $user->id, 'recipe_id' => $recipe->id],
                    [
                        'overall' => 3 + ($userIndex * 3 + $recipeIndex * 5) % 3,
                        'taste' => 3 + ($userIndex + $recipeIndex * 2) % 3,
                        'difficulty' => 1 + ($userIndex * 2 + $recipeIndex) % 5,
                        'appearance' => 3 + ($userIndex * 5 + $recipeIndex) % 3,
                    ],
                );

                if (($userIndex + $recipeIndex) % 3 === 0) {
                    Favourite::updateOrCreate(['user_id' => $user->id, 'recipe_id' => $recipe->id]);
                }
            }
        }
    }
}
