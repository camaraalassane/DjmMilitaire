<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eligibilite extends Model
{
    use HasFactory;

    protected $fillable = [
        'militaire_id',
        'type',
        'cible',
        'date_eligibilite',
    ];

    protected $casts = [
        'date_eligibilite' => 'date',
    ];

    public function militaire()
    {
        return $this->belongsTo(Militaire::class);
    }
}