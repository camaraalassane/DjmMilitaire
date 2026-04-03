<?php

namespace App\Imports;

use App\Models\Militaire;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;

class MilitairesImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use Importable, SkipsErrors;

    private $importedCount = 0;
    private $skippedCount = 0;

    public function model(array $row)
    {
        try {
            $this->importedCount++;

            $data = [
                'matricule' => $row['matricule'] ?? null,
                'nom' => $row['nom'] ?? null,
                'prenom' => $row['prenom'] ?? null,
                'date_naissance' => $this->parseDate($row['date_naissance'] ?? null),
                'date_entree_service' => $this->parseDate($row['date_entree_service'] ?? null),
                'grade_actuel' => $row['grade_actuel'] ?? null,
                'date_derniere_promotion' => $this->parseDate($row['date_derniere_promotion'] ?? null),
                'specialite' => $row['specialite'] ?? null,
                'statut' => $row['statut'] ?? 'actif',
                'a_permis_conduire' => filter_var($row['a_permis_conduire'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_justice' => filter_var($row['a_fait_justice'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_discipline' => filter_var($row['a_fait_discipline'] ?? false, FILTER_VALIDATE_BOOLEAN),
                // Certificats sous-officiers
                'a_fait_cat1' => filter_var($row['a_fait_cat1'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_cat2' => filter_var($row['a_fait_cat2'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_cia' => filter_var($row['a_fait_cia'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_ba1' => filter_var($row['a_fait_ba1'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_ba2' => filter_var($row['a_fait_ba2'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_bmp1' => filter_var($row['a_fait_bmp1'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_bmp2' => filter_var($row['a_fait_bmp2'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_bs' => filter_var($row['a_fait_bs'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_ct2' => filter_var($row['a_fait_ct2'] ?? false, FILTER_VALIDATE_BOOLEAN),
                // Formations officiers
                'a_fait_apli' => filter_var($row['a_fait_apli'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_cfcu' => filter_var($row['a_fait_cfcu'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_cpo' => filter_var($row['a_fait_cpo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_cem' => filter_var($row['a_fait_cem'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_certificat_etat_major' => filter_var($row['a_fait_certificat_etat_major'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'a_fait_ecole_guerre' => filter_var($row['a_fait_ecole_guerre'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            // Dates d'obtention
            $dateFields = [
                'date_obtention_cat1', 'date_obtention_cat2', 'date_obtention_cia',
                'date_obtention_ba1', 'date_obtention_ba2', 'date_obtention_bmp1',
                'date_obtention_bmp2', 'date_obtention_bs', 'date_obtention_ct2',
                'date_obtention_apli', 'date_obtention_cfcu', 'date_obtention_cpo',
                'date_obtention_cem', 'date_obtention_certificat_etat_major',
                'date_obtention_ecole_guerre'
            ];

            foreach ($dateFields as $field) {
                $data[$field] = isset($row[$field]) ? $this->parseDate($row[$field]) : null;
            }

            return new Militaire($data);

        } catch (\Exception $e) {
            $this->skippedCount++;
            \Log::error('Erreur import ligne', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'matricule' => 'required|unique:militaires,matricule',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'required',
            'date_entree_service' => 'required',
            'grade_actuel' => 'required|exists:grades,nom_grade',
            'statut' => 'nullable|in:actif,retraité,déserteur,décédé,formation,stage',
        ];
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        // Si c'est un nombre Excel
        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        }

        // Sinon, essayer de parser la chaîne
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}