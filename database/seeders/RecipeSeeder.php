<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Chef;
use App\Models\Cuisine;
use App\Models\DietaryTag;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\TimeBand;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Loads the eight recipes from database/seeders/data/recipes.php.
 *
 * Chefs, cuisines, dietary tags and ingredients are created on demand from the recipe
 * data, so adding a recipe that uses a new ingredient needs no separate edit. The
 * fixed vocabularies -- time bands, units and categories -- must already exist, and
 * are seeded by ReferenceDataSeeder.
 */
class RecipeSeeder extends Seeder
{
    private array $ingredientPlurals = [];

    public function run(): void
    {
        $this->ingredientPlurals = (require database_path('seeders/data/reference.php'))['ingredient_plurals'];

        foreach (require database_path('seeders/data/recipes.php') as $data) {
            DB::transaction(fn () => $this->seedRecipe($data));
        }
    }

    private function seedRecipe(array $data): void
    {
        $recipe = Recipe::updateOrCreate(['slug' => $data['slug']], [
            'title' => $data['title'],
            'description' => $data['description'],
            'tips' => $data['tips'],
            'chef_id' => $this->chefId($data['chef']),
            'cuisine_id' => $this->cuisineId($data['cuisine']),
            'prep_time_band_id' => $this->timeBandId($data['prep_time_band']),
            'cook_time_band_id' => $this->timeBandId($data['cook_time_band']),
            'servings_min' => $data['servings_min'],
            'servings_max' => $data['servings_max'],
            'image_path' => $data['image_path'],
            'source_url' => $data['source_url'],
        ]);

        // Re-seeding replaces the recipe's lines rather than appending to them.
        $recipe->ingredients()->delete();
        $recipe->ingredientSections()->delete();
        $recipe->steps()->delete();

        $recipe->categories()->sync($this->categoryIds($data['categories']));
        $recipe->dietaryTags()->sync($this->dietaryTagIds($data['dietary_tags']));

        $this->seedIngredients($recipe, $data['sections']);
        $this->seedSteps($recipe, $data['steps']);
    }

    private function seedIngredients(Recipe $recipe, array $sections): void
    {
        // Ordering is continuous across sections so that the list can be rendered
        // either grouped by section or as one flat list.
        $lineNumber = 1;

        foreach ($sections as $sectionNumber => $section) {
            $sectionId = $section['title'] === null ? null : $recipe->ingredientSections()->create([
                'title' => $section['title'],
                'sort_order' => $sectionNumber + 1,
            ])->id;

            foreach ($section['ingredients'] as $line) {
                $recipe->ingredients()->create([
                    'ingredient_id' => $this->ingredientId($line['ingredient']),
                    'section_id' => $sectionId,
                    'unit_id' => $this->unitId($line['unit']),
                    'quantity' => $line['quantity'],
                    'quantity_max' => $line['quantity_max'] ?? null,
                    'note' => $line['note'],
                    'sort_order' => $lineNumber++,
                ]);
            }
        }
    }

    private function seedSteps(Recipe $recipe, array $steps): void
    {
        foreach ($steps as $index => $step) {
            $recipe->steps()->create([
                'step_number' => $index + 1,
                'instruction' => $step['instruction'],
                'duration_minutes' => $step['minutes'],
            ]);
        }
    }

    private function chefId(?string $name): ?int
    {
        return $name === null ? null
            : Chef::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
    }

    private function cuisineId(?string $name): ?int
    {
        return $name === null ? null
            : Cuisine::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
    }

    private function ingredientId(string $name): int
    {
        return Ingredient::updateOrCreate(['slug' => Str::slug($name)], [
            'name' => $name,
            'plural_name' => $this->ingredientPlurals[$name] ?? null,
        ])->id;
    }

    private function unitId(?string $name): ?int
    {
        return $name === null ? null : Unit::where('slug', Str::slug($name))->sole()->id;
    }

    private function timeBandId(string $name): int
    {
        return TimeBand::where('slug', Str::slug($name))->sole()->id;
    }

    /** @return array<int, int> */
    private function categoryIds(array $names): array
    {
        $ids = Category::whereIn('slug', array_map(Str::slug(...), $names))->pluck('id')->all();

        if (count($ids) !== count($names)) {
            throw new \RuntimeException('Unknown category in: '.implode(', ', $names));
        }

        return $ids;
    }

    /** @return array<int, int> */
    private function dietaryTagIds(array $names): array
    {
        return array_map(
            fn (string $name) => DietaryTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id,
            $names,
        );
    }
}
