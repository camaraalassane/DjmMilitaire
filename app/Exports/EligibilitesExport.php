<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class EligibilitesExport implements WithMultipleSheets
{
    protected $eligibilites;
    protected $type;

    public function __construct($eligibilites, $type = 'all')
    {
        $this->eligibilites = $eligibilites;
        $this->type = $type;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->type === 'all') {
            // Grouper les promotions par grade cible
            $promotionsGrouped = [];
            foreach ($this->eligibilites['promotions'] ?? [] as $item) {
                $key = $item['grade_cible'] ?? 'Autre';
                if (!isset($promotionsGrouped[$key])) {
                    $promotionsGrouped[$key] = [];
                }
                $promotionsGrouped[$key][] = $item;
            }

            // Grouper les formations par nom
            $formationsGrouped = [];
            foreach ($this->eligibilites['formations'] ?? [] as $item) {
                $key = $item['nom_formation'] ?? 'Autre';
                if (!isset($formationsGrouped[$key])) {
                    $formationsGrouped[$key] = [];
                }
                $formationsGrouped[$key][] = $item;
            }

            // Créer une feuille pour chaque groupe de promotions
            foreach ($promotionsGrouped as $gradeCible => $items) {
                $sheets[] = new PromotionSheet($items, $gradeCible);
            }

            // Créer une feuille pour chaque groupe de formations
            foreach ($formationsGrouped as $nomFormation => $items) {
                $sheets[] = new FormationSheet($items, $nomFormation);
            }

            // Créer une feuille pour les retraites
            if (!empty($this->eligibilites['retraites'])) {
                $sheets[] = new RetraitesSheet($this->eligibilites['retraites']);
            }
        } elseif ($this->type === 'promotions') {
            $sheets[] = new PromotionsSheet($this->eligibilites);
        } elseif ($this->type === 'formations') {
            $sheets[] = new FormationsSheet($this->eligibilites);
        } elseif ($this->type === 'retraites') {
            $sheets[] = new RetraitesSheet($this->eligibilites);
        }

        return $sheets;
    }
}

/**
 * Feuille pour un groupe de promotions
 */
class PromotionSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $items;
    protected $gradeCible;

    public function __construct($items, $gradeCible)
    {
        $this->items = $items;
        $this->gradeCible = $gradeCible;
    }

    public function collection()
    {
        $rows = new Collection();
        
        foreach ($this->items as $item) {
            $rows->push([
                $item['type'] ?? '',
                $item['grade_cible'] ?? '',
                $item['militaire']['matricule'] ?? '',
                $item['militaire']['nom'] ?? '',
                $item['militaire']['prenom'] ?? '',
                $item['militaire']['grade_actuel'] ?? '',
                $item['message'] ?? '',
                $item['date_estimation'] ? date('d/m/Y', strtotime($item['date_estimation'])) : '',
            ]);
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'TYPE',
            'GRADE CIBLE',
            'MATRICULE',
            'NOM',
            'PRÉNOM',
            'GRADE ACTUEL',
            'CONDITION',
            'DATE ESTIMATION',
        ];
    }

    public function title(): string
    {
        // Nettoyer le nom de la feuille (max 31 caractères)
        $title = str_replace('/', '-', $this->gradeCible);
        return substr($title, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 18,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 18,
            'G' => 40,
            'H' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                    ]);

                for ($i = 2; $i <= $lastRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8F9FA'],
                                ],
                            ]);
                    }
                }

                $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}

/**
 * Feuille pour un groupe de formations
 */
class FormationSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $items;
    protected $nomFormation;

    public function __construct($items, $nomFormation)
    {
        $this->items = $items;
        $this->nomFormation = $nomFormation;
    }

    public function collection()
    {
        $rows = new Collection();
        
        foreach ($this->items as $item) {
            $rows->push([
                $item['formation'] ?? '',
                $item['nom_formation'] ?? '',
                $item['militaire']['matricule'] ?? '',
                $item['militaire']['nom'] ?? '',
                $item['militaire']['prenom'] ?? '',
                $item['militaire']['grade_actuel'] ?? '',
                $item['message'] ?? '',
                $item['date_estimation'] ? date('d/m/Y', strtotime($item['date_estimation'])) : '',
            ]);
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'FORMATION',
            'NOM FORMATION',
            'MATRICULE',
            'NOM',
            'PRÉNOM',
            'GRADE ACTUEL',
            'CONDITION',
            'DATE ESTIMATION',
        ];
    }

    public function title(): string
    {
        $title = str_replace(['/', '\\', '*', '?', ':', '[', ']'], '-', $this->nomFormation);
        return substr($title, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 25,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 18,
            'G' => 40,
            'H' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                    ]);

                for ($i = 2; $i <= $lastRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8F9FA'],
                                ],
                            ]);
                    }
                }

                $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}

/**
 * Feuille pour les promotions (export simple)
 */
class PromotionsSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        $rows = new Collection();
        
        foreach ($this->items as $item) {
            $rows->push([
                $item['type'] ?? '',
                $item['grade_cible'] ?? '',
                $item['militaire']['matricule'] ?? '',
                $item['militaire']['nom'] ?? '',
                $item['militaire']['prenom'] ?? '',
                $item['militaire']['grade_actuel'] ?? '',
                $item['message'] ?? '',
                $item['date_estimation'] ? date('d/m/Y', strtotime($item['date_estimation'])) : '',
            ]);
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'TYPE',
            'GRADE CIBLE',
            'MATRICULE',
            'NOM',
            'PRÉNOM',
            'GRADE ACTUEL',
            'CONDITION',
            'DATE ESTIMATION',
        ];
    }

    public function title(): string
    {
        return 'Promotions';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 18,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 18,
            'G' => 40,
            'H' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                    ]);

                for ($i = 2; $i <= $lastRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8F9FA'],
                                ],
                            ]);
                    }
                }

                $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}

/**
 * Feuille pour les formations (export simple)
 */
class FormationsSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        $rows = new Collection();
        
        foreach ($this->items as $item) {
            $rows->push([
                $item['formation'] ?? '',
                $item['nom_formation'] ?? '',
                $item['militaire']['matricule'] ?? '',
                $item['militaire']['nom'] ?? '',
                $item['militaire']['prenom'] ?? '',
                $item['militaire']['grade_actuel'] ?? '',
                $item['message'] ?? '',
                $item['date_estimation'] ? date('d/m/Y', strtotime($item['date_estimation'])) : '',
            ]);
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'FORMATION',
            'NOM FORMATION',
            'MATRICULE',
            'NOM',
            'PRÉNOM',
            'GRADE ACTUEL',
            'CONDITION',
            'DATE ESTIMATION',
        ];
    }

    public function title(): string
    {
        return 'Formations';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 25,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 18,
            'G' => 40,
            'H' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                    ]);

                for ($i = 2; $i <= $lastRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8F9FA'],
                                ],
                            ]);
                    }
                }

                $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}

/**
 * Feuille pour les retraites
 */
class RetraitesSheet implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        $rows = new Collection();
        
        foreach ($this->items as $item) {
            $rows->push([
                $item['militaire']['matricule'] ?? '',
                $item['militaire']['nom'] ?? '',
                $item['militaire']['prenom'] ?? '',
                $item['militaire']['grade_actuel'] ?? '',
                $item['date_retraite'] ? date('d/m/Y', strtotime($item['date_retraite'])) : '',
                $item['mois_restants'] . ' mois',
            ]);
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'MATRICULE',
            'NOM',
            'PRÉNOM',
            'GRADE ACTUEL',
            'DATE RETRAITE',
            'MOIS RESTANTS',
        ];
    }

    public function title(): string
    {
        return 'Retraites';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 20,
            'D' => 18,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $sheet->getStyle('A1:' . $lastColumn . $lastRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                    ]);

                for ($i = 2; $i <= $lastRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $lastColumn . $i)
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F8F9FA'],
                                ],
                            ]);
                    }
                }

                $sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}