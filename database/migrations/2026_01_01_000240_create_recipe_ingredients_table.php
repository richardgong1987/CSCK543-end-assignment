<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One line of a recipe's ingredient list: how much of which ingredient, and how
     * it should be prepared. A surrogate key is used rather than (recipe_id,
     * ingredient_id) because the same ingredient legitimately appears twice in one
     * recipe -- olive oil in both the base and the topping, for example.
     */
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            // NULL means the line is not under a named heading.
            $table->foreignId('section_id')->nullable()
                ->constrained('recipe_ingredient_sections')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->restrictOnDelete();

            // NULL where the source states no amount at all ("icing sugar, for dusting").
            $table->decimal('quantity', 8, 3)->nullable();
            // Upper bound for ranges such as "3-4 green chillies"; NULL for exact amounts.
            $table->decimal('quantity_max', 8, 3)->nullable();
            // Qualifier printed after the ingredient name: "finely chopped", "to serve".
            $table->string('note')->nullable();
            $table->unsignedSmallInteger('sort_order');

            $table->timestamps();

            $table->unique(['recipe_id', 'sort_order']);
            // Serves the "which recipes use this ingredient" search.
            $table->index('ingredient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
