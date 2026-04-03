<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = ['nom_grade', 'code_grade', 'type_grade', 'ordre','retraite_obligatoire'];

    public function militaires()
    {
        return $this->hasMany(Militaire::class, 'grade_actuel', 'nom_grade');
    }
}