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
        Schema::create('additional_courses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->decimal('duration', 8, 2);
            $table->string('institution', 150);
            $table->boolean('has_certification')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('url_course')->nullable();
            $table->string('certificate_path')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('invoice_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_courses');
    }
};
