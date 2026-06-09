<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificat extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom_certificat',
        'niveau_certificat',
        'grade_associe',
        'conditions',
        'anciennete_requise',
        'certificat_precedent',
        'duree_certificat_precedent'
    ];

    /**
     * Les attributs qui doivent être convertis (castés).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'conditions' => 'array', // Convertit le tableau PHP en JSON pour MySQL/SQLite à l'écriture, et inversement à la lecture
        'anciennete_requise' => 'integer',
        'duree_certificat_precedent' => 'integer',
    ];
}