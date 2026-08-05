<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Contrat extends Model
{
    protected $fillable = [
        'militaire_id',
        'date_debut',
        'date_fin',
        'date_renouvellement',
        'duree_annees',
        'statut',
        'observations'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_renouvellement' => 'date',
    ];

    public function militaire(): BelongsTo
    {
        return $this->belongsTo(Militaire::class);
    }

    /**
     * Vérifier si le contrat est arrivé à échéance
     */
    public function isExpired(): bool
    {
        if (!$this->date_fin) {
            return false;
        }
        return $this->date_fin->isPast();
    }

    /**
     * Vérifier si le contrat peut être renouvelé (moins de 6 mois avant la fin)
     */
    public function canBeRenewed(): bool
    {
        if (!$this->date_fin) {
            return false;
        }
        $sixMonthsBefore = $this->date_fin->copy()->subMonths(6);
        return now()->greaterThanOrEqualTo($sixMonthsBefore) && !$this->isExpired();
    }

    /**
     * Vérifier si le militaire a 5 ans de service
     */
    public static function hasFiveYearsService(int $militaireId): bool
    {
        /** @var Contrat|null $contrat */
        $contrat = self::where('militaire_id', $militaireId)
            ->where('statut', 'actif')
            ->latest('date_debut')
            ->first();

        if (!$contrat) {
            return false;
        }

        $serviceYears = now()->diffInYears($contrat->date_debut);
        return $serviceYears >= 5;
    }

    /**
     * Récupérer les contrats expirés
     */
    public static function getExpiredContracts()
    {
        return self::where('statut', 'actif')
            ->where('date_fin', '<', now())
            ->get();
    }

    /**
     * Récupérer les contrats qui vont expirer dans les X mois
     */
    public static function getContractsExpiringIn(int $months = 6)
    {
        return self::where('statut', 'actif')
            ->where('date_fin', '>=', now())
            ->where('date_fin', '<=', now()->addMonths($months))
            ->get();
    }
}
