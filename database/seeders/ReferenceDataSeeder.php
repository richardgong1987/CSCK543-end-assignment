<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TimeBand;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the fixed vocabularies the recipes refer to. Runs before RecipeSeeder,
 * which looks these up by slug.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $reference = require database_path('seeders/data/reference.php');

        foreach ($reference['time_bands'] as $band) {
            TimeBand::updateOrCreate(['slug' => Str::slug($band['name'])], $band);
        }

        foreach ($reference['units'] as $unit) {
            Unit::updateOrCreate(['slug' => Str::slug($unit['name'])], $unit);
        }

        foreach ($reference['categories'] as $category) {
            Category::updateOrCreate(['slug' => Str::slug($category['name'])], $category);
        }
    }
}
