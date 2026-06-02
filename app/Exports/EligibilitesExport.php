<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class EligibilitesExport implements WithMultipleSheets
{
    protected $data;
    protected $type;

    public function __construct($data, $type = 'all')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->type === 'promotions' || $this->type === 'all') {
            $promotionsByGrade = [];
            foreach ($this->data['promotions'] ?? [] as $promo) {
                $gradeCible = $promo['grade_cible'];
                if (!isset($promotionsByGrade[$gradeCible])) {
                    $promotionsByGrade[$gradeCible] = [];
                }
                $promotionsByGrade[$gradeCible][] = $promo;
            }
            
            foreach ($promotionsByGrade as $gradeCible => $promotions) {
                $sheets[] = new PromotionSheet($gradeCible, $promotions);
            }
        }

        if ($this->type === 'formations' || $this->type === 'all') {
            $formationsByName = [];
            foreach ($this->data['formations'] ?? [] as $formation) {
                $nomFormation = $formation['nom_formation'];
                if (!isset($formationsByName[$nomFormation])) {
                    $formationsByName[$nomFormation] = [];
                }
                $formationsByName[$nomFormation][] = $formation;
            }
            
            foreach ($formationsByName as $nomFormation => $formations) {
                $sheets[] = new FormationSheet($nomFormation, $formations);
            }
        }

        if ($this->type === 'retraites' || $this->type === 'all') {
            if (!empty($this->data['retraites'])) {
                $sheets[] = new RetraiteSheet($this->data['retraites']);
            }
        }

        return $sheets;
    }
}

/**
 * Feuille pour les promotions par grade cible
 */
class PromotionSheet implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $gradeCible;
    protected $promotions;

    public function __construct($gradeCible, $promotions)
    {
        $this->gradeCible = $gradeCible;
        $this->promotions = $promotions;
    }

    public function collection()
    {
        $rows = [];
        foreach ($this->promotions as $item) {
            $rows[] = [
                $item['militaire']['matricule'],
                $item['militaire']['nom'],
                $item['militaire']['prenom'],
                $item['militaire']['grade_actuel'],
                $item['grade_cible'],  // GRADE CIBLE
                $item['annee_proposition'] ?? $this->extractAnnee($item['date_estimation']),
                $this->formatDate($item['date_anciennete'] ?? ''),
            ];
        }
        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'MATRICULE',
            'NOM',
            'PRENOM',
            'GRADE ACTUEL',
            'GRADE CIBLE',
            'ANNEE PROPOSITION',
            'DATE ANCIENNETE'
        ];
    }

    public function title(): string
    {
        $title = "Promotion_{$this->gradeCible}";
        return substr($title, 0, 31);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // MATRICULE
            'B' => 20,  // NOM
            'C' => 20,  // PRENOM
            'D' => 25,  // GRADE ACTUEL
            'E' => 20,  // GRADE CIBLE
            'F' => 18,  // ANNEE PROPOSITION
            'G' => 15,  // DATE ANCIENNETE
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFont()->setSize(11);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:G1')->getFill()->getStartColor()->setARGB('FF0284c7');
        $sheet->getStyle('A1:G1')->getFont()->getColor()->setARGB('FFFFFFFF');
        
        return [];
    }

    private function extractAnnee($date)
    {
        if (!$date) return '';
        return substr($date, 0, 4);
    }

    private function formatDate($date)
    {
        if (!$date) return '';
        return \Carbon\Carbon::parse($date)->format('d/m/Y');
    }
}

/**
 * Feuille pour les formations par nom de formation
 */
class FormationSheet implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $nomFormation;
    protected $formations;

    public function __construct($nomFormation, $formations)
    {
        $this->nomFormation = $nomFormation;
        $this->formations = $formations;
    }

    public function collection()
    {
        $rows = [];
        foreach ($this->formations as $item) {
            $rows[] = [
                $item['militaire']['matricule'],
                $item['militaire']['nom'],
                $item['militaire']['prenom'],
                $item['militaire']['grade_actuel'],
                $item['nom_formation'],  // NOM DE LA FORMATION
                $item['annee_proposition'] ?? $this->extractAnnee($item['date_estimation']),
                $this->formatDate($item['date_conditions'] ?? ''),
            ];
        }
        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'MATRICULE',
            'NOM',
            'PRENOM',
            'GRADE ACTUEL',
            'FORMATION',
            'ANNEE PROPOSITION',
            'DATE CONDITIONS'
        ];
    }

    public function title(): string
    {
        // Nettoyer le nom de la formation pour le nom de l'onglet
        $title = $this->nomFormation;
        $title = str_replace(['Certificat d\'Aptitude Technique Niveau 1', 'Certificat d\'Aptitude Technique Niveau 2', 'Certificat d\'Instruction d\'Armes', 'Brevet d\'Aptitude Niveau 1', 'Brevet d\'Aptitude Niveau 2', 'Cour d\'Application', 'Cour des Futurs Commandants d\'Unité', 'Cour d\'État-Major', 'Certificat d\'État-Major', 'École de Guerre'], '', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        
        // Mapping simple
        $mapping = [
            'Certificat d\'Aptitude Technique Niveau 1' => 'CAT1',
            'Certificat d\'Aptitude Technique Niveau 2' => 'CAT2',
            'Certificat d\'Instruction d\'Armes' => 'CIA',
            'Brevet d\'Aptitude Niveau 1' => 'BA1',
            'Brevet d\'Aptitude Niveau 2' => 'BA2',
            'Cour d\'Application' => 'APLI',
            'Cour des Futurs Commandants d\'Unité' => 'CFCU',
            'Cour d\'État-Major' => 'CEM',
            'Certificat d\'État-Major' => 'CERT_EM',
            'École de Guerre' => 'ECOLE_GUERRE',
        ];
        
        $shortName = $mapping[$this->nomFormation] ?? $this->nomFormation;
        $title = "Formation_" . $shortName;
        return substr($title, 0, 31);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // MATRICULE
            'B' => 20,  // NOM
            'C' => 20,  // PRENOM
            'D' => 25,  // GRADE ACTUEL
            'E' => 35,  // FORMATION
            'F' => 18,  // ANNEE PROPOSITION
            'G' => 15,  // DATE CONDITIONS
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFont()->setSize(11);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:G1')->getFill()->getStartColor()->setARGB('FFF97316');
        $sheet->getStyle('A1:G1')->getFont()->getColor()->setARGB('FFFFFFFF');
        
        return [];
    }

    private function extractAnnee($date)
    {
        if (!$date) return '';
        return substr($date, 0, 4);
    }

    private function formatDate($date)
    {
        if (!$date) return '';
        return \Carbon\Carbon::parse($date)->format('d/m/Y');
    }
}

/**
 * Feuille pour les retraites
 */
class RetraiteSheet implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    protected $retraites;

    public function __construct($retraites)
    {
        $this->retraites = $retraites;
    }

    public function collection()
    {
        $rows = [];
        foreach ($this->retraites as $item) {
            $rows[] = [
                $item['militaire']['matricule'],
                $item['militaire']['nom'],
                $item['militaire']['prenom'],
                $item['militaire']['grade_actuel'],
                $item['date_retraite_formatted'],
                $item['mois_restants'],
            ];
        }
        return new Collection($rows);
    }

    public function headings(): array
    {
        return [
            'MATRICULE',
            'NOM',
            'PRENOM',
            'GRADE ACTUEL',
            'DATE RETRAITE',
            'MOIS RESTANTS'
        ];
    }

    public function title(): string
    {
        return "Retraites_proches";
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 20,
            'C' => 20,
            'D' => 25,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFont()->setSize(11);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFF97316');
        $sheet->getStyle('A1:F1')->getFont()->getColor()->setARGB('FFFFFFFF');
        
        return [];
    }
}