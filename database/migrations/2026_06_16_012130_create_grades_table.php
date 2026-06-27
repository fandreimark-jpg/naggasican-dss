<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');                     // kapag na-delete ang student, natatanggal din ang grades
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->onDelete('restrict');
            $table->foreignId('section_id')
                  ->constrained('sections')
                  ->onDelete('restrict');
            $table->foreignId('encoded_by')
                  ->constrained('users')
                  ->onDelete('restrict');                    // kung sino ang adviser na nag-encode
            $table->integer('grading_period');               // 1, 2, or 3
            $table->decimal('grade', 5, 2);                 // e.g. 87.50
            $table->string('school_year');                   // e.g. "2024-2025"
            $table->timestamps();

            // Unique constraint: isang grade lang per student per subject per grading per school year
            $table->unique(
                ['student_id', 'subject_id', 'grading_period', 'school_year'],
                'unique_grade_per_period'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};