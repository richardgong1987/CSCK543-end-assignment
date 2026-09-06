<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One rating per user per recipe. `overall` is the score the user gives the
     * recipe as a whole -- it is supplied independently, not averaged from the three
     * optional facets, because a high difficulty score is not a good thing the way a
     * high taste score is. A recipe's average rating is derived at query time and is
     * deliberately not stored on `recipes`.
     */
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('overall');
            $table->unsignedTinyInteger('taste')->nullable();
            $table->unsignedTinyInteger('difficulty')->nullable();
            $table->unsignedTinyInteger('appearance')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'recipe_id']);
            // Serves "average rating for this recipe" aggregation and sorting.
            $table->index(['recipe_id', 'overall']);
        });

        // Scores run from 1 to 5. Kept at the database level so the rule holds for
        // seeders and manual SQL too, not just for requests that go through Laravel.
        // SQLite, which the test suite uses, cannot add a constraint after the fact;
        // there the range is enforced by request validation alone.
        if (DB::getDriverName() === 'mysql') {
            foreach (['overall', 'taste', 'difficulty', 'appearance'] as $column) {
                DB::statement(
                    "ALTER TABLE ratings ADD CONSTRAINT chk_ratings_{$column} CHECK ({$column} IS NULL OR ({$column} BETWEEN 1 AND 5))"
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
