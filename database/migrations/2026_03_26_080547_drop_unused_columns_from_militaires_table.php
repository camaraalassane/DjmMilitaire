<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            // Supprimer les colonnes de date de retraite et certificats
            $table->dropColumn([
                'date_retraite',
                'a_fait_cat1',
                'a_fait_cat2',
                'a_fait_cia',
                'a_fait_ba1',
                'a_fait_ba2',
                'date_obtention_cat1',
                'date_obtention_cat2',
                'date_obtention_cia',
                'date_obtention_ba1',
                'date_obtention_ba2',
                'a_fait_bmp1',
                'a_fait_bmp2',
                'a_fait_bs',
                'a_fait_ct2',
                'date_obtention_bmp1',
                'date_obtention_bmp2',
                'date_obtention_bs',
                'date_obtention_ct2',
                'a_fait_apli',
                'date_obtention_apli',
                'a_fait_cfcu',
                'date_obtention_cfcu',
                'a_fait_cem',
                'date_obtention_cem',
                'a_fait_certificat_em',
                'date_obtention_certificat_em',
                'a_fait_ecole_guerre',
                'date_obtention_ecole_guerre',
                'a_fait_cpo',
                'date_obtention_cpo',
                'a_fait_certificat_etat_major',
                'date_obtention_certificat_etat_major',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            // Recréer les colonnes en cas de rollback
            $table->date('date_retraite')->nullable();
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
            $table->boolean('a_fait_bmp1')->default(false);
            $table->boolean('a_fait_bmp2')->default(false);
            $table->boolean('a_fait_bs')->default(false);
            $table->boolean('a_fait_ct2')->default(false);
            $table->date('date_obtention_bmp1')->nullable();
            $table->date('date_obtention_bmp2')->nullable();
            $table->date('date_obtention_bs')->nullable();
            $table->date('date_obtention_ct2')->nullable();
            $table->boolean('a_fait_apli')->default(false);
            $table->date('date_obtention_apli')->nullable();
            $table->boolean('a_fait_cfcu')->default(false);
            $table->date('date_obtention_cfcu')->nullable();
            $table->boolean('a_fait_cem')->default(false);
            $table->date('date_obtention_cem')->nullable();
            $table->boolean('a_fait_certificat_em')->default(false);
            $table->date('date_obtention_certificat_em')->nullable();
            $table->boolean('a_fait_ecole_guerre')->default(false);
            $table->date('date_obtention_ecole_guerre')->nullable();
            $table->boolean('a_fait_cpo')->default(false);
            $table->date('date_obtention_cpo')->nullable();
            $table->boolean('a_fait_certificat_etat_major')->default(false);
            $table->date('date_obtention_certificat_etat_major')->nullable();
        });
    }
};