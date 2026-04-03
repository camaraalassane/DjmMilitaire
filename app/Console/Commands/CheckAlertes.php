<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Militaire;
use App\Models\Alerte;
use App\Models\Eligibilite;
use Carbon\Carbon;

class CheckAlertes extends Command
{
    protected $signature = 'alertes:check';
    protected $description = 'Vérifier et générer les alertes pour tous les militaires';

    public function handle()
    {
        \Log::info('Début de la commande alertes:check');

        $militaires = Militaire::where('statut', 'actif')->get();
        \Log::info('Nombre de militaires actifs : ' . $militaires->count());
        $totalPromo = 0;
        $totalForm = 0;
        $totalRetraite = 0;

        foreach ($militaires as $militaire) {
            // Vérifier les promotions
            $totalPromo += $this->checkPromotions($militaire);
            
            // Vérifier les formations (certificats)
            $totalForm + $this->checkFormationsOfficiers($militaire);
            
            // Vérifier les retraites
            $totalRetraite += $this->checkRetraites($militaire);
        }

        $total = $totalPromo + $totalForm + $totalRetraite;

        \Log::info("Alertes générées", [
            'promotions' => $totalPromo,
            'formations' => $totalForm,
            'retraites' => $totalRetraite,
            'total' => $total
        ]);

        $this->info("{$total} nouvelles alertes créées.");
        
        // Supprimer les vieilles alertes vues
        $vieillesAlertes = Alerte::where('est_vue', true)
            ->where('created_at', '<', Carbon::now()->subMonths(3))
            ->delete();
            
        $this->info("Anciennes alertes supprimées.");
    }

    private function checkPromotions(Militaire $militaire)
    {
        $alertes = 0;
        if ($militaire->statut !== 'actif') return $alertes;

        $grade = $militaire->grade_actuel;
        $anciennete = $militaire->anciennete;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $conditionsBase = !$militaire->a_fait_justice && !$militaire->a_fait_discipline;

        // Récupérer les niveaux de certificats obtenus
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();

        // CAT1 - Soldat vers Caporal
        if ($grade == 'Soldat 1' && !in_array('CAT1', $certificatsObtenus) && $anciennete >= 5 && $conditionsBase) {
            $dateEligibilite = Carbon::now(); // ou une date future si condition remplie maintenant
            if ($this->updateEligibilite($militaire, 'certificat', 'CAT1', $dateEligibilite)) {
                $this->createAlerte($militaire, 'formation', 
                    "Proposable pour CAT1 : 5 ans d'ancienneté atteints", 
                    Carbon::now()->addMonth());
                $alertes++;
            }
        }

        // CAT2 - Caporal vers Sergent
        if ($grade == 'Caporal' && in_array('CAT1', $certificatsObtenus) && !in_array('CAT2', $certificatsObtenus) && $conditionsBase){
            if ($ancienneteGrade >= 3 && $age < 47) {
                $this->creerAlerte($militaire, 'promotion',
                    "Proposable pour CAT2 (3 ans au grade de Caporal avec CAT1)",
                    Carbon::now()->addMonth());
            }else if ($age >= 47) {
                $this->creerAlerte($militaire, 'promotion',
                    "Proposable pour Caporal-chef (avoir plus de 47 ans etant caporal)",
                    Carbon::now()->addMonth());
            }
        }

        // CIA - Sergent vers Sergent-Chef
        if (in_array($grade, ['Sergent', 'Sergent-Chef']) && !in_array('CIA', $certificatsObtenus) && $militaire->a_permis_conduire && $ancienneteGrade >= 3 && $conditionsBase) {
            $this->createAlerte($militaire, 'promotion',
                "Proposable pour CIA (3 ans comme sous-officier et permis)",
                Carbon::now()->addMonth());
            $alertes++;
        }

        // BA1 - Sergent-Chef vers Adjudant
        if ($grade == 'Sergent-Chef' && in_array('CIA', $certificatsObtenus) && !in_array('BA1', $certificatsObtenus)) {
            $cia = $militaire->certificats->where('niveau_certificat', 'CIA')->first();
            $anneesDepuisCIA = $cia && $cia->pivot->date_obtention
                ? Carbon::parse($cia->pivot->date_obtention)->diffInYears(Carbon::now())
                : 0;

            if ($anneesDepuisCIA >= 3 && $anciennete >= 8 && $conditionsBase) {
                $this->createAlerte($militaire, 'promotion',
                    "Proposable pour BA1 (CIA depuis 3 ans et 8 ans de service)",
                    Carbon::now()->addMonth());
                $alertes++;
            }
        }

        // BA2 - Adjudant vers Adjudant-Chef
        if ($grade == 'Adjudant' && !in_array('BA2', $certificatsObtenus) && !in_array('BMP1', $certificatsObtenus) && !in_array('BMP2', $certificatsObtenus) && !in_array('BS', $certificatsObtenus) && !in_array('CT2', $certificatsObtenus)) {
            $ba1 = $militaire->certificats->where('niveau_certificat', 'BA1')->first();
            $anneesDepuisBA1 = $ba1 && $ba1->pivot->date_obtention
                ? Carbon::parse($ba1->pivot->date_obtention)->diffInYears(Carbon::now())
                : 0;

            if ($anneesDepuisBA1 >= 3 && $conditionsBase) {
                // BA2 (ne nécessite pas le CIA)
                $this->createAlerte($militaire, 'promotion',
                    "Éligible pour Adjudant-Chef (BA2)",
                    Carbon::now()->addMonth());
                $alertes++;

                // Voies équivalentes nécessitant le CIA
                if (in_array('CIA', $certificatsObtenus)) {
                    $this->createAlerte($militaire, 'promotion',
                        "Éligible pour Adjudant-Chef (par équivalent BMP1, BMP2, BS, CT2 avec CIA)",
                        Carbon::now()->addMonth());
                    $alertes++;
                }
            }
        }

        return $alertes;
    }

    private function checkFormationsOfficiers(Militaire $militaire)
    {
        $alertes = 0;
        if ($militaire->statut !== 'actif') return $alertes;

        $grade = $militaire->grade_actuel;
        $age = $militaire->age;
        $ancienneteGrade = $militaire->ancienneteGrade;
        $certificatsObtenus = $militaire->certificats->pluck('niveau_certificat')->toArray();

        // 1. APLI
        if (in_array($grade, ['Sous-lieutenant', 'Lieutenant', 'Capitaine', 'Commandant', 'Lieutenant-colonel', 'Colonel', 'Colonel-Major']) && !in_array('APLI', $certificatsObtenus) && $age <= 50) {
            $this->createAlerte($militaire, 'formation',
                "Proposable pour Cour d'Application (APLI) - grade minimum sous-lieutenant et âge ≤ 50 ans",
                Carbon::now()->addMonth());
            $alertes++;
        }

        // 2. CFCU
        if (in_array($grade, ['Capitaine', 'Commandant', 'Lieutenant-colonel', 'Colonel']) && !in_array('CFCU', $certificatsObtenus) && (in_array('APLI', $certificatsObtenus) || $grade == 'Capitaine')) {
            $this->createAlerte($militaire, 'formation',
                "Proposable pour Cour des Futurs Commandants d'Unité (CFCU) ou (CPO) - avoir fait APLI ou être capitaine",
                Carbon::now()->addMonth());
            $alertes++;
        }

        // 3. CEM
        if (in_array($grade, ['Capitaine', 'Commandant']) && !in_array('CEM', $certificatsObtenus)) {
            if (($grade == 'Capitaine' && $ancienneteGrade >= 3) || $grade == 'Commandant') {
                if ($age <= 45) {
                    $this->createAlerte($militaire, 'formation',
                        "Proposable pour Cour d'état-major (CEM) - capitaine avec 3 ans ou commandant, âge ≤ 45",
                        Carbon::now()->addMonth());
                    $alertes++;
                }
            }
        }

        // 4. Certificat d'État-major
        if ($grade == 'Commandant' && !in_array('Certificat état-major', $certificatsObtenus) && $age > 45) {
            $this->createAlerte($militaire, 'formation',
                "Proposable pour Certificat d'État-major - commandant et âge > 45 ans",
                Carbon::now()->addMonth());
            $alertes++;
        }

        // 5. École de guerre
        if (in_array($grade, ['Lieutenant-colonel', 'Colonel', 'Colonel-Major', 'Général de brigade']) && !in_array('École de guerre', $certificatsObtenus) && $ancienneteGrade >= 2 && $age <= 53) {
            $this->createAlerte($militaire, 'formation',
                "Proposable pour l'École de guerre - lieutenant-colonel avec 2 ans d'ancienneté, âge ≤ 53",
                Carbon::now()->addMonth());
            $alertes++;
        }

        return $alertes;
    }

    private function checkFormations(Militaire $militaire)
    {
        // Si vous avez d'autres formations obligatoires, ajoutez-les ici
        return 0;
    }

    /**
     * Vérifie les retraites proches (dans les 6 mois)
     */
    private function checkRetraites(Militaire $militaire)
    {
        if ($militaire->estEligibleRetraite()) {
            $moisRestants = Carbon::now()->diffInMonths($militaire->date_retraite);
            if ($moisRestants <= 6 && $moisRestants >= 0) {
                $this->createAlerte($militaire, 'retraite',
                    "Retraite dans {$moisRestants} mois (le {$militaire->date_retraite->format('d/m/Y')})",
                    $militaire->date_retraite);
                return 1;
            }
        }
        return 0;
    }

    /**
     * Met à jour ou insère une éligibilité selon la règle :
     * - Si l'enregistrement n'existe pas, on le crée.
     * - S'il existe, on compare la nouvelle date avec l'ancienne.
     *   On met à jour uniquement si nouvelle date >= ancienne date.
     *
     * @return bool True si l'enregistrement a été créé ou mis à jour, false sinon.
     */
    private function updateEligibilite($militaire, $type, $cible, $dateEligibilite)
    {
        $existing = Eligibilite::where('militaire_id', $militaire->id)
            ->where('type', $type)
            ->where('cible', $cible)
            ->first();

        if (!$existing) {
            // Création
            Eligibilite::create([
                'militaire_id' => $militaire->id,
                'type' => $type,
                'cible' => $cible,
                'date_eligibilite' => $dateEligibilite,
            ]);
            return true;
        }

        // Mise à jour seulement si nouvelle date >= ancienne
        if ($dateEligibilite->greaterThanOrEqualTo($existing->date_eligibilite)) {
            $existing->update(['date_eligibilite' => $dateEligibilite]);
            return true;
        }

        return false; // Pas de mise à jour
    }

    /**
     * Crée une alerte si elle n'existe pas déjà
     */
    private function createAlerte($militaire, $type, $message, $dateEcheance)
    {
        $existe = Alerte::where('militaire_id', $militaire->id)
            ->where('type_alerte', $type)
            ->where('est_vue', false)
            ->where('message', $message)
            ->exists();

        if (!$existe) {
            Alerte::create([
                'militaire_id' => $militaire->id,
                'type_alerte' => $type,
                'message' => $message,
                'date_echeance' => $dateEcheance,
            ]);
        }
    }
}