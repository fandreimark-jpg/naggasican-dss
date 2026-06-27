<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('lrn', 12)->unique();             // Learner Reference Number (12 digits)
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->foreignId('section_id')
                  ->constrained('sections')
                  ->onDelete('restrict');
            $table->enum('gender', ['male', 'female']);
            $table->date('birthdate')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};