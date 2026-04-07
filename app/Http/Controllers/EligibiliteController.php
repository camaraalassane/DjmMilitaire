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
     * Affiche la liste des éligibilités.
     */
    public function index()
    {
        $eligibilites = $this->getEligibilites();
        
        return Inertia::render('eligibilites/index', [
            'eligibilites' => $eligibilites
        ]);
    }

    /**
     * Exporte les éligibilités vers un fichier Excel.
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'all');
        $eligibilites = $this->getEligibilites($type);
        return Excel::download(new EligibilitesExport($eligibilites), "eligibilites_{$type}.xlsx");
    }

    /**
     * Calcule toutes les éligibilités (promotions, formations, retraites).
     */
    private function getEligibilites($type = 'all')
    {
        $militaires = Militaire::where('statut', 'actif')->with('certificats')->get();
        $eligibilites = [
            'promotions' => [],
            'formations' => [],
            'retraites' => [] 
        ];

        foreach ($militaires as $militaire) {
            $this->checkPromotions($militaire, $eligibilites);
            $this->checkFormations($militaire, $eligibilites);
            $this->checkRetraite($militaire, $eligibilites);
        }

        // Trier par date d'estimation
        usort($eligibilites['promotions'], fn($a, $b) => $a['date_estimation'] <=> $b['date_estimation']);
        usort($eligibilites['formations'], fn($a, $b) => $a['date_estimation'] <=> $b['date_estimation']);
        usort($eligibilites['retraites'], fn($a, $b) => $a['date_retraite'] <=> $b['date_retraite']);

        if ($type !== 'all') {
            return [$type => $eligibilites[$type]];
        }

        return $eligibilites;
    }

    /**
     * Vérifie les éligibilités aux promotions (changements de grade uniquement)
     * 
     * Les propositions se font 3 fois par an :
     * - 1er janvier : pour les dossiers préparés en octobre/novembre/décembre
     * - 1er octobre : pour les dossiers préparés en mai/juin/juillet/août/septembre/avril
     * - 1er avril : pour les dossiers préparés en janvier/février/mars
     */
    private function checkPromotions($militaire, &$eligibilites)
    {
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
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Caporal',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (5 ans d'ancienneté requis)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Caporal → Sergent (après CAT2)
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus) && $conditionsBase) {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 0, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Sergent',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (avoir CAT2 et être Caporal)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Caporal → Caporal-chef (âge ≥ 47 ans, 3 ans comme Caporal, CAT1, sans CAT2)
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47 && $conditionsBase) {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Caporal-chef',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (âge ≥ 47 ans, 3 ans comme Caporal, avoir CAT1)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Sergent → Sergent-Chef (2 ans de grade et 5 ans de service)
        if ($grade == 'Sergent' && $conditionsBase && $anciennete >= 5) {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Sergent-Chef',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (2 ans comme Sergent, 5 ans de service)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Sergent-Chef → Adjudant (3 ans de grade)
        if ($grade == 'Sergent-Chef' && $conditionsBase) {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Adjudant',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (3 ans d'ancienneté étant Sergent-Chef)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Adjudant → Adjudant-Chef (2 ans de grade)
        if ($grade == 'Adjudant' && $conditionsBase) {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Adjudant-Chef',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (2 ans d'ancienneté étant Adjudant)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Adjudant-Chef → Adjudant-Chef Major (CIA, BA1, BA2, âge ≥ 45)
        if ($grade == 'Adjudant-Chef' && in_array('CIA', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && in_array('BA2', $certificatsObtenus) && $age >= 45 && $conditionsBase) {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Adjudant-Chef Major',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (CIA, BA1, BA2 et âge ≥ 45 ans)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // === PROMOTIONS OFFICIERS ===
        
        // Sous-lieutenant → Lieutenant (2 ans)
        if ($grade == 'Sous-lieutenant') {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 2, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Lieutenant',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (2 ans au grade de Sous-lieutenant)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Lieutenant → Capitaine (3 ans)
        if ($grade == 'Lieutenant') {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Capitaine',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (3 ans au grade de Lieutenant)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Capitaine → Commandant (3 ans d'ancienneté)
        if ($grade == 'Capitaine') {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Commandant',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (3 ans d'ancienneté au grade de Capitaine)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Commandant → Lieutenant-colonel (3 ans)
        if ($grade == 'Commandant') {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Lieutenant-colonel',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (3 ans au grade de Commandant)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Lieutenant-colonel → Colonel (3 ans)
        if ($grade == 'Lieutenant-colonel') {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 3, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Colonel',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (3 ans au grade de Lieutenant-colonel)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // Colonel → Colonel-Major (6 ans)
        if ($grade == 'Colonel') {
            $dateProposition = $this->calculerDatePropositionParAncienneteGrade($militaire->date_derniere_promotion, 6, $moisActuel);
            if ($dateProposition) {
                $eligibilites['promotions'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'type' => 'Promotion',
                    'grade_cible' => 'Colonel-Major',
                    'message' => "Proposable au {$dateProposition->format('d/m/Y')} (6 ans d'ancienneté au grade de Colonel)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
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
                    $eligibilites['promotions'][] = [
                        'militaire' => [
                            'id' => $militaire->id,
                            'matricule' => $militaire->matricule,
                            'nom' => $militaire->nom,
                            'prenom' => $militaire->prenom,
                            'grade_actuel' => $militaire->grade_actuel,
                        ],
                        'type' => 'Promotion',
                        'grade_cible' => 'Sous-lieutenant',
                        'message' => "Proposable au {$dateProposition->format('d/m/Y')} (BA2, âge ≤ 45 ans, 15 ans de service)",
                        'date_estimation' => $dateProposition->format('Y-m-d'),
                    ];
                }
            }
        }
    }

    /**
     * Vérifie les éligibilités aux formations (cours, stages, diplômes)
     */
    private function checkFormations($militaire, &$eligibilites)
    {
        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $anciennete = $militaire->anciennete;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;
        
        $aujourdhui = Carbon::now();
        $moisActuel = (int)$aujourdhui->format('n');

        // === FORMATIONS SOUS-OFFICIERS ET MILITAIRES DU RANG ===
        
        // CAT1 : Formation pour devenir Caporal
        if ($grade == 'Soldat 1' && !in_array('CAT1', $certificatsObtenus) && $ancienneteGrade >= 5 && $conditionsBase) {
            $dateProposition = $this->getProchaineDateProposition($moisActuel);
            $eligibilites['formations'][] = [
                'militaire' => [
                    'id' => $militaire->id,
                    'matricule' => $militaire->matricule,
                    'nom' => $militaire->nom,
                    'prenom' => $militaire->prenom,
                    'grade_actuel' => $militaire->grade_actuel,
                ],
                'formation' => 'CAT1',
                'nom_formation' => 'Certificat d\'Aptitude Technique Niveau 1',
                'message' => "Proposable pour CAT1 au {$dateProposition->format('d/m/Y')} (5 ans d'ancienneté au grade de Soldat 1)",
                'date_estimation' => $dateProposition->format('Y-m-d'),
            ];
        }

        // CAT2 : Formation pour devenir Sergent
        if ($grade == 'Caporal' && $age < 47 && !in_array('CAT2', $certificatsObtenus) && $ancienneteGrade >= 3 && $conditionsBase && in_array('CAT1', $certificatsObtenus)) {
            $dateProposition = $this->getProchaineDateProposition($moisActuel);
            $eligibilites['formations'][] = [
                'militaire' => [
                    'id' => $militaire->id,
                    'matricule' => $militaire->matricule,
                    'nom' => $militaire->nom,
                    'prenom' => $militaire->prenom,
                    'grade_actuel' => $militaire->grade_actuel,
                ],
                'formation' => 'CAT2',
                'nom_formation' => 'Certificat d\'Aptitude Technique Niveau 2',
                'message' => "Proposable pour CAT2 au {$dateProposition->format('d/m/Y')} (3 ans d'ancienneté au grade de Caporal avec CAT1)",
                'date_estimation' => $dateProposition->format('Y-m-d'),
            ];
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
                $dateProposition = $this->getProchaineDateProposition($moisActuel);
                $message = "Proposable pour CIA au {$dateProposition->format('d/m/Y')} (permis de conduire requis)";
                if ($grade == 'Sergent') {
                    $message .= " - 3 ans de grade sous-officier";
                } elseif ($grade == 'Sergent-Chef') {
                    $message .= " - 1 an de grade sous-officier";
                }
                
                $eligibilites['formations'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'formation' => 'CIA',
                    'nom_formation' => 'Certificat d\'Instruction d\'Armes',
                    'message' => $message,
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
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
                $dateProposition = $this->getProchaineDateProposition($moisActuel);
                $eligibilites['formations'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'formation' => 'BA1',
                    'nom_formation' => 'Brevet d\'Aptitude Niveau 1',
                    'message' => "Proposable pour BA1 au {$dateProposition->format('d/m/Y')} (CIA depuis 3 ans et 8 ans de service)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
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
                $dateProposition = $this->getProchaineDateProposition($moisActuel);
                $eligibilites['formations'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'formation' => 'BA2',
                    'nom_formation' => 'Brevet d\'Aptitude Niveau 2',
                    'message' => "Proposable pour BA2 au {$dateProposition->format('d/m/Y')} (BA1, CT2 ou BMP1 depuis 3 ans)",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // === FORMATIONS OFFICIERS ===
        
        // 1. APLI (Cour d'Application)
        if (in_array($grade, ['Sous-lieutenant', 'Lieutenant', 'Capitaine']) 
            && !in_array('APLI', $certificatsObtenus) && !in_array('CFCU', $certificatsObtenus) && $age <= 50) {
            $dateProposition = $this->getProchaineDateProposition($moisActuel);
            $eligibilites['formations'][] = [
                'militaire' => [
                    'id' => $militaire->id,
                    'matricule' => $militaire->matricule,
                    'nom' => $militaire->nom,
                    'prenom' => $militaire->prenom,
                    'grade_actuel' => $militaire->grade_actuel,
                ],
                'formation' => 'APLI',
                'nom_formation' => 'Cour d\'Application',
                'message' => "Proposable pour APLI au {$dateProposition->format('d/m/Y')} (Grade minimum sous-lieutenant et âge ≤ 50 ans)",
                'date_estimation' => $dateProposition->format('Y-m-d'),
            ];
        }

        // 2. CFCU (Cour des Futurs Commandants d'Unité)
        if (in_array($grade, ['Lieutenant', 'Capitaine']) && !in_array('CFCU', $certificatsObtenus)) {
            $estEligible = false;
            $message = '';
            
            if ($grade == 'Capitaine') {
                $estEligible = true;
                $message = 'Capitaine éligible pour CFCU';
            } elseif (in_array('APLI', $certificatsObtenus)) {
                $estEligible = true;
                $message = 'Avoir fait APLI';
            }
            
            if ($estEligible) {
                $dateProposition = $this->getProchaineDateProposition($moisActuel);
                $eligibilites['formations'][] = [
                    'militaire' => [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                    ],
                    'formation' => 'CFCU',
                    'nom_formation' => 'Cour des Futurs Commandants d\'Unité',
                    'message' => "Proposable pour CFCU au {$dateProposition->format('d/m/Y')} ({$message})",
                    'date_estimation' => $dateProposition->format('Y-m-d'),
                ];
            }
        }

        // 3. CEM (Cour d'état-major)
        if (in_array($grade, ['Capitaine', 'Commandant']) && !in_array('CEM', $certificatsObtenus)) {
            if (($grade == 'Capitaine' && $ancienneteGrade >= 3) || $grade == 'Commandant') {
                if ($age <= 45) {
                    $dateProposition = $this->getProchaineDateProposition($moisActuel);
                    $eligibilites['formations'][] = [
                        'militaire' => [
                            'id' => $militaire->id,
                            'matricule' => $militaire->matricule,
                            'nom' => $militaire->nom,
                            'prenom' => $militaire->prenom,
                            'grade_actuel' => $militaire->grade_actuel,
                        ],
                        'formation' => 'CEM',
                        'nom_formation' => 'Cour d\'état-major',
                        'message' => "Proposable pour CEM au {$dateProposition->format('d/m/Y')} (Capitaine avec 3 ans ou commandant, âge ≤ 45)",
                        'date_estimation' => $dateProposition->format('Y-m-d'),
                    ];
                }
            }
        }

        // 4. Certificat d'État-major
        if (!in_array('Certificat État-major', $certificatsObtenus) && $grade == 'Commandant' && $age > 45) {
            $dateProposition = $this->getProchaineDateProposition($moisActuel);
            $eligibilites['formations'][] = [
                'militaire' => [
                    'id' => $militaire->id,
                    'matricule' => $militaire->matricule,
                    'nom' => $militaire->nom,
                    'prenom' => $militaire->prenom,
                    'grade_actuel' => $militaire->grade_actuel,
                ],
                'formation' => 'Certificat État-major',
                'nom_formation' => 'Certificat d\'État-major',
                'message' => "Proposable pour Certificat d'État-major au {$dateProposition->format('d/m/Y')} (Commandant et âge > 45 ans)",
                'date_estimation' => $dateProposition->format('Y-m-d'),
            ];
        }

        // 5. École de guerre
        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major']) 
            && !in_array('ECOLE_GUERRE', $certificatsObtenus) && $ancienneteGrade >= 2 && $age <= 53) {
            $dateProposition = $this->getProchaineDateProposition($moisActuel);
            $eligibilites['formations'][] = [
                'militaire' => [
                    'id' => $militaire->id,
                    'matricule' => $militaire->matricule,
                    'nom' => $militaire->nom,
                    'prenom' => $militaire->prenom,
                    'grade_actuel' => $militaire->grade_actuel,
                ],
                'formation' => 'École de guerre',
                'nom_formation' => 'École de guerre',
                'message' => "Proposable pour l'École de guerre au {$dateProposition->format('d/m/Y')} (Lieutenant-colonel avec 2 ans d'ancienneté, âge ≤ 53)",
                'date_estimation' => $dateProposition->format('Y-m-d'),
            ];
        }
    }

    /**
     * Vérifie les retraites proches (dans les 12 mois).
     */
    private function checkRetraite($militaire, &$eligibilites)
    {
        $dateRetraite = $militaire->calculerDateRetraite();
        
        if ($dateRetraite) {
            $aujourdhui = Carbon::now()->startOfDay();
            $dateRetraiteCarbon = Carbon::parse($dateRetraite)->startOfDay();
            
            $diffJours = $aujourdhui->diffInDays($dateRetraiteCarbon);
            $moisRestants = floor($diffJours / 30);
            $joursRestants = $diffJours % 30;
            
            if ($moisRestants <= 12 && $diffJours >= 0) {
                $moisFormate = $this->formaterMoisRestants($moisRestants, $joursRestants);
                
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
                    'jours_restants' => $joursRestants,
                    'mois_restants_formate' => $moisFormate,
                ];
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
}