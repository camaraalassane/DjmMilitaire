<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RetraitesAnneeNExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $retraitesAnneeN;

    public function __construct($retraitesAnneeN)
    {
        $this->retraitesAnneeN = $retraitesAnneeN;
    }

    public function array(): array
    {
        $data = [];
        $annee = $this->retraitesAnneeN['annee'];
        
        $data[] = ['LISTE DES RETRAITES POUR L\'ANNÉE ' . $annee];
        $data[] = ['Date d\'export : ' . date('d/m/Y H:i')];
        $data[] = [''];
        $data[] = ['Matricule', 'Nom complet', 'Grade actuel', 'Date de retraite'];
        
        foreach ($this->retraitesAnneeN['retraites'] as $retraite) {
            $data[] = [
                $retraite['matricule'],
                $retraite['nom'] . ' ' . $retraite['prenom'],
                $retraite['grade_actuel'],
                $retraite['date_retraite_formatted'],
            ];
        }
        
        $data[] = [''];
        $data[] = ['TOTAL RETRAITES : ' . $this->retraitesAnneeN['total']];
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}