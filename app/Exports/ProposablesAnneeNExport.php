<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProposablesAnneeNExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function array(): array
    {
        $data = [];
        $annee = $this->result['annee'];
        
        // Trier par grade puis par ancienneté décroissante
        $proposables = $this->result['proposables'];
        
        $data[] = ['LISTE DES MILITAIRES PROPOSABLES POUR L\'ANNÉE ' . $annee];
        $data[] = ['Date d\'export : ' . date('d/m/Y H:i')];
        $data[] = [''];
        $data[] = ['Grade actuel', 'Ancienneté grade', 'Matricule', 'Nom complet', 'Grade cible', 'Date ancienneté', 'Date proposition'];
        
        $currentGrade = '';
        foreach ($proposables as $proposable) {
            if ($currentGrade !== $proposable['grade_actuel']) {
                if ($currentGrade !== '') {
                    $data[] = [''];
                }
                $currentGrade = $proposable['grade_actuel'];
            }
            
            $data[] = [
                $proposable['grade_actuel'],
                $proposable['anciennete_grade_formatted'] ?? '-',
                $proposable['matricule'],
                $proposable['nom'] . ' ' . $proposable['prenom'],
                $proposable['grade_cible'],
                $proposable['date_anciennete_formatted'],
                $proposable['date_proposition_formatted'],
            ];
        }
        
        $data[] = [''];
        $data[] = ['TOTAL PROPOSABLES : ' . count($proposables)];
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Style titre principal
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0284c7']
            ],
            'alignment' => ['horizontal' => 'center']
        ]);
        
        // Style date d'export
        $sheet->getStyle('A2:G2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']],
        ]);
        
        // Style en-têtes colonnes
        $sheet->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3b82f6']
            ],
            'alignment' => ['horizontal' => 'center'],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '2563eb']
                ]
            ]
        ]);
        
        // Style pour chaque ligne
        $row = 5;
        $currentGrade = '';
        $colorIndex = 0;
        $colors = ['f0f9ff', 'eff6ff', 'e0f2fe', 'f8fafc', 'f1f5f9'];
        
        foreach ($this->result['proposables'] as $proposable) {
            if ($currentGrade !== $proposable['grade_actuel']) {
                $currentGrade = $proposable['grade_actuel'];
                $colorIndex = ($colorIndex + 1) % count($colors);
            }
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $colors[$colorIndex]]
                ]
            ]);
            $row++;
        }
        
        // Style total
        $lastRow = count($this->result['proposables']) + 6;
        $sheet->getStyle("A{$lastRow}:G{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'dbeafe']
            ]
        ]);
        
        foreach(range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return [];
    }
}