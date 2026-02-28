<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['corta', 'larga'])->default('corta');
            $table->string('reason')->comment('Ej: baño, almuerzo, vacaciones, etc.');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pauses');
    }
};
