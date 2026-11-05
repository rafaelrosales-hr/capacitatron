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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('duration', 8, 2);
            $table->time('start_time'); // hora de incio del curso
            $table->date('start_course');
            $table->date('end_course')->nullable();
            $table->enum('agent_type', ['INTERNO', 'EXTERNO', 'OTRO']);
            $table->string('rfc_agent')->nullable();
            $table->enum('format', ['PRESENCIAL', 'ONLINE', 'HIBRIDO']);
            $table->text('training_objetive');
            $table->string('institution');
            $table->string('teacher');
            $table->boolean('is_active')->default(true);
            $table->year('year')->nullable();
            $table->unsignedBigInteger('subject_area_key');
            $table->timestamps();

            $table->foreign('subject_area_key')->references('key')->on('subject_areas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
