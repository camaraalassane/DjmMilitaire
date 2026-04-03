<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }

    public function certificats()
    {
        return $this->belongsToMany(Certificat::class)
                    ->withPivot('date_obtention')
                    ->withTimestamps();
    }

    // Accesseurs
    public function getAgeAttribute()
    {
        return Carbon::parse($this->date_naissance)->age;
    }

    public function getAncienneteAttribute()
    {
        return Carbon::parse($this->date_entree_service)->diffInYears(Carbon::now());
    }

    public function getAncienneteGradeAttribute()
    {
        if ($this->date_derniere_promotion) {
            return Carbon::parse($this->date_derniere_promotion)->diffInYears(Carbon::now());
        }
        return $this->getAncienneteAttribute();
    }

    public function getAncienneteDetailleeAttribute()
    {
        if (!$this->date_entree_service) {
            return '';
        }
        $diff = $this->date_entree_service->diff(now());
        $years = $diff->y;
        $months = $diff->m;
        return $years . ' ans ' . ($months > 0 ? $months . ' mois' : '');
    }

    /**
     * Calcule la date de retraite en fonction du grade (ne sauvegarde pas en base)
     */
    public function calculerDateRetraite()
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
    public function estEligibleRetraite()
    {
        $dateRetraite = $this->calculerDateRetraite();
        if ($dateRetraite) {
            return Carbon::now()->diffInMonths($dateRetraite) <= 6;
        }
        return false;
    }
}