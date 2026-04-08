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
        $grades = [
            [
                'id' => 1,
                'nom_grade' => 'Soldat 2',
                'code_grade' => 'Sdt2',
                'type_grade' => 'militaire du rang',
                'ordre' => 1,
                'age_retraite' => 50,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 2,
                'nom_grade' => 'Soldat 1',
                'code_grade' => 'Sdt1',
                'type_grade' => 'militaire du rang',
                'ordre' => 2,
                'age_retraite' => 50,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 3,
                'nom_grade' => 'Caporal',
                'code_grade' => 'Cpl',
                'type_grade' => 'militaire du rang',
                'ordre' => 3,
                'age_retraite' => 50,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 4,
                'nom_grade' => 'Caporal-chef',
                'code_grade' => 'Cpl-Chef',
                'type_grade' => 'militaire du rang',
                'ordre' => 4,
                'age_retraite' => 50,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 5,
                'nom_grade' => 'Sergent',
                'code_grade' => 'Sgt',
                'type_grade' => 'sous-officier',
                'ordre' => 5,
                'age_retraite' => 53,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 6,
                'nom_grade' => 'Sergent-Chef',
                'code_grade' => 'Sch',
                'type_grade' => 'sous-officier',
                'ordre' => 6,
                'age_retraite' => 53,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 7,
                'nom_grade' => 'Adjudant',
                'code_grade' => 'Adj',
                'type_grade' => 'sous-officier',
                'ordre' => 7,
                'age_retraite' => 56,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 8,
                'nom_grade' => 'Adjudant-Chef',
                'code_grade' => 'AdC',
                'type_grade' => 'sous-officier',
                'ordre' => 8,
                'age_retraite' => 56,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 9,
                'nom_grade' => 'Adjudant-Chef major',
                'code_grade' => 'ACM',
                'type_grade' => 'sous-officier',
                'ordre' => 9,
                'age_retraite' => 58,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 10,
                'nom_grade' => 'Sous-lieutenant',
                'code_grade' => 'SLt',
                'type_grade' => 'officier subalterne',
                'ordre' => 10,
                'age_retraite' => 60,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 11,
                'nom_grade' => 'Lieutenant',
                'code_grade' => 'LTN',
                'type_grade' => 'officier subalterne',
                'ordre' => 11,
                'age_retraite' => 60,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 12,
                'nom_grade' => 'Capitaine',
                'code_grade' => 'CNE',
                'type_grade' => 'officier subalterne',
                'ordre' => 12,
                'age_retraite' => 60,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 13,
                'nom_grade' => 'Commandant',
                'code_grade' => 'CDT',
                'type_grade' => 'officier supérieur',
                'ordre' => 13,
                'age_retraite' => 62,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 14,
                'nom_grade' => 'Lieutenant-colonel',
                'code_grade' => 'LCL',
                'type_grade' => 'officier supérieur',
                'ordre' => 14,
                'age_retraite' => 62,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 15,
                'nom_grade' => 'Colonel',
                'code_grade' => 'COL',
                'type_grade' => 'officier supérieur',
                'ordre' => 15,
                'age_retraite' => 62,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 16,
                'nom_grade' => 'Colonel-Major',
                'code_grade' => 'CLM',
                'type_grade' => 'officier supérieur',
                'ordre' => 16,
                'age_retraite' => 62,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 17,
                'nom_grade' => 'Général de brigade',
                'code_grade' => 'GBR',
                'type_grade' => 'officier général',
                'ordre' => 17,
                'age_retraite' => 65,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 18,
                'nom_grade' => 'Général de division',
                'code_grade' => 'GDV',
                'type_grade' => 'officier général',
                'ordre' => 18,
                'age_retraite' => 65,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 19,
                'nom_grade' => 'Général de corps d\'armée',
                'code_grade' => 'GCA',
                'type_grade' => 'officier général',
                'ordre' => 19,
                'age_retraite' => 65,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
            ],
            [
                'id' => 20,
                'nom_grade' => 'Général d\'armée',
                'code_grade' => 'GAR',
                'type_grade' => 'officier général',
                'ordre' => 20,
                'age_retraite' => 65,
                'created_at' => Carbon::parse('2026-03-07 02:41:20'),
                'updated_at' => Carbon::parse('2026-03-07 02:41:20')
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