<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cambia la columna duration de integer a decimal(8,2) para admitir números con punto decimal.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('duration', 8, 2)->change();
        });

        Schema::table('additional_courses', function (Blueprint $table) {
            $table->decimal('duration', 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedInteger('duration')->change();
        });

        Schema::table('additional_courses', function (Blueprint $table) {
            $table->unsignedInteger('duration')->change();
        });
    }
};
