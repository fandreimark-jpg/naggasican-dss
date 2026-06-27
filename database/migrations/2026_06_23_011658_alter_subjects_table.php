<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('grade_level');
        });

        Schema::table('subjects', function (Blueprint $table) {
            // core = lahat ng Grade 11 makikita
            // elective = specific sa track/specialization
            $table->enum('type', ['core', 'elective'])->after('name');
            $table->integer('grade_level')->after('type'); // 11 or 12
            $table->foreignId('track_id')
                  ->nullable()
                  ->after('grade_level')
                  ->constrained('tracks')
                  ->onDelete('set null');
            $table->foreignId('specialization_id')
                  ->nullable()
                  ->after('track_id')
                  ->constrained('specializations')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
            $table->dropForeign(['specialization_id']);
            $table->dropColumn(['type', 'grade_level', 'track_id', 'specialization_id']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('grade_level')->after('name');
        });
    }
};