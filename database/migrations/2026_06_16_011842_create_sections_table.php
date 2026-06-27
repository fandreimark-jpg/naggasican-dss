<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "Narra", "Molave"
            $table->integer('grade_level');                  // 7, 8, 9, 10
            $table->foreignId('adviser_id')
                  ->constrained('users')
                  ->onDelete('restrict');                    // hindi pwedeng burahin ang user na may section
            $table->string('school_year');                   // e.g. "2024-2025"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};