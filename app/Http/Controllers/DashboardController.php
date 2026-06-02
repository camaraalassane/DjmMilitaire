<?php

namespace App\Http\Controllers;

use App\Models\Militaire;
use App\Models\Alerte;
use App\Models\Grade;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProposablesAnneeNExport;
use App\Exports\ProposablesAnneeN1Export;
use App\Exports\RetraitesAnneeNExport;
use App\Exports\RetraitesAnneeN1Export;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord.
     */
    public function index()
    {
        $alertesNonVues = Alerte::where('est_vue', false)
            ->with(['militaire' => fn($q) => $q->select('id', 'nom', 'prenom', 'matricule')])
            ->orderBy('date_echeance')
            ->take(20)
            ->get()
            ->map(fn($alerte) => [
                'id' => $alerte->id,
                'message' => $alerte->message,
                'type_alerte' => $alerte->type_alerte,
                'date_echeance' => $alerte->date_echeance,
                'est_vue' => $alerte->est_vue,
                'militaire' => $alerte->militaire ? [
                    'id' => $alerte->militaire->id,
                    'nom' => $alerte->militaire->nom,
                    'prenom' => $alerte->militaire->prenom,
                    'matricule' => $alerte->militaire->matricule,
                ] : null,
            ]);

        $statistiques = [
            'total_militaires' => Militaire::count(),
            'militaires_actifs' => Militaire::where('statut', 'actif')->count(),
            'alertes_non_vues' => Alerte::where('est_vue', false)->count(),
            'total_retraites_n' => 0,
            'total_retraites_n1' => 0,
            'total_proposables_n' => 0,
            'total_proposables_n1' => 0,
        ];

        $grades = Grade::withCount(['militaires' => fn($q) => $q->where('statut', 'actif')])
            ->get(['id', 'nom_grade', 'type_grade'])
            ->map(fn($g) => [
                'id' => $g->id,
                'nom_grade' => $g->nom_grade,
                'type_grade' => $g->type_grade,
                'militaires_count' => $g->militaires_count,
            ]);

        return Inertia::render('Dashboard', [
            'alertesNonVues' => $alertesNonVues,
            'statistiques' => $statistiques,
            'grades' => $grades,
        ]);
    }

    /**
     * Charge une section spécifique du tableau de bord.
     */
    public function section(Request $request)
    {
        $section = $request->get('section');

        switch ($section) {
            case 'retraitesN':
                $result = $this->calculerRetraitesPourAnnee(0);
                $data = $result['retraites'];
                $title = "Retraites - Année {$result['annee']}";
                $icon = 'pi pi-calendar text-red-500';
                $total = $result['total'];
                $sortField = 'date_retraite';
                $columns = [
                    ['field' => 'grade_actuel', 'header' => 'Grade actuel', 'sortable' => true],
                    ['field' => 'matricule', 'header' => 'Matricule', 'sortable' => true],
                    ['field' => 'nom_complet', 'header' => 'Nom et Prénom', 'sortable' => true],
                    ['field' => 'date_retraite_formatted', 'header' => 'Date de retraite', 'sortable' => true],
                ];
                break;

            case 'retraitesN1':
                $result = $this->calculerRetraitesPourAnnee(1);
                $data = $result['retraites'];
                $title = "Retraites - Année {$result['annee']}";
                $icon = 'pi pi-calendar-plus text-orange-500';
                $total = $result['total'];
                $sortField = 'date_retraite';
                $columns = [
                    ['field' => 'grade_actuel', 'header' => 'Grade actuel', 'sortable' => true],
                    ['field' => 'matricule', 'header' => 'Matricule', 'sortable' => true],
                    ['field' => 'nom_complet', 'header' => 'Nom et Prénom', 'sortable' => true],
                    ['field' => 'date_retraite_formatted', 'header' => 'Date de retraite', 'sortable' => true],
                ];
                break;

            case 'proposablesN':
                $result = $this->calculerProposablesPourAnnee(0);
                $data = $result['proposables'];
                $title = "Proposables - Année {$result['annee']}";
                $icon = 'pi pi-star text-purple-500';
                $total = $result['total'];
                $sortField = 'date_proposition';
                $columns = [
                    ['field' => 'grade_actuel', 'header' => 'Grade actuel', 'sortable' => true],
                    ['field' => 'anciennete_grade_formatted', 'header' => 'Ancienneté grade', 'sortable' => true],
                    ['field' => 'matricule', 'header' => 'Matricule', 'sortable' => true],
                    ['field' => 'nom_complet', 'header' => 'Nom et Prénom', 'sortable' => true],
                    ['field' => 'grade_cible', 'header' => 'Grade cible', 'sortable' => true],
                    ['field' => 'date_anciennete_formatted', 'header' => 'Date ancienneté', 'sortable' => true],
                    ['field' => 'date_proposition_formatted', 'header' => 'Date proposition', 'sortable' => true],
                ];
                break;

            case 'proposablesN1':
                $proposablesN = $this->calculerProposablesPourAnnee(0);
                $exclusIds = collect($proposablesN['proposables'])->pluck('id')->toArray();
                $result = $this->calculerProposablesPourAnnee(1, $exclusIds);
                $data = $result['proposables'];
                $title = "Proposables - Année {$result['annee']}";
                $icon = 'pi pi-star text-indigo-500';
                $total = $result['total'];
                $sortField = 'date_proposition';
                $columns = [
                    ['field' => 'grade_actuel', 'header' => 'Grade actuel', 'sortable' => true],
                    ['field' => 'anciennete_grade_formatted', 'header' => 'Ancienneté grade', 'sortable' => true],
                    ['field' => 'matricule', 'header' => 'Matricule', 'sortable' => true],
                    ['field' => 'nom_complet', 'header' => 'Nom et Prénom', 'sortable' => true],
                    ['field' => 'grade_cible', 'header' => 'Grade cible', 'sortable' => true],
                    ['field' => 'date_anciennete_formatted', 'header' => 'Date ancienneté', 'sortable' => true],
                    ['field' => 'date_proposition_formatted', 'header' => 'Date proposition', 'sortable' => true],
                ];
                break;

            default:
                abort(400, 'Section invalide');
        }

        return response()->json([
            'data' => $data,
            'title' => $title,
            'icon' => $icon,
            'total' => $total,
            'sortField' => $sortField,
            'columns' => $columns,
        ]);
    }

    // -------------------------------------------------------------------------
    // Méthodes de calcul
    // -------------------------------------------------------------------------

    /**
     * Retourne la date de proposition (31 décembre de l'année cible)
     */
    private function getDateProposition($anneeCible)
    {
        return Carbon::create($anneeCible, 12, 31, 23, 59, 59);
    }

    /**
     * Calcule les retraites pour une année donnée.
     */
    private function calculerRetraitesPourAnnee($decalageAnnees, $idsExclus = [])
    {
        $aujourdhui = Carbon::now();
        $anneeCible = $aujourdhui->year + $decalageAnnees;
        $dateDebut = Carbon::create($anneeCible, 1, 1)->startOfDay();
        $dateFin = Carbon::create($anneeCible, 12, 31)->endOfDay();
        $exclusSet = array_flip($idsExclus);
        
        $militairesActifs = Militaire::where('statut', 'actif')->get();
        $retraites = [];

        foreach ($militairesActifs as $militaire) {
            if (isset($exclusSet[$militaire->id])) continue;
            $dateRetraite = $militaire->calculerDateRetraite();
            if ($dateRetraite && Carbon::parse($dateRetraite)->between($dateDebut, $dateFin)) {
                $retraites[] = [
                    'id' => $militaire->id,
                    'matricule' => $militaire->matricule,
                    'nom' => $militaire->nom,
                    'prenom' => $militaire->prenom,
                    'nom_complet' => $militaire->nom . ' ' . $militaire->prenom,
                    'grade_actuel' => $militaire->grade_actuel,
                    'date_retraite' => $dateRetraite->format('Y-m-d'),
                    'date_retraite_formatted' => $dateRetraite->format('d/m/Y'),
                ];
            }
        }
        
        usort($retraites, function($a, $b) {
            $gradeCompare = strcmp($a['grade_actuel'], $b['grade_actuel']);
            if ($gradeCompare !== 0) {
                return $gradeCompare;
            }
            return strcmp($a['date_retraite'], $b['date_retraite']);
        });
        
        return [
            'annee' => $anneeCible,
            'retraites' => $retraites,
            'total' => count($retraites)
        ];
    }

    /**
     * Calcule l'ancienneté dans le grade au format années/mois.
     */
    private function getAncienneteGradeFormatted($militaire, $dateProposition)
    {
        $dateReference = $dateProposition;
        $dateDernierePromotion = $militaire->date_derniere_promotion;
        $dateEntreeService = $militaire->date_entree_service;
        
        $dateAnciennete = $dateDernierePromotion ?? $dateEntreeService;
        
        if (!$dateAnciennete) {
            return '0 an';
        }
        
        $diff = Carbon::parse($dateAnciennete)->diff($dateReference);
        $years = $diff->y;
        $months = $diff->m;
        
        if ($years > 0 && $months > 0) {
            return "{$years} an" . ($years > 1 ? 's' : '') . " et {$months} mois";
        } elseif ($years > 0) {
            return "{$years} an" . ($years > 1 ? 's' : '');
        } elseif ($months > 0) {
            return "{$months} mois";
        } else {
            return "moins d'1 mois";
        }
    }

    /**
     * Calcule les proposables pour une année donnée.
     * 
     * @param int $decalageAnnees 0 pour l'année N, 1 pour l'année N+1
     * @param array $idsExclus IDs des militaires à exclure
     */
    private function calculerProposablesPourAnnee($decalageAnnees, $idsExclus = [])
    {
        $aujourdhui = Carbon::now();
        $anneeCible = $aujourdhui->year + $decalageAnnees;
        $dateProposition = $this->getDateProposition($anneeCible);
        
        $exclusSet = array_flip($idsExclus);
        $militairesActifs = Militaire::where('statut', 'actif')
            ->with('certificats')
            ->get();
        
        $proposables = [];
        
        foreach ($militairesActifs as $militaire) {
            if (isset($exclusSet[$militaire->id])) {
                continue;
            }
            
            // Passer l'année cible pour calculer l'ancienneté à cette date
            $gradeCible = $this->getGradeCible($militaire, $anneeCible);
            if (!$gradeCible) {
                continue;
            }
            
            $dateAnciennete = $this->getDateAncienneteReelle($militaire, $gradeCible);
            if (!$dateAnciennete) {
                continue;
            }
            
            // Vérifier si la date d'ancienneté est <= au 31 décembre de l'année cible
            if ($dateAnciennete <= $dateProposition) {
                $proposables[] = [
                    'id' => $militaire->id,
                    'matricule' => $militaire->matricule,
                    'nom' => $militaire->nom,
                    'prenom' => $militaire->prenom,
                    'nom_complet' => $militaire->nom . ' ' . $militaire->prenom,
                    'grade_actuel' => $militaire->grade_actuel,
                    'grade_cible' => $gradeCible,
                    'date_proposition' => $dateProposition->format('Y-m-d'),
                    'date_proposition_formatted' => $dateProposition->format('d/m/Y'),
                    'date_anciennete' => $dateAnciennete->format('Y-m-d'),
                    'date_anciennete_formatted' => $dateAnciennete->format('d/m/Y'),
                    'anciennete_grade_formatted' => $this->getAncienneteGradeFormatted($militaire, $dateProposition),
                ];
            }
        }
        
        usort($proposables, function($a, $b) {
            $gradeCompare = strcmp($a['grade_actuel'], $b['grade_actuel']);
            if ($gradeCompare !== 0) {
                return $gradeCompare;
            }
            return strcmp($a['date_anciennete'], $b['date_anciennete']);
        });
        
        return [
            'annee' => $anneeCible,
            'proposables' => $proposables,
            'total' => count($proposables)
        ];
    }

    /**
     * Détermine le grade cible pour un militaire à une année donnée.
     * 
     * @param Militaire $militaire
     * @param int $anneeReference Année de référence (2026 pour N, 2027 pour N+1)
     */
    private function getGradeCible($militaire, $anneeReference)
    {
        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;
        
        // Calcul de l'ancienneté dans le grade au 31 décembre de l'année de référence
        $dateReference = Carbon::create($anneeReference, 12, 31, 23, 59, 59);
        $dateDernierePromotion = $militaire->date_derniere_promotion;
        
        if ($dateDernierePromotion) {
            $ancienneteGrade = $dateDernierePromotion->diffInYears($dateReference);
        } else {
            $ancienneteGrade = 0;
        }
        
        // Ancienneté totale au 31 décembre de l'année de référence
        $dateEntreeService = $militaire->date_entree_service;
        if ($dateEntreeService) {
            $ancienneteTotale = $dateEntreeService->diffInYears($dateReference);
        } else {
            $ancienneteTotale = 0;
        }

        // === PROMOTIONS OFFICIERS ===
        if ($grade == 'Sous-lieutenant' && $ancienneteGrade >= 2) {
            return 'Lieutenant';
        }
        
        if ($grade == 'Lieutenant' && $ancienneteGrade >= 3) {
            return 'Capitaine';
        }
        
        if ($grade == 'Capitaine' && $ancienneteGrade >= 3) {
            return 'Commandant';
        }
        
        if ($grade == 'Commandant' && $ancienneteGrade >= 3) {
            return 'Lieutenant-colonel';
        }
        
        if ($grade == 'Lieutenant-colonel' && $ancienneteGrade >= 3) {
            return 'Colonel';
        }
        
        if ($grade == 'Colonel' && $ancienneteGrade >= 6) {
            return 'Colonel-Major';
        }
        
        // === PROMOTIONS SOUS-OFFICIERS ===
        if ($grade == 'Soldat 1' && in_array('CAT1', $certificatsObtenus) && $conditionsBase && $ancienneteTotale >= 5) {
            return 'Caporal';
        }
        
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus) && $conditionsBase) {
            return 'Sergent';
        }
        
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47 && $ancienneteGrade >= 3 && $conditionsBase) {
            return 'Caporal-chef';
        }
        
        if ($grade == 'Sergent' && $conditionsBase && $ancienneteGrade >= 2 && $ancienneteTotale >= 5) {
            return 'Sergent-Chef';
        }
        
        if ($grade == 'Sergent-Chef' && $conditionsBase && $ancienneteGrade >= 3) {
            return 'Adjudant';
        }
        
        if ($grade == 'Adjudant' && $conditionsBase && $ancienneteGrade >= 2) {
            return 'Adjudant-Chef';
        }
        
        if ($grade == 'Adjudant-Chef' && in_array('CIA', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && in_array('BA2', $certificatsObtenus) && $age >= 45 && $conditionsBase && $ancienneteGrade >= 2) {
            return 'Adjudant-Chef Major';
        }
        
        // Passage sous-officier → officier
        if (in_array('BA2', $certificatsObtenus)) {
            if (($grade == 'Adjudant-Chef' && $age <= 45 && $ancienneteTotale >= 15 && $ancienneteGrade >= 2)
                || ($grade == 'Adjudant-Chef major' && $ancienneteGrade >= 2)) {
                return 'Sous-lieutenant';
            }
        }
        
        return null;
    }

    /**
     * Calcule la date réelle où l'ancienneté est atteinte pour une promotion.
     */
    private function getDateAncienneteReelle($militaire, $gradeCible)
    {
        $gradeActuel = $militaire->grade_actuel;
        $dateDernierePromotion = $militaire->date_derniere_promotion;
        $dateEntreeService = $militaire->date_entree_service;
        
        // Lieutenant → Capitaine (3 ans)
        if ($gradeActuel == 'Lieutenant' && $gradeCible == 'Capitaine') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(3);
            }
        }
        
        // Sous-lieutenant → Lieutenant (2 ans)
        if ($gradeActuel == 'Sous-lieutenant' && $gradeCible == 'Lieutenant') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(2);
            }
        }
        
        // Capitaine → Commandant (3 ans)
        if ($gradeActuel == 'Capitaine' && $gradeCible == 'Commandant') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(3);
            }
        }
        
        // Commandant → Lieutenant-colonel (3 ans)
        if ($gradeActuel == 'Commandant' && $gradeCible == 'Lieutenant-colonel') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(3);
            }
        }
        
        // Lieutenant-colonel → Colonel (3 ans)
        if ($gradeActuel == 'Lieutenant-colonel' && $gradeCible == 'Colonel') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(3);
            }
        }
        
        // Colonel → Colonel-Major (6 ans)
        if ($gradeActuel == 'Colonel' && $gradeCible == 'Colonel-Major') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(6);
            }
        }
        
        // Soldat 1 → Caporal (5 ans après entrée)
        if ($gradeActuel == 'Soldat 1' && $gradeCible == 'Caporal') {
            if ($dateEntreeService) {
                return Carbon::parse($dateEntreeService)->addYears(5);
            }
        }
        
        // Caporal → Sergent (3 ans après CAT1)
        if ($gradeActuel == 'Caporal' && $gradeCible == 'Sergent') {
            $certifCAT1 = $militaire->certificats->where('niveau_certificat', 'CAT1')->first();
            if ($certifCAT1 && $certifCAT1->pivot->date_obtention) {
                return Carbon::parse($certifCAT1->pivot->date_obtention)->addYears(3);
            }
        }
        
        // Caporal → Caporal-chef (3 ans)
        if ($gradeActuel == 'Caporal' && $gradeCible == 'Caporal-chef') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(3);
            }
        }
        
        // Sergent → Sergent-Chef (2 ans)
        if ($gradeActuel == 'Sergent' && $gradeCible == 'Sergent-Chef') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(2);
            }
        }
        
        // Sergent-Chef → Adjudant (3 ans)
        if ($gradeActuel == 'Sergent-Chef' && $gradeCible == 'Adjudant') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(3);
            }
        }
        
        // Adjudant → Adjudant-Chef (2 ans)
        if ($gradeActuel == 'Adjudant' && $gradeCible == 'Adjudant-Chef') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(2);
            }
        }
        
        // Adjudant-Chef → Adjudant-Chef Major (2 ans)
        if ($gradeActuel == 'Adjudant-Chef' && $gradeCible == 'Adjudant-Chef Major') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(2);
            }
        }
        
        // Adjudant-Chef → Sous-lieutenant (2 ans)
        if ($gradeActuel == 'Adjudant-Chef' && $gradeCible == 'Sous-lieutenant') {
            if ($dateDernierePromotion) {
                return Carbon::parse($dateDernierePromotion)->addYears(2);
            }
        }
        
        return null;
    }

    // --- Méthodes d'export ---
    
    public function exportProposablesAnneeN()
    {
        $result = $this->calculerProposablesPourAnnee(0);
        return Excel::download(new ProposablesAnneeNExport($result), 'proposables_annee_' . $result['annee'] . '_' . Carbon::now()->format('Y_m_d') . '.xlsx');
    }

    public function exportProposablesAnneeN1()
    {
        $proposablesN = $this->calculerProposablesPourAnnee(0);
        $exclusIds = collect($proposablesN['proposables'])->pluck('id')->toArray();
        $result = $this->calculerProposablesPourAnnee(1, $exclusIds);
        return Excel::download(new ProposablesAnneeN1Export($result), 'proposables_annee_' . $result['annee'] . '_' . Carbon::now()->format('Y_m_d') . '.xlsx');
    }

    public function exportRetraitesAnneeN()
    {
        $result = $this->calculerRetraitesPourAnnee(0);
        return Excel::download(new RetraitesAnneeNExport($result), 'retraites_annee_' . $result['annee'] . '_' . Carbon::now()->format('Y_m_d') . '.xlsx');
    }

    public function exportRetraitesAnneeN1()
    {
        $retraitesN = $this->calculerRetraitesPourAnnee(0);
        $exclusIds = collect($retraitesN['retraites'])->pluck('id')->toArray();
        $result = $this->calculerRetraitesPourAnnee(1, $exclusIds);
        return Excel::download(new RetraitesAnneeN1Export($result), 'retraites_annee_' . $result['annee'] . '_' . Carbon::now()->format('Y_m_d') . '.xlsx');
    }

    public function marquerAlerteVue(Alerte $alerte)
    {
        $alerte->update(['est_vue' => true]);
        return request()->wantsJson() ? response()->json(['success' => true]) : redirect()->back()->with('success', 'Alerte marquée comme vue.');
    }
}