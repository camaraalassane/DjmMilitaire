<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Pour SQLite, on vérifie d'abord si la colonne existe
        $columns = DB::select("PRAGMA table_info(alertes)");
        $columnExists = false;
        $columnType = null;

        foreach ($columns as $column) {
            if ($column->name === 'type_alerte') {
                $columnExists = true;
                $columnType = $column->type;
                break;
            }
        }

        // Si la colonne n'existe pas, on l'ajoute
        if (!$columnExists) {
            Schema::table('alertes', function (Blueprint $table) {
                $table->string('type_alerte')->nullable()->after('id');
            });
        } else {
            // Si la colonne existe déjà, on ne fait rien ou on la modifie
            // Pour SQLite, on ne peut pas modifier facilement le type
            // On va juste ajouter une colonne temporaire
            Schema::table('alertes', function (Blueprint $table) {
                if (!Schema::hasColumn('alertes', 'type_alerte_temp')) {
                    $table->string('type_alerte_temp')->nullable();
                }
            });

            // Copier les données
            DB::statement("UPDATE alertes SET type_alerte_temp = type_alerte WHERE type_alerte_temp IS NULL");

            // Supprimer l'ancienne colonne
            Schema::table('alertes', function (Blueprint $table) {
                $table->dropColumn('type_alerte');
            });

            // Renommer la nouvelle colonne
            Schema::table('alertes', function (Blueprint $table) {
                $table->renameColumn('type_alerte_temp', 'type_alerte');
            });
        }
    }

    public function down()
    {
        // Si nécessaire, on peut supprimer la colonne
        if (Schema::hasColumn('alertes', 'type_alerte')) {
            Schema::table('alertes', function (Blueprint $table) {
                $table->dropColumn('type_alerte');
            });
        }
    }
};
