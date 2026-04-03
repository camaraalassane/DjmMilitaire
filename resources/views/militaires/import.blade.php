@extends('layouts.app')

@section('title', 'Importation Excel')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Importation depuis Excel</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h6>Format du fichier Excel requis :</h6>
            <p>Le fichier doit contenir une première ligne d'en-tête avec les noms de colonnes ci-dessous. Les colonnes obligatoires sont marquées d'un astérisque (*). Les autres sont optionnelles.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <h6>Informations générales :</h6>
                    <ul>
                        <li><code>matricule</code> * (texte)</li>
                        <li><code>nom</code> * (texte)</li>
                        <li><code>prenom</code> * (texte)</li>
                        <li><code>date_naissance</code> * (AAAA-MM-JJ)</li>
                        <li><code>date_entree_service</code> * (AAAA-MM-JJ)</li>
                        <li><code>grade_actuel</code> * (doit exister dans la base)</li>
                        <li><code>date_derniere_promotion</code> (AAAA-MM-JJ)</li>
                        <li><code>specialite</code> (texte)</li>
                        <li><code>statut</code> (actif, retraité, déserteur, décédé, démobilisé)</li>
                        <li><code>a_permis_conduire</code> (0/1)</li>
                        <li><code>a_fait_justice</code> (0/1)</li>
                        <li><code>a_fait_discipline</code> (0/1)</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>Certificats sous-officiers :</h6>
                    <ul>
                        <li><code>a_fait_cat1</code> (0/1) + <code>date_obtention_cat1</code></li>
                        <li><code>a_fait_cat2</code> (0/1) + <code>date_obtention_cat2</code></li>
                        <li><code>a_fait_cia</code> (0/1) + <code>date_obtention_cia</code></li>
                        <li><code>a_fait_ba1</code> (0/1) + <code>date_obtention_ba1</code></li>
                        <li><code>a_fait_ba2</code> (0/1) + <code>date_obtention_ba2</code></li>
                        <li><code>a_fait_bmp1</code> (0/1) + <code>date_obtention_bmp1</code></li>
                        <li><code>a_fait_bmp2</code> (0/1) + <code>date_obtention_bmp2</code></li>
                        <li><code>a_fait_bs</code> (0/1) + <code>date_obtention_bs</code></li>
                        <li><code>a_fait_ct2</code> (0/1) + <code>date_obtention_ct2</code></li>
                    </ul>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12">
                    <h6>Formations officiers :</h6>
                    <ul>
                        <li><code>a_fait_apli</code> (0/1) + <code>date_obtention_apli</code></li>
                        <li><code>a_fait_cfcu</code> (0/1) + <code>date_obtention_cfcu</code></li>
                        <li><code>a_fait_cem</code> (0/1) + <code>date_obtention_cem</code></li>
                        <li><code>a_fait_certificat_etat_major</code> (0/1) + <code>date_obtention_certificat_etat_major</code></li>
                        <li><code>a_fait_ecole_guerre</code> (0/1) + <code>date_obtention_ecole_guerre</code></li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-3">
                <p><strong>Remarques :</strong></p>
                <ul>
                    <li>Les dates peuvent être au format texte (AAAA-MM-JJ) ou au format nombre Excel (automatiquement converti).</li>
                    <li>Pour chaque certificat, si la colonne <code>a_fait_xxx</code> est à 1, vous pouvez renseigner la date d'obtention correspondante. Si elle est omise ou à 0, la date est ignorée.</li>
                    <li>Les colonnes de certificats ne sont pas obligatoires ; les valeurs par défaut sont 0 (non obtenu) et date nulle.</li>
                </ul>
            </div>
        </div>

        <form action="{{ route('militaires.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="fichier" class="form-label">Fichier Excel (.xlsx, .xls, .csv) *</label>
                <input type="file" class="form-control @error('fichier') is-invalid @enderror"
                       id="fichier" name="fichier" accept=".xlsx,.xls,.csv" required>
                @error('fichier')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Taille maximale : 2 Mo.</div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-upload"></i> Importer
                </button>
                <a href="{{ route('militaires.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
        </form>

        <div class="mt-4">
            <h6>Télécharger le modèle :</h6>
            <a href="{{ asset('modele/modele_militaires.xlsx') }}" class="btn btn-outline-primary" download>
                <i class="bi bi-download"></i> Modèle Excel
            </a>
            <small class="text-muted ms-2">(Placez votre fichier modèle dans <code>public/modele/</code>)</small>
        </div>
    </div>
</div>
@endsection