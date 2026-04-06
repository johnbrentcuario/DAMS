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
    Schema::create('physical_locations', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // The Office/Unit name
        $table->string('color')->nullable(); // For UI color-coding
        $table->json('storage_paths')->nullable(); // Array of strings
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_locations');
    }
};
