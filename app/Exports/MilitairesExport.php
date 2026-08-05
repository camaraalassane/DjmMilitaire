<?php

namespace App\Exports;

use App\Models\Militaire;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MilitairesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $search;
    protected $grade;
    protected $statut;

    public function __construct($search = null, $grade = null, $statut = null)
    {
        $this->search = $search;
        $this->grade = $grade;
        $this->statut = $statut;
    }

    public function query()
    {
        $query = Militaire::query()
            ->with('certificats'); // Précharger les certificats pour éviter N+1

        if ($this->search) {
            $search = preg_replace('/\s+/', ' ', $this->search);
            $searchTerms = explode(' ', trim($search));
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty($term)) {
                        $q->where(function($subQ) use ($term) {
                            $subQ->where('nom', 'LIKE', "%{$term}%")
                                 ->orWhere('prenom', 'LIKE', "%{$term}%")
                                 ->orWhere('matricule', 'LIKE', "%{$term}%");
                        });
                    }
                }
            });
        }

        if ($this->grade) {
            \Log::info('✅ Export - application du filtre grade : ' . $this->grade);
            $query->where('grade_actuel', $this->grade);
        }

        if ($this->statut) {
            $query->where('statut', $this->statut);
        }

        // Tri simple pour l'export
        $query->orderBy('nom')->orderBy('prenom');

        return $query;
    }

    public function headings(): array
    {
        return [
            'Matricule',
            'Nom',
            'Prénom',
            'Grade',
            'Date entrée service',
            'Âge (ans)',
            'Ancienneté (ans)',
            'Statut',
            'Position actuelle',
            'Fonction passée',
            'Fonction actuelle',
            'Spécialité',
            'Permis conduire',
            'Certificats obtenus',          // Nouvelle colonne
            'Formations complémentaires'    // Nouvelle colonne (si vous avez ce champ)
        ];
    }

    public function map($militaire): array
    {
        // Récupération des certificats (niveaux)
        $certificats = $militaire->certificats->pluck('niveau_certificat')->filter()->implode(', ');
        // Si vous avez un champ "formation" dans le militaire, ajoutez-le, sinon laissez vide
        $formations = $militaire->formations ?? ''; // à adapter selon votre modèle

        return [
            $militaire->matricule,
            $militaire->nom,
            $militaire->prenom,
            $militaire->grade_actuel,
            $militaire->date_entree_service?->format('d/m/Y') ?? '',
            round($militaire->age),                          // Arrondi à l'entier
            round($militaire->anciennete),                  // Arrondi à l'entier
            $militaire->statut,
            $militaire->position_actuelle ?? '',
            $militaire->fonction_passee ?? '',
            $militaire->fonction_actuelle ?? '',
            $militaire->specialite ?? '',
            $militaire->a_permis_conduire ? 'Oui' : 'Non',
            $certificats,
            $formations,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}