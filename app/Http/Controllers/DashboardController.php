<?php

namespace App\Http\Controllers;

use App\Models\Militaire;
use App\Models\Alerte;
use App\Models\Grade;
use App\Models\Certificat;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProposablesAnneeNExport;
use App\Exports\ProposablesAnneeN1Export;

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
        $militairesActifs = Militaire::where('statut', 'actif')->with('certificats')->get();
        
        // Calculer les retraites proches (dans les 12 mois)
        $aujourdhui = Carbon::now()->startOfDay();
        
        $militairesProchesRetraite = $militairesActifs
            ->map(function ($militaire) use ($aujourdhui) {
                $dateRetraite = $militaire->calculerDateRetraite();
                if ($dateRetraite) {
                    $diffJours = $aujourdhui->diffInDays($dateRetraite);
                    $moisRestants = floor($diffJours / 30);
                    $joursRestants = $diffJours % 30;
                    
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

        // Calculer les militaires proposables pour l'année N (année en cours)
        $proposablesAnneeN = $this->calculerProposablesPourAnnee($militairesActifs, 0);
        
        // Récupérer les IDs des militaires déjà proposables dans l'année N
        $idsProposablesAnneeN = [];
        foreach (['janvier', 'avril', 'octobre'] as $periode) {
            if (isset($proposablesAnneeN[$periode]['proposables'])) {
                foreach ($proposablesAnneeN[$periode]['proposables'] as $proposable) {
                    $idsProposablesAnneeN[] = $proposable['id'];
                }
            }
        }
        $idsProposablesAnneeN = array_unique($idsProposablesAnneeN);
        
        // Calculer les militaires proposables pour l'année N+1 (année prochaine)
        // En excluant ceux déjà proposables dans l'année N
        $proposablesAnneeN1 = $this->calculerProposablesPourAnnee($militairesActifs, 1, $idsProposablesAnneeN);

        $statistiques = [
            'total_militaires' => Militaire::count(),
            'militaires_actifs' => $militairesActifs->count(),
            'alertes_non_vues' => Alerte::where('est_vue', false)->count(),
            'prochaines_retraites' => $militairesProchesRetraite->count(),
            'total_proposables_n' => $proposablesAnneeN['total'],
            'total_proposables_n1' => $proposablesAnneeN1['total'],
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
            'proposablesAnneeN' => $proposablesAnneeN,
            'proposablesAnneeN1' => $proposablesAnneeN1,
            'statistiques' => $statistiques,
            'grades' => $grades
        ]);
    }

    /**
     * Calcule les militaires proposables pour une année donnée (N ou N+1)
     * Périodes: Janvier, Avril, Octobre de l'année cible
     * 
     * @param $militairesActifs Collection des militaires actifs
     * @param int $decalageAnnees 0 pour année N, 1 pour année N+1
     * @param array $idsExclus IDs des militaires à exclure (ceux déjà proposables dans l'année précédente)
     */
   /**
 * Calcule les militaires proposables pour une année donnée (N ou N+1)
 * Périodes: Janvier, Avril, Octobre de l'année cible
 */
private function calculerProposablesPourAnnee($militairesActifs, $decalageAnnees, $idsExclus = [])
{
    $aujourdhui = Carbon::now();
    $anneeCible = $aujourdhui->year + $decalageAnnees;
    
    // Définir les 3 périodes de l'année cible avec leurs couleurs distinctes
    $periodes = [
        'janvier' => [
            'nom' => 'Proposition du 1er Janvier',
            'date' => Carbon::create($anneeCible, 1, 1),
            'ordre' => 1,
            'couleur' => 'bg-purple-500',
            'couleur_texte' => 'text-purple-500',
            'couleur_border' => 'border-purple-500',
            'icon' => 'pi-calendar-plus'
        ],
        'avril' => [
            'nom' => 'Proposition du 1er Avril',
            'date' => Carbon::create($anneeCible, 4, 1),
            'ordre' => 2,
            'couleur' => 'bg-green-500',
            'couleur_texte' => 'text-green-500',
            'couleur_border' => 'border-green-500',
            'icon' => 'pi-calendar-plus'
        ],
        'octobre' => [
            'nom' => 'Proposition du 1er Octobre',
            'date' => Carbon::create($anneeCible, 10, 1),
            'ordre' => 3,
            'couleur' => 'bg-blue-500',
            'couleur_texte' => 'text-blue-500',
            'couleur_border' => 'border-blue-500',
            'icon' => 'pi-calendar'
        ]
    ];
    
    $resultats = [];
    $totalProposables = 0;
    $militairesDejaProposables = [];
    
    foreach ($periodes as $key => $periode) {
        $proposables = [];
        $datePeriode = $periode['date'];
        $estPeriodePassee = $datePeriode < $aujourdhui;
        
        foreach ($militairesActifs as $militaire) {
            // Exclure les militaires déjà proposables dans l'année précédente
            if (in_array($militaire->id, $idsExclus)) {
                continue;
            }
            
            // Exclure les militaires déjà proposables dans une période antérieure de la même année
            if (in_array($militaire->id, $militairesDejaProposables)) {
                continue;
            }
            
            // Vérifier si le militaire est proposable pour cette période
            $estProposable = $this->verifierProposablePourPeriode($militaire, $datePeriode);
            
            if ($estProposable) {
                $gradeCible = $this->getGradeCible($militaire);
                if ($gradeCible) {
                    $proposables[] = [
                        'id' => $militaire->id,
                        'matricule' => $militaire->matricule,
                        'nom' => $militaire->nom,
                        'prenom' => $militaire->prenom,
                        'grade_actuel' => $militaire->grade_actuel,
                        'grade_cible' => $gradeCible,
                        'date_proposition' => $datePeriode->format('Y-m-d'),
                        'date_derniere_promotion' => $militaire->date_derniere_promotion ? Carbon::parse($militaire->date_derniere_promotion)->format('d/m/Y') : null,
                    ];
                    $militairesDejaProposables[] = $militaire->id;
                }
            }
        }
        
        $resultats[$key] = [
            'nom' => $periode['nom'],
            'date' => $datePeriode->format('Y-m-d'),
            'date_formatted' => $datePeriode->format('d/m/Y'),
            'couleur' => $periode['couleur'],
            'couleur_texte' => $periode['couleur_texte'],
            'couleur_border' => $periode['couleur_border'],
            'icon' => $periode['icon'],
            'proposables' => $proposables,
            'count' => count($proposables),
            'est_passee' => $estPeriodePassee
        ];
        
        $totalProposables += count($proposables);
    }
    
    // Réorganiser dans l'ordre chronologique (Janvier, Avril, Octobre)
    $ordrePeriodes = ['janvier', 'avril', 'octobre'];
    $resultatsOrdonnes = [];
    foreach ($ordrePeriodes as $periodeKey) {
        if (isset($resultats[$periodeKey])) {
            $resultatsOrdonnes[$periodeKey] = $resultats[$periodeKey];
        }
    }
    $resultatsOrdonnes['total'] = $totalProposables;
    $resultatsOrdonnes['annee'] = $anneeCible;
    
    return $resultatsOrdonnes;
}
    /**
     * Vérifie si un militaire est proposable pour une période spécifique
     */
    private function verifierProposablePourPeriode($militaire, $dateProposition)
    {
        // Calculer la date à laquelle les conditions sont remplies
        $dateConditionsRemplies = $this->getDateConditionsRemplies($militaire, $dateProposition);
        
        if (!$dateConditionsRemplies) {
            return false;
        }
        
        // Le militaire est proposable si la date des conditions est atteinte avant ou à la date de proposition
        // Et que la date des conditions est postérieure à la dernière date de proposition traitée
        return $dateConditionsRemplies <= $dateProposition;
    }

    /**
     * Calcule la date à laquelle les conditions de promotion sont remplies
     */
    private function getDateConditionsRemplies($militaire, $dateProposition)
    {
        $grade = $militaire->grade_actuel;
        $anciennete = $militaire->anciennete;
        $age = $militaire->age;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $dateDernierePromotion = $militaire->date_derniere_promotion;
        $dateEntreeService = $militaire->date_entree_service;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        
        // Récupérer le nombre d'années requis pour la promotion
        $anneesRequises = $this->getAnneesRequisesPourPromotion($grade, $militaire);
        
        if (!$anneesRequises) {
            return null;
        }
        
        // La date de référence est la date de dernière promotion ou la date d'entrée en service
        $dateReference = $dateDernierePromotion ?? $dateEntreeService;
        
        if (!$dateReference) {
            return null;
        }
        
        // Calculer la date à laquelle les années requises sont atteintes
        $dateConditionsRemplies = Carbon::parse($dateReference)->addYears($anneesRequises);
        
        return $dateConditionsRemplies;
    }
    
    /**
     * Récupère le nombre d'années requises pour la promotion selon le grade
     */
    private function getAnneesRequisesPourPromotion($grade, $militaire)
    {
        $age = $militaire->age;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        
        switch ($grade) {
            case 'Soldat 1':
                if (in_array('CAT1', $certificatsObtenus)) {
                    return 5;
                }
                break;
                
            case 'Caporal':
                if (in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus)) {
                    return 3;
                }
                if (in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47) {
                    return 3;
                }
                break;
                
            case 'Sergent':
                return 2;
                
            case 'Sergent-Chef':
                return 3;
                
            case 'Adjudant':
                return 2;
                
            case 'Adjudant-Chef':
                $certificatsRequis = in_array('CIA', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && in_array('BA2', $certificatsObtenus);
                if ($certificatsRequis && $age >= 45) {
                    return 2;
                }
                if (in_array('BA2', $certificatsObtenus) && $age <= 45) {
                    return 2;
                }
                break;
                
            case 'Sous-lieutenant':
                return 2;
                
            case 'Lieutenant':
                return 3;
                
            case 'Capitaine':
                return 3;
                
            case 'Commandant':
                return 3;
                
            case 'Lieutenant-colonel':
                return 3;
                
            case 'Colonel':
                return 6;
        }
        
        return null;
    }

    /**
     * Récupère le grade cible pour un militaire
     */
    private function getGradeCible($militaire)
    {
        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();
        
        $mapGrades = [
            'Soldat 1' => 'Caporal',
            'Caporal' => function() use ($certificatsObtenus, $age, $ancienneteGrade) {
                if (in_array('CAT1', $certificatsObtenus) && in_array('CAT2', $certificatsObtenus)) return 'Sergent';
                if (in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $age >= 47 && $ancienneteGrade >= 3) return 'Caporal-chef';
                return null;
            },
            'Sergent' => 'Sergent-Chef',
            'Sergent-Chef' => 'Adjudant',
            'Adjudant' => 'Adjudant-Chef',
            'Adjudant-Chef' => function() use ($certificatsObtenus, $age) {
                if (in_array('CIA', $certificatsObtenus) && in_array('BA1', $certificatsObtenus) && in_array('BA2', $certificatsObtenus) && $age >= 45) return 'Adjudant-Chef Major';
                if (in_array('BA2', $certificatsObtenus) && $age <= 45) return 'Sous-lieutenant';
                return null;
            },
            'Sous-lieutenant' => 'Lieutenant',
            'Lieutenant' => 'Capitaine',
            'Capitaine' => 'Commandant',
            'Commandant' => 'Lieutenant-colonel',
            'Lieutenant-colonel' => 'Colonel',
            'Colonel' => 'Colonel-Major',
        ];
        
        if (isset($mapGrades[$grade])) {
            $cible = $mapGrades[$grade];
            return is_callable($cible) ? $cible() : $cible;
        }
        
        return null;
    }

    /**
     * Exporte les militaires proposables pour l'année N
     */
    public function exportProposablesAnneeN()
    {
        $militairesActifs = Militaire::where('statut', 'actif')->with('certificats')->get();
        $proposablesAnneeN = $this->calculerProposablesPourAnnee($militairesActifs, 0);
        
        return Excel::download(new ProposablesAnneeNExport($proposablesAnneeN), 'proposables_annee_' . $proposablesAnneeN['annee'] . '_' . Carbon::now()->format('Y_m_d') . '.xlsx');
    }

    /**
     * Exporte les militaires proposables pour l'année N+1
     */
    public function exportProposablesAnneeN1()
    {
        $militairesActifs = Militaire::where('statut', 'actif')->with('certificats')->get();
        
        // D'abord calculer l'année N pour connaître les exclus
        $proposablesAnneeN = $this->calculerProposablesPourAnnee($militairesActifs, 0);
        $idsExclus = [];
        foreach (['janvier', 'avril', 'octobre'] as $periode) {
            if (isset($proposablesAnneeN[$periode]['proposables'])) {
                foreach ($proposablesAnneeN[$periode]['proposables'] as $proposable) {
                    $idsExclus[] = $proposable['id'];
                }
            }
        }
        $idsExclus = array_unique($idsExclus);
        
        $proposablesAnneeN1 = $this->calculerProposablesPourAnnee($militairesActifs, 1, $idsExclus);
        
        return Excel::download(new ProposablesAnneeN1Export($proposablesAnneeN1), 'proposables_annee_' . $proposablesAnneeN1['annee'] . '_' . Carbon::now()->format('Y_m_d') . '.xlsx');
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
}