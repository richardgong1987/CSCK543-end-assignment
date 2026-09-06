<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            // Singular display form, e.g. "g", "tbsp", "clove".
            $table->string('name')->unique();
            // NULL where the plural is written the same way ("2 tbsp", "500 g").
            $table->string('plural_name')->nullable();
            // False for metric symbols written flush against the number ("500g"),
            // true for words that need a space ("2 tbsp", "3 cloves").
            $table->boolean('requires_space')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
