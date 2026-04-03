<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CertificatController extends Controller
{
    /**
     * Afficher la liste des certificats.
     */
    public function index()
    {
        $certificats = Certificat::orderBy('niveau_certificat')
            ->paginate(20)
            ->through(fn ($certificat) => [
                'id' => $certificat->id,
                'nom_certificat' => $certificat->nom_certificat,
                'niveau_certificat' => $certificat->niveau_certificat,
                'grade_associe' => $certificat->grade_associe,
                'anciennete_requise' => $certificat->anciennete_requise,
                'certificat_precedent' => $certificat->certificat_precedent,
                'duree_certificat_precedent' => $certificat->duree_certificat_precedent,
                'conditions_count' => $this->countConditions($certificat->conditions),
            ]);

        return Inertia::render('certificats/index', [ // Notez le "I" majuscule
            'certificats' => $certificats
        ]);
    }

    /**
     * Afficher les détails d'un certificat.
     */
    public function show(Certificat $certificat)
    {
        return Inertia::render('certificats/show', [ // Notez le "S" majuscule
            'certificat' => [
                'id' => $certificat->id,
                'nom_certificat' => $certificat->nom_certificat,
                'niveau_certificat' => $certificat->niveau_certificat,
                'grade_associe' => $certificat->grade_associe,
                'anciennete_requise' => $certificat->anciennete_requise,
                'certificat_precedent' => $certificat->certificat_precedent,
                'duree_certificat_precedent' => $certificat->duree_certificat_precedent,
                'conditions' => $this->formatConditions($certificat->conditions),
                'created_at' => $certificat->created_at?->format('d/m/Y'),
                'updated_at' => $certificat->updated_at?->format('d/m/Y'),
            ]
        ]);
    }

    /**
     * Compter le nombre de conditions
     */
    private function countConditions($conditions)
    {
        if (empty($conditions)) return 0;
        
        if (is_string($conditions)) {
            $conditions = json_decode($conditions, true);
        }
        
        return is_array($conditions) ? count($conditions) : 0;
    }

    /**
     * Formater les conditions pour l'affichage
     */
    private function formatConditions($conditions)
    {
        if (empty($conditions)) return [];
        
        if (is_array($conditions)) {
            return $conditions;
        }
        
        if (is_string($conditions)) {
            return json_decode($conditions, true) ?? [];
        }
        
        return [];
    }
}