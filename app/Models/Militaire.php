<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Militaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'date_naissance',
        'date_entree_service',
        'grade_actuel',
        'date_derniere_promotion',
        'specialite',
        'position_actuelle',
        'fonction_passee',
        'fonction_actuelle',
        'statut',
        'a_permis_conduire',
        'a_fait_justice',
        'a_fait_discipline',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_entree_service' => 'date',
        'date_derniere_promotion' => 'date',
        'a_permis_conduire' => 'boolean',
        'a_fait_justice' => 'boolean',
        'a_fait_discipline' => 'boolean',
    ];

    // Relations
    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    public function certificats(): BelongsToMany
    {
        return $this->belongsToMany(Certificat::class)
                    ->withPivot('date_obtention')
                    ->withTimestamps();
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    public function contratActif(): HasOne
    {
        return $this->hasOne(Contrat::class)->where('statut', 'actif')->latest('date_debut');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_actuel', 'nom_grade');
    }

    /**
     * Retourne la date de référence pour les calculs d'ancienneté (31 décembre de l'année en cours)
     */
    private static function getDateReference(): Carbon
    {
        $currentYear = Carbon::now()->year;
        return Carbon::create($currentYear, 12, 31, 23, 59, 59);
    }

    // Accesseurs
    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_naissance)->age;
    }

    /**
     * Calcule l'ancienneté totale au 31 décembre de l'année en cours
     */
    public function getAncienneteAttribute(): int
    {
        $dateReference = self::getDateReference();
        return Carbon::parse($this->date_entree_service)->diffInYears($dateReference);
    }

    /**
     * Calcule l'ancienneté dans le grade au 31 décembre de l'année en cours
     */
    public function getAncienneteGradeAttribute(): int
    {
        $dateReference = self::getDateReference();

        if ($this->date_derniere_promotion) {
            return Carbon::parse($this->date_derniere_promotion)->diffInYears($dateReference);
        }
        return $this->getAncienneteAttribute();
    }

    /**
     * Ancienneté détaillée (années et mois) au 31 décembre de l'année en cours
     */
    public function getAncienneteDetailleeAttribute(): string
    {
        if (!$this->date_entree_service) {
            return '';
        }

        $dateReference = self::getDateReference();
        $diff = $this->date_entree_service->diff($dateReference);
        $years = $diff->y;
        $months = $diff->m;

        return $years . ' ans ' . ($months > 0 ? $months . ' mois' : '');
    }

    /**
     * Ancienneté dans le grade détaillée (années et mois) au 31 décembre de l'année en cours
     */
    public function getAncienneteGradeDetailleeAttribute(): string
    {
        $dateReference = self::getDateReference();

        if ($this->date_derniere_promotion) {
            $diff = $this->date_derniere_promotion->diff($dateReference);
            $years = $diff->y;
            $months = $diff->m;
            return $years . ' ans ' . ($months > 0 ? $months . ' mois' : '');
        }

        return $this->getAncienneteDetailleeAttribute();
    }

    /**
     * Calcule la date de retraite en fonction du grade (ne sauvegarde pas en base)
     */
    public function calculerDateRetraite(): ?Carbon
    {
        $grade = Grade::where('nom_grade', $this->grade_actuel)->first();

        if ($grade && $grade->retraite_obligatoire) {
            $ageRetraite = $grade->retraite_obligatoire;
            return Carbon::parse($this->date_naissance)->addYears($ageRetraite);
        }

        return null;
    }

    /**
     * Vérifie si le militaire est éligible à la retraite (dans les 6 mois)
     */
    public function estEligibleRetraite(): bool
    {
        $dateRetraite = $this->calculerDateRetraite();
        if ($dateRetraite) {
            return Carbon::now()->diffInMonths($dateRetraite) <= 6;
        }
        return false;
    }

    /**
     * Vérifie si l'ancienneté dans le grade atteint un nombre d'années spécifique
     * (utile pour les vérifications de promotion)
     */
    public function aAncienneteGradeMin(int $annees): bool
    {
        $dateReference = self::getDateReference();

        if (!$this->date_derniere_promotion) {
            return false;
        }

        $datePromotion = Carbon::parse($this->date_derniere_promotion);
        $ageEnAnnees = $datePromotion->diffInYears($dateReference);

        // Vérifier si l'anniversaire de promotion est passé dans l'année
        $moisJourPromotion = $datePromotion->format('m-d');
        $moisJourReference = $dateReference->format('m-d');

        // Si la date anniversaire n'est pas encore passée au 31/12, on retire 1 an
        if ($moisJourPromotion > $moisJourReference) {
            $ageEnAnnees--;
        }

        return $ageEnAnnees >= $annees;
    }

    /**
     * Vérifie si l'ancienneté totale atteint un nombre d'années spécifique
     */
    public function aAncienneteMin(int $annees): bool
    {
        $dateReference = self::getDateReference();
        $dateEntree = Carbon::parse($this->date_entree_service);
        $ageEnAnnees = $dateEntree->diffInYears($dateReference);

        // Vérifier si l'anniversaire d'entrée est passé dans l'année
        $moisJourEntree = $dateEntree->format('m-d');
        $moisJourReference = $dateReference->format('m-d');

        if ($moisJourEntree > $moisJourReference) {
            $ageEnAnnees--;
        }

        return $ageEnAnnees >= $annees;
    }

    /**
     * Vérifie si le militaire a 5 ans de service (pour renouvellement de contrat)
     */
    public function aCinqAnsService(): bool
    {
        if (!$this->date_entree_service) {
            return false;
        }

        $dateEntree = Carbon::parse($this->date_entree_service);
        $serviceYears = $dateEntree->diffInYears(now());

        return $serviceYears >= 5;
    }

    /**
     * Récupère le contrat actif du militaire
     */
    public function getContratActifAttribute(): ?Contrat
    {
        return $this->contrats()->where('statut', 'actif')->latest('date_debut')->first();
    }
}
