<?php

namespace App\Http\Controllers;

use App\Models\Militaire;
use App\Exports\EligibilitesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class EligibiliteController extends Controller
{
    /**
     * Affiche la page des éligibilités avec les listes de filtres.
     */
    public function index()
    {
        $formationsListe = $this->getFormationsListe();
        $gradesListe = $this->getGradesListe();
        
        return Inertia::render('eligibilites/index', [
            'formationsListe' => $formationsListe,
            'gradesListe' => $gradesListe
        ]);
    }

    /**
     * API: Récupère les éligibilités filtrées.
     */
    public function getFiltered(Request $request)
    {
        $type = $request->input('type', '');
        $formation = $request->input('formation', '');
        $grade = $request->input('grade', '');
        
        $eligibilites = $this->getEligibilites($type, $formation, $grade);
        
        return response()->json($eligibilites);
    }

    /**
     * Récupère la liste unique de toutes les formations.
     */
    private function getFormationsListe()
    {
        return [
            ['id' => 'CAT1', 'nom' => 'CAT1 (Certificat Technique Niveau 1)'],
            ['id' => 'CAT2', 'nom' => 'CAT2 (Certificat Technique Niveau 2)'],
            ['id' => 'CIA', 'nom' => 'CIA (Certificat Instruction d\'Armes)'],
            ['id' => 'BA1', 'nom' => 'BA1 (Brevet Aptitude Niveau 1)'],
            ['id' => 'BA2', 'nom' => 'BA2 (Brevet Aptitude Niveau 2)'],
            ['id' => 'APLI', 'nom' => 'APLI (Cour d\'Application)'],
            ['id' => 'CFCU', 'nom' => 'CFCU (Cour des Futurs Commandants)'],
            ['id' => 'CEM', 'nom' => 'CEM (Cour d\'État-Major)'],
            ['id' => 'CERT_EM', 'nom' => 'Certificat d\'État-Major'],
            ['id' => 'ECOLE_GUERRE', 'nom' => 'École de Guerre'],
        ];
    }

    /**
     * Récupère la liste unique de tous les grades.
     */
    private function getGradesListe()
    {
        $grades = \App\Models\Grade::orderBy('ordre')->get();
        return $grades->map(fn($g) => [
            'id' => $g->nom_grade,
            'nom' => $g->nom_grade
        ]);
    }

    /**
     * Calcule les éligibilités avec filtres.
     */
    private function getEligibilites($type = '', $formation = '', $grade = '')
    {
        $query = Militaire::where('statut', 'actif')->with('certificats');
        
        if (!empty($grade)) {
            $query->where('grade_actuel', $grade);
        }
        
        $militaires = $query->get();
        $eligibilites = [
            'promotions' => [],
            'formations' => [],
            'retraites' => [],
            'statistiques' => [
                'total_promotions' => 0,
                'total_formations' => 0,
                'total_retraites' => 0
            ]
        ];

        foreach ($militaires as $militaire) {
            $this->checkPromotions($militaire, $eligibilites, $type);
            $this->checkFormations($militaire, $eligibilites, $type, $formation);
            $this->checkRetraite($militaire, $eligibilites, $type);
        }

        usort($eligibilites['promotions'], fn($a, $b) => $a['date_estimation'] <=> $b['date_estimation']);
        usort($eligibilites['formations'], fn($a, $b) => $a['date_estimation'] <=> $b['date_estimation']);
        usort($eligibilites['retraites'], fn($a, $b) => $a['date_retraite'] <=> $b['date_retraite']);
        
        $eligibilites['statistiques']['total_promotions'] = count($eligibilites['promotions']);
        $eligibilites['statistiques']['total_formations'] = count($eligibilites['formations']);
        $eligibilites['statistiques']['total_retraites'] = count($eligibilites['retraites']);

        return $eligibilites;
    }

    /**
     * Vérifie les éligibilités aux promotions.
     */
    private function checkPromotions($militaire, &$eligibilites, $type = '')
    {
        if (!empty($type) && $type !== 'promotions') return;
        
        $grade = $militaire->grade_actuel;
        $anciennete = $militaire->anciennete;
        $age = $militaire->age;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        
        $dateProposition = $this->getDateProposition();

        // Soldat 1 → Caporal
        if ($grade == 'Soldat 1' && in_array('CAT1', $certificatsObtenus) && $conditionsBase && $anciennete >= 5) {
            $dateAnciennete = Carbon::parse($militaire->date_entree_service)->addYears(5);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Caporal', $dateProposition, $dateAnciennete);
        }

        // Caporal → Sergent
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus) && $conditionsBase) {
            $certifCAT1 = $militaire->certificats->where('niveau_certificat', 'CAT1')->first();
            $dateAnciennete = null;
            if ($certifCAT1 && $certifCAT1->pivot->date_obtention) {
                $dateAnciennete = Carbon::parse($certifCAT1->pivot->date_obtention)->addYears(3);
            }
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Sergent', $dateProposition, $dateAnciennete);
        }

        // Caporal → Caporal-chef
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47 && $ancienneteGrade >= 3 && $conditionsBase) {
            $dateAnciennete = $militaire->date_derniere_promotion ? Carbon::parse($militaire->date_derniere_promotion)->addYears(3) : null;
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Caporal-chef', $dateProposition, $dateAnciennete, "âge ≥ 47 ans");
        }

        // Sergent → Sergent-Chef
        if ($grade == 'Sergent' && $conditionsBase && $ancienneteGrade >= 2 && $anciennete >= 5) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(2);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Sergent-Chef', $dateProposition, $dateAnciennete);
        }

        // Sergent-Chef → Adjudant
        if ($grade == 'Sergent-Chef' && $conditionsBase && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Adjudant', $dateProposition, $dateAnciennete);
        }

        // Adjudant → Adjudant-Chef
        if ($grade == 'Adjudant' && $conditionsBase && $ancienneteGrade >= 2) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(2);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Adjudant-Chef', $dateProposition, $dateAnciennete);
        }

        // Sous-lieutenant → Lieutenant
        if ($grade == 'Sous-lieutenant' && $ancienneteGrade >= 2) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(2);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Lieutenant', $dateProposition, $dateAnciennete);
        }

        // Lieutenant → Capitaine
        if ($grade == 'Lieutenant' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Capitaine', $dateProposition, $dateAnciennete);
        }

        // Capitaine → Commandant
        if ($grade == 'Capitaine' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Commandant', $dateProposition, $dateAnciennete);
        }

        // Commandant → Lieutenant-colonel
        if ($grade == 'Commandant' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Lieutenant-colonel', $dateProposition, $dateAnciennete);
        }

        // Lieutenant-colonel → Colonel
        if ($grade == 'Lieutenant-colonel' && $ancienneteGrade >= 3) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(3);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Colonel', $dateProposition, $dateAnciennete);
        }

        // Colonel → Colonel-Major
        if ($grade == 'Colonel' && $ancienneteGrade >= 6) {
            $dateAnciennete = Carbon::parse($militaire->date_derniere_promotion)->addYears(6);
            $eligibilites['promotions'][] = $this->formatPromotion($militaire, 'Colonel-Major', $dateProposition, $dateAnciennete);
        }
    }

    /**
     * Vérifie les éligibilités aux formations.
     */
    private function checkFormations($militaire, &$eligibilites, $type = '', $formationFiltre = '')
    {
        if (!empty($type) && $type !== 'formations') return;
        
        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $anciennete = $militaire->anciennete;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;
        
        $dateProposition = $this->getDateProposition();

        // CAT1
        if ($grade == 'Soldat 1' && !in_array('CAT1', $certificatsObtenus) && $ancienneteGrade >= 5 && $conditionsBase) {
            if (empty($formationFiltre) || $formationFiltre === 'CAT1') {
                $dateConditions = Carbon::parse($militaire->date_entree_service)->addYears(5);
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'CAT1', 'Certificat d\'Aptitude Technique Niveau 1', $dateProposition, $dateConditions);
            }
        }

        // CAT2
        if ($grade == 'Caporal' && $age < 47 && !in_array('CAT2', $certificatsObtenus) && $ancienneteGrade >= 3 && $conditionsBase && in_array('CAT1', $certificatsObtenus)) {
            if (empty($formationFiltre) || $formationFiltre === 'CAT2') {
                $certifCAT1 = $militaire->certificats->where('niveau_certificat', 'CAT1')->first();
                $dateConditions = null;
                if ($certifCAT1 && $certifCAT1->pivot->date_obtention) {
                    $dateConditions = Carbon::parse($certifCAT1->pivot->date_obtention)->addYears(3);
                }
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'CAT2', 'Certificat d\'Aptitude Technique Niveau 2', $dateProposition, $dateConditions);
            }
        }

        // CIA
        if (in_array($grade, ['Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef']) && !in_array('CIA', $certificatsObtenus) && $conditionsBase && $militaire->a_permis_conduire) {
            if (empty($formationFiltre) || $formationFiltre === 'CIA') {
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'CIA', 'Certificat d\'Instruction d\'Armes', $dateProposition, null, "permis de conduire requis");
            }
        }

        // BA1
        if (in_array($grade, ['Sergent-Chef', 'Adjudant', 'Adjudant-Chef']) && !in_array('BA1', $certificatsObtenus) && in_array('CIA', $certificatsObtenus) && $conditionsBase && $anciennete >= 8) {
            if (empty($formationFiltre) || $formationFiltre === 'BA1') {
                $certifCIA = $militaire->certificats->where('niveau_certificat', 'CIA')->first();
                $dateConditions = null;
                if ($certifCIA && $certifCIA->pivot->date_obtention) {
                    $dateConditions = Carbon::parse($certifCIA->pivot->date_obtention)->addYears(3);
                }
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'BA1', 'Brevet d\'Aptitude Niveau 1', $dateProposition, $dateConditions);
            }
        }

        // BA2
        if (in_array($grade, ['Adjudant', 'Adjudant-Chef']) && !in_array('BA2', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && $conditionsBase) {
            if (empty($formationFiltre) || $formationFiltre === 'BA2') {
                $certifBA1 = $militaire->certificats->where('niveau_certificat', 'BA1')->first();
                $dateConditions = null;
                if ($certifBA1 && $certifBA1->pivot->date_obtention) {
                    $dateConditions = Carbon::parse($certifBA1->pivot->date_obtention)->addYears(3);
                }
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'BA2', 'Brevet d\'Aptitude Niveau 2', $dateProposition, $dateConditions);
            }
        }

        // APLI
        if (in_array($grade, ['Sous-lieutenant', 'Lieutenant', 'Capitaine']) && !in_array('APLI', $certificatsObtenus) && !in_array('CFCU', $certificatsObtenus) && $age <= 50) {
            if (empty($formationFiltre) || $formationFiltre === 'APLI') {
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'APLI', 'Cour d\'Application', $dateProposition, null, "âge ≤ 50 ans");
            }
        }

        // CFCU
        if (in_array($grade, ['Lieutenant', 'Capitaine']) && !in_array('CFCU', $certificatsObtenus)) {
            if (empty($formationFiltre) || $formationFiltre === 'CFCU') {
                if ($grade == 'Capitaine' || in_array('APLI', $certificatsObtenus)) {
                    $condition = $grade == 'Capitaine' ? "grade Capitaine" : "APLI validé";
                    $eligibilites['formations'][] = $this->formatFormation($militaire, 'CFCU', 'Cour des Futurs Commandants d\'Unité', $dateProposition, null, $condition);
                }
            }
        }

        // CEM
        if (in_array($grade, ['Capitaine', 'Commandant']) && !in_array('CEM', $certificatsObtenus)) {
            if (empty($formationFiltre) || $formationFiltre === 'CEM') {
                if (($grade == 'Capitaine' && $ancienneteGrade >= 3) || $grade == 'Commandant') {
                    if ($age <= 45) {
                        $eligibilites['formations'][] = $this->formatFormation($militaire, 'CEM', 'Cour d\'État-Major', $dateProposition, null, "âge ≤ 45 ans");
                    }
                }
            }
        }

        // Certificat d'État-Major
        if ($grade == 'Commandant' && $age > 45 && !in_array('CERT_EM', $certificatsObtenus)) {
            if (empty($formationFiltre) || $formationFiltre === 'CERT_EM') {
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'CERT_EM', 'Certificat d\'État-Major', $dateProposition, null, "âge > 45 ans");
            }
        }

        // École de Guerre
        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major']) && !in_array('ECOLE_GUERRE', $certificatsObtenus) && $ancienneteGrade >= 2 && $age <= 53) {
            if (empty($formationFiltre) || $formationFiltre === 'ECOLE_GUERRE') {
                $eligibilites['formations'][] = $this->formatFormation($militaire, 'ECOLE_GUERRE', 'École de Guerre', $dateProposition, null, "âge ≤ 53 ans");
            }
        }
    }

    /**
     * Vérifie les retraites proches.
     */
    private function checkRetraite($militaire, &$eligibilites, $type = '')
    {
        if (!empty($type) && $type !== 'retraites') return;
        
        $dateRetraite = $militaire->calculerDateRetraite();
        
        if ($dateRetraite) {
            $diffJours = Carbon::now()->startOfDay()->diffInDays($dateRetraite);
            $moisRestants = floor($diffJours / 30);
            
            if ($moisRestants <= 12 && $diffJours >= 0) {
                $eligibilites['retraites'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'date_retraite' => $dateRetraite->format('Y-m-d'),
                    'date_retraite_formatted' => $dateRetraite->format('d/m/Y'),
                    'mois_restants' => $moisRestants,
                ];
            }
        }
    }

    /**
     * Formate une promotion pour l'affichage.
     */
    private function formatPromotion($militaire, $gradeCible, $dateProposition, $dateAnciennete = null, $detail = '')
    {
        $anneeProposition = $dateProposition->format('Y');
        
        if ($dateAnciennete && $dateAnciennete->year <= $anneeProposition) {
            $dateAncienneteTexte = $dateAnciennete->format('d/m/Y');
            $message = "Proposable pour {$anneeProposition} (ancienneté atteinte le {$dateAncienneteTexte})";
        } elseif ($dateAnciennete) {
            $dateAncienneteTexte = $dateAnciennete->format('d/m/Y');
            $message = "Proposable pour {$anneeProposition} (ancienneté atteinte le {$dateAncienneteTexte} - dans l'année)";
        } elseif ($detail) {
            $message = "Proposable pour {$anneeProposition} ({$detail})";
        } else {
            $message = "Proposable pour {$anneeProposition}";
        }
        
        return [
            'militaire' => [
                'id' => $militaire->id,
                'matricule' => $militaire->matricule,
                'nom' => $militaire->nom,
                'prenom' => $militaire->prenom,
                'grade_actuel' => $militaire->grade_actuel,
            ],
            'type' => 'Promotion',
            'grade_cible' => $gradeCible,
            'message' => $message,
            'date_estimation' => $dateProposition->format('Y-m-d'),
            'annee_proposition' => $anneeProposition,
            'date_anciennete' => $dateAnciennete ? $dateAnciennete->format('Y-m-d') : null,
        ];
    }

    /**
     * Formate une formation pour l'affichage.
     */
    private function formatFormation($militaire, $formation, $nomFormation, $dateProposition, $dateConditions = null, $conditionTexte = '')
    {
        $anneeProposition = $dateProposition->format('Y');
        
        if ($dateConditions) {
            $dateConditionsTexte = $dateConditions->format('d/m/Y');
            $message = "Proposable pour {$anneeProposition} (conditions remplies le {$dateConditionsTexte})";
        } elseif ($conditionTexte) {
            $message = "Proposable pour {$anneeProposition} ({$conditionTexte})";
        } else {
            $message = "Proposable pour {$anneeProposition}";
        }
        
        return [
            'militaire' => [
                'id' => $militaire->id,
                'matricule' => $militaire->matricule,
                'nom' => $militaire->nom,
                'prenom' => $militaire->prenom,
                'grade_actuel' => $militaire->grade_actuel,
            ],
            'formation' => $formation,
            'nom_formation' => $nomFormation,
            'message' => $message,
            'date_estimation' => $dateProposition->format('Y-m-d'),
            'annee_proposition' => $anneeProposition,
            'date_conditions' => $dateConditions ? $dateConditions->format('Y-m-d') : null,
        ];
    }

    /**
     * Retourne l'année de proposition (31 décembre de l'année en cours)
     */
    private function getDateProposition()
    {
        $annee = Carbon::now()->year;
        return Carbon::create($annee, 12, 31, 23, 59, 59);
    }

    /**
     * Exporte les éligibilités vers Excel.
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'all');
        $formation = $request->input('formation', '');
        $grade = $request->input('grade', '');
        
        $eligibilites = $this->getEligibilites($type, $formation, $grade);
        return Excel::download(new EligibilitesExport($eligibilites, $type), "eligibilites_{$type}.xlsx");
    }
}