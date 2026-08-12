<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->string('sexe', 1)->nullable()->after('telephone');
            $table->string('groupe_sanguin')->nullable()->after('sexe');
            $table->string('personne_a_contacter')->nullable()->after('groupe_sanguin');
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropColumn(['sexe', 'groupe_sanguin', 'personne_a_contacter']);
        });
    }
};
