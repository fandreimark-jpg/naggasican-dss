<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')
                  ->constrained('tracks')
                  ->onDelete('cascade');
            $table->string('name');        // e.g. "HUMSS"
            $table->string('code');        // e.g. "HUMSS"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specializations');
    }
};