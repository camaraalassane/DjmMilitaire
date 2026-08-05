<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('militaire_id')->constrained()->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->date('date_renouvellement')->nullable();
            $table->integer('duree_annees')->default(5);
            $table->string('statut')->default('actif'); // actif, expire, renouvele
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contrats');
    }
};
