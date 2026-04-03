<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'militaire_id',
        'type_alerte',
        'message',
        'date_echeance',
        'est_vue',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'est_vue' => 'boolean',
    ];

    public function militaire()
    {
        return $this->belongsTo(Militaire::class);
    }
}