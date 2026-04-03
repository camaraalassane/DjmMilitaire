<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('militaire_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['certificat', 'promotion']);
            $table->string('cible'); // ex: 'CAT1', 'Sergent', etc.
            $table->date('date_eligibilite');
            $table->timestamps();

            // Un militaire ne peut avoir qu'une seule éligibilité par type/cible
            $table->unique(['militaire_id', 'type', 'cible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibilites');
    }
};