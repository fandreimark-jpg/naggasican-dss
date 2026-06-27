<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Palitan ang grade_level — SHS na (11 at 12 lang)
            $table->dropColumn('grade_level');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->integer('grade_level')->after('name'); // 11 or 12
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
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign(['track_id']);
            $table->dropForeign(['specialization_id']);
            $table->dropColumn(['track_id', 'specialization_id']);
            $table->dropColumn('grade_level');
        });

        Schema::table('sections', function (Blueprint $table) {
            $table->integer('grade_level')->after('name');
        });
    }
};