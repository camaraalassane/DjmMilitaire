<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Militaire;
use App\Models\Alerte;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ContratController extends Controller
{
    /**
     * Afficher la liste des militaires qui ont renouvelé leur contrat
     */
    public function index(Request $request)
    {
        // Récupérer TOUS les militaires avec leurs contrats
        $militaires = Militaire::with(['grade', 'contrats' => function($query) {
            $query->latest('date_debut');
        }])->get();

        // LISTE DES MILITAIRES QUI ONT RENOUVELÉ LEUR CONTRAT
        // Un militaire est considéré comme "renouvelé" s'il a un contrat actif ET un contrat renouvele
        $militairesRenouveles = $militaires->filter(function($militaire) {
            $contratActif = $militaire->contrats->where('statut', 'actif')->first();
            $contratRenouvele = $militaire->contrats->where('statut', 'renouvele')->first();
            return $contratActif !== null && $contratRenouvele !== null;
        })->map(function($militaire) {
            $contratActif = $militaire->contrats->where('statut', 'actif')->first();
            $contratRenouvele = $militaire->contrats->where('statut', 'renouvele')->first();

            // Calcul des années de service
            $anneesService = 0;
            if ($contratActif && $contratActif->date_debut) {
                $dateDebut = Carbon::parse($contratActif->date_debut);
                $anneesService = $dateDebut->diffInYears(now());
                $anneesService = max(0, $anneesService);
            }

            return [
                'id' => (int) $militaire->id,
                'matricule' => $militaire->matricule,
                'nom' => $militaire->nom,
                'prenom' => $militaire->prenom,
                'grade' => $militaire->grade ? [
                    'id' => $militaire->grade->id,
                    'nom' => $militaire->grade->nom_grade,
                ] : null,
                'date_entree_service' => $militaire->date_entree_service?->format('d/m/Y'),
                'contrat_actif' => $contratActif ? [
                    'id' => $contratActif->id,
                    'date_debut' => $contratActif->date_debut?->format('d/m/Y'),
                    'date_fin' => $contratActif->date_fin?->format('d/m/Y'),
                    'duree_annees' => $contratActif->duree_annees,
                    'statut' => $contratActif->statut,
                ] : null,
                'contrat_renouvele' => $contratRenouvele ? [
                    'id' => $contratRenouvele->id,
                    'date_debut' => $contratRenouvele->date_debut?->format('d/m/Y'),
                    'date_fin' => $contratRenouvele->date_fin?->format('d/m/Y'),
                    'duree_annees' => $contratRenouvele->duree_annees,
                    'statut' => $contratRenouvele->statut,
                    'date_renouvellement' => $contratRenouvele->date_renouvellement?->format('d/m/Y'),
                ] : null,
                'annees_service' => $anneesService,
                'date_renouvellement' => $contratRenouvele?->date_renouvellement?->format('d/m/Y'),
            ];
        })->values();

        // Si un militaireId est passé, le mettre en avant
        $militaireId = $request->input('militaire') ? (int) $request->input('militaire') : null;
        $openModal = $request->input('openModal', false) ? true : false;

        // Récupérer le militaire spécifique même s'il n'est pas dans la liste
        $militaireCible = null;
        if ($militaireId) {
            $militaireCible = Militaire::with(['grade', 'contrats' => function($query) {
                $query->orderBy('date_debut', 'desc');
            }])->find($militaireId);

            if ($militaireCible) {
                $contratActif = $militaireCible->contrats->where('statut', 'actif')->first();
                $contratRenouvele = $militaireCible->contrats->where('statut', 'renouvele')->first();
                $anneesService = 0;
                if ($contratActif && $contratActif->date_debut) {
                    $dateDebut = Carbon::parse($contratActif->date_debut);
                    $anneesService = $dateDebut->diffInYears(now());
                    $anneesService = max(0, $anneesService);
                }

                $militaireCible = [
                    'id' => (int) $militaireCible->id,
                    'matricule' => $militaireCible->matricule,
                    'nom' => $militaireCible->nom,
                    'prenom' => $militaireCible->prenom,
                    'grade' => $militaireCible->grade ? [
                        'id' => $militaireCible->grade->id,
                        'nom' => $militaireCible->grade->nom_grade,
                    ] : null,
                    'date_entree_service' => $militaireCible->date_entree_service?->format('d/m/Y'),
                    'contrat_actif' => $contratActif ? [
                        'id' => $contratActif->id,
                        'date_debut' => $contratActif->date_debut?->format('d/m/Y'),
                        'date_fin' => $contratActif->date_fin?->format('d/m/Y'),
                        'duree_annees' => $contratActif->duree_annees,
                        'statut' => $contratActif->statut,
                    ] : null,
                    'contrat_renouvele' => $contratRenouvele ? [
                        'id' => $contratRenouvele->id,
                        'date_debut' => $contratRenouvele->date_debut?->format('d/m/Y'),
                        'date_fin' => $contratRenouvele->date_fin?->format('d/m/Y'),
                        'duree_annees' => $contratRenouvele->duree_annees,
                        'statut' => $contratRenouvele->statut,
                        'date_renouvellement' => $contratRenouvele->date_renouvellement?->format('d/m/Y'),
                    ] : null,
                    'annees_service' => $anneesService,
                    'date_renouvellement' => $contratRenouvele?->date_renouvellement?->format('d/m/Y'),
                ];
            }
        }

        return Inertia::render('contrats/index', [
            'militairesRenouveles' => $militairesRenouveles,
            'militaireId' => $militaireId,
            'openModal' => $openModal,
            'militaireCible' => $militaireCible,
        ]);
    }

    /**
     * Renouveler un contrat
     */
    public function store(Request $request)
    {
        $request->validate([
            'militaire_id' => 'required|exists:militaires,id',
            'date_debut' => 'required|date',
            'duree_annees' => 'required|integer|min:1|max:10',
            'observations' => 'nullable|string',
        ]);

        // Récupérer la date depuis le formulaire
        $dateDebut = $request->date_debut;
        if (is_string($dateDebut)) {
            $dateDebut = Carbon::parse($dateDebut);
        } elseif ($dateDebut instanceof \DateTime) {
            $dateDebut = Carbon::instance($dateDebut);
        } else {
            $dateDebut = Carbon::parse($dateDebut);
        }

        // Récupérer l'ancien contrat actif
        $ancienContrat = Contrat::where('militaire_id', $request->militaire_id)
            ->where('statut', 'actif')
            ->latest('date_debut')
            ->first();

        if ($ancienContrat) {
            // Marquer l'ancien contrat comme renouvelé
            $ancienContrat->update([
                'statut' => 'renouvele',
                'date_fin' => $dateDebut->copy()->subDay(),
                'date_renouvellement' => now(),
            ]);
        }

        // Créer le nouveau contrat
        $dateFin = $dateDebut->copy()->addYears($request->duree_annees);

        $contrat = Contrat::create([
            'militaire_id' => $request->militaire_id,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'duree_annees' => $request->duree_annees,
            'statut' => 'actif',
            'observations' => $request->observations,
            'date_renouvellement' => now(),
        ]);

        // Supprimer les alertes de renouvellement pour ce militaire
        Alerte::where('militaire_id', $request->militaire_id)
            ->where('type_alerte', 'contrat')
            ->delete();

        return redirect()->back()->with('success', 'Contrat renouvelé avec succès.');
    }

    /**
     * Afficher les détails d'un contrat
     */
    public function show(Contrat $contrat)
    {
        return response()->json($contrat->load('militaire'));
    }

    /**
     * Afficher l'historique des contrats d'un militaire
     */
    public function historique(int $militaireId)
    {
        $contrats = Contrat::where('militaire_id', $militaireId)
            ->orderBy('date_debut', 'desc')
            ->get()
            ->map(function($contrat) {
                return [
                    'id' => $contrat->id,
                    'date_debut' => $contrat->date_debut?->format('d/m/Y'),
                    'date_fin' => $contrat->date_fin?->format('d/m/Y'),
                    'duree_annees' => $contrat->duree_annees,
                    'statut' => $contrat->statut,
                    'observations' => $contrat->observations,
                    'created_at' => $contrat->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json($contrats);
    }

    /**
     * Vérifier et générer les alertes de contrat
     */
    public function checkAndGenerateAlerts()
    {
        $gradesEligibles = [
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

        $militaires = Militaire::with(['contrats' => function($query) {
            $query->where('statut', 'actif')->latest('date_debut');
        }])->whereIn('grade_actuel', $gradesEligibles)->get();

        $createdCount = 0;
        $alertesExistantes = 0;

        foreach ($militaires as $militaire) {
            $contratActif = $militaire->contrats->first();

            if (!$contratActif) {
                continue;
            }

            $serviceYears = now()->diffInYears($contratActif->date_debut);

            if ($serviceYears >= 5) {
                $alerteExiste = Alerte::where('militaire_id', $militaire->id)
                    ->where('type_alerte', 'contrat')
                    ->where('est_vue', false)
                    ->exists();

                if ($alerteExiste) {
                    $alertesExistantes++;
                } else {
                    Alerte::create([
                        'militaire_id' => $militaire->id,
                        'type_alerte' => 'contrat',
                        'message' => "Renouvellement de contrat requis pour {$militaire->prenom} {$militaire->nom} ({$serviceYears} ans de service)",
                        'date_echeance' => $contratActif->date_fin ?? now()->addMonths(6),
                        'est_vue' => false,
                    ]);
                    $createdCount++;
                }
            }
        }

        $message = "{$createdCount} alerte(s) de renouvellement créée(s)";
        if ($alertesExistantes > 0) {
            $message .= ", {$alertesExistantes} alerte(s) existante(s) déjà présentes";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Statistiques des contrats
     */
    public function stats()
    {
        $totalActifs = Contrat::where('statut', 'actif')->count();
        $totalExpires = Contrat::where('statut', 'expire')->count();
        $totalRenouveles = Contrat::where('statut', 'renouvele')->count();

        $militaires = Militaire::with(['contrats' => function($query) {
            $query->where('statut', 'actif')->latest('date_debut');
        }])->get();

        $a5ans = 0;
        $a10ans = 0;
        $a15ans = 0;

        foreach ($militaires as $militaire) {
            $contrat = $militaire->contrats->first();
            if ($contrat) {
                $annees = now()->diffInYears($contrat->date_debut);
                if ($annees >= 15) $a15ans++;
                elseif ($annees >= 10) $a10ans++;
                elseif ($annees >= 5) $a5ans++;
            }
        }

        return response()->json([
            'contrats_actifs' => $totalActifs,
            'contrats_expires' => $totalExpires,
            'contrats_renouveles' => $totalRenouveles,
            'militaires_5_ans' => $a5ans,
            'militaires_10_ans' => $a10ans,
            'militaires_15_ans' => $a15ans,
        ]);
    }

    /**
     * Annuler un renouvellement
     */
    public function annulerRenouvellement(Request $request)
    {
        $request->validate([
            'militaire_id' => 'required|exists:militaires,id',
        ]);

        $militaireId = $request->militaire_id;

        // Récupérer le contrat actif (le nouveau)
        $nouveauContrat = Contrat::where('militaire_id', $militaireId)
            ->where('statut', 'actif')
            ->latest('date_debut')
            ->first();

        if (!$nouveauContrat) {
            return redirect()->back()->with('error', 'Aucun contrat actif trouvé.');
        }

        // Récupérer le dernier contrat renouvelé (l'ancien)
        $ancienContrat = Contrat::where('militaire_id', $militaireId)
            ->where('statut', 'renouvele')
            ->latest('date_renouvellement')
            ->first();

        if (!$ancienContrat) {
            return redirect()->back()->with('error', 'Aucun contrat renouvelé trouvé.');
        }

        // Restaurer l'ancien contrat
        $ancienContrat->update([
            'statut' => 'actif',
            'date_fin' => $nouveauContrat->date_fin,
            'date_renouvellement' => null,
        ]);

        // Supprimer le nouveau contrat
        $nouveauContrat->delete();

        // Recréer l'alerte
        $serviceYears = $ancienContrat->date_debut->diffInYears(now());
        $serviceYears = max(0, $serviceYears);

        Alerte::create([
            'militaire_id' => $militaireId,
            'type_alerte' => 'contrat',
            'message' => "Renouvellement de contrat requis pour le militaire (renouvellement annulé) - {$serviceYears} ans de service",
            'date_echeance' => $ancienContrat->date_fin ?? now()->addMonths(6),
            'est_vue' => false,
        ]);

        return redirect()->back()->with('success', 'Renouvellement annulé avec succès.');
    }
}
