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
        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // Customer ID de CATAPULT
            $table->string('customer_id', 16)->unique();
            // Datos personales
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('birthday');
            // Se mapeará a powerField1 en CATAPULT
            $table->string('country');
            // Contacto (al menos uno será obligatorio mediante validación)
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable()->unique();
            // OTP
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            // No se usa login por contraseña
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Password Reset Tokens
        |--------------------------------------------------------------------------
        */
        Schema::create('password_reset_tokens', function (Blueprint $table) {

            $table->string('email')->primary();

            $table->string('token');

            $table->timestamp('created_at')->nullable();
        });

        /*
        |--------------------------------------------------------------------------
        | Sessions
        |--------------------------------------------------------------------------
        */
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
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
