<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite ne permet pas de modifier une colonne directement, donc on recrée la table
        Schema::create('certificats_new', function (Blueprint $table) {
            $table->id();
            $table->string('nom_certificat');
            $table->string('niveau_certificat'); // devient string
            $table->string('grade_associe');
            $table->json('conditions')->nullable();
            $table->integer('anciennete_requise')->nullable();
            $table->string('certificat_precedent')->nullable();
            $table->integer('duree_certificat_precedent')->nullable();
            $table->timestamps();
        });

        // Copier les données de l'ancienne table
        DB::table('certificats')->orderBy('id')->chunk(100, function ($certificats) {
            foreach ($certificats as $cert) {
                DB::table('certificats_new')->insert([
                    'id' => $cert->id,
                    'nom_certificat' => $cert->nom_certificat,
                    'niveau_certificat' => $cert->niveau_certificat,
                    'grade_associe' => $cert->grade_associe,
                    'conditions' => $cert->conditions,
                    'anciennete_requise' => $cert->anciennete_requise,
                    'certificat_precedent' => $cert->certificat_precedent,
                    'duree_certificat_precedent' => $cert->duree_certificat_precedent,
                    'created_at' => $cert->created_at,
                    'updated_at' => $cert->updated_at,
                ]);
            }
        });

        // Supprimer l'ancienne table
        Schema::drop('certificats');

        // Renommer la nouvelle table
        Schema::rename('certificats_new', 'certificats');
    }

    public function down(): void
    {
        // Pour revenir en arrière, on recrée l'ancienne structure avec enum
        Schema::create('certificats_old', function (Blueprint $table) {
            $table->id();
            $table->string('nom_certificat');
            $table->enum('niveau_certificat', ['CAT1', 'CAT2', 'CIA', 'BA1', 'BA2', 'CT1', 'BMP1']);
            $table->string('grade_associe');
            $table->json('conditions')->nullable();
            $table->integer('anciennete_requise')->nullable();
            $table->string('certificat_precedent')->nullable();
            $table->integer('duree_certificat_precedent')->nullable();
            $table->timestamps();
        });

        // Restaurer les données en filtrant les valeurs qui ne sont pas dans l'enum
        $validEnum = ['CAT1', 'CAT2', 'CIA', 'BA1', 'BA2', 'CT1', 'BMP1'];
        DB::table('certificats')->orderBy('id')->chunk(100, function ($certificats) use ($validEnum) {
            foreach ($certificats as $cert) {
                if (in_array($cert->niveau_certificat, $validEnum)) {
                    DB::table('certificats_old')->insert([
                        'id' => $cert->id,
                        'nom_certificat' => $cert->nom_certificat,
                        'niveau_certificat' => $cert->niveau_certificat,
                        'grade_associe' => $cert->grade_associe,
                        'conditions' => $cert->conditions,
                        'anciennete_requise' => $cert->anciennete_requise,
                        'certificat_precedent' => $cert->certificat_precedent,
                        'duree_certificat_precedent' => $cert->duree_certificat_precedent,
                        'created_at' => $cert->created_at,
                        'updated_at' => $cert->updated_at,
                    ]);
                }
            }
        });

        Schema::drop('certificats');
        Schema::rename('certificats_old', 'certificats');
    }
};