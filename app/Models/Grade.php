<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom_grade',
        'code_grade',
        'type_grade',
        'ordre',
        'retraite_obligatoire',
    ];

    /**
     * Les attributs qui doivent être convertis (castés).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ordre' => 'integer',
        'retraite_obligatoire' => 'integer',
    ];

    /**
     * Obtenir les militaires associés à ce grade.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function militaires(): HasMany
    {
        return $this->hasMany(Militaire::class, 'grade_actuel', 'nom_grade');
    }
}