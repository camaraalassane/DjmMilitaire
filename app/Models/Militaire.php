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
        'telephone',
        'sexe',
        'groupe_sanguin',
        'personne_a_contacter',
        'telephone_personne_contacter',
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

    // =========================================================
    // RELATIONS
    // =========================================================

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    /**
     * Relation Many-to-Many avec Certificat
     * Table pivot : certificat_militaire
     */
    public function certificats(): BelongsToMany
    {
        return $this->belongsToMany(Certificat::class, 'certificat_militaire')
                    ->withPivot('id', 'date_obtention')
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

    // =========================================================
    // MÉTHODES D'ANCIENNETÉ
    // =========================================================

    private static function getDateReference(): Carbon
    {
        $currentYear = Carbon::now()->year;
        return Carbon::create($currentYear, 12, 31, 23, 59, 59);
    }

    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_naissance)->age;
    }

    public function getAncienneteAttribute(): int
    {
        $dateReference = self::getDateReference();
        return Carbon::parse($this->date_entree_service)->diffInYears($dateReference);
    }

    public function getAncienneteGradeAttribute(): int
    {
        $dateReference = self::getDateReference();

        if ($this->date_derniere_promotion) {
            return Carbon::parse($this->date_derniere_promotion)->diffInYears($dateReference);
        }
        return $this->getAncienneteAttribute();
    }

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

    // =========================================================
    // MÉTHODES RETRAITE
    // =========================================================

    public function calculerDateRetraite(): ?Carbon
    {
        $grade = Grade::where('nom_grade', $this->grade_actuel)->first();

        if ($grade && $grade->retraite_obligatoire) {
            $ageRetraite = $grade->retraite_obligatoire;
            return Carbon::parse($this->date_naissance)->addYears($ageRetraite);
        }

        return null;
    }

    public function estEligibleRetraite(): bool
    {
        $dateRetraite = $this->calculerDateRetraite();
        if ($dateRetraite) {
            return Carbon::now()->diffInMonths($dateRetraite) <= 6;
        }
        return false;
    }

    // =========================================================
    // MÉTHODES DE VÉRIFICATION
    // =========================================================

    public function aAncienneteGradeMin(int $annees): bool
    {
        $dateReference = self::getDateReference();

        if (!$this->date_derniere_promotion) {
            return false;
        }

        $datePromotion = Carbon::parse($this->date_derniere_promotion);
        $ageEnAnnees = $datePromotion->diffInYears($dateReference);

        $moisJourPromotion = $datePromotion->format('m-d');
        $moisJourReference = $dateReference->format('m-d');

        if ($moisJourPromotion > $moisJourReference) {
            $ageEnAnnees--;
        }

        return $ageEnAnnees >= $annees;
    }

    public function aAncienneteMin(int $annees): bool
    {
        $dateReference = self::getDateReference();
        $dateEntree = Carbon::parse($this->date_entree_service);
        $ageEnAnnees = $dateEntree->diffInYears($dateReference);

        $moisJourEntree = $dateEntree->format('m-d');
        $moisJourReference = $dateReference->format('m-d');

        if ($moisJourEntree > $moisJourReference) {
            $ageEnAnnees--;
        }

        return $ageEnAnnees >= $annees;
    }

    public function aCinqAnsService(): bool
    {
        if (!$this->date_entree_service) {
            return false;
        }

        $dateEntree = Carbon::parse($this->date_entree_service);
        $serviceYears = $dateEntree->diffInYears(now());

        return $serviceYears >= 5;
    }

    public function getContratActifAttribute(): ?Contrat
    {
        return $this->contrats()->where('statut', 'actif')->latest('date_debut')->first();
    }
}
