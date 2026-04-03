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
     */
    private function checkPromotions($militaire, &$eligibilites)
    {
        $grade = $militaire->grade_actuel;
        $anciennete = $militaire->anciennete;
        $age = $militaire->age;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;

        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();

        // === PROMOTIONS SOUS-OFFICIERS ET MILITAIRES DU RANG ===
        
        // Soldat 1 → Caporal (après CAT1)
        if ($grade == 'Soldat 1' && in_array('CAT1', $certificatsObtenus) && $anciennete >= 5 && $conditionsBase) {
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
                'message' => '5 ans d\'ancienneté',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Caporal → Sergent (après CAT2)
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus) && $conditionsBase) {
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
                'message' => 'Avoir CAT2 et être Caporal',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Caporal → Caporal-chef (âge ≥ 47 ans, 3 ans comme Caporal, CAT1, sans CAT2)
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47 && $ancienneteGrade >= 3 && $conditionsBase) {
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
                'message' => 'Âge ≥ 47 ans, 3 ans comme Caporal, avoir CAT1',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Sergent → Sergent-Chef (2 ans de grade et 5 ans de service)
        if ($grade == 'Sergent' && $conditionsBase && $ancienneteGrade >= 2 && $anciennete >= 5) {
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
                'message' => '2 ans comme Sergent, 5 ans de service',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Sergent-Chef → Adjudant (3 ans de grade)
        if ($grade == 'Sergent-Chef' && $ancienneteGrade >= 3 && $conditionsBase) {
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
                'message' => '3 ans d\'ancienneté étant Sergent-Chef',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Adjudant → Adjudant-Chef (2 ans de grade)
        if ($grade == 'Adjudant' && $ancienneteGrade >= 2 && $conditionsBase) {
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
                'message' => '2 ans d\'ancienneté étant Adjudant',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Adjudant-Chef → Adjudant-Chef Major (CIA, BA1, BA2, âge ≥ 45)
        if ($grade == 'Adjudant-Chef' && $ancienneteGrade >= 2 && in_array('CIA', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && in_array('BA2', $certificatsObtenus) && $age >= 45) {
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
                'message' => 'CIA, BA1, BA2 et âge ≥ 45 ans',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // === PROMOTIONS OFFICIERS ===
        
        // Sous-lieutenant → Lieutenant
        if ($grade == 'Sous-lieutenant' && $ancienneteGrade == 2) {
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
                'message' => '2 ans au grade de Sous-lieutenant',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Lieutenant → Capitaine
        if ($grade == 'Lieutenant' && $ancienneteGrade >= 3) {
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
                'message' => '3 ans au grade de Lieutenant',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

       // Capitaine → Commandant (3 ans d'ancienneté dans le grade)
if ($grade == 'Capitaine' && $ancienneteGrade >= 3) {
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
        'message' => '3 ans d\'ancienneté au grade de Capitaine',
        'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
    ];
}

        // Commandant → Lieutenant-colonel
        if ($grade == 'Commandant' && $ancienneteGrade >= 3) {
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
                'message' => '3 ans au grade de Commandant',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Lieutenant-colonel → Colonel
        if ($grade == 'Lieutenant-colonel' && $ancienneteGrade >= 3) {
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
                'message' => '3 ans au grade de Lieutenant-colonel',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Colonel → Colonel-Major
        if ($grade == 'Colonel' && $ancienneteGrade >= 6) {
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
                'message' => '6 ans d\'ancienneté au grade de Colonel',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

        // Adjudant-Chef vers Sous-lieutenant (passage sous-officier → officier)
        if (in_array('BA2', $certificatsObtenus)) {
            if (($grade == 'Adjudant-Chef' && $age <= 45 && $anciennete >= 15 && $ancienneteGrade >= 2)
                || ($grade == 'Adjudant-Chef major' && $ancienneteGrade >= 2)) {
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
                    'message' => 'BA2, âge ≤ 45 ans, 15 ans de service',
                    'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
                ];
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

        // === FORMATIONS SOUS-OFFICIERS ET MILITAIRES DU RANG ===
        
        // CAT1 : Formation pour devenir Caporal
        if ($grade == 'Soldat 1' && !in_array('CAT1', $certificatsObtenus) && $ancienneteGrade >= 5 && $conditionsBase) {
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
                'message' => '5 ans d\'ancienneté au grade de Soldat 1',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

      // CAT2 : Formation pour devenir Sergent
// Un caporal ne peut pas faire CAT2 si âge >= 47 ans (car il deviendrait caporal-chef)
if ($grade == 'Caporal' && $age < 47 && !in_array('CAT2', $certificatsObtenus) && $ancienneteGrade >= 3 && $conditionsBase && in_array('CAT1', $certificatsObtenus)) {
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
        'message' => '3 ans d\'ancienneté au grade de Caporal avec CAT1',
        'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
    ];
}

        // CIA : Certificat d'Instruction d'Armes
        $gradesCIA = ['Sergent', 'Sergent-Chef', 'Adjudant', 'Adjudant-Chef', 'Adjudant-Chef major'];
        if (in_array($grade, $gradesCIA) && !in_array('CIA', $certificatsObtenus) && $conditionsBase && $militaire->a_permis_conduire) {
            // Vérifier l'ancienneté selon le grade
            $ancienneteOk = match($grade) {
                'Sergent' => $ancienneteGrade >= 3,
                'Sergent-Chef' => $ancienneteGrade >= 1,
                'Adjudant', 'Adjudant-Chef', 'Adjudant-Chef major' => true,
                default => false
            };
            
            if ($ancienneteOk) {
                $message = "Proposable pour CIA (permis de conduire requis)";
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
                    'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
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
                    'message' => 'CIA depuis 3 ans et 8 ans de service',
                    'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
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
                    'message' => 'BA1, CT2 ou BMP1 depuis 3 ans',
                    'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
                ];
            }
        }

        // === FORMATIONS OFFICIERS ===
        
        // 1. APLI (Cour d'Application)
        if (in_array($grade, ['Sous-lieutenant', 'Lieutenant', 'Capitaine', 'Commandant', 'Lieutenant-colonel', 'Colonel', 'Colonel-Major']) 
            && !in_array('APLI', $certificatsObtenus) && $age <= 50) {
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
                'message' => 'Grade minimum sous-lieutenant et âge ≤ 50 ans',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }

       // 2. CFCU (Cour des Futurs Commandants d'Unité)
// Peut être fait par les capitaines (sans APLI) ou par les autres grades avec APLI
if (in_array($grade, ['Lieutenant', 'Capitaine']) 
    && !in_array('CFCU', $certificatsObtenus)) {
    
    $estEligible = false;
    $message = '';
    
    // Cas 1: Capitaine - peut faire CFCU sans APLI
    if ($grade == 'Capitaine') {
        $estEligible = true;
        $message = 'Capitaine éligible pour CFCU';
    }
    // Cas 2: Autres grades (Lieutenant) - nécessitent APLI
    elseif (in_array('APLI', $certificatsObtenus)) {
        $estEligible = true;
        $message = 'Avoir fait APLI';
    }
    
    if ($estEligible) {
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
            'message' => $message,
            'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
        ];
    }
}
       // 3. CEM (Cour d'état-major)
// Formation pour les capitaines (3 ans) et commandants (âge ≤ 45)
if (in_array($grade, ['Capitaine', 'Commandant']) && !in_array('CEM', $certificatsObtenus)) {
    if (($grade == 'Capitaine' && $ancienneteGrade >= 3) || $grade == 'Commandant') {
        if ($age <= 45) {
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
                'message' => 'Capitaine avec 3 ans ou commandant, âge ≤ 45',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }
    }
}

// 4. Certificat d'État-major
// Formation pour les commandants avec âge > 45 ans (alternative au CEM)
if (!in_array('Certificat État-major', $certificatsObtenus) && $grade == 'Commandant' && $age > 45) {
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
        'message' => 'Commandant et âge > 45 ans',
        'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
    ];
}

        // 5. École de guerre
        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major']) 
            && !in_array('ECOLE_GUERRE', $certificatsObtenus) && $ancienneteGrade >= 2 && $age <= 53) {
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
                'message' => 'Lieutenant-colonel avec 2 ans d\'ancienneté, âge ≤ 53',
                'date_estimation' => Carbon::now()->addMonth()->format('Y-m-d'),
            ];
        }
    }

    /**
 * Vérifie les retraites proches (dans les 12 mois).
 */
private function checkRetraite($militaire, &$eligibilites)
{
    // Calculer la date de retraite dynamiquement
    $dateRetraite = $militaire->calculerDateRetraite();
    
    if ($dateRetraite) {
        $aujourdhui = Carbon::now()->startOfDay();
        $dateRetraiteCarbon = Carbon::parse($dateRetraite)->startOfDay();
        
        $diffJours = $aujourdhui->diffInDays($dateRetraiteCarbon);
        $moisRestants = floor($diffJours / 30);
        $joursRestants = $diffJours % 30;
        
        // Changement ici : de 6 à 12 mois
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