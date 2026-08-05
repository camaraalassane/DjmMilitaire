<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Models\Contrat;
use App\Models\Militaire;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AlerteController extends Controller
{
    /**
     * Afficher la liste des alertes.
     */
    public function index(Request $request)
    {
        $query = Alerte::with('militaire');

        // Filtre par type
        if ($request->filled('type')) {
            $query->where('type_alerte', $request->type);
        }

        // Filtre par statut (vue/non vue)
        if ($request->filled('statut')) {
            if ($request->statut === 'vue') {
                $query->where('est_vue', true);
            } elseif ($request->statut === 'non_vue') {
                $query->where('est_vue', false);
            }
        }

        // Recherche améliorée
        if ($request->filled('search')) {
            $search = preg_replace('/\s+/', ' ', $request->search);
            $searchTerms = explode(' ', trim($search));

            $query->whereHas('militaire', function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty($term)) {
                        $q->where(function($subQ) use ($term) {
                            $subQ->where('nom', 'like', "%{$term}%")
                                 ->orWhere('prenom', 'like', "%{$term}%")
                                 ->orWhere('matricule', 'like', "%{$term}%");
                        });
                    }
                }
            });
        }

        $alertes = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($alerte) => [
                'id' => $alerte->id,
                'type_alerte' => $alerte->type_alerte,
                'message' => $alerte->message,
                'date_echeance' => $alerte->date_echeance?->format('Y-m-d'),
                'date_echeance_formatted' => $alerte->date_echeance?->format('d/m/Y'),
                'est_vue' => $alerte->est_vue,
                'created_at' => $alerte->created_at?->format('d/m/Y H:i'),
                'militaire' => $alerte->militaire ? [
                    'id' => $alerte->militaire->id,
                    'nom' => $alerte->militaire->nom,
                    'prenom' => $alerte->militaire->prenom,
                    'matricule' => $alerte->militaire->matricule,
                    'grade_actuel' => $alerte->militaire->grade_actuel,
                ] : null,
            ]);

        // Statistiques globales
        $statistiques = [
            'total' => Alerte::count(),
            'non_vues' => Alerte::where('est_vue', false)->count(),
            'vues' => Alerte::where('est_vue', true)->count(),
            'promotions' => Alerte::where('type_alerte', 'promotion')->count(),
            'formations' => Alerte::where('type_alerte', 'formation')->count(),
            'retraites' => Alerte::where('type_alerte', 'retraite')->count(),
            'contrats' => Alerte::where('type_alerte', 'contrat')->count(),
        ];

        return Inertia::render('alertes/index', [
            'alertes' => $alertes,
            'statistiques' => $statistiques,
            'filters' => $request->only(['search', 'type', 'statut']),
        ]);
    }

    /**
     * Marquer une alerte comme vue (supprimée)
     */
    public function marquerVue(Alerte $alerte)
    {
        $alerte->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Alerte supprimée avec succès.');
    }

    /**
     * Supprimer toutes les alertes non vues
     */
    public function marquerToutVue()
    {
        $alertes = Alerte::where('est_vue', false);
        $count = $alertes->count();

        if ($count > 0) {
            $alertes->delete();
            $message = "{$count} alerte(s) ont été supprimées avec succès.";
        } else {
            $message = "Aucune alerte à supprimer.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * GRADES ÉLIGIBLES POUR LES ALERTES DE CONTRAT
     * Sous-officiers jusqu'à 1ère classe uniquement
     */
    private function getGradesEligiblesContrat(): array
    {
        return [
            'Soldat 2',
            'Soldat 1',
            'Caporal',
            'Caporal-chef',
            'Sergent',
            'Sergent-Chef',
            'Adjudant',
            'Adjudant-Chef',
            'Major'
        ];
    }

    /**
     * Vérifier et créer les alertes pour les renouvellements de contrat
     */
    public function checkRenouvellements()
    {
        $gradesEligibles = $this->getGradesEligiblesContrat();

        $militaires = Militaire::whereIn('grade_actuel', $gradesEligibles)->get();

        $createdCount = 0;
        $alreadyExists = 0;
        $moins5ans = 0;
        $sansDateEntree = 0;

        foreach ($militaires as $militaire) {
            if (!$militaire->date_entree_service) {
                $sansDateEntree++;
                continue;
            }

            $serviceYears = floor($militaire->date_entree_service->diffInYears(now()));

            if ($serviceYears < 5) {
                $moins5ans++;
                Alerte::where('militaire_id', $militaire->id)
                    ->where('type_alerte', 'contrat')
                    ->delete();
                continue;
            }

            $alerteExiste = Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'contrat')
                ->exists();

            if ($alerteExiste) {
                $alreadyExists++;
            } else {
                Alerte::create([
                    'militaire_id' => $militaire->id,
                    'type_alerte' => 'contrat',
                    'message' => "Renouvellement de contrat requis pour {$militaire->prenom} {$militaire->nom} ({$serviceYears} ans de service) - Grade: {$militaire->grade_actuel}",
                    'date_echeance' => now()->addMonths(6),
                    'est_vue' => false,
                ]);
                $createdCount++;
            }
        }

        if ($createdCount > 0) {
            $message = "✅ {$createdCount} nouvelle(s) alerte(s) de contrat créée(s) avec succès !";
        } elseif ($alreadyExists > 0) {
            $message = "ℹ️ Tous les militaires éligibles ({$alreadyExists}) ont déjà une alerte de contrat.";
        } else {
            $message = "ℹ️ Aucun militaire éligible trouvé (grade éligible + 5 ans de service).";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Vérifier les alertes pour un militaire spécifique
     */
    public function verifierAlertesMilitaire(int $militaireId): bool
    {
        $militaire = Militaire::find($militaireId);

        if (!$militaire) {
            return false;
        }

        $gradesEligibles = $this->getGradesEligiblesContrat();

        Alerte::where('militaire_id', $militaire->id)
            ->where('type_alerte', 'contrat')
            ->delete();

        if (!in_array($militaire->grade_actuel, $gradesEligibles)) {
            return false;
        }

        if (!$militaire->date_entree_service) {
            return false;
        }

        $serviceYears = floor($militaire->date_entree_service->diffInYears(now()));

        if ($serviceYears < 5) {
            return false;
        }

        Alerte::create([
            'militaire_id' => $militaire->id,
            'type_alerte' => 'contrat',
            'message' => "Renouvellement de contrat requis pour {$militaire->prenom} {$militaire->nom} ({$serviceYears} ans de service) - Grade: {$militaire->grade_actuel}",
            'date_echeance' => now()->addMonths(6),
            'est_vue' => false,
        ]);

        return true;
    }

    /**
     * Générer toutes les alertes automatiquement
     */
    public function genererAlertes()
    {
        $this->genererAlertesPromotion();
        $this->genererAlertesRetraite();
        $this->checkRenouvellements();

        return redirect()->back()->with('success', 'Toutes les alertes ont été générées.');
    }

    /**
     * Générer les alertes de promotion
     */
    private function genererAlertesPromotion(): int
    {
        $militaires = Militaire::all();
        $createdCount = 0;

        foreach ($militaires as $militaire) {
            if ($militaire->aAncienneteGradeMin(3)) {
                $alerteExiste = Alerte::where('militaire_id', $militaire->id)
                    ->where('type_alerte', 'promotion')
                    ->where('est_vue', false)
                    ->exists();

                if (!$alerteExiste) {
                    Alerte::create([
                        'militaire_id' => $militaire->id,
                        'type_alerte' => 'promotion',
                        'message' => "{$militaire->prenom} {$militaire->nom} est éligible pour une promotion",
                        'date_echeance' => now()->addMonths(6),
                        'est_vue' => false,
                    ]);
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }

    /**
     * Générer les alertes de retraite
     */
    private function genererAlertesRetraite(): int
    {
        $militaires = Militaire::all();
        $createdCount = 0;

        foreach ($militaires as $militaire) {
            if ($militaire->estEligibleRetraite()) {
                $alerteExiste = Alerte::where('militaire_id', $militaire->id)
                    ->where('type_alerte', 'retraite')
                    ->where('est_vue', false)
                    ->exists();

                if (!$alerteExiste) {
                    $dateRetraite = $militaire->calculerDateRetraite();
                    Alerte::create([
                        'militaire_id' => $militaire->id,
                        'type_alerte' => 'retraite',
                        'message' => "{$militaire->prenom} {$militaire->nom} est éligible à la retraite",
                        'date_echeance' => $dateRetraite,
                        'est_vue' => false,
                    ]);
                    $createdCount++;
                }
            }
        }

        return $createdCount;
    }
}
