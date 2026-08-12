<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Militaire;
use App\Models\Grade;
use App\Models\Alerte;
use App\Models\Certificat;
use App\Models\Contrat;
use App\Models\CertificatDocument;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MilitairesExport;
use App\Imports\MilitairesImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

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
                'telephone' => $militaire->telephone,
                'sexe' => $militaire->sexe,
                'groupe_sanguin' => $militaire->groupe_sanguin,
                'personne_a_contacter' => $militaire->personne_a_contacter,
                'telephone_personne_contacter' => $militaire->telephone_personne_contacter,
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

        $certificats = Certificat::orderBy('id')->get()->map(fn ($certificat) => [
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
            'telephone' => 'nullable|string|max:20',
            'sexe' => 'nullable|string|max:10',
            'groupe_sanguin' => 'nullable|string|max:10',
            'personne_a_contacter' => 'nullable|string|max:255',
            'telephone_personne_contacter' => 'nullable|string|max:20',
            'statut' => 'required|string',
            'a_permis_conduire' => 'nullable',
            'a_fait_justice' => 'nullable',
            'a_fait_discipline' => 'nullable',
        ]);

        $data = $this->extractData($request);
        $militaire = Militaire::create($data);

        if ($request->has('certificats')) {
            $this->syncCertificatsWithDocuments($militaire, $request->certificats);
        }

        $militaire->load('certificats');

        $this->verifierAlertes($militaire);
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

        $certificats = $militaire->certificats->map(function ($certificat) {
            $document = CertificatDocument::where('militaire_certificat_id', $certificat->pivot->id)->first();

            return [
                'id' => $certificat->id,
                'nom_certificat' => $certificat->nom_certificat,
                'niveau_certificat' => $certificat->niveau_certificat,
                'date_obtention' => $certificat->pivot->date_obtention
                    ? Carbon::parse($certificat->pivot->date_obtention)->format('d/m/Y')
                    : null,
                'document_id' => $document?->id,
                'document_nom' => $document?->nom_fichier,
            ];
        });

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
            'telephone' => $militaire->telephone,
            'sexe' => $militaire->sexe,
            'groupe_sanguin' => $militaire->groupe_sanguin,
            'personne_a_contacter' => $militaire->personne_a_contacter,
            'telephone_personne_contacter' => $militaire->telephone_personne_contacter,
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

        $certificats = Certificat::orderBy('id')->get()->map(fn ($certificat) => [
            'id' => $certificat->id,
            'nom_certificat' => $certificat->nom_certificat,
            'niveau_certificat' => $certificat->niveau_certificat,
        ]);

        $certificatsDuMilitaire = [];
        foreach ($militaire->certificats as $certificat) {
            $document = CertificatDocument::where('militaire_certificat_id', $certificat->pivot->id)->first();

            $certificatsDuMilitaire[$certificat->id] = [
                'obtenu' => true,
                'date_obtention' => $certificat->pivot->date_obtention
                    ? Carbon::parse($certificat->pivot->date_obtention)->format('Y-m-d')
                    : null,
                'document' => $document ? [
                    'id' => $document->id,
                    'nom_fichier' => $document->nom_fichier,
                ] : null,
            ];
        }

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
                'telephone' => $militaire->telephone,
                'sexe' => $militaire->sexe,
                'groupe_sanguin' => $militaire->groupe_sanguin,
                'personne_a_contacter' => $militaire->personne_a_contacter,
                'telephone_personne_contacter' => $militaire->telephone_personne_contacter,
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
            'telephone' => 'nullable|string|max:20',
            'sexe' => 'nullable|string|max:10',
            'groupe_sanguin' => 'nullable|string|max:10',
            'personne_a_contacter' => 'nullable|string|max:255',
            'telephone_personne_contacter' => 'nullable|string|max:20',
            'statut' => 'required|string',
            'a_permis_conduire' => 'nullable',
            'a_fait_justice' => 'nullable',
            'a_fait_discipline' => 'nullable',
        ]);

        $data = $this->extractData($request, $militaire);
        $militaire->update($data);

        if ($request->has('certificats')) {
            $this->syncCertificatsWithDocuments($militaire, $request->certificats);
        }

        $militaire->load('certificats');

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
        $fields = [
            'matricule', 'nom', 'prenom', 'date_naissance', 'date_entree_service',
            'grade_actuel', 'date_derniere_promotion', 'specialite', 'statut',
            'position_actuelle', 'fonction_passee', 'fonction_actuelle',
            'telephone', 'sexe', 'groupe_sanguin', 'personne_a_contacter',
            'telephone_personne_contacter'
        ];

        $data = [];
        foreach ($fields as $field) {
            if ($request->has($field)) {
                $val = $request->input($field);
                $data[$field] = ($val === '' || $val === 'null' || $val === null) ? null : $val;
            }
        }

        $booleanFields = [
            'a_fait_cat1', 'a_fait_cat2', 'a_fait_cia', 'a_fait_ba1', 'a_fait_ba2',
            'a_fait_bmp1', 'a_fait_bmp2', 'a_fait_bs', 'a_fait_ct2',
            'a_fait_apli', 'a_fait_cfcu', 'a_fait_cem',
            'a_fait_certificat_etat_major', 'a_fait_ecole_guerre',
            'a_permis_conduire', 'a_fait_justice', 'a_fait_discipline'
        ];

        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->boolean($field);
            }
        }

        return $data;
    }

    /**
     * Synchronise les certificats et gère les documents de manière sécurisée sans supprimer les ID pivot existants.
     */
    private function syncCertificatsWithDocuments(Militaire $militaire, array $certificatsData)
    {
        $existingCertificats = \DB::table('certificat_militaire')
            ->where('militaire_id', $militaire->id)
            ->pluck('id', 'certificat_id')
            ->toArray();

        foreach ($certificatsData as $certificatId => $data) {
            $certificatId = (int) $certificatId;
            $obtenu = isset($data['obtenu']) && ($data['obtenu'] === '1' || $data['obtenu'] === 1 || $data['obtenu'] === true || $data['obtenu'] === 'true');

            if ($obtenu) {
                $dateObtention = (!empty($data['date_obtention']) && $data['date_obtention'] !== 'null') ? $data['date_obtention'] : null;

                if (isset($existingCertificats[$certificatId])) {
                    $pivotId = $existingCertificats[$certificatId];
                    \DB::table('certificat_militaire')
                        ->where('id', $pivotId)
                        ->update([
                            'date_obtention' => $dateObtention,
                            'updated_at' => now(),
                        ]);
                } else {
                    $militaire->certificats()->attach($certificatId, [
                        'date_obtention' => $dateObtention,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $pivotId = \DB::table('certificat_militaire')
                        ->where('militaire_id', $militaire->id)
                        ->where('certificat_id', $certificatId)
                        ->value('id');
                }

                if (isset($data['document']) && $data['document']) {
                    $this->handleCertificatDocument($pivotId, $data['document']);
                }
            } else {
                if (isset($existingCertificats[$certificatId])) {
                    $pivotId = $existingCertificats[$certificatId];
                    $existingDoc = CertificatDocument::where('militaire_certificat_id', $pivotId)->first();
                    if ($existingDoc) {
                        if (Storage::disk('public')->exists($existingDoc->chemin_fichier)) {
                            Storage::disk('public')->delete($existingDoc->chemin_fichier);
                        }
                        $existingDoc->delete();
                    }
                    \DB::table('certificat_militaire')->where('id', $pivotId)->delete();
                }
            }
        }
    }

    /**
     * Gère l'upload/suppression des documents
     */
    private function handleCertificatDocument($pivotId, $documentData)
    {
        // Si le document est null ou false, supprimer l'existant
        if (!$documentData) {
            $existingDoc = CertificatDocument::where('militaire_certificat_id', $pivotId)->first();
            if ($existingDoc) {
                Storage::disk('public')->delete($existingDoc->chemin_fichier);
                $existingDoc->delete();
            }
            return;
        }

        // Si c'est un fichier uploadé
        if (is_array($documentData) && isset($documentData['file'])) {
            $file = $documentData['file'];
        } elseif ($documentData instanceof \Illuminate\Http\UploadedFile) {
            $file = $documentData;
        } else {
            return;
        }

        // Supprimer l'ancien document s'il existe
        $existingDoc = CertificatDocument::where('militaire_certificat_id', $pivotId)->first();
        if ($existingDoc) {
            Storage::disk('public')->delete($existingDoc->chemin_fichier);
            $existingDoc->delete();
        }

        // Enregistrer le nouveau document
        $path = $file->store('certificats_documents', 'public');

        CertificatDocument::create([
            'militaire_certificat_id' => $pivotId,
            'nom_fichier' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'type_fichier' => $file->getMimeType(),
            'taille' => $file->getSize(),
        ]);
    }

    /**
     * Nettoie les documents orphelins
     */
    private function cleanupOrphanDocuments(Militaire $militaire)
    {
        try {
            $allPivotIds = \DB::table('militaire_certificat')
                ->where('militaire_id', $militaire->id)
                ->pluck('id')
                ->toArray();

            $activePivotIds = $militaire->certificats()
                ->get()
                ->pluck('pivot.id')
                ->toArray();

            $orphanPivotIds = array_diff($allPivotIds, $activePivotIds);

            if (empty($orphanPivotIds)) {
                return;
            }

            $documents = CertificatDocument::whereIn('militaire_certificat_id', $orphanPivotIds)->get();

            foreach ($documents as $document) {
                if (Storage::disk('public')->exists($document->chemin_fichier)) {
                    Storage::disk('public')->delete($document->chemin_fichier);
                }
                $document->delete();
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors du nettoyage des documents orphelins : ' . $e->getMessage());
        }
    }

    /**
     * Vérifier toutes les alertes pour un militaire
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
     */
    private function verifierAlerteContrat(Militaire $militaire): void
    {
        if ($militaire->statut !== 'actif') {
            return;
        }

        $gradesEligibles = [
            'Soldat 2', 'Soldat 1', 'Caporal', 'Caporal-chef',
            'Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef', 'Major'
        ];

        if (!in_array($militaire->grade_actuel, $gradesEligibles)) {
            return;
        }

        if (!$militaire->date_entree_service) {
            return;
        }

        $serviceYears = floor($militaire->date_entree_service->diffInYears(now()));

        if ($serviceYears < 5) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'contrat')
                ->delete();
            return;
        }

        Alerte::where('militaire_id', $militaire->id)
            ->where('type_alerte', 'contrat')
            ->delete();

        $message = "Renouvellement de contrat requis pour {$militaire->prenom} {$militaire->nom} ({$serviceYears} ans de service) - Grade: {$militaire->grade_actuel}";

        Alerte::create([
            'militaire_id' => $militaire->id,
            'type_alerte' => 'contrat',
            'message' => $message,
            'date_echeance' => now()->addMonths(6),
            'est_vue' => false,
        ]);
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

    /**
     * Vérifie les promotions et supprime les alertes si la promotion est obtenue
     */
    private function verifierPromotions(Militaire $militaire): void
    {
        if ($militaire->statut !== 'actif') return;

        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        $dateProposition = $this->getDateProposition();

        // Supprimer les alertes de promotion si le grade est déjà atteint
        if ($grade == 'Caporal' || $grade == 'Caporal-chef' || in_array($grade, ['Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef', 'Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Caporal%')
                ->delete();
        }

        if (in_array($grade, ['Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef', 'Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Sergent%')
                ->delete();
        }

        if (in_array($grade, ['Sergent-Chef', 'Adjudant', 'Adjudant-Chef', 'Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Sergent-Chef%')
                ->delete();
        }

        if (in_array($grade, ['Adjudant', 'Adjudant-Chef', 'Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Adjudant%')
                ->delete();
        }

        if (in_array($grade, ['Adjudant-Chef', 'Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Adjudant-Chef%')
                ->delete();
        }

        if (in_array($grade, ['Lieutenant', 'Capitaine', 'Commandant', 'Lieutenant-colonel', 'Colonel', 'Colonel-Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Lieutenant%')
                ->delete();
        }

        if (in_array($grade, ['Capitaine', 'Commandant', 'Lieutenant-colonel', 'Colonel', 'Colonel-Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Capitaine%')
                ->delete();
        }

        if (in_array($grade, ['Commandant', 'Lieutenant-colonel', 'Colonel', 'Colonel-Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Commandant%')
                ->delete();
        }

        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Lieutenant-colonel%')
                ->delete();
        }

        if (in_array($grade, ['Colonel', 'Colonel-Major'])) {
            Alerte::where('militaire_id', $militaire->id)
                ->where('type_alerte', 'promotion')
                ->where('message', 'LIKE', '%Colonel%')
                ->delete();
        }

        // Créer les alertes de promotion
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

    /**
     * Vérifie les formations - SUPPRIME TOUJOURS LES ALERTES AVANT D'EN CRÉER
     */
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

    // 1. APLI
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Cour d'Application%")
        ->delete();

    if (!in_array('APLI', $certificatsObtenus)) {
        if (in_array($grade, ['Sous-lieutenant', 'Lieutenant', 'Capitaine']) && !in_array('CFCU', $certificatsObtenus) && $age <= 50) {
            $this->creerAlerteFormation($militaire, 'APLI', "Cour d'Application", $dateProposition, null);
        }
    }

    // 2. CFCU
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Cour des Futurs Commandants d'Unité%")
        ->delete();

    if (!in_array('CFCU', $certificatsObtenus)) {
        if (in_array($grade, ['Lieutenant', 'Capitaine'])) {
            if ($grade == 'Capitaine' || in_array('APLI', $certificatsObtenus)) {
                $this->creerAlerteFormation($militaire, 'CFCU', "Cour des Futurs Commandants d'Unité", $dateProposition, null);
            }
        }
    }

    // 3. CAT1
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Certificat d'Aptitude Technique Niveau 1%")
        ->delete();

    if (!in_array('CAT1', $certificatsObtenus)) {
        if (in_array($grade, ['Soldat 2', 'Soldat 1']) && $ancienneteGrade >= 5 && $conditionsBase) {
            $dateConditions = Carbon::parse($militaire->date_entree_service)->addYears(5);
            $this->creerAlerteFormation($militaire, 'CAT1', "Certificat d'Aptitude Technique Niveau 1", $dateProposition, $dateConditions);
        }
    }

    // 4. CAT2
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Certificat d'Aptitude Technique Niveau 2%")
        ->delete();

    if (!in_array('CAT2', $certificatsObtenus)) {
        if ($grade == 'Caporal' && $age < 47 && $ancienneteGrade >= 3 && $conditionsBase && in_array('CAT1', $certificatsObtenus)) {
            $certifCAT1 = $militaire->certificats->where('niveau_certificat', 'CAT1')->first();
            $dateConditions = null;
            if ($certifCAT1 && $certifCAT1->pivot->date_obtention) {
                $dateConditions = Carbon::parse($certifCAT1->pivot->date_obtention)->addYears(3);
            }
            $this->creerAlerteFormation($militaire, 'CAT2', "Certificat d'Aptitude Technique Niveau 2", $dateProposition, $dateConditions);
        }
    }

    // 5. CIA
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Certificat Interarmes%")
        ->delete();

    if (!in_array('CIA', $certificatsObtenus)) {
        if (in_array($grade, ['Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef']) && $conditionsBase && $militaire->a_permis_conduire) {
            $this->creerAlerteFormation($militaire, 'CIA', "Certificat Interarmes (CIA)", $dateProposition, null);
        }
    }

    // 6. BA1
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Brevet d'Arme N1%")
        ->delete();

    if (!in_array('BA1', $certificatsObtenus)) {
        if (in_array($grade, ['Sergent-Chef', 'Adjudant', 'Adjudant-Chef']) && in_array('CIA', $certificatsObtenus) && $conditionsBase && $anciennete >= 8) {
            $certifCIA = $militaire->certificats->where('niveau_certificat', 'CIA')->first();
            $dateConditions = null;
            if ($certifCIA && $certifCIA->pivot->date_obtention) {
                $dateConditions = Carbon::parse($certifCIA->pivot->date_obtention)->addYears(3);
            }
            $this->creerAlerteFormation($militaire, 'BA1', "Brevet d'Arme N1", $dateProposition, $dateConditions);
        }
    }

    // 7. BA2
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Brevet d'Arme N2%")
        ->delete();

    if (!in_array('BA2', $certificatsObtenus)) {
        if (in_array($grade, ['Adjudant', 'Adjudant-Chef']) && in_array('BA1', $certificatsObtenus) && $conditionsBase) {
            $certifBA1 = $militaire->certificats->where('niveau_certificat', 'BA1')->first();
            $dateConditions = null;
            if ($certifBA1 && $certifBA1->pivot->date_obtention) {
                $dateConditions = Carbon::parse($certifBA1->pivot->date_obtention)->addYears(3);
            }
            $this->creerAlerteFormation($militaire, 'BA2', "Brevet d'Arme N2", $dateProposition, $dateConditions);
        }
    }



    // 9. CERT_EM
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%Certificat d'État-Major%")
        ->delete();

    if (!in_array('CERT_EM', $certificatsObtenus)) {
        if ($grade == 'Commandant' && $age > 45) {
            $this->creerAlerteFormation($militaire, 'CERT_EM', "Certificat d'État-Major", $dateProposition, null);
        }
    }

    // 10. ECOLE_GUERRE
    Alerte::where('militaire_id', $militaire->id)
        ->where('type_alerte', 'formation')
        ->where('message', 'LIKE', "%École de Guerre%")
        ->delete();

    if (!in_array('ECOLE_GUERRE', $certificatsObtenus)) {
        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major']) && $ancienneteGrade >= 2 && $age <= 53) {
            $this->creerAlerteFormation($militaire, 'ECOLE_GUERRE', "École de Guerre", $dateProposition, null);
        }
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
                'Position actuelle', 'Fonction passée', 'Fonction actuelle', '0123456789',
                'M', 'O+', 'Jean DIOP', '771234567'
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
            'position_actuelle', 'fonction_passee', 'fonction_actuelle', 'telephone',
            'sexe', 'groupe_sanguin', 'personne_a_contacter', 'telephone_personne_contacter'
        ];
    }
}
