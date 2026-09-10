<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liturgical_calendar_events', function (Blueprint $table) {
            $table->id();

            // Core fields
            $table->date('date')->index();
            $table->string('title');
            $table->enum('rank', [
                'solemnity',
                'feast',
                'memorial',
                'optional_memorial',
                'weekday',
            ])->index();

            // Liturgical color (name + hex)
            $table->enum('color', [
                'violet',
                'white',
                'red',
                'green',
                'rose',
            ])->nullable();
            $table->string('color_hex', 10)->nullable();

            // Season
            $table->enum('season', [
                'advent',
                'christmas',
                'lent',
                'triduum',
                'easter',
                'ordinary_time',
            ])->index();

            // Liturgical year (e.g., 2026, 2027)
            $table->integer('year')->index();

            // Readings (citations + lectionary numbers)
            $table->json('readings')->nullable();

            // Notes (transfers, abrogations, US proper calendar notes)
            $table->json('notes')->nullable();

            // Metadata (holy day, rank priority, weekday cycle, LOTH volume, etc.)
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liturgical_calendar_events');
    }
};
