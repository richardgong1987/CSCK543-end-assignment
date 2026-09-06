<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A recipe belongs to one or more categories and a category holds many recipes.
     * The junction carries no attributes of its own, so the pair of foreign keys is
     * the primary key.
     */
    public function up(): void
    {
        Schema::create('recipe_category', function (Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            $table->primary(['recipe_id', 'category_id']);
            // The primary key already serves recipe -> categories; this serves the
            // "all recipes in this category" direction.
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_category');
    }
};
