<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MilitairesTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected array $headers;
    protected array $exampleRow;

    public function __construct(array $headers, array $exampleRow)
    {
        $this->headers = $headers;
        $this->exampleRow = $exampleRow;
    }

    public function array(): array
    {
        return [$this->exampleRow];
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = chr(64 + count($this->headers)); // Assumes <= 26 columns
        if (count($this->headers) > 26) {
            $lastCol = 'A' . chr(64 + count($this->headers) - 26);
        }

        return [
            // Style de l'en-tête
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0284C7'], // sky-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ],
            // Style de la ligne d'exemple
            2 => [
                'font' => [
                    'italic' => true,
                    'color' => ['rgb' => '6B7280'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F9FF'], // sky-50
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $widths = [];
        $colLetters = range('A', 'Z');

        foreach ($this->headers as $i => $header) {
            $col = $i < 26 ? $colLetters[$i] : 'A' . $colLetters[$i - 26];
            $widths[$col] = max(strlen($header) + 4, 15);
        }

        return $widths;
    }
}
