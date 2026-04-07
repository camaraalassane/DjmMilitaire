<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        // Filtre par recherche (sur le nom du militaire)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('militaire', function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
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

        // Statistiques
        $statistiques = [
            'total' => Alerte::count(),
            'non_vues' => Alerte::where('est_vue', false)->count(),
            'vues' => Alerte::where('est_vue', true)->count(),
        ];

        // Options pour les filtres
        $typesAlertes = [
            ['label' => 'Promotion', 'value' => 'promotion'],
            ['label' => 'Formation', 'value' => 'formation'],
            ['label' => 'Retraite', 'value' => 'retraite'],
           // ['label' => 'Certificat', 'value' => 'certificat'],
        ];

        return Inertia::render('alertes/index', [
            'alertes' => $alertes,
            'statistiques' => $statistiques,
            'filters' => $request->only(['search', 'type', 'statut']),
            'typesAlertes' => $typesAlertes,
        ]);
    }

    /**
     * Marquer une alerte comme vue.
     */
/**
 * Supprimer une alerte.
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
 * Supprimer toutes les alertes non vues.
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
}