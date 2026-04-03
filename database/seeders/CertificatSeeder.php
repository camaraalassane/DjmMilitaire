<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificat;

class CertificatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $certificats = [
            // Certificat d'Aptitude Technique Niveau 1 (CAT1) - pour devenir Caporal
            [
                'nom_certificat' => 'Certificat d\'Aptitude Technique Niveau 1',
                'niveau_certificat' => 'CAT1',
                'grade_associe' => 'Caporal',
                'conditions' => json_encode([
                    'grade_requis' => 'Soldat 1',
                    'anciennete_min' => 5,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 5,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null
            ],

            // Certificat d'Aptitude Technique Niveau 2 (CAT2) - pour devenir Sergent
            [
                'nom_certificat' => 'Certificat d\'Aptitude Technique Niveau 2',
                'niveau_certificat' => 'CAT2',
                'grade_associe' => 'Sergent',
                'conditions' => json_encode([
                    'certificat_precedent' => 'CAT1',
                    'duree_certificat_precedent' => 3,
                    'grade_requis' => 'Caporal',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 3, // anciennete dans le grade de Caporal
                'certificat_precedent' => 'CAT1',
                'duree_certificat_precedent' => 3
            ],

            // Certificat d'Instruction d'Armes (CIA) - pour devenir Sergent-Chef
            [
                'nom_certificat' => 'Certificat d\'Instruction d\'Armes',
                'niveau_certificat' => 'CIA',
                'grade_associe' => 'Sergent-Chef',
                'conditions' => json_encode([
                    'permis_conduire' => true,
                    'anciennete_sous_officier' => 3,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 3,
                'certificat_precedent' => 'CAT2',
                'duree_certificat_precedent' => null
            ],

            // Brevet d'Aptitude Niveau 1 (BA1) - pour devenir Adjudant
            [
                'nom_certificat' => 'Brevet d\'Aptitude Niveau 1',
                'niveau_certificat' => 'BA1',
                'grade_associe' => 'Adjudant',
                'conditions' => json_encode([
                    'grade_min' => 'Sergent-Chef',
                    'certificat_cia' => true,
                    'duree_cia' => 3,
                    'anciennete_service' => 8,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 8,
                'certificat_precedent' => 'CIA',
                'duree_certificat_precedent' => 3
            ],

            // Brevet d'Aptitude Niveau 2 (BA2) - pour devenir Adjudant-Chef
            [
                'nom_certificat' => 'Brevet d\'Aptitude Niveau 2',
                'niveau_certificat' => 'BA2',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => json_encode([
                    'grade_min' => 'Adjudant',
                    'certificat_ba1' => true,
                    'duree_ba1' => 3,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 3,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3
            ],

            // Brevet Militaire Professionnel niveau 2 (BMP1) - équivalent à BA2 (avec CIA)
            [
                'nom_certificat' => 'Brevet Militaire Professionnel niveau 1',
                'niveau_certificat' => 'BMP1',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => json_encode([
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA', // Nécessite également CIA pour l'équivalence
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 3,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3
            ],
            
            // Brevet Militaire Professionnel niveau 2 (BMP2) - équivalent à BA2 (avec CIA)
            [
                'nom_certificat' => 'Brevet Militaire Professionnel niveau 2',
                'niveau_certificat' => 'BMP2',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => json_encode([
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA', // Nécessite également CIA pour l'équivalence
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 3,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3
            ],

            // Brevet de Spécialité (BS) - équivalent à BA2 (avec CIA)
            [
                'nom_certificat' => 'Brevet de Spécialité',
                'niveau_certificat' => 'BS',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => json_encode([
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 3,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3
            ],

            // Certificat Technique niveau 2 (CT2) - équivalent à BA2 (avec CIA)
            [
                'nom_certificat' => 'Certificat Technique niveau 2',
                'niveau_certificat' => 'CT2',
                'grade_associe' => 'Adjudant-Chef',
                'conditions' => json_encode([
                    'grade_min' => 'Adjudant',
                    'certificat_requis' => 'BA1',
                    'duree_certificat' => 3,
                    'certificat_complementaire' => 'CIA',
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => 3,
                'certificat_precedent' => 'BA1',
                'duree_certificat_precedent' => 3
            ],

            // ------------------------------------------------------------
            // Formations des officiers
            // ------------------------------------------------------------

            // Cour d'Application (APLI)
            [
                'nom_certificat' => 'Cour d\'Application',
                'niveau_certificat' => 'APLI',
                'grade_associe' => 'Lieutenant', // Après APLI, passage probable au grade de Lieutenant
                'conditions' => json_encode([
                    'grade_min' => 'Sous-lieutenant',
                    'age_max' => 50,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null
            ],

            // Cour des Capitaines / CFCU / CPO
            [
                'nom_certificat' => 'Cour des Capitaines / CFCU / CPO',
                'niveau_certificat' => 'CFCU',
                'grade_associe' => 'Commandant', // Après CFCU, accès au grade de Commandant
                'conditions' => json_encode([
                    'or' => [
                        ['certificat_requis' => 'APLI'],
                        ['grade_min' => 'Capitaine']
                    ],
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null
            ],

            // Cour d'état-major (CEM)
            [
                'nom_certificat' => 'Cour d\'état-major',
                'niveau_certificat' => 'CEM',
                'grade_associe' => 'Lieutenant-colonel', // Après CEM, accès au grade de Lieutenant-colonel
                'conditions' => json_encode([
                    'or' => [
                        ['grade_min' => 'Capitaine', 'anciennete_grade_min' => 3],
                        ['grade_min' => 'Commandant', 'age_max' => 45]
                    ],
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null
            ],

            // Certificat d'état-major
            [
                'nom_certificat' => 'Certificat d\'état-major',
                'niveau_certificat' => 'CERT_EM',
                'grade_associe' => 'Colonel', // Après Certificat EM, accès au grade de Colonel
                'conditions' => json_encode([
                    'grade_min' => 'Commandant',
                    'age_comparison' => '>', // strictement supérieur à 45 ans
                    'age_min' => 45,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null
            ],

            // École de guerre / Brevet Supérieur de Second Degré
            [
                'nom_certificat' => 'École de guerre / Brevet Supérieur de Second Degré',
                'niveau_certificat' => 'ECOLE_GUERRE',
                'grade_associe' => 'Colonel', // Après École de guerre, accès au grade de Colonel ou supérieur
                'conditions' => json_encode([
                    'grade_min' => 'Lieutenant-colonel',
                    'anciennete_grade_min' => 2,
                    'age_max' => 53,
                    'pas_de_probleme_disciplinaire' => true,
                    'pas_de_probleme_judiciaire' => true,
                    'pas_deserteur' => true
                ]),
                'anciennete_requise' => null,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null
            ],

            [
                'nom_certificat' => 'École d\'État-Major (ESM)',
                'niveau_certificat' => 'ESM',
                'grade_associe' => 'Officier supérieur',
                'conditions' => json_encode([
                    'grade_min' => 'Capitaine',
                    'anciennete_service' => 10,
                ]),
                'anciennete_requise' => 10,
                'certificat_precedent' => null,
                'duree_certificat_precedent' => null,
            ],
            [
                'nom_certificat' => 'Cours supérieur d\'état-major',
                'niveau_certificat' => 'CSEM',
                'grade_associe' => 'Lieutenant-colonel',
                'conditions' => json_encode([
                    'grade_min' => 'Commandant',
                    'certificat_precedent' => 'ESM',
                ]),
                'anciennete_requise' => 5,
                'certificat_precedent' => 'ESM',
                'duree_certificat_precedent' => 3,
            ],
        ];

        foreach ($certificats as $certificat) {
            Certificat::updateOrCreate(
                ['niveau_certificat' => $certificat['niveau_certificat']],
                $certificat
            );
        }
    }
}