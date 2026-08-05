<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Militaire;
use App\Models\Grade;
use App\Models\Alerte;
use App\Models\Certificat;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MilitairesExport;
use App\Imports\MilitairesImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Artisan;

class MilitaireController extends Controller
{
    public function index(Request $request)
    {
        $query = Militaire::query();
        $query->leftJoin('grades', 'militaires.grade_actuel', '=', 'grades.nom_grade');

        if ($request->filled('search')) {
            $search = preg_replace('/\s+/', ' ', $request->search);
            $searchTerms = explode(' ', trim($search));
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty($term)) {
                        $q->where(function($subQ) use ($term) {
                            $subQ->where('militaires.nom', 'LIKE', "%{$term}%")
                                 ->orWhere('militaires.prenom', 'LIKE', "%{$term}%")
                                 ->orWhere('militaires.matricule', 'LIKE', "%{$term}%");
                        });
                    }
                }
            });
        }

        if ($request->filled('grade')) {
            $query->where('militaires.grade_actuel', $request->grade);
        }

        if ($request->filled('statut')) {
            $query->where('militaires.statut', $request->statut);
        } else {
            $query->where('militaires.statut', 'actif');
        }

        $statsQuery = clone $query;
        $statistiques = [
            'total'     => (clone $statsQuery)->count(),
            'actifs'    => (clone $statsQuery)->where('militaires.statut', 'actif')->count(),
            'retraites' => (clone $statsQuery)->where('militaires.statut', 'retraité')->count(),
            'alertes'   => Alerte::where('est_vue', false)
                            ->whereIn('militaire_id', (clone $statsQuery)->select('militaires.id'))->count(),
        ];

        $query->orderByRaw('COALESCE(grades.ordre, 999) DESC')
              ->orderBy('militaires.nom')
              ->orderBy('militaires.prenom');

        $militaires = $query->select('militaires.*')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($militaire) => [
                'id' => $militaire->id,
                'matricule' => $militaire->matricule,
                'nom' => $militaire->nom,
                'prenom' => $militaire->prenom,
                'grade_actuel' => $militaire->grade_actuel,
                'date_entree_service' => $militaire->date_entree_service?->format('d/m/Y'),
                'date_derniere_promotion' => $militaire->date_derniere_promotion?->format('d/m/Y'),
                'specialite' => $militaire->specialite,
                'position_actuelle' => $militaire->position_actuelle,
                'fonction_passee' => $militaire->fonction_passee,
                'fonction_actuelle' => $militaire->fonction_actuelle,
                'statut' => $militaire->statut,
                'age' => $militaire->age,
                'anciennete' => $militaire->anciennete,
                'anciennete_grade' => $militaire->ancienneteGrade,
                'a_permis_conduire' => $militaire->a_permis_conduire,
                'alertes_count' => $militaire->alertes()->where('est_vue', false)->count(),
                'est_eligible_retraite' => $militaire->estEligibleRetraite(),
                'date_retraite' => $militaire->calculerDateRetraite()?->format('d/m/Y'),
            ]);

        $grades = Grade::orderBy('ordre', 'desc')->get();

        return Inertia::render('militaires/index', [
            'militaires'   => $militaires,
            'statistiques' => $statistiques,
            'filters'      => $request->only(['search', 'grade', 'statut']),
            'grades'       => $grades,
        ]);
    }

    public function create()
    {
        $grades = Grade::orderBy('ordre')->get()->map(fn ($grade) => [
            'id' => $grade->id,
            'nom_grade' => $grade->nom_grade,
            'code_grade' => $grade->code_grade,
            'ordre' => $grade->ordre,
            'type_grade' => $grade->type_grade,
        ]);

        $certificats = Certificat::all()->map(fn ($certificat) => [
            'id' => $certificat->id,
            'nom_certificat' => $certificat->nom_certificat,
            'niveau_certificat' => $certificat->niveau_certificat,
        ]);

        return Inertia::render('militaires/create', [
            'grades' => $grades,
            'certificats' => $certificats
        ]);
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'matricule' => 'required|string|unique:militaires',
        'nom' => 'required|string|max:100',
        'prenom' => 'required|string|max:100',
        'date_naissance' => 'required|date',
        'date_entree_service' => 'required|date|before_or_equal:today',
        'grade_actuel' => 'required|string',
        'date_derniere_promotion' => 'nullable|date|before_or_equal:today',
        'specialite' => 'nullable|string|max:200',
        'position_actuelle' => 'nullable|string|max:255',
        'fonction_passee' => 'nullable|string|max:255',
        'fonction_actuelle' => 'nullable|string|max:255',
        'a_permis_conduire' => 'boolean',
    ]);

    $data = $this->extractData($request);
    $militaire = Militaire::create($data);

    if ($request->has('certificats')) {
        $this->syncCertificats($militaire, $request->certificats);
    }

    $militaire->load('certificats');

    // VÉRIFIER LES ALERTES (promotion, formation, retraite)
    $this->verifierAlertes($militaire);

    // 🔧 CRÉER UNIQUEMENT L'ALERTE CONTRAT (PAS DE CONTRAT)
    $this->verifierAlerteContrat($militaire);

    return redirect()->route('militaires.index')
        ->with('success', 'Militaire ajouté avec succès.');
}

    public function show(Militaire $militaire)
    {
        $militaire->load(['certificats', 'alertes' => function($q) {
            $q->orderBy('created_at', 'desc')->limit(10);
        }]);

        $contratActif = $militaire->contrats()->where('statut', 'actif')->latest('date_debut')->first();

        $alertes = $militaire->alertes->map(fn ($alerte) => [
            'id' => $alerte->id,
            'type_alerte' => $alerte->type_alerte,
            'message' => $alerte->message,
            'date_echeance' => $alerte->date_echeance?->format('d/m/Y'),
            'est_vue' => $alerte->est_vue,
            'created_at' => $alerte->created_at?->format('d/m/Y H:i'),
        ]);

        $certificats = $militaire->certificats->map(fn ($certificat) => [
            'id' => $certificat->id,
            'nom_certificat' => $certificat->nom_certificat,
            'niveau_certificat' => $certificat->niveau_certificat,
            'date_obtention' => $certificat->pivot->date_obtention
                ? Carbon::parse($certificat->pivot->date_obtention)->format('d/m/Y')
                : null,
        ]);

        $dateRetraite = $militaire->calculerDateRetraite();

        $militaireData = [
            'id' => $militaire->id,
            'matricule' => $militaire->matricule,
            'nom' => $militaire->nom,
            'prenom' => $militaire->prenom,
            'date_naissance' => $militaire->date_naissance?->format('d/m/Y'),
            'date_entree_service' => $militaire->date_entree_service?->format('d/m/Y'),
            'date_retraite' => $dateRetraite?->format('d/m/Y'),
            'grade_actuel' => $militaire->grade_actuel,
            'date_derniere_promotion' => $militaire->date_derniere_promotion?->format('d/m/Y'),
            'specialite' => $militaire->specialite,
            'position_actuelle' => $militaire->position_actuelle,
            'fonction_passee' => $militaire->fonction_passee,
            'fonction_actuelle' => $militaire->fonction_actuelle,
            'statut' => $militaire->statut,
            'a_permis_conduire' => $militaire->a_permis_conduire,
            'a_fait_justice' => $militaire->a_fait_justice,
            'a_fait_discipline' => $militaire->a_fait_discipline,
            'age' => $militaire->age,
            'anciennete' => $militaire->anciennete,
            'anciennete_grade' => $militaire->ancienneteGrade,
            'est_eligible_retraite' => $militaire->estEligibleRetraite(),
        ];

        return Inertia::render('militaires/show', [
            'militaire' => $militaireData,
            'certificats' => $certificats,
            'alertes' => $alertes,
            'contratActif' => $contratActif ? [
                'id' => $contratActif->id,
                'date_debut' => $contratActif->date_debut?->format('d/m/Y'),
                'date_fin' => $contratActif->date_fin?->format('d/m/Y'),
                'duree_annees' => $contratActif->duree_annees,
                'statut' => $contratActif->statut,
            ] : null,
        ]);
    }

    public function edit(Militaire $militaire)
    {
        $grades = Grade::orderBy('ordre')->get()->map(fn ($grade) => [
            'id' => $grade->id,
            'nom_grade' => $grade->nom_grade,
            'code_grade' => $grade->code_grade,
            'ordre' => $grade->ordre,
            'type_grade' => $grade->type_grade,
        ]);

        $certificats = Certificat::all()->map(fn ($certificat) => [
            'id' => $certificat->id,
            'nom_certificat' => $certificat->nom_certificat,
            'niveau_certificat' => $certificat->niveau_certificat,
        ]);

        $certificatsDuMilitaire = $militaire->certificats->keyBy('id')->map(fn ($certificat) => [
            'obtenu' => true,
            'date_obtention' => $certificat->pivot->date_obtention
                ? Carbon::parse($certificat->pivot->date_obtention)->format('Y-m-d')
                : null,
        ]);

        return Inertia::render('militaires/edit', [
            'militaire' => [
                'id' => $militaire->id,
                'matricule' => $militaire->matricule,
                'nom' => $militaire->nom,
                'prenom' => $militaire->prenom,
                'date_naissance' => $militaire->date_naissance?->format('Y-m-d'),
                'date_entree_service' => $militaire->date_entree_service?->format('Y-m-d'),
                'grade_actuel' => $militaire->grade_actuel,
                'date_derniere_promotion' => $militaire->date_derniere_promotion?->format('Y-m-d'),
                'specialite' => $militaire->specialite,
                'position_actuelle' => $militaire->position_actuelle,
                'fonction_passee' => $militaire->fonction_passee,
                'fonction_actuelle' => $militaire->fonction_actuelle,
                'statut' => $militaire->statut,
                'a_permis_conduire' => $militaire->a_permis_conduire,
                'a_fait_justice' => $militaire->a_fait_justice,
                'a_fait_discipline' => $militaire->a_fait_discipline,
            ],
            'grades' => $grades,
            'certificats' => $certificats,
            'certificats_du_militaire' => $certificatsDuMilitaire
        ]);
    }

    public function update(Request $request, Militaire $militaire)
    {
        $validated = $request->validate([
            'matricule' => 'required|string|unique:militaires,matricule,' . $militaire->id,
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'required|date',
            'date_entree_service' => 'required|date',
            'grade_actuel' => 'required|string',
            'date_derniere_promotion' => 'nullable|date',
            'specialite' => 'nullable|string|max:200',
            'position_actuelle' => 'nullable|string|max:255',
            'fonction_passee' => 'nullable|string|max:255',
            'fonction_actuelle' => 'nullable|string|max:255',
            'statut' => 'required|in:actif,retraité,déserteur,décédé,démobilisé,formation,stage',
            'a_permis_conduire' => 'boolean',
            'a_fait_justice' => 'boolean',
            'a_fait_discipline' => 'boolean',
        ]);

        $data = $this->extractData($request, $militaire);
        $militaire->update($data);

        if ($request->has('certificats')) {
            $this->syncCertificats($militaire, $request->certificats);
        }

        $militaire->load('certificats');

        // Vérifier les alertes (promotion, formation, retraite, contrat)
        $this->verifierAlertes($militaire);

        return redirect()->route('militaires.show', $militaire)
            ->with('success', 'Militaire mis à jour avec succès.');
    }

    public function destroy(Militaire $militaire)
    {
        $militaire->delete();
        return redirect()->route('militaires.index')
            ->with('success', 'Militaire supprimé avec succès.');
    }

    public function importForm()
    {
        return Inertia::render('militaires/import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'fichier' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new MilitairesImport();
            Excel::import($import, $request->file('fichier'));

            Artisan::call('alertes:check');
            $output = Artisan::output();
            Log::info('Résultat de alertes:check', ['output' => $output]);

            $message = "Importation réussie. {$import->getImportedCount()} ligne(s) importée(s).";
            if (method_exists($import, 'getSkippedCount') && $import->getSkippedCount() > 0) {
                $message .= " {$import->getSkippedCount()} ligne(s) ignorée(s).";
            }

            return redirect()->route('militaires.index')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('militaires.import')->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function exportTemplate()
    {
        return Excel::download(new MilitairesExportTemplate(), 'modele_militaires.xlsx');
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $grade = $request->input('grade');
        $statut = $request->input('statut');

        \Log::info('📊 Export reçu - paramètres bruts :', $request->all());

        $search = ($search && $search !== 'null') ? $search : null;
        $grade = ($grade && $grade !== 'null') ? $grade : null;
        $statut = ($statut && $statut !== 'null') ? $statut : null;

        \Log::info('🎯 Paramètres après nettoyage :', [
            'search' => $search,
            'grade' => $grade,
            'statut' => $statut
        ]);

        if ($grade) {
            $count = Militaire::where('grade_actuel', $grade)->count();
            \Log::info('📊 Nombre de militaires avec grade "' . $grade . '" : ' . $count);
        }

        return Excel::download(
            new MilitairesExport($search, $grade, $statut),
            'militaires_' . date('Y-m-d') . '.xlsx'
        );
    }

    // =========================================================
    // MÉTHODES PRIVÉES
    // =========================================================

    private function extractData(Request $request, ?Militaire $militaire = null): array
    {
        $data = $request->only([
            'matricule', 'nom', 'prenom', 'date_naissance', 'date_entree_service',
            'grade_actuel', 'date_derniere_promotion', 'specialite', 'statut',
            'position_actuelle', 'fonction_passee', 'fonction_actuelle'
        ]);

        $booleanFields = [
            'a_fait_cat1', 'a_fait_cat2', 'a_fait_cia', 'a_fait_ba1', 'a_fait_ba2',
            'a_fait_bmp1', 'a_fait_bmp2', 'a_fait_bs', 'a_fait_ct2',
            'a_fait_apli', 'a_fait_cfcu', 'a_fait_cem',
            'a_fait_certificat_etat_major', 'a_fait_ecole_guerre',
            'a_permis_conduire', 'a_fait_justice', 'a_fait_discipline'
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = $request->boolean($field);
        }

        $dateFields = [
            'date_obtention_cat1', 'date_obtention_cat2', 'date_obtention_cia',
            'date_obtention_ba1', 'date_obtention_ba2', 'date_obtention_bmp1',
            'date_obtention_bmp2', 'date_obtention_bs', 'date_obtention_ct2',
            'date_obtention_apli', 'date_obtention_cfcu',
            'date_obtention_cem', 'date_obtention_certificat_etat_major',
            'date_obtention_ecole_guerre'
        ];

        foreach ($dateFields as $field) {
            $data[$field] = $request->input($field);
        }

        foreach ($booleanFields as $boolField) {
            if (!$data[$boolField]) {
                $dateField = 'date_obtention_' . substr($boolField, 8);
                if (isset($data[$dateField])) {
                    $data[$dateField] = null;
                }
            }
        }

        foreach ($dateFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function syncCertificats(Militaire $militaire, array $certificatsData)
    {
        $certificats = [];
        foreach ($certificatsData as $certificatId => $data) {
            if (isset($data['obtenu']) && $data['obtenu']) {
                $certificats[$certificatId] = [
                    'date_obtention' => $data['date_obtention'] ?? null,
                ];
            }
        }
        $militaire->certificats()->sync($certificats);
    }

    /**
     * Vérifier toutes les alertes pour un militaire
     * Promotion, Formation, Retraite, Contrat
     */
    public function verifierAlertes(Militaire $militaire)
    {
        $this->verifierPromotions($militaire);
        $this->verifierFormations($militaire);
        $this->verifierRetraite($militaire);
        $this->verifierAlerteContrat($militaire);
    }

    /**
 * Vérifier et créer l'alerte contrat pour un militaire
 * 🔧 UNIQUEMENT L'ALERTE, PAS LE CONTRAT
 */
/**
 * Vérifier et créer l'alerte contrat pour un militaire
 * 🔧 UNIQUEMENT L'ALERTE, PAS LE CONTRAT
 */
private function verifierAlerteContrat(Militaire $militaire): void
{
    // 1. Vérifier si le militaire est actif
    if ($militaire->statut !== 'actif') {
        return;
    }

    // 2. Grades éligibles (sous-officiers jusqu'à 1ère classe)
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

    // 3. Vérifier si le grade est éligible
    if (!in_array($militaire->grade_actuel, $gradesEligibles)) {
        return;
    }

    // 4. Vérifier si le militaire a une date d'entrée en service
    if (!$militaire->date_entree_service) {
        return;
    }

    // 🔧 5. CORRECTION : Calculer les années de service (inverser l'ordre)
    $serviceYears = floor($militaire->date_entree_service->diffInYears(now()));

    // 6. Si moins de 5 ans, pas d'alerte
    if ($serviceYears < 5) {
        // Supprimer l'alerte si elle existe (au cas où)
        Alerte::where('militaire_id', $militaire->id)
            ->where('type_alerte', 'contrat')
            ->delete();
        return;
    }

    // 7. Supprimer l'ancienne alerte contrat
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'contrat')
        ->delete();

    // 8. Créer l'alerte contrat
    $message = "Renouvellement de contrat requis pour {$militaire->prenom} {$militaire->nom} ({$serviceYears} ans de service) - Grade: {$militaire->grade_actuel}";

    Alerte::create([
        'militaire_id' => $militaire->id,
        'type_alerte' => 'contrat',
        'message' => $message,
        'date_echeance' => now()->addMonths(6),
        'est_vue' => false,
    ]);
}

    /**
     * Créer une alerte contrat
     */
    private function creerAlerteContrat(Militaire $militaire, int $serviceYears): void
    {
        $message = "Renouvellement de contrat requis pour {$militaire->prenom} {$militaire->nom} ({$serviceYears} ans de service) - Grade: {$militaire->grade_actuel}";

        $this->creerAlerte($militaire, 'contrat', $message, now()->addMonths(6));
    }

    private function getDateProposition()
    {
        $annee = Carbon::now()->year;
        return Carbon::create($annee, 12, 31, 23, 59, 59);
    }

    private function getDateAnciennetePromotion(Militaire $militaire, string $gradeCible)
    {
        $gradeActuel = $militaire->grade_actuel;
        $dateDernierePromotion = $militaire->date_derniere_promotion;
        $dateEntreeService = $militaire->date_entree_service;

        $regles = [
            'Caporal'          => ['grade' => 'Soldat 1',         'annees' => 5, 'base' => 'entree'],
            'Sergent'          => ['grade' => 'Caporal',          'annees' => 3, 'base' => 'promotion'],
            'Sergent-Chef'     => ['grade' => 'Sergent',          'annees' => 2, 'base' => 'promotion'],
            'Adjudant'         => ['grade' => 'Sergent-Chef',     'annees' => 3, 'base' => 'promotion'],
            'Adjudant-Chef'    => ['grade' => 'Adjudant',         'annees' => 2, 'base' => 'promotion'],
            'Lieutenant'       => ['grade' => 'Sous-lieutenant',  'annees' => 2, 'base' => 'promotion'],
            'Capitaine'        => ['grade' => 'Lieutenant',       'annees' => 3, 'base' => 'promotion'],
            'Commandant'       => ['grade' => 'Capitaine',        'annees' => 3, 'base' => 'promotion'],
            'Lieutenant-colonel' => ['grade' => 'Commandant',    'annees' => 3, 'base' => 'promotion'],
            'Colonel'          => ['grade' => 'Lieutenant-colonel','annees' => 3, 'base' => 'promotion'],
            'Colonel-Major'    => ['grade' => 'Colonel',          'annees' => 6, 'base' => 'promotion'],
        ];

        foreach ($regles as $cible => $regle) {
            if ($cible === $gradeCible && $regle['grade'] === $gradeActuel) {
                if ($regle['base'] === 'entree' && $dateEntreeService) {
                    return Carbon::parse($dateEntreeService)->addYears($regle['annees']);
                } elseif ($regle['base'] === 'promotion' && $dateDernierePromotion) {
                    return Carbon::parse($dateDernierePromotion)->addYears($regle['annees']);
                }
            }
        }

        return null;
    }

    private function verifierPromotions(Militaire $militaire): void
    {
        if ($militaire->statut !== 'actif') return;

        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        $dateProposition = $this->getDateProposition();

        if ($grade == 'Soldat 1' && in_array('CAT1', $certificatsObtenus) && $conditionsBase && $militaire->anciennete >= 5) {
            $dateAnciennete = Carbon::parse($militaire->date_entree_service)->addYears(5);
            $this->creerAlertePromotion($militaire, 'Caporal', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus) && $conditionsBase) {
            $certifCAT1 = $militaire->certificats->where('niveau_certificat', 'CAT1')->first();
            $dateAnciennete = null;
            if ($certifCAT1 && $certifCAT1->pivot->date_obtention) {
                $dateAnciennete = Carbon::parse($certifCAT1->pivot->date_obtention)->addYears(3);
            }
            $this->creerAlertePromotion($militaire, 'Sergent', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47 && $ancienneteGrade >= 3 && $conditionsBase) {
            $dateAnciennete = $militaire->date_derniere_promotion
                ? Carbon::parse($militaire->date_derniere_promotion)->addYears(3)
                : null;
            $this->creerAlertePromotion($militaire, 'Caporal-chef', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Sergent' && $conditionsBase && $ancienneteGrade >= 2 && $militaire->anciennete >= 5) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(2);
            $this->creerAlertePromotion($militaire, 'Sergent-Chef', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Sergent-Chef' && $conditionsBase && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $this->creerAlertePromotion($militaire, 'Adjudant', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Adjudant' && $conditionsBase && $ancienneteGrade >= 2) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(2);
            $this->creerAlertePromotion($militaire, 'Adjudant-Chef', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Sous-lieutenant' && $ancienneteGrade >= 2) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(2);
            $this->creerAlertePromotion($militaire, 'Lieutenant', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Lieutenant' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $this->creerAlertePromotion($militaire, 'Capitaine', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Capitaine' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $this->creerAlertePromotion($militaire, 'Commandant', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Commandant' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $this->creerAlertePromotion($militaire, 'Lieutenant-colonel', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Lieutenant-colonel' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $this->creerAlertePromotion($militaire, 'Colonel', $dateProposition, $dateAnciennete);
        }

        if ($grade == 'Colonel' && $ancienneteGrade >= 6) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(6);
            $this->creerAlertePromotion($militaire, 'Colonel-Major', $dateProposition, $dateAnciennete);
        }
    }

    private function verifierFormations(Militaire $militaire): void
    {
        if ($militaire->statut !== 'actif') return;

        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $anciennete = $militaire->anciennete;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;
        $dateProposition = $this->getDateProposition();

        if (in_array($grade, ['Soldat 2', 'Soldat 1']) && !in_array('CAT1', $certificatsObtenus) && $ancienneteGrade >= 5 && $conditionsBase) {
            $dateConditions = Carbon::parse($militaire->date_entree_service)->addYears(5);
            $this->creerAlerteFormation($militaire, 'CAT1', "Certificat d'Aptitude Technique Niveau 1", $dateProposition, $dateConditions);
        }

        if ($grade == 'Caporal' && $age < 47 && !in_array('CAT2', $certificatsObtenus) && $ancienneteGrade >= 3 && $conditionsBase && in_array('CAT1', $certificatsObtenus)) {
            $certifCAT1 = $militaire->certificats->where('niveau_certificat', 'CAT1')->first();
            $dateConditions = null;
            if ($certifCAT1 && $certifCAT1->pivot->date_obtention) {
                $dateConditions = Carbon::parse($certifCAT1->pivot->date_obtention)->addYears(3);
            }
            $this->creerAlerteFormation($militaire, 'CAT2', "Certificat d'Aptitude Technique Niveau 2", $dateProposition, $dateConditions);
        }

        if (in_array($grade, ['Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef']) && !in_array('CIA', $certificatsObtenus) && $conditionsBase && $militaire->a_permis_conduire) {
            $this->creerAlerteFormation($militaire, 'CIA', "Certificat d'Instruction d'Armes", $dateProposition, null);
        }

        if (in_array($grade, ['Sergent-Chef', 'Adjudant', 'Adjudant-Chef']) && !in_array('BA1', $certificatsObtenus) && in_array('CIA', $certificatsObtenus) && $conditionsBase && $anciennete >= 8) {
            $certifCIA = $militaire->certificats->where('niveau_certificat', 'CIA')->first();
            $dateConditions = null;
            if ($certifCIA && $certifCIA->pivot->date_obtention) {
                $dateConditions = Carbon::parse($certifCIA->pivot->date_obtention)->addYears(3);
            }
            $this->creerAlerteFormation($militaire, 'BA1', "Brevet d'Aptitude Niveau 1", $dateProposition, $dateConditions);
        }

        if (in_array($grade, ['Adjudant', 'Adjudant-Chef']) && !in_array('BA2', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && $conditionsBase) {
            $certifBA1 = $militaire->certificats->where('niveau_certificat', 'BA1')->first();
            $dateConditions = null;
            if ($certifBA1 && $certifBA1->pivot->date_obtention) {
                $dateConditions = Carbon::parse($certifBA1->pivot->date_obtention)->addYears(3);
            }
            $this->creerAlerteFormation($militaire, 'BA2', "Brevet d'Aptitude Niveau 2", $dateProposition, $dateConditions);
        }

        if (in_array($grade, ['Sous-lieutenant', 'Lieutenant', 'Capitaine']) && !in_array('APLI', $certificatsObtenus) && !in_array('CFCU', $certificatsObtenus) && $age <= 50) {
            $this->creerAlerteFormation($militaire, 'APLI', "Cour d'Application", $dateProposition, null);
        }

        if (in_array($grade, ['Lieutenant', 'Capitaine']) && !in_array('CFCU', $certificatsObtenus)) {
            if ($grade == 'Capitaine' || in_array('APLI', $certificatsObtenus)) {
                $this->creerAlerteFormation($militaire, 'CFCU', "Cour des Futurs Commandants d'Unité", $dateProposition, null);
            }
        }

        if (in_array($grade, ['Capitaine', 'Commandant']) && !in_array('CEM', $certificatsObtenus)) {
            if (($grade == 'Capitaine' && $ancienneteGrade >= 3) || $grade == 'Commandant') {
                if ($age <= 45) {
                    $this->creerAlerteFormation($militaire, 'CEM', "Cour d'État-Major", $dateProposition, null);
                }
            }
        }

        if ($grade == 'Commandant' && $age > 45 && !in_array('CERT_EM', $certificatsObtenus)) {
            $this->creerAlerteFormation($militaire, 'CERT_EM', "Certificat d'État-Major", $dateProposition, null);
        }

        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major']) && !in_array('ECOLE_GUERRE', $certificatsObtenus) && $ancienneteGrade >= 2 && $age <= 53) {
            $this->creerAlerteFormation($militaire, 'ECOLE_GUERRE', "École de Guerre", $dateProposition, null);
        }
    }

    private function creerAlertePromotion(Militaire $militaire, string $gradeCible, $dateProposition, $dateAnciennete): void
    {
        $anneeProposition = $dateProposition->format('Y');
        $dateAncienneteTexte = $dateAnciennete ? $dateAnciennete->format('d/m/Y') : 'conditions remplies';
        $message = "Proposable pour {$anneeProposition} (ancienneté atteinte le {$dateAncienneteTexte}) - Promotion à {$gradeCible}";
        $this->creerAlerte($militaire, 'promotion', $message, $dateProposition);
    }

    private function creerAlerteFormation(Militaire $militaire, string $formation, string $nomFormation, $dateProposition, $dateConditions): void
    {
        $anneeProposition = $dateProposition->format('Y');
        $dateConditionsTexte = $dateConditions ? $dateConditions->format('d/m/Y') : 'conditions remplies';
        $message = "Proposable pour {$anneeProposition} (conditions remplies le {$dateConditionsTexte}) - Formation {$nomFormation}";
        $this->creerAlerte($militaire, 'formation', $message, $dateProposition);
    }

    private function verifierRetraite(Militaire $militaire): void
    {
        $dateRetraite = $militaire->calculerDateRetraite();

        if ($dateRetraite) {
            $diffJours = Carbon::now()->startOfDay()->diffInDays($dateRetraite);
            $moisRestants = floor($diffJours / 30);

            if ($moisRestants <= 12 && $diffJours >= 0) {
                $message = "Retraite dans {$moisRestants} mois (le " . $dateRetraite->format('d/m/Y') . ")";
                $this->creerAlerte($militaire, 'retraite', $message, $dateRetraite);
            }
        }
    }

    private function creerAlerte(Militaire $militaire, string $type, string $message, $dateEcheance = null): void
    {
        $existe = Alerte::where('militaire_id', $militaire->id)
            ->where('type_alerte', $type)
            ->where('est_vue', false)
            ->where('message', $message)
            ->exists();

        if (!$existe) {
            $echeance = $dateEcheance ?? Carbon::now()->addDays(2);
            Alerte::create([
                'militaire_id' => $militaire->id,
                'type_alerte'  => $type,
                'message'      => $message,
                'date_echeance' => $echeance,
            ]);
        }
    }
}

// =========================================================
// Classe template d'export (modèle pour import)
// =========================================================
class MilitairesExportTemplate implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                '12345', 'DIALLO', 'Baba', '1984-07-19', '2015-06-01',
                'Soldat', '', 'Infanterie', 'actif', 1,
                0, '', 0, '', 0, '', 0, '', 0, '', 0, '',
                0, '', 0, '', 0, '', 0, '', 0, '',
                0, '', 0, '', 0, '', 0, '', 0, '',
                'Position actuelle', 'Fonction passée', 'Fonction actuelle'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'matricule', 'nom', 'prenom', 'date_naissance', 'date_entree_service',
            'grade_actuel', 'date_derniere_promotion', 'specialite', 'statut', 'a_permis_conduire',
            'a_fait_cat1', 'date_obtention_cat1', 'a_fait_cat2', 'date_obtention_cat2',
            'a_fait_cia', 'date_obtention_cia', 'a_fait_ba1', 'date_obtention_ba1',
            'a_fait_ba2', 'date_obtention_ba2', 'a_fait_bmp1', 'date_obtention_bmp1',
            'a_fait_bmp2', 'date_obtention_bmp2', 'a_fait_bs', 'date_obtention_bs',
            'a_fait_ct2', 'date_obtention_ct2', 'a_fait_apli', 'date_obtention_apli',
            'a_fait_cfcu', 'date_obtention_cfcu', 'a_fait_cem', 'date_obtention_cem',
            'a_fait_certificat_etat_major', 'date_obtention_certificat_etat_major',
            'a_fait_ecole_guerre', 'date_obtention_ecole_guerre',
            'position_actuelle', 'fonction_passee', 'fonction_actuelle'
        ];
    }
}
