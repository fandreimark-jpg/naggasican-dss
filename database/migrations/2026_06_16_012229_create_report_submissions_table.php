<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')
                  ->constrained('sections')
                  ->onDelete('cascade');
            $table->foreignId('submitted_by')
                  ->constrained('users')
                  ->onDelete('restrict');                    // adviser na nag-submit
            $table->integer('grading_period');               // 1, 2, or 3
            $table->enum('status', ['pending', 'submitted', 'approved'])
                  ->default('pending');
            $table->string('school_year');
            $table->timestamp('submitted_at')->nullable();   // exact time ng submission
            $table->timestamp('approved_at')->nullable();    // exact time ng approval ng admin
            $table->timestamps();

            // Isang submission lang per section per grading period per school year
            $table->unique(
                ['section_id', 'grading_period', 'school_year'],
                'unique_submission_per_period'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_submissions');
    }
};