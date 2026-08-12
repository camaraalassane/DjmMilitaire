<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificatDocument extends Model
{
    protected $fillable = [
        'militaire_certificat_id',
        'nom_fichier',
        'chemin_fichier',
        'type_fichier',
        'taille'
    ];

    /**
     * Relation vers l'enregistrement pivot dans la table certificat_militaire
     */
    public function pivot()
    {
        return $this->belongsTo(Certificat::class, 'militaire_certificat_id', 'id');
    }
}
