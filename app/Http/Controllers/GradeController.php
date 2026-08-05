<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    /**
     * Afficher la liste des grades.
     */
    public function index(Request $request)
    {
        $query = Grade::query();

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
                            $subQ->where('nom_grade', 'like', "%{$term}%")
                                 ->orWhere('code_grade', 'like', "%{$term}%")
                                 ->orWhere('type_grade', 'like', "%{$term}%");
                        });
                    }
                }
            });
        }

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type_grade', $request->type);
        }

        $grades = $query->orderBy('ordre')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($grade) => [
                'id' => $grade->id,
                'code_grade' => $grade->code_grade,
                'nom_grade' => $grade->nom_grade,
                'type_grade' => $grade->type_grade,
                'ordre' => $grade->ordre,
                'effectif_actif' => $grade->militaires()->where('statut', 'actif')->count(),
                'effectif_total' => $grade->militaires()->count(),
            ]);

        // Statistiques globales
        $statistiques = [
            'total_grades' => Grade::count(),
            'types_grades' => Grade::distinct('type_grade')->count('type_grade'),
            'total_militaires' => \App\Models\Militaire::where('statut', 'actif')->count(),
        ];

        // Options pour les filtres
        $typesGrades = Grade::distinct('type_grade')
            ->pluck('type_grade')
            ->map(fn ($type) => ['label' => $type, 'value' => $type])
            ->toArray();

        return Inertia::render('grades/index', [
            'grades' => $grades,
            'statistiques' => $statistiques,
            'filters' => $request->only(['search', 'type']),
            'typesGrades' => $typesGrades,
        ]);
    }

    /**
     * Afficher les détails d'un grade.
     */
    public function show(Request $request, Grade $grade)
    {
        // Récupérer les militaires ayant ce grade avec recherche améliorée
        $militairesQuery = $grade->militaires()->where('statut', 'actif');
        
        // RECHERCHE AMÉLIORÉE POUR LES MILITAIRES DANS LE GRADE (CORRECTION SERVEUR)
        if ($request->filled('search')) {
            // Supprime les espaces multiples et nettoie les bords
            $search = preg_replace('/\s+/', ' ', $request->search);
            $searchTerms = explode(' ', trim($search));
            
            $militairesQuery->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty($term)) {
                        // Force l'application du AND entre chaque mot, mais OR entre les colonnes
                        $q->where(function($subQ) use ($term) {
                            $subQ->where('nom', 'like', "%{$term}%")
                                 ->orWhere('prenom', 'like', "%{$term}%")
                                 ->orWhere('matricule', 'like', "%{$term}%");
                        });
                    }
                }
            });
        }
        
        $militaires = $militairesQuery
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($militaire) => [
                'id' => $militaire->id,
                'matricule' => $militaire->matricule,
                'nom' => $militaire->nom,
                'prenom' => $militaire->prenom,
                'date_entree_service' => $militaire->date_entree_service?->format('d/m/Y'),
                'date_retraite' => $militaire->date_retraite?->format('d/m/Y'),
                'specialite' => $militaire->specialite,
                'age' => $militaire->age,
                'anciennete' => $militaire->anciennete,
            ]);

        // Statistiques du grade
        $effectif_total = $grade->militaires()->count();
        $effectif_actif = $grade->militaires()->where('statut', 'actif')->count();
        $effectif_retraite = $grade->militaires()->where('statut', 'retraité')->count();

        return Inertia::render('grades/show', [
            'grade' => [
                'id' => $grade->id,
                'code_grade' => $grade->code_grade,
                'nom_grade' => $grade->nom_grade,
                'type_grade' => $grade->type_grade,
                'ordre' => $grade->ordre,
                'description' => $grade->description,
            ],
            'militaires' => $militaires,
            'statistiques' => [
                'effectif_total' => $effectif_total,
                'effectif_actif' => $effectif_actif,
                'effectif_retraite' => $effectif_retraite,
            ],
        ]);
    }

    /**
     * Enregistrer un nouveau grade.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_grade' => 'required|string|max:20|unique:grades,code_grade',
            'nom_grade'  => 'required|string|max:100',
            'type_grade' => 'required|string|max:50',
            'ordre'      => 'required|integer|min:0|unique:grades,ordre',
            'description'=> 'nullable|string',
        ]);

        $grade = Grade::create($validated);

        return redirect()->route('grades.index')
                         ->with('success', 'Grade créé avec succès.');
    }
}