<?php

namespace Database\Seeders;

use App\Models\Certificat;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CertificatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dateDefault = Carbon::parse('2026-03-07 02:41:21');

        $certificats = [
            // --- SOUS-OFFICIERS & MILITAIRES DU RANG (Ordre de carrière) ---
            [
                'id' => 1,
                'nom_certificat' => 'Brevet Élémentaire (BE)',
                'niveau_certificat' => 'BE',
                'grade_associe' => 'Soldat 1',
                'conditions' => [
                    'grade_requis' => 'Soldat 2',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 2,
                'nom_certificat' => "Certificat d'Aptitude Technique Niveau 1",
                'niveau_certificat' => 'CAT1',
                'grade_associe' => 'Caporal',
                'conditions' => [
                    'grade_requis' => 'Soldat 1',
                    'anciennete_min' => 5,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => 5,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 3,
                'nom_certificat' => "Certificat d'Aptitude Technique Niveau 2",
                'niveau_certificat' => 'CAT2',
                'grade_associe' => 'Sergent',
                'conditions' => [
                    'certificat_precedent' => 'CAT1',
                    'duree_certificat_precedent' => 3,
                    'grade_requis' => 'Caporal',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'CAT1',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 4,
                'nom_certificat' => "Certificat Interarmes (CIA)",
                'niveau_certificat' => 'CIA',
                'grade_associe' => 'Sergent-Chef',
                'conditions' => [
                    'permis_conduire' => true,
                    'anciennete_sous_officier' => 3,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'CAT2',
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 5,
                'nom_certificat' => "Brevet d'Arme N1",
                'niveau_certificat' => 'BA1',
                'grade_associe' => 'Adjudant',
                'conditions' => [
                    'grade_min' => 'Sergent-Chef',
                    'certificat_cia' => true,
                    'duree_cia' => 3,
                    'anciennete_service' => 8,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => 8,
                'certificat_precedent' => 'CIA',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 6,
                'nom_certificat' => "Brevet d'Arme N2",
                'niveau_certificat' => 'BA2',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => [
                    'grade_min' => 'Adjudant',
                    'certificat_ba1' => true,
                    'duree_ba1' => 3,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 7,
                'nom_certificat' => 'Certificat Technique N1 (CT1)',
                'niveau_certificat' => 'CT1',
                'grade_associe' => 'Adjudant',
                'conditions' => [
                    'grade_min' => 'Sergent-Chef',
                    'certificat_cia' => true,
                    'duree_cia' => 3,
                    'anciennete_service' => 8,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => 8,
                'certificat_precedent' => 'CIA',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 8,
                'nom_certificat' => 'Certificat Technique N2',
                'niveau_certificat' => 'CT2',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => [
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 9,
                'nom_certificat' => 'Brevet Militaire Professionnel N1',
                'niveau_certificat' => 'BMP1',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => [
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 10,
                'nom_certificat' => 'Brevet Militaire Professionnel N2',
                'niveau_certificat' => 'BMP2',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => [
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 11,
                'nom_certificat' => 'Brevet Supérieur',
                'niveau_certificat' => 'BS',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => [
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],

            // --- FORMATIONS OFFICIERS ---
            [
                'id' => 12,
                'nom_certificat' => "Cour d'Application",
                'niveau_certificat' => 'APLI',
                'grade_associe' => 'Lieutenant',
                'conditions' => [
                    'grade_min' => 'Sous-lieutenant',
                    'age_max' => 50,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 13,
                'nom_certificat' => 'Cour des Capitaines / CFCU / CPO',
                'niveau_certificat' => 'CFCU',
                'grade_associe' => 'Commandant',
                'conditions' => [
                    'or' => [
                        ['certificat_requis' => 'APLI'],
                        ['grade_min' => 'Capitaine']
                    ],
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 14,
                'nom_certificat' => "Certificat d'état-major",
                'niveau_certificat' => 'CERT_EM',
                'grade_associe' => 'Colonel',
                'conditions' => [
                    'grade_min' => 'Commandant',
                    'age_comparison' => '>',
                    'age_min' => 45,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 15,
                'nom_certificat' => "École de guerre / Brevet Supérieur de Second Degré",
                'niveau_certificat' => 'ECOLE_GUERRE',
                'grade_associe' => 'Colonel',
                'conditions' => [
                    'grade_min' => 'Lieutenant-colonel',
                    'anciennete_grade_min' => 2,
                    'age_max' => 53,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 16,
                'nom_certificat' => "École d'État-Major (EEM)",
                'niveau_certificat' => 'EEM',
                'grade_associe' => 'Officier supérieur',
                'conditions' => [
                    'grade_min' => 'Capitaine',
                    'anciennete_service' => 10
                ],
                'anciennete_requise' => 10,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 17,
                'nom_certificat' => "Cours supérieur d'état-major",
                'niveau_certificat' => 'CSEM',
                'grade_associe' => 'Lieutenant-colonel',
                'conditions' => [
                    'grade_min' => 'Commandant',
                    'certificat_precedent' => 'EEM'
                ],
                'anciennete_requise' => null,
                'certificat_precedent' => 'EEM',
                'duree_certificat_precedent' => 3,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
        ];

        foreach ($certificats as $certificat) {
            Certificat::updateOrCreate(
                ['id' => $certificat['id']],
                $certificat
            );
        }

        // Supprimer les certificats avec un ID > 17 s'il y en a
        Certificat::where('id', '>', 17)->delete();
    }
}