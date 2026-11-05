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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('curp', 18)->unique();
            $table->string('name', 30); //nombre
            $table->string('surname', 30); // ap_pat
            $table->string('second_surname', 30); //ap_mat
            $table->string('state', 30); //estado
            $table->string('municipality', 50); //municipio
            $table->string('ocupation')->nullable(); //ocupacion
            $table->string('education_level', 50); //nivel de estudios
            $table->string('major_title'); //dooc aprovatorio
            $table->enum('institution', ['PÚBLICA', 'PRIVADA']);
            $table->string('workplace')->default('HR Ratings Media S.A de C.V')->nullable(); //centro de trabajo
            $table->string('area', 30)->nullable();
            $table->string('major')->nullable(); //puesto
            $table->string('major_description')->nullable(); //puesto en español
            $table->string('email')->unique()->nullable();
            $table->string('google_id')->unique()->nullable(); //NOTE - inicio de sesion con google
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('ocupation_key')->nullable();
            $table->string('employee_number', 20)->unique()->nullable(); // NUMERO DE COLABORADOR
            $table->string('username', 50)->unique()->nullable();        // NOMBRE DE USUARIO
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('ocupation_key')->references('key')->on('ocupations');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
