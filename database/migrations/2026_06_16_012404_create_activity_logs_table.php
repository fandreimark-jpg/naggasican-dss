<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('action');                        // e.g. "encoded grade", "submitted report"
            $table->string('table_name')->nullable();        // e.g. "grades", "report_submissions"
            $table->unsignedBigInteger('record_id')->nullable(); // ID ng record na na-affect
            $table->text('description')->nullable();         // optional detailed description
            $table->string('ip_address', 45)->nullable();   // para sa audit trail
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};