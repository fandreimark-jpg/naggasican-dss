<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');
            $table->integer('grading_period');               // 1, 2, or 3
            $table->decimal('average_grade', 5, 2);         // computed average across all subjects
            $table->enum('risk_level', ['low', 'moderate', 'high']);
            $table->string('school_year');
            $table->timestamp('generated_at');               // kung kailan na-generate ng Python analytics
            $table->timestamps();

            // I-update lang ang risk result, hindi duplicate
            $table->unique(
                ['student_id', 'grading_period', 'school_year'],
                'unique_risk_per_period'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_results');
    }
};