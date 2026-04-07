<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProposablesAnneeN1Export implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $proposablesAnneeN1;

    public function __construct($proposablesAnneeN1)
    {
        $this->proposablesAnneeN1 = $proposablesAnneeN1;
    }

    public function array(): array
    {
        $data = [];
        $annee = $this->proposablesAnneeN1['annee'];
        
        $data[] = ['LISTE DES MILITAIRES PROPOSABLES POUR L\'ANNÉE ' . $annee];
        $data[] = ['Date d\'export : ' . date('d/m/Y H:i')];
        $data[] = [''];
        $data[] = ['Période', 'Date proposition', 'Matricule', 'Nom complet', 'Grade actuel', 'Grade cible'];
        
        $periodes = ['janvier', 'avril', 'octobre'];
        $nomsPeriodes = [
            'janvier' => '1er Janvier',
            'avril' => '1er Avril',
            'octobre' => '1er Octobre'
        ];
        
        foreach ($periodes as $periode) {
            if (isset($this->proposablesAnneeN1[$periode]) && 
                isset($this->proposablesAnneeN1[$periode]['proposables'])) {
                
                foreach ($this->proposablesAnneeN1[$periode]['proposables'] as $proposable) {
                    $data[] = [
                        $nomsPeriodes[$periode],
                        $this->proposablesAnneeN1[$periode]['date_formatted'],
                        $proposable['matricule'],
                        $proposable['nom'] . ' ' . $proposable['prenom'],
                        $proposable['grade_actuel'],
                        $proposable['grade_cible'],
                    ];
                }
            }
        }
        
        $data[] = [''];
        $data[] = ['TOTAL PROPOSABLES : ' . $this->proposablesAnneeN1['total']];
        
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