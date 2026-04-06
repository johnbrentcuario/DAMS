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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->string('fullname');
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->string('priority', 16)->default('normal');
            $table->boolean('completed')->default(false);
            $table->timestamps();
            $table->foreignId('physical_location_id')->nullable()->constrained()->onDelete('set null');
            $table->string('physical_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
