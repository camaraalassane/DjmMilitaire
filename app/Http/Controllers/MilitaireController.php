<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Militaire;
use App\Models\Grade;
use App\Models\Alerte;
use App\Models\Certificat;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MilitairesImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Artisan;

class MilitaireController extends Controller
{
    /**
     * Affiche la liste des militaires actifs.
     */
    public function index(Request $request)
    {
        $query = Militaire::query();

        // Filtre par recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%");
            });
        }

        // Filtre par grade
        if ($request->filled('grade')) {
            $query->where('grade_actuel', $request->grade);
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            $query->where('statut', 'actif');
        }

        $militaires = $query->orderBy('nom')
            ->orderBy('prenom')
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
                'statut' => $militaire->statut,
                'age' => $militaire->age,
                'anciennete' => $militaire->anciennete,
                'anciennete_grade' => $militaire->ancienneteGrade,
                'a_permis_conduire' => $militaire->a_permis_conduire,
                'alertes_count' => $militaire->alertes()->where('est_vue', false)->count(),
                'est_eligible_retraite' => $militaire->estEligibleRetraite(),
                'date_retraite' => $militaire->calculerDateRetraite()?->format('d/m/Y'),
            ]);
        
        $statistiques = [
            'total' => Militaire::count(),
            'actifs' => Militaire::where('statut', 'actif')->count(),
            'retraites' => Militaire::where('statut', 'retraité')->count(),
            'alertes' => Alerte::where('est_vue', false)->count(),
        ];

        $grades = Grade::orderBy('ordre')->get();

        return Inertia::render('militaires/index', [
            'militaires' => $militaires,
            'statistiques' => $statistiques,
            'filters' => $request->only(['search', 'grade', 'statut']),
            'grades' => $grades
        ]);
    }

    /**
     * Affiche le formulaire de création d'un militaire.
     */
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

    /**
     * Enregistre un nouveau militaire.
     */
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
            'a_permis_conduire' => 'boolean',
        ]);

        $data = $this->extractData($request);
        $militaire = Militaire::create($data);
        
        // Synchroniser les certificats
        if ($request->has('certificats')) {
            $this->syncCertificats($militaire, $request->certificats);
        }

        $militaire->load('certificats');
        
        // Vérifier et créer les alertes
        $this->verifierAlertes($militaire);

        return redirect()->route('militaires.index')
            ->with('success', 'Militaire ajouté avec succès.');
    }

    /**
     * Affiche le détail d'un militaire.
     */
    public function show(Militaire $militaire)
    {
        $militaire->load(['certificats', 'alertes' => function($q) {
            $q->orderBy('created_at', 'desc')->limit(10);
        }]);

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
            'alertes' => $alertes
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un militaire.
     */
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
            'date_obtention' => $certificat->pivot->date_obtention ? Carbon::parse($certificat->pivot->date_obtention)->format('Y-m-d') : null,
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

    /**
     * Met à jour un militaire existant.
     */
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
            'statut' => 'required|in:actif,retraité,déserteur,décédé,démobilisé,formation,stage',
            'a_permis_conduire' => 'boolean',
            'a_fait_justice' => 'boolean',
            'a_fait_discipline' => 'boolean',
        ]);

        $data = $this->extractData($request, $militaire);
        $militaire->update($data);
        
        // Synchroniser les certificats
        if ($request->has('certificats')) {
            $this->syncCertificats($militaire, $request->certificats);
        }

        $militaire->load('certificats');

        $this->verifierAlertes($militaire);

        return redirect()->route('militaires.show', $militaire)
            ->with('success', 'Militaire mis à jour avec succès.');
    }

    /**
     * Supprime un militaire.
     */
    public function destroy(Militaire $militaire)
    {
        $militaire->delete();
        return redirect()->route('militaires.index')
            ->with('success', 'Militaire supprimé avec succès.');
    }

    /**
     * Affiche le formulaire d'import Excel.
     */
    public function importForm()
    {
        return Inertia::render('militaires/import');
    }

    /**
     * Traite l'import Excel.
     */
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

    /**
     * Télécharge un modèle Excel pour l'import.
     */
    public function exportTemplate()
    {
        return Excel::download(new MilitairesExportTemplate, 'modele_militaires.xlsx');
    }

    /**
     * Extrait et prépare les données du formulaire (création ou mise à jour).
     */
    private function extractData(Request $request, ?Militaire $militaire = null): array
    {
        $data = $request->only([
            'matricule', 'nom', 'prenom', 'date_naissance', 'date_entree_service',
            'grade_actuel', 'date_derniere_promotion', 'specialite', 'statut'
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

    /**
     * Synchronise les certificats associés au militaire (table pivot).
     */
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
     * Vérifie et crée les alertes pour un militaire.
     */
    public function verifierAlertes(Militaire $militaire)
    {
        // Alertes de promotion (changement de grade)
        $this->verifierPromotions($militaire);
        
        // Alertes de formation (cours, stages, écoles)
        $this->verifierFormations($militaire);
        
        // Alertes de retraite
        $this->verifierRetraite($militaire);
    }

   /**
 * Vérifie les éligibilités aux promotions (changements de grade)
 * Type d'alerte: 'promotion'
 * 
 * Les propositions se font 3 fois par an :
 * - 1er janvier : pour les dossiers préparés en octobre/novembre/décembre
 * - 1er octobre : pour les dossiers préparés en mai/juin/juillet/août/septembre/avril
 * - 1er avril : pour les dossiers préparés en janvier/février/mars
 */
private function verifierPromotions(Militaire $militaire)
{
    if ($militaire->statut !== 'actif') return;

    $grade = $militaire->grade_actuel;
    $anciennete = $militaire->anciennete;
    $age = $militaire->age;
    $ancienneteGrade = $militaire->ancienneteGrade;
    $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;

    $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
    
    $aujourdhui = Carbon::now();
    $moisActuel = (int)$aujourdhui->format('n');

    // === PROMOTIONS SOUS-OFFICIERS ET MILITAIRES DU RANG ===
    
    // Soldat 1 → Caporal (après CAT1)
    if ($grade == 'Soldat 1' && in_array('CAT1', $certificatsObtenus) && $conditionsBase) {
        $dateProposition = $this->calculerDateProposition($anciennete, 5, $moisActuel);
        if ($dateProposition && $anciennete >= 5) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Caporal au {$dateProposition->format('d/m/Y')} (5 ans d'ancienneté requis)",
                Carbon::now()->addDays(2));
        }
    }

    // Caporal → Sergent (après CAT2)
    if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus) && $conditionsBase) {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 0, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Sergent au {$dateProposition->format('d/m/Y')} (avoir CAT2 et être Caporal)",
                Carbon::now()->addDays(2));
        }
    }

    // Caporal → Caporal-chef (âge ≥ 47 ans, 3 ans comme Caporal, CAT1, sans CAT2)
    if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47 && $conditionsBase) {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Caporal-chef au {$dateProposition->format('d/m/Y')} (âge ≥ 47 ans, 3 ans comme Caporal, avoir CAT1)",
                Carbon::now()->addDays(2));
        }
    }

    // Sergent → Sergent-Chef (2 ans de grade et 5 ans de service)
    if ($grade == 'Sergent' && $conditionsBase && $anciennete >= 5) {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Sergent-Chef au {$dateProposition->format('d/m/Y')} (2 ans comme Sergent, 5 ans de service)",
                Carbon::now()->addDays(2));
        }
    }

    // Sergent-Chef → Adjudant (3 ans de grade)
    if ($grade == 'Sergent-Chef' && $conditionsBase) {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Adjudant au {$dateProposition->format('d/m/Y')} (3 ans d'ancienneté étant Sergent-Chef)",
                Carbon::now()->addDays(2));
        }
    }

    // Adjudant → Adjudant-Chef (2 ans de grade)
    if ($grade == 'Adjudant' && $conditionsBase) {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Adjudant-Chef au {$dateProposition->format('d/m/Y')} (2 ans d'ancienneté étant Adjudant)",
                Carbon::now()->addDays(2));
        }
    }

    // Adjudant-Chef → Adjudant-Chef Major (CIA, BA1, BA2, âge ≥ 45)
    if ($grade == 'Adjudant-Chef' && in_array('CIA', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && in_array('BA2', $certificatsObtenus) && $age >= 45 && $conditionsBase) {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Adjudant-Chef Major au {$dateProposition->format('d/m/Y')} (CIA, BA1, BA2 et âge ≥ 45 ans)",
                Carbon::now()->addDays(2));
        }
    }

    // === PROMOTIONS OFFICIERS ===
    
    // Sous-lieutenant → Lieutenant (2 ans)
    if ($grade == 'Sous-lieutenant') {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Lieutenant au {$dateProposition->format('d/m/Y')} (2 ans au grade de Sous-lieutenant)",
                Carbon::now()->addDays(2));
        }
    }

    // Lieutenant → Capitaine (3 ans)
    if ($grade == 'Lieutenant') {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Capitaine au {$dateProposition->format('d/m/Y')} (3 ans au grade de Lieutenant)",
                Carbon::now()->addDays(2));
        }
    }

    // Capitaine → Commandant (3 ans d'ancienneté)
    if ($grade == 'Capitaine') {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Commandant au {$dateProposition->format('d/m/Y')} (3 ans d'ancienneté au grade de Capitaine)",
                Carbon::now()->addDays(2));
        }
    }

    // Commandant → Lieutenant-colonel (3 ans)
    if ($grade == 'Commandant') {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Lieutenant-colonel au {$dateProposition->format('d/m/Y')} (3 ans au grade de Commandant)",
                Carbon::now()->addDays(2));
        }
    }

    // Lieutenant-colonel → Colonel (3 ans)
    if ($grade == 'Lieutenant-colonel') {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Colonel au {$dateProposition->format('d/m/Y')} (3 ans au grade de Lieutenant-colonel)",
                Carbon::now()->addDays(2));
        }
    }

    // Colonel → Colonel-Major (6 ans)
    if ($grade == 'Colonel') {
        $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 6, $moisActuel);
        if ($dateProposition) {
            $this->creerAlerte($militaire, 'promotion',
                "Proposable pour Colonel-Major au {$dateProposition->format('d/m/Y')} (6 ans d'ancienneté au grade de Colonel)",
                Carbon::now()->addDays(2));
        }
    }

    // Adjudant-Chef vers Sous-lieutenant (passage sous-officier → officier)
    if (in_array('BA2', $certificatsObtenus)) {
        $estEligible = false;
        $anneesRequis = 0;
        
        if ($grade == 'Adjudant-Chef' && $age <= 45 && $anciennete >= 15) {
            $estEligible = true;
            $anneesRequis = 2;
        } elseif ($grade == 'Adjudant-Chef major') {
            $estEligible = true;
            $anneesRequis = 2;
        }
        
        if ($estEligible) {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, $anneesRequis, $moisActuel);
            if ($dateProposition) {
                $this->creerAlerte($militaire, 'promotion',
                    "Proposable pour Sous-lieutenant au {$dateProposition->format('d/m/Y')} (BA2, âge ≤ 45 ans, 15 ans de service)",
                    Carbon::now()->addDays(2));
            }
        }
    }
}

/**
 * Calcule la date de proposition pour une promotion basée sur l'ancienneté totale (années de service)
 * 
 * @param int $anciennete Années de service du militaire
 * @param int $anneesRequis Années d'ancienneté requises
 * @param int $moisActuel Mois actuel (1-12)
 * @return Carbon|null Date de proposition ou null si non éligible
 */
private function calculerDateProposition($anciennete, $anneesRequis, $moisActuel)
{
    if ($anciennete < $anneesRequis) {
        return null;
    }
    
    return $this->getProchaineDateProposition($moisActuel);
}

/**
 * Calcule la date de proposition basée sur la date de dernière promotion
 * 
 * @param string|null $dateDernierePromotion Date de la dernière promotion
 * @param int $anneesRequis Années requises dans le grade actuel
 * @param int $moisActuel Mois actuel (1-12)
 * @return Carbon|null Date de proposition ou null si non éligible
 */
private function calculerDatePropositionParAncienneteGrade($dateDernierePromotion, $anneesRequis, $moisActuel)
{
    if (!$dateDernierePromotion) {
        return null;
    }
    
    $datePromotion = Carbon::parse($dateDernierePromotion);
    $aujourdhui = Carbon::now();
    
    // Calcule la date à laquelle les années requises seront atteintes
    $dateAncienneteAtteinte = $datePromotion->copy()->addYears($anneesRequis);
    
    // Si la date d'ancienneté n'est pas encore atteinte, pas de proposition
    if ($dateAncienneteAtteinte->gt($aujourdhui)) {
        return null;
    }
    
    return $this->getProchaineDateProposition($moisActuel);
}

/**
 * Détermine la prochaine date de proposition selon le mois actuel
 * 
 * Périodes de proposition :
 * - 1er janvier : pour les dossiers préparés en octobre, novembre, décembre
 * - 1er octobre : pour les dossiers préparés en mai, juin, juillet, août, septembre, avril
 * - 1er avril : pour les dossiers préparés en janvier, février, mars
 * 
 * @param int $moisActuel Mois actuel (1-12)
 * @return Carbon Date de la prochaine proposition
 */
private function getProchaineDateProposition($moisActuel)
{
    $annee = Carbon::now()->year;
    
    // Période octobre-novembre-décembre -> proposition au 1er janvier de l'année suivante
    if ($moisActuel >= 10) {
        return Carbon::create($annee + 1, 1, 1);
    }
    
    // Période mai-juin-juillet-août-septembre-avril -> proposition au 1er octobre de l'année en cours
    // Note: avril (4) est inclus dans cette période
    if (($moisActuel >= 5 && $moisActuel <= 9) || $moisActuel == 4) {
        return Carbon::create($annee, 10, 1);
    }
    
    // Période janvier-février-mars -> proposition au 1er avril de l'année en cours
    return Carbon::create($annee, 4, 1);
}
    /**
     * Vérifie les éligibilités aux formations (cours, stages, écoles)
     * Type d'alerte: 'formation'
     */
    private function verifierFormations(Militaire $militaire)
    {
        if ($militaire->statut !== 'actif') return;

        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $anciennete = $militaire->anciennete;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;

        // === FORMATIONS SOUS-OFFICIERS ET MILITAIRES DU RANG ===
        
        // CAT1 : Formation pour devenir Caporal
        if ($grade == 'Soldat 1' && !in_array('CAT1', $certificatsObtenus) && $ancienneteGrade >= 5 && $conditionsBase) {
            $this->creerAlerte($militaire, 'formation',
                "Proposable pour CAT1 (5 ans d'ancienneté au grade de Soldat 1)",
                Carbon::now()->addDays(2));
        }

        // CAT2 : Formation pour devenir Sergent (âge < 47 ans)
        if ($grade == 'Caporal' && $age < 47 && !in_array('CAT2', $certificatsObtenus) && $ancienneteGrade >= 3 && $conditionsBase && in_array('CAT1', $certificatsObtenus)) {
            $this->creerAlerte($militaire, 'formation',
                "Proposable pour CAT2 (3 ans d'ancienneté au grade de Caporal avec CAT1)",
                Carbon::now()->addDays(2));
        }

        // CIA : Certificat d'Instruction d'Armes
        $gradesCIA = ['Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef', 'Adjudant-Chef major'];
        if (in_array($grade, $gradesCIA) && !in_array('CIA', $certificatsObtenus) && $conditionsBase && $militaire->a_permis_conduire) {
            $ancienneteOk = match($grade) {
                'Sergent' => $ancienneteGrade >= 3,
                'Sergent-Chef' => $ancienneteGrade >= 1,
                'Adjudant', 'Adjudant-Chef', 'Adjudant-Chef major' => true,
                default => false
            };
            
            if ($ancienneteOk) {
                $this->creerAlerte($militaire, 'formation',
                    "Proposable pour CIA (permis de conduire requis)" . 
                    ($grade == 'Sergent' ? " - 3 ans de grade sous-officier" : ($grade == 'Sergent-Chef' ? " - 1 an de grade sous-officier" : "")),
                    Carbon::now()->addDays(2));
            }
        }

        // BA1 : Brevet d'Aptitude Niveau 1
        $gradesBA1 = ['Sergent-Chef', 'Adjudant', 'Adjudant-Chef'];
        if (in_array($grade, $gradesBA1) && !in_array('BA1', $certificatsObtenus) && in_array('CIA', $certificatsObtenus) && $conditionsBase) {
            $certifCIA = $militaire->certificats->where('niveau_certificat', 'CIA')->first();
            $anneesDepuisCIA = $certifCIA && $certifCIA->pivot->date_obtention
                ? Carbon::parse($certifCIA->pivot->date_obtention)->diffInYears(Carbon::now())
                : 0;
            if ($anneesDepuisCIA >= 3 && $anciennete >= 8) {
                $this->creerAlerte($militaire, 'formation',
                    "Proposable pour BA1 (CIA depuis 3 ans et 8 ans de service)",
                    Carbon::now()->addDays(2));
            }
        }

        // BA2 : Brevet d'Aptitude Niveau 2
        $gradesBA2 = ['Adjudant', 'Adjudant-Chef', 'Adjudant-Chef major'];
        if (in_array($grade, $gradesBA2) && !in_array('BA2', $certificatsObtenus) && $conditionsBase) {
            $aBA1 = in_array('BA1', $certificatsObtenus);
            $aCT2 = in_array('CT2', $certificatsObtenus);
            $aBMP1 = in_array('BMP1', $certificatsObtenus);
            
            $dateObtention = null;
            if ($aBA1) {
                $certifBA1 = $militaire->certificats->where('niveau_certificat', 'BA1')->first();
                $dateObtention = $certifBA1 && $certifBA1->pivot->date_obtention 
                    ? Carbon::parse($certifBA1->pivot->date_obtention) 
                    : null;
            } elseif ($aCT2) {
                $certifCT2 = $militaire->certificats->where('niveau_certificat', 'CT2')->first();
                $dateObtention = $certifCT2 && $certifCT2->pivot->date_obtention 
                    ? Carbon::parse($certifCT2->pivot->date_obtention) 
                    : null;
            } elseif ($aBMP1) {
                $certifBMP1 = $militaire->certificats->where('niveau_certificat', 'BMP1')->first();
                $dateObtention = $certifBMP1 && $certifBMP1->pivot->date_obtention 
                    ? Carbon::parse($certifBMP1->pivot->date_obtention) 
                    : null;
            }
            
            $anneesDepuis = $dateObtention ? $dateObtention->diffInYears(Carbon::now()) : 0;
            
            if ($anneesDepuis >= 3 && ($aBA1 || $aCT2 || $aBMP1)) {
                $this->creerAlerte($militaire, 'formation',
                    "Proposable pour BA2 (BA1, CT2 ou BMP1 depuis 3 ans)",
                    Carbon::now()->addDays(2));
            }
        }

        // === FORMATIONS OFFICIERS ===
        
        // 1. APLI (Cour d'Application)
        if (in_array($grade, ['Sous-lieutenant', 'Lieutenant', 'Capitaine']) 
            && !in_array('APLI', $certificatsObtenus) && !in_array('CFCU', $certificatsObtenus) && $age <= 50) {
            $this->creerAlerte($militaire, 'formation',
                "Proposable pour cour d'APLI (Grade minimum sous-lieutenant et âge ≤ 50 ans)",
                Carbon::now()->addDays(2));
        }

        // 2. CFCU (Cour des Futurs Commandants d'Unité)
        // Les capitaines sont éligibles sans APLI, les lieutenants nécessitent APLI
        if (in_array($grade, ['Lieutenant', 'Capitaine']) && !in_array('CFCU', $certificatsObtenus)) {
            $estEligible = false;
            
            if ($grade == 'Capitaine') {
                $estEligible = true;
                $message = "Proposable pour CFCU (Capitaine éligible)";
            } elseif (in_array('APLI', $certificatsObtenus)) {
                $estEligible = true;
                $message = "Proposable pour CFCU (avoir fait APLI)";
            }
            
            if ($estEligible) {
                $this->creerAlerte($militaire, 'formation', $message, Carbon::now()->addDays(2));
            }
        }

// 3. CEM (Cour d'état-major) - Formation pour officiers
// Concerne les capitaines avec 3 ans d'ancienneté et les commandants (âge ≤ 45)
if (in_array($grade, ['Capitaine', 'Commandant']) && !in_array('CEM', $certificatsObtenus)) {
    if (($grade == 'Capitaine' && $ancienneteGrade >= 3) || $grade == 'Commandant') {
        if ($age <= 45) {
            $this->creerAlerte($militaire, 'formation',
                "Proposable pour Cour d'état-major (CEM) - capitaine avec 3 ans ou commandant, âge ≤ 45",
                Carbon::now()->addDays(2));
        }
    }
}

// 4. Certificat d'État-major (anciennement dans CEM) - Formation pour officiers
// Concerne les commandants avec âge > 45 ans
if (!in_array('Certificat État-major', $certificatsObtenus) && $grade == 'Commandant' && $age > 45) {
    $this->creerAlerte($militaire, 'formation',
        "Proposable pour Certificat d'État-major - commandant et âge > 45 ans",
        Carbon::now()->addDays(2));
}
        // 5. École de guerre
        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major']) 
            && !in_array('ECOLE_GUERRE', $certificatsObtenus) && $ancienneteGrade >= 2 && $age <= 53) {
            $this->creerAlerte($militaire, 'formation',
                "Proposable pour l'École de guerre - lieutenant-colonel avec 2 ans d'ancienneté, âge ≤ 53",
                Carbon::now()->addDays(2));
        }
    }

    /**
     * Vérifie les retraites proches (dans les 12 mois).
     */
    private function verifierRetraite(Militaire $militaire)
    {
        $dateRetraite = $militaire->calculerDateRetraite();
        
        if ($dateRetraite) {
            $aujourdhui = Carbon::now()->startOfDay();
            $dateRetraiteCarbon = Carbon::parse($dateRetraite)->startOfDay();
            
            $diffJours = $aujourdhui->diffInDays($dateRetraiteCarbon);
            $moisRestants = floor($diffJours / 30);
            $joursRestants = $diffJours % 30;
            
            // Changé de 6 à 12 mois
            if ($moisRestants <= 12 && $diffJours >= 0) {
                $message = $this->formaterMessageRetraite($moisRestants, $joursRestants, $dateRetraite);
                $this->creerAlerte($militaire, 'retraite', $message, $dateRetraite);
            }
        }
    }

    /**
     * Formate le message de retraite
     */
    private function formaterMessageRetraite($mois, $jours, $dateRetraite)
    {
        $dateFormatee = Carbon::parse($dateRetraite)->format('d/m/Y');
        
        if ($mois == 0) {
            if ($jours == 0) {
                return "Retraite aujourd'hui (le {$dateFormatee})";
            } elseif ($jours == 1) {
                return "Retraite demain (le {$dateFormatee})";
            } else {
                return "Retraite dans {$jours} jour" . ($jours > 1 ? 's' : '') . " (le {$dateFormatee})";
            }
        } elseif ($mois == 1 && $jours == 0) {
            return "Retraite dans 1 mois (le {$dateFormatee})";
        } elseif ($mois == 1 && $jours > 0) {
            return "Retraite dans 1 mois et {$jours} jour" . ($jours > 1 ? 's' : '') . " (le {$dateFormatee})";
        } elseif ($mois > 1 && $jours == 0) {
            return "Retraite dans {$mois} mois (le {$dateFormatee})";
        } else {
            return "Retraite dans {$mois} mois et {$jours} jour" . ($jours > 1 ? 's' : '') . " (le {$dateFormatee})";
        }
    }

 /**
 * Crée une alerte pour un militaire.
 */
private function creerAlerte(Militaire $militaire, $type, $message, $dateEcheance = null)
{
    $existe = Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', $type)
        ->where('est_vue', false)
        ->where('message', $message)
        ->exists();

    if (!$existe) {
        // Si aucune date d'échéance n'est fournie, on met +2 jours
        $echeance = $dateEcheance ?? Carbon::now()->addDays(2);
        
        Alerte::create([
            'militaire_id' => $militaire->id,
            'type_alerte' => $type,
            'message' => $message,
            'date_echeance' => $echeance,
        ]);
    }
}
}

/**
 * Classe d'export pour le modèle Excel (template d'import)
 */
class MilitairesExportTemplate implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                '12345',           // matricule
                'DIALLO',          // nom
                'Baba',            // prenom
                '1984-07-19',      // date_naissance
                '2015-06-01',      // date_entree_service
                'Soldat',          // grade_actuel
                '',                // date_derniere_promotion
                'Infanterie',      // specialite
                'actif',           // statut
                1,                 // a_permis_conduire
                // Certificats sous-officiers (booléens + dates)
                0, '', 0, '', 0, '', 0, '', 0, '', // cat1,cat2,cia,ba1,ba2
                0, '', // bmp1
                0, '', // bmp2
                0, '', // bs
                0, '', // ct2
                // Formations officiers
                0, '', 0, '', 0, '', 0, '', 0, '', // apli,cfcu,cem,certificat_etat_major,ecole_guerre
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'matricule',
            'nom',
            'prenom',
            'date_naissance',
            'date_entree_service',
            'grade_actuel',
            'date_derniere_promotion',
            'specialite',
            'statut',
            'a_permis_conduire',
            // Certificats sous-officiers
            'a_fait_cat1',
            'date_obtention_cat1',
            'a_fait_cat2',
            'date_obtention_cat2',
            'a_fait_cia',
            'date_obtention_cia',
            'a_fait_ba1',
            'date_obtention_ba1',
            'a_fait_ba2',
            'date_obtention_ba2',
            'a_fait_bmp1',
            'date_obtention_bmp1',
            'a_fait_bmp2',
            'date_obtention_bmp2',
            'a_fait_bs',
            'date_obtention_bs',
            'a_fait_ct2',
            'date_obtention_ct2',
            // Formations officiers
            'a_fait_apli',
            'date_obtention_apli',
            'a_fait_cfcu',
            'date_obtention_cfcu',
            'a_fait_cem',
            'date_obtention_cem',
            'a_fait_certificat_etat_major',
            'date_obtention_certificat_etat_major',
            'a_fait_ecole_guerre',
            'date_obtention_ecole_guerre',
        ];
    }
}