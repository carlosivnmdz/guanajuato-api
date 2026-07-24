<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {

            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->morphs('otpable');

        });
    }

    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {

            $table->dropMorphs('otpable');

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

        });
    }
};