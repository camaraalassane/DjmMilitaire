<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('certificat_documents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('militaire_certificat_id')
              ->constrained('certificat_militaire') // ✅ Changez ici !
              ->onDelete('cascade');
        $table->string('nom_fichier');
        $table->string('chemin_fichier');
        $table->string('type_fichier')->nullable();
        $table->integer('taille')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('certificat_documents');
    }
};
