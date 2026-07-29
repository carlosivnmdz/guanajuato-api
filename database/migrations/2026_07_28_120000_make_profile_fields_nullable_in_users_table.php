<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los clientes que llegan sincronizados desde CATAPULT (dados de
     * alta directo en tienda, no por la app) pueden no tener todos
     * estos datos capturados. Se relajan para que el sync no truene
     * al intentar guardarlos.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->change();
            $table->date('birthday')->nullable()->change();
            $table->string('country')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable(false)->change();
            $table->date('birthday')->nullable(false)->change();
            $table->string('country')->nullable(false)->change();
        });
    }
};
