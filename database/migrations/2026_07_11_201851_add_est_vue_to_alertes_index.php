<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('alertes', function (Blueprint $table) {
            // Supprimer l'ancien index
            $table->dropIndex(['militaire_id', 'type_alerte']);

            // Créer le nouvel index avec est_vue
            $table->index(['militaire_id', 'type_alerte', 'est_vue']);
        });
    }

    public function down()
    {
        Schema::table('alertes', function (Blueprint $table) {
            $table->dropIndex(['militaire_id', 'type_alerte', 'est_vue']);
            $table->index(['militaire_id', 'type_alerte']);
        });
    }
};
