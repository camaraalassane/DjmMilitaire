<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->string('position_actuelle')->nullable()->after('specialite');
            $table->string('fonction_passee')->nullable()->after('position_actuelle');
            $table->string('fonction_actuelle')->nullable()->after('fonction_passee');
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropColumn(['position_actuelle', 'fonction_passee', 'fonction_actuelle']);
        });
    }
};