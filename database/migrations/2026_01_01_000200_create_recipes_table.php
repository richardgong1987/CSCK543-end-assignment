<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description');
            $table->text('tips')->nullable();

            $table->foreignId('chef_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cuisine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prep_time_band_id')->constrained('time_bands')->restrictOnDelete();
            $table->foreignId('cook_time_band_id')->constrained('time_bands')->restrictOnDelete();

            // "Serves 6-8" is stored as its bounds; a single figure repeats in both.
            // The display text is rendered from these rather than stored again.
            $table->unsignedSmallInteger('servings_min');
            $table->unsignedSmallInteger('servings_max');

            $table->string('image_path')->nullable();
            // NULL means the recipe has no external source, i.e. it originated here.
            $table->string('source_url')->nullable();

            $table->timestamps();

            // Serves title keyword search and the default alphabetical ordering.
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
