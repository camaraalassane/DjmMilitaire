<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            // Militaires du rang
            ['nom_grade' => 'Soldat 2', 'code_grade' => 'Sdt2', 'type_grade' => 'militaire du rang', 'ordre' => 1],
            ['nom_grade' => 'Soldat 1', 'code_grade' => 'Sdt1', 'type_grade' => 'militaire du rang', 'ordre' => 2],
            ['nom_grade' => 'Caporal', 'code_grade' => 'Cpl', 'type_grade' => 'militaire du rang', 'ordre' => 3],
            ['nom_grade' => 'Caporal-chef', 'code_grade' => 'Cpl-Chef', 'type_grade' => 'militaire du rang', 'ordre' => 4],
            
            // Sous-officiers
            ['nom_grade' => 'Sergent', 'code_grade' => 'Sgt', 'type_grade' => 'sous-officier', 'ordre' => 5],
            ['nom_grade' => 'Sergent-Chef', 'code_grade' => 'Sch', 'type_grade' => 'sous-officier', 'ordre' => 6],
            ['nom_grade' => 'Adjudant', 'code_grade' => 'Adj', 'type_grade' => 'sous-officier', 'ordre' => 7],
            ['nom_grade' => 'Adjudant-Chef', 'code_grade' => 'AdC', 'type_grade' => 'sous-officier', 'ordre' => 8],
            ['nom_grade' => 'Adjudant-Chef major', 'code_grade' => 'ACM', 'type_grade' => 'sous-officier', 'ordre' => 9],
            
            // Officiers subalternes
            ['nom_grade' => 'Sous-lieutenant', 'code_grade' => 'SLt', 'type_grade' => 'officier subalterne', 'ordre' => 10],
            ['nom_grade' => 'Lieutenant', 'code_grade' => 'LTN', 'type_grade' => 'officier subalterne', 'ordre' => 11],
            ['nom_grade' => 'Capitaine', 'code_grade' => 'CNE', 'type_grade' => 'officier subalterne', 'ordre' => 12],
            
            // Officiers supérieurs
            ['nom_grade' => 'Commandant', 'code_grade' => 'CDT', 'type_grade' => 'officier supérieur', 'ordre' => 13],
            ['nom_grade' => 'Lieutenant-colonel', 'code_grade' => 'LCL', 'type_grade' => 'officier supérieur', 'ordre' => 14],
            ['nom_grade' => 'Colonel', 'code_grade' => 'COL', 'type_grade' => 'officier supérieur', 'ordre' => 15],
            ['nom_grade' => 'Colonel-Major', 'code_grade' => 'CLM', 'type_grade' => 'officier supérieur', 'ordre' => 16],
            
            // Officiers généraux
            ['nom_grade' => 'Général de brigade', 'code_grade' => 'GBR', 'type_grade' => 'officier général', 'ordre' => 17],
            ['nom_grade' => 'Général de division', 'code_grade' => 'GDV', 'type_grade' => 'officier général', 'ordre' => 18],
            ['nom_grade' => 'Général de corps d\'armée', 'code_grade' => 'GCA', 'type_grade' => 'officier général', 'ordre' => 19],
            ['nom_grade' => 'Général d\'armée', 'code_grade' => 'GAR', 'type_grade' => 'officier général', 'ordre' => 20],
        ];

        foreach ($grades as $grade) {
            Grade::create($grade);
        }
    }
}