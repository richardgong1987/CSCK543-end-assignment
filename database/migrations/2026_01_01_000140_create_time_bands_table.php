<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Source recipes express timings as ranges ("less than 30 mins", "1 to 2 hours")
     * rather than exact figures. Holding the label and its bounds here keeps the text
     * a user reads and the numbers a query sorts on in one place, instead of repeating
     * the same label string on every recipe row.
     */
    public function up(): void
    {
        Schema::create('time_bands', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->unsignedSmallInteger('min_minutes');
            // NULL means the band has no upper bound ("over 2 hours").
            $table->unsignedSmallInteger('max_minutes')->nullable();
            // Two bands can share an upper bound ("less than 30 mins" and "10 to 30
            // mins"), so the display and sort order is chosen rather than derived.
            $table->unsignedSmallInteger('sort_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_bands');
    }
};
