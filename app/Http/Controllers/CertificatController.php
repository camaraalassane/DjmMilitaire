<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificatController extends Controller
{
    /**
     * Afficher la liste des certificats.
     */
    public function index(Request $request)
    {
        $query = Certificat::query();

        // RECHERCHE AMÉLIORÉE AVEC GESTION DES ESPACES (CORRECTION SERVEUR)
        if ($request->filled('search')) {
            // Supprime les espaces multiples et nettoie les bords
            $search = preg_replace('/\s+/', ' ', $request->search);
            $searchTerms = explode(' ', trim($search));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty($term)) {
                        // Force l'application du AND entre chaque mot, mais OR entre les colonnes
                        $q->where(function($subQ) use ($term) {
                            $subQ->where('nom_certificat', 'like', "%{$term}%")
                                 ->orWhere('niveau_certificat', 'like', "%{$term}%")
                                 ->orWhere('grade_associe', 'like', "%{$term}%");
                        });
                    }
                }
            });
        }

        // FILTRE PAR NIVEAU
        if ($request->filled('niveau')) {
            $query->where('niveau_certificat', $request->niveau);
        }

        $certificats = $query->orderBy('niveau_certificat')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($certificat) => [
                'id' => $certificat->id,
                'nom_certificat' => $certificat->nom_certificat,
                'niveau_certificat' => $certificat->niveau_certificat,
                'grade_associe' => $certificat->grade_associe,
                'anciennete_requise' => $certificat->anciennete_requise,
                'certificat_precedent' => $certificat->certificat_precedent,
                'duree_certificat_precedent' => $certificat->duree_certificat_precedent,
                'conditions_count' => $this->countConditions($certificat->conditions),
            ]);

        return Inertia::render('certificats/index', [
            'certificats' => $certificats,
            'filters' => $request->only(['search', 'niveau']), // Passage des filtres à la vue
        ]);
    }

    /**
     * Afficher les détails d'un certificat.
     */
    public function show(Certificat $certificat)
    {
        return Inertia::render('certificats/show', [
            'certificat' => [
                'id' => $certificat->id,
                'nom_certificat' => $certificat->nom_certificat,
                'niveau_certificat' => $certificat->niveau_certificat,
                'grade_associe' => $certificat->grade_associe,
                'anciennete_requise' => $certificat->anciennete_requise,
                'certificat_precedent' => $certificat->certificat_precedent,
                'duree_certificat_precedent' => $certificat->duree_certificat_precedent,
                'conditions' => $this->formatConditions($certificat->conditions),
                'created_at' => $certificat->created_at?->format('d/m/Y'),
                'updated_at' => $commissions_count = $certificat->updated_at?->format('d/m/Y'),
            ]
        ]);
    }

    /**
     * Compter le nombre de conditions
     */
    private function countConditions($conditions)
    {
        if (empty($conditions)) return 0;
        
        if (is_string($conditions)) {
            $conditions = json_decode($conditions, true);
        }
        
        return is_array($conditions) ? count($conditions) : 0;
    }

    /**
     * Formater les conditions pour l'affichage
     */
    private function formatConditions($conditions)
    {
        if (empty($conditions)) return [];
        
        if (is_array($conditions)) {
            return $conditions;
        }
        
        if (is_string($conditions)) {
            return json_decode($conditions, true) ?? [];
        }
        
        return [];
    }
}