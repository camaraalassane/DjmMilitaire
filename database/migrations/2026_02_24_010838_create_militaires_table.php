<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('militaires', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->date('date_entree_service');
            $table->string('grade_actuel');
            $table->date('date_derniere_promotion')->nullable();
            $table->date('date_retraite')->nullable();
            $table->string('specialite')->nullable();
            $table->enum('statut', ['actif', 'retraité', 'déserteur', 'décédé', 'démobilisé'])->default('actif');

            // Formations de base (existantes)
            $table->boolean('a_fait_cat1')->default(false);
            $table->boolean('a_fait_cat2')->default(false);
            $table->boolean('a_fait_cia')->default(false);
            $table->boolean('a_fait_ba1')->default(false);
            $table->boolean('a_fait_ba2')->default(false);
            $table->date('date_obtention_cat1')->nullable();
            $table->date('date_obtention_cat2')->nullable();
            $table->date('date_obtention_cia')->nullable();
            $table->date('date_obtention_ba1')->nullable();
            $table->date('date_obtention_ba2')->nullable();

            // --- Nouveaux certificats (BMP, BS, CT2) ---
            $table->boolean('a_fait_bmp1')->default(false);
            $table->boolean('a_fait_bmp2')->default(false);
            $table->boolean('a_fait_bs')->default(false);
            $table->boolean('a_fait_ct2')->default(false);
            $table->date('date_obtention_bmp1')->nullable();
            $table->date('date_obtention_bmp2')->nullable();
            $table->date('date_obtention_bs')->nullable();
            $table->date('date_obtention_ct2')->nullable();

            // Formations des officiers (existantes)
            $table->boolean('a_fait_apli')->default(false); // Cour d'Application (APLI)
            $table->date('date_obtention_apli')->nullable();

            // Cour des Capitaines / CFCU / CPO (perfectionnement des officiers)
            $table->boolean('a_fait_cfcu')->default(false);
            $table->date('date_obtention_cfcu')->nullable();

            // Cour d'état-major (CEM)
            $table->boolean('a_fait_cem')->default(false);
            $table->date('date_obtention_cem')->nullable();

            // Certificat d'état-major (ancienne version, à conserver pour compatibilité)
            $table->boolean('a_fait_certificat_em')->default(false);
            $table->date('date_obtention_certificat_em')->nullable();

            // École de guerre
            $table->boolean('a_fait_ecole_guerre')->default(false);
            $table->date('date_obtention_ecole_guerre')->nullable();

            // --- Nouvelle formation officier : CPO (Cour de Perfectionnement des Officiers) ---
            $table->boolean('a_fait_cpo')->default(false);
            $table->date('date_obtention_cpo')->nullable();

            // --- Nouvelle formation : Certificat d'état-major (nom complet) ---
            $table->boolean('a_fait_certificat_etat_major')->default(false);
            $table->date('date_obtention_certificat_etat_major')->nullable();

            // Autres informations
            $table->boolean('a_permis_conduire')->default(false);
            $table->boolean('a_fait_justice')->default(false);
            $table->boolean('a_fait_discipline')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('militaires');
    }
};