<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->foreignId('separation_mode_id')
                ->nullable()
                ->constrained('separation_modes')
                ->nullOnDelete()
                ->after('physical_path');
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['separation_mode_id']);
            $table->dropColumn('separation_mode_id');
        });
    }
};
