<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_dietary_tag', function (Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dietary_tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['recipe_id', 'dietary_tag_id']);
            // Serves "all vegan recipes" style filtering.
            $table->index('dietary_tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_dietary_tag');
    }
};
