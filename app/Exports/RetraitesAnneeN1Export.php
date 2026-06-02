<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RetraitesAnneeN1Export implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
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
        $retraites = $this->result['retraites'];
        
        $data[] = ['LISTE DES RETRAITES POUR L\'ANNÉE ' . $annee];
        $data[] = ['Date d\'export : ' . date('d/m/Y H:i')];
        $data[] = [''];
        $data[] = ['Grade actuel', 'Matricule', 'Nom complet', 'Date de retraite'];
        
        $currentGrade = '';
        foreach ($retraites as $retraite) {
            if ($currentGrade !== $retraite['grade_actuel']) {
                if ($currentGrade !== '') {
                    $data[] = [''];
                }
                $currentGrade = $retraite['grade_actuel'];
            }
            
            $data[] = [
                $retraite['grade_actuel'],
                $retraite['matricule'],
                $retraite['nom'] . ' ' . $retraite['prenom'],
                $retraite['date_retraite_formatted'],
            ];
        }
        
        $data[] = [''];
        $data[] = ['TOTAL RETRAITES : ' . count($retraites)];
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Style titre principal
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ea580c']
            ],
            'alignment' => ['horizontal' => 'center']
        ]);
        
        // Style date d'export
        $sheet->getStyle('A2:D2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '6B7280']],
        ]);
        
        // Style en-têtes colonnes
        $sheet->getStyle('A4:D4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'f97316']
            ],
            'alignment' => ['horizontal' => 'center'],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'ea580c']
                ]
            ]
        ]);
        
        // Style pour chaque ligne
        $row = 5;
        $currentGrade = '';
        $colorIndex = 0;
        $colors = ['fff7ed', 'ffedd5', 'fed7aa', 'fef3c7', 'fffbeb'];
        
        foreach ($this->result['retraites'] as $retraite) {
            if ($currentGrade !== $retraite['grade_actuel']) {
                $currentGrade = $retraite['grade_actuel'];
                $colorIndex = ($colorIndex + 1) % count($colors);
            }
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $colors[$colorIndex]]
                ]
            ]);
            $row++;
        }
        
        // Style total
        $lastRow = count($this->result['retraites']) + 6;
        $sheet->getStyle("A{$lastRow}:D{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'fed7aa']
            ]
        ]);
        
        foreach(range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        return [];
    }
}