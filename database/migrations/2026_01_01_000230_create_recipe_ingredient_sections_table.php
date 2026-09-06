<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Longer recipes group their ingredients under headings such as "For the base"
     * and "For the topping". The heading belongs to the recipe, not to the ingredient
     * line, so it is held here rather than repeated on every line beneath it.
     */
    public function up(): void
    {
        Schema::create('recipe_ingredient_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();

            $table->unique(['recipe_id', 'sort_order']);
            // One recipe cannot have two sections with the same heading.
            $table->unique(['recipe_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredient_sections');
    }
};
