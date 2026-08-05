<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dateDefault = Carbon::parse('2026-03-07 02:41:20');

        $grades = [
            [
                'id' => 1,
                'nom_grade' => 'Soldat 2',
                'code_grade' => 'Sdt2',
                'type_grade' => 'militaire du rang',
                'ordre' => 1,
                'retraite_obligatoire' => 50,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 2,
                'nom_grade' => 'Soldat 1',
                'code_grade' => 'Sdt1',
                'type_grade' => 'militaire du rang',
                'ordre' => 2,
                'retraite_obligatoire' => 50,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 3,
                'nom_grade' => 'Caporal',
                'code_grade' => 'Cpl',
                'type_grade' => 'militaire du rang',
                'ordre' => 3,
                'retraite_obligatoire' => 50,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 4,
                'nom_grade' => 'Caporal-chef',
                'code_grade' => 'Cpl-Chef',
                'type_grade' => 'militaire du rang',
                'ordre' => 4,
                'retraite_obligatoire' => 50,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 23,  // ESOA
                'nom_grade' => 'Eleve Sous Officier d\'Active',
                'code_grade' => 'ESOA',
                'type_grade' => 'sous-officier',
                'ordre' => 5,
                'retraite_obligatoire' => 53,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 5,
                'nom_grade' => 'Sergent',
                'code_grade' => 'Sgt',
                'type_grade' => 'sous-officier',
                'ordre' => 6,
                'retraite_obligatoire' => 53,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 6,
                'nom_grade' => 'Sergent-Chef',
                'code_grade' => 'Sch',
                'type_grade' => 'sous-officier',
                'ordre' => 7,
                'retraite_obligatoire' => 53,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 7,
                'nom_grade' => 'Adjudant',
                'code_grade' => 'Adj',
                'type_grade' => 'sous-officier',
                'ordre' => 8,
                'retraite_obligatoire' => 56,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 8,
                'nom_grade' => 'Adjudant-Chef',
                'code_grade' => 'AdC',
                'type_grade' => 'sous-officier',
                'ordre' => 9,
                'retraite_obligatoire' => 56,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 9,
                'nom_grade' => 'Adjudant-Chef major',
                'code_grade' => 'ACM',
                'type_grade' => 'sous-officier',
                'ordre' => 10,
                'retraite_obligatoire' => 58,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 22,  // EOA
                'nom_grade' => 'Eleve Officier d\'Active',
                'code_grade' => 'EOA',
                'type_grade' => 'officier subalterne',
                'ordre' => 11,
                'retraite_obligatoire' => 60,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 10,
                'nom_grade' => 'Sous-lieutenant',
                'code_grade' => 'SLt',
                'type_grade' => 'officier subalterne',
                'ordre' => 12,
                'retraite_obligatoire' => 60,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 11,
                'nom_grade' => 'Lieutenant',
                'code_grade' => 'LTN',
                'type_grade' => 'officier subalterne',
                'ordre' => 13,
                'retraite_obligatoire' => 60,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 12,
                'nom_grade' => 'Capitaine',
                'code_grade' => 'CNE',
                'type_grade' => 'officier subalterne',
                'ordre' => 14,
                'retraite_obligatoire' => 60,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 13,
                'nom_grade' => 'Commandant',
                'code_grade' => 'CDT',
                'type_grade' => 'officier supérieur',
                'ordre' => 15,
                'retraite_obligatoire' => 62,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 14,
                'nom_grade' => 'Lieutenant-colonel',
                'code_grade' => 'LCL',
                'type_grade' => 'officier supérieur',
                'ordre' => 16,
                'retraite_obligatoire' => 62,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 15,
                'nom_grade' => 'Colonel',
                'code_grade' => 'COL',
                'type_grade' => 'officier supérieur',
                'ordre' => 17,
                'retraite_obligatoire' => 62,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 16,
                'nom_grade' => 'Colonel-Major',
                'code_grade' => 'CLM',
                'type_grade' => 'officier supérieur',
                'ordre' => 18,
                'retraite_obligatoire' => 62,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 17,
                'nom_grade' => 'Général de brigade',
                'code_grade' => 'GBR',
                'type_grade' => 'officier général',
                'ordre' => 19,
                'retraite_obligatoire' => 65,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 18,
                'nom_grade' => 'Général de division',
                'code_grade' => 'GDV',
                'type_grade' => 'officier général',
                'ordre' => 20,
                'retraite_obligatoire' => 65,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 19,
                'nom_grade' => 'Général de corps d\'armée',
                'code_grade' => 'GCA',
                'type_grade' => 'officier général',
                'ordre' => 21,
                'retraite_obligatoire' => 65,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
            [
                'id' => 20,
                'nom_grade' => 'Général d\'armée',
                'code_grade' => 'GAR',
                'type_grade' => 'officier général',
                'ordre' => 22,
                'retraite_obligatoire' => 65,
                'created_at' => $dateDefault,
                'updated_at' => $dateDefault
            ],
        ];

        foreach ($grades as $grade) {
            Grade::updateOrCreate(
                ['id' => $grade['id']],
                $grade
            );
        }
    }
}