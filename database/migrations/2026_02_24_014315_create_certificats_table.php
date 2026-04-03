<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificats', function (Blueprint $table) {
            $table->id();
            $table->string('nom_certificat');
            // Ajout des formations d'officiers dans l'enum
            $table->enum('niveau_certificat', [
                'CAT1', 'CAT2', 'CIA', 'BA1', 'BA2', 'CT1', 'BMP1','BMP2','BS','CT2',
                'APLI', 'CFCU', 'CEM', 'CERT_EM', 'ECOLE_GUERRE'
            ]);
            $table->string('grade_associe');
            $table->json('conditions'); // Stockera les conditions spécifiques (grade, âge, etc.)
            $table->integer('anciennete_requise')->nullable();
            $table->string('certificat_precedent')->nullable();
            $table->integer('duree_certificat_precedent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificats');
    }
};