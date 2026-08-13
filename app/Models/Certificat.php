<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_certificat',
        'niveau_certificat',
        'grade_associe',
        'conditions',
        'anciennete_requise',
        'certificat_precedent',
        'duree_certificat_precedent'
    ];

    protected $casts = [
        'conditions' => 'array',
        'anciennete_requise' => 'integer',
        'duree_certificat_precedent' => 'integer',
    ];

    /**
     * Relation Many-to-Many avec Militaire
     * ✅ Table pivot : militaire_certificat
     */
    public function militaires()
    {
        return $this->belongsToMany(Militaire::class, 'certificat_militaire')
                    ->withPivot('date_obtention')
                    ->withTimestamps();
    }
}
