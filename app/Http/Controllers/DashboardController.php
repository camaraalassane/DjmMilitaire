<?php

namespace App\Http\Controllers;

use App\Models\Militaire;
use App\Models\Alerte;
use App\Models\Grade;
use App\Models\Eligibilite;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $alertesNonVues = Alerte::where('est_vue', false)
            ->with('militaire')
            ->orderBy('date_echeance')
            ->take(20)
            ->get()
            ->map(function ($alerte) {
                return [
                    'id' => $alerte->id,
                    'message' => $alerte->message,
                    'type_alerte' => $alerte->type_alerte,
                    'date_echeance' => $alerte->date_echeance,
                    'est_vue' => $alerte->est_vue,
                    'militaire' => [
                        'id' => $alerte->militaire->id,
                        'nom' => $alerte->militaire->nom,
                        'prenom' => $alerte->militaire->prenom,
                        'matricule' => $alerte->militaire->matricule
                    ]
                ];
            });

        // Récupérer tous les militaires actifs
        $militairesActifs = Militaire::where('statut', 'actif')->get();
        
        // Calculer les retraites proches (dans les 12 mois)
        $aujourdhui = Carbon::now()->startOfDay();
        
        $militairesProchesRetraite = $militairesActifs
            ->map(function ($militaire) use ($aujourdhui) {
                $dateRetraite = $militaire->calculerDateRetraite();
                if ($dateRetraite) {
                    $diffJours = $aujourdhui->diffInDays($dateRetraite);
                    $moisRestants = floor($diffJours / 30);
                    $joursRestants = $diffJours % 30;
                    
                    // Ne retourner que les retraites à venir
                    if ($diffJours >= 0) {
                        return [
                            'id' => $militaire->id,
                            'nom' => $militaire->nom,
                            'prenom' => $militaire->prenom,
                            'matricule' => $militaire->matricule,
                            'grade_actuel' => $militaire->grade_actuel,
                            'date_retraite' => $dateRetraite->format('Y-m-d'),
                            'statut' => $militaire->statut,
                            'mois_restants' => $moisRestants,
                            'jours_restants' => $joursRestants,
                            'mois_restants_formate' => $this->formaterMoisRestants($moisRestants, $joursRestants),
                        ];
                    }
                }
                return null;
            })
            ->filter(function ($item) {
                return $item && $item['mois_restants'] <= 12;
            })
            ->sortBy('date_retraite')
            ->take(10)
            ->values();

        $statistiques = [
            'total_militaires' => Militaire::count(),
            'militaires_actifs' => $militairesActifs->count(),
            'alertes_non_vues' => Alerte::where('est_vue', false)->count(),
            'prochaines_retraites' => $militairesProchesRetraite->count(),
        ];

        // Statistiques par grade
        $grades = Grade::withCount(['militaires' => function($query) {
            $query->where('statut', 'actif');
        }])->get()
        ->map(function ($grade) {
            return [
                'id' => $grade->id,
                'nom_grade' => $grade->nom_grade,
                'type_grade' => $grade->type_grade,
                'militaires_count' => $grade->militaires_count
            ];
        });

        return Inertia::render('Dashboard', [
            'alertesNonVues' => $alertesNonVues,
            'militairesProchesRetraite' => $militairesProchesRetraite,
            'statistiques' => $statistiques,
            'grades' => $grades
        ]);
    }

    /**
     * Formate le nombre de mois restants pour un affichage lisible
     */
    private function formaterMoisRestants($mois, $jours)
    {
        if ($mois == 0) {
            if ($jours == 0) {
                return "Aujourd'hui";
            } elseif ($jours == 1) {
                return "Demain";
            } else {
                return "Dans {$jours} jour" . ($jours > 1 ? 's' : '');
            }
        } elseif ($mois == 1 && $jours == 0) {
            return "Dans 1 mois";
        } elseif ($mois == 1 && $jours > 0) {
            return "Dans 1 mois et {$jours} jour" . ($jours > 1 ? 's' : '');
        } elseif ($mois > 1 && $jours == 0) {
            return "Dans {$mois} mois";
        } elseif ($mois > 1 && $jours > 0) {
            return "Dans {$mois} mois et {$jours} jour" . ($jours > 1 ? 's' : '');
        } else {
            return "Dans {$mois} mois";
        }
    }

    public function marquerAlerteVue(Alerte $alerte)
    {
        $alerte->update(['est_vue' => true]);
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Alerte marquée comme vue.');
    }

    public function eligibilites()
    {
        $eligibilites = Eligibilite::with('militaire')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('eligibilites.index', compact('eligibilites'));
    }
}