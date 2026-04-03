<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificat_militaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('militaire_id')->constrained()->onDelete('cascade');
            $table->foreignId('certificat_id')->constrained()->onDelete('cascade');
            $table->date('date_obtention')->nullable();
            $table->timestamps();

            $table->unique(['militaire_id', 'certificat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificat_militaire');
    }
};