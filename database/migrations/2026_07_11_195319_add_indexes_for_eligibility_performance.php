<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Index sur la table militaires
        Schema::table('militaires', function (Blueprint $table) {
            // Index pour les filtres de recherche
            $table->index('statut');
            $table->index('grade_actuel');
            $table->index('matricule');
            $table->index(['nom', 'prenom']);

            // Index composés pour les requêtes fréquentes
            $table->index(['statut', 'grade_actuel']);
            $table->index(['statut', 'date_entree_service']);
            $table->index(['grade_actuel', 'date_derniere_promotion']);

            // Index pour les calculs d'ancienneté
            $table->index('date_entree_service');
            $table->index('date_derniere_promotion');
            $table->index('date_naissance');

            // Index pour les booléens fréquemment utilisés
            $table->index('a_permis_conduire');
            $table->index('a_fait_justice');
            $table->index('a_fait_discipline');
        });

        // Index sur la table certificat_militaire (pivot)
        Schema::table('certificat_militaire', function (Blueprint $table) {
            $table->index('date_obtention');
            $table->index(['militaire_id', 'certificat_id']);
        });

        // Index sur la table alertes
        Schema::table('alertes', function (Blueprint $table) {
            $table->index(['militaire_id', 'type_alerte']);
            $table->index('est_vue');
            $table->index('date_echeance');
        });

        // Index sur la table contrats
        Schema::table('contrats', function (Blueprint $table) {
            $table->index(['militaire_id', 'statut']);
            $table->index('date_debut');
            $table->index('date_fin');
            $table->index('statut');
        });

        // Index sur la table eligibilites (si elle existe)
        if (Schema::hasTable('eligibilites')) {
            Schema::table('eligibilites', function (Blueprint $table) {
                $table->index(['militaire_id', 'type']);
                $table->index('date_eligibilite');
                $table->index('type');
            });
        }
    }

    public function down()
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropIndex(['statut']);
            $table->dropIndex(['grade_actuel']);
            $table->dropIndex(['matricule']);
            $table->dropIndex(['nom', 'prenom']);
            $table->dropIndex(['statut', 'grade_actuel']);
            $table->dropIndex(['statut', 'date_entree_service']);
            $table->dropIndex(['grade_actuel', 'date_derniere_promotion']);
            $table->dropIndex(['date_entree_service']);
            $table->dropIndex(['date_derniere_promotion']);
            $table->dropIndex(['date_naissance']);
            $table->dropIndex(['a_permis_conduire']);
            $table->dropIndex(['a_fait_justice']);
            $table->dropIndex(['a_fait_discipline']);
        });

        Schema::table('certificat_militaire', function (Blueprint $table) {
            $table->dropIndex(['date_obtention']);
            $table->dropIndex(['militaire_id', 'certificat_id']);
        });

        Schema::table('alertes', function (Blueprint $table) {
            $table->dropIndex(['militaire_id', 'type_alerte']);
            $table->dropIndex(['est_vue']);
            $table->dropIndex(['date_echeance']);
        });

        Schema::table('contrats', function (Blueprint $table) {
            $table->dropIndex(['militaire_id', 'statut']);
            $table->dropIndex(['date_debut']);
            $table->dropIndex(['date_fin']);
            $table->dropIndex(['statut']);
        });

        if (Schema::hasTable('eligibilites')) {
            Schema::table('eligibilites', function (Blueprint $table) {
                $table->dropIndex(['militaire_id', 'type']);
                $table->dropIndex(['date_eligibilite']);
                $table->dropIndex(['type']);
            });
        }
    }
};
