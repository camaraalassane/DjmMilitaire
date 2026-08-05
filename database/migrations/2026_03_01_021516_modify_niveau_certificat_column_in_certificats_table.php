<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifier si la table existe avant de la modifier
        if (Schema::hasTable('certificats')) {
            // Vérifier si la colonne existe
            if (Schema::hasColumn('certificats', 'niveau_certificat')) {
                // Modifier la colonne directement sans supprimer la table
                Schema::table('certificats', function (Blueprint $table) {
                    $table->string('niveau_certificat')->nullable()->change();
                });
            } else {
                // Si la colonne n'existe pas, on l'ajoute
                Schema::table('certificats', function (Blueprint $table) {
                    $table->string('niveau_certificat')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        // Revenir en arrière si nécessaire
        if (Schema::hasTable('certificats') && Schema::hasColumn('certificats', 'niveau_certificat')) {
            Schema::table('certificats', function (Blueprint $table) {
                $table->string('niveau_certificat', 50)->change();
            });
        }
    }
};
