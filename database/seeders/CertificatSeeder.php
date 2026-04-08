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
        $certificats = [
            [
                'id' => 1,
                'nom_certificat' => 'Certificat d\'Aptitude Technique Niveau 1',
                'niveau_certificat' => 'CAT1',
                'grade_obtenu' => 'Caporal',
                'conditions' => '{"grade_requis":"Soldat 1","anciennete_min":5,"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 5,
                'certificat_requis' => null,
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 2,
                'nom_certificat' => 'Certificat d\'Aptitude Technique Niveau 2',
                'niveau_certificat' => 'CAT2',
                'grade_obtenu' => 'Sergent',
                'conditions' => '{"certificat_precedent":"CAT1","duree_certificat_precedent":3,"grade_requis":"Caporal","pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 3,
                'certificat_requis' => 'CAT1',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 3,
                'nom_certificat' => 'Certificat d\'Instruction d\'Armes',
                'niveau_certificat' => 'CIA',
                'grade_obtenu' => 'Sergent-Chef',
                'conditions' => '{"permis_conduire":true,"anciennete_sous_officier":3,"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 3,
                'certificat_requis' => 'CAT2',
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 4,
                'nom_certificat' => 'Brevet d\'Aptitude Niveau 1',
                'niveau_certificat' => 'BA1',
                'grade_obtenu' => 'Adjudant',
                'conditions' => '{"grade_min":"Sergent-Chef","certificat_cia":true,"duree_cia":3,"anciennete_service":8,"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 8,
                'certificat_requis' => 'CIA',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 5,
                'nom_certificat' => 'Brevet d\'Aptitude Niveau 2',
                'niveau_certificat' => 'BA2',
                'grade_obtenu' => 'Adjudant-Chef',
                'conditions' => '{"grade_min":"Adjudant","certificat_ba1":true,"duree_ba1":3,"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 3,
                'certificat_requis' => 'BA1',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 6,
                'nom_certificat' => 'Brevet Militaire Professionnel niveau 1',
                'niveau_certificat' => 'BMP1',
                'grade_obtenu' => 'Adjudant-Chef',
                'conditions' => '{"grade_min":"Adjudant","certificat_requis":"BA1","duree_certificat":3,"certificat_complementaire":"CIA","pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 3,
                'certificat_requis' => 'BA1',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 7,
                'nom_certificat' => 'Brevet Militaire Professionnel niveau 2',
                'niveau_certificat' => 'BMP2',
                'grade_obtenu' => 'Adjudant-Chef',
                'conditions' => '{"grade_min":"Adjudant","certificat_requis":"BA1","duree_certificat":3,"certificat_complementaire":"CIA","pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 3,
                'certificat_requis' => 'BA1',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 8,
                'nom_certificat' => 'Brevet de Spécialité',
                'niveau_certificat' => 'BS',
                'grade_obtenu' => 'Adjudant-Chef',
                'conditions' => '{"grade_min":"Adjudant","certificat_requis":"BA1","duree_certificat":3,"certificat_complementaire":"CIA","pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 3,
                'certificat_requis' => 'BA1',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 9,
                'nom_certificat' => 'Certificat Technique niveau 2',
                'niveau_certificat' => 'CT2',
                'grade_obtenu' => 'Adjudant-Chef',
                'conditions' => '{"grade_min":"Adjudant","certificat_requis":"BA1","duree_certificat":3,"certificat_complementaire":"CIA","pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => 3,
                'certificat_requis' => 'BA1',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 10,
                'nom_certificat' => 'Cour d\'Application',
                'niveau_certificat' => 'APLI',
                'grade_obtenu' => 'Lieutenant',
                'conditions' => '{"grade_min":"Sous-lieutenant","age_max":50,"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => null,
                'certificat_requis' => null,
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 11,
                'nom_certificat' => 'Cour des Capitaines / CFCU / CPO',
                'niveau_certificat' => 'CFCU',
                'grade_obtenu' => 'Commandant',
                'conditions' => '{"or":[{"certificat_requis":"APLI"},{"grade_min":"Capitaine"}],"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => null,
                'certificat_requis' => null,
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 12,
                'nom_certificat' => 'Cour d\'état-major',
                'niveau_certificat' => 'CEM',
                'grade_obtenu' => 'Lieutenant-colonel',
                'conditions' => '{"or":[{"grade_min":"Capitaine","anciennete_grade_min":3},{"grade_min":"Commandant","age_max":45}],"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => null,
                'certificat_requis' => null,
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 13,
                'nom_certificat' => 'Certificat d\'état-major',
                'niveau_certificat' => 'CERT_EM',
                'grade_obtenu' => 'Colonel',
                'conditions' => '{"grade_min":"Commandant","age_comparison":">","age_min":45,"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => null,
                'certificat_requis' => null,
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 14,
                'nom_certificat' => 'École de guerre / Brevet Supérieur de Second Degré',
                'niveau_certificat' => 'ECOLE_GUERRE',
                'grade_obtenu' => 'Colonel',
                'conditions' => '{"grade_min":"Lieutenant-colonel","anciennete_grade_min":2,"age_max":53,"pas_de_probleme_disciplinaire":true,"pas_de_probleme_judiciaire":true,"pas_deserteur":true}',
                'duree_validite' => null,
                'certificat_requis' => null,
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 15,
                'nom_certificat' => 'École d\'État-Major (ESM)',
                'niveau_certificat' => 'ESM',
                'grade_obtenu' => 'Officier supérieur',
                'conditions' => '{"grade_min":"Capitaine","anciennete_service":10}',
                'duree_validite' => 10,
                'certificat_requis' => null,
                'duree_certificat_requis' => null,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
            [
                'id' => 16,
                'nom_certificat' => 'Cours supérieur d\'état-major',
                'niveau_certificat' => 'CSEM',
                'grade_obtenu' => 'Lieutenant-colonel',
                'conditions' => '{"grade_min":"Commandant","certificat_precedent":"ESM"}',
                'duree_validite' => 5,
                'certificat_requis' => 'ESM',
                'duree_certificat_requis' => 3,
                'created_at' => Carbon::parse('2026-03-07 02:41:21'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:21')
            ],
        ];

        foreach ($certificats as $certificat) {
            Certificat::updateOrCreate(
                ['id' => $certificat['id']],
                $certificat
            );
        }
    }
}