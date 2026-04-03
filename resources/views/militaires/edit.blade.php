@extends('layouts.app')

@section('title', 'Modifier un militaire')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Modifier le militaire : {{ $militaire->nom }} {{ $militaire->prenom }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('militaires.update', $militaire) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Informations générales -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="matricule" class="form-label">Matricule *</label>
                        <input type="text" class="form-control @error('matricule') is-invalid @enderror"
                               id="matricule" name="matricule" value="{{ old('matricule', $militaire->matricule) }}" required>
                        @error('matricule')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="grade_actuel" class="form-label">Grade actuel *</label>
                        <select class="form-select @error('grade_actuel') is-invalid @enderror"
                                id="grade_actuel" name="grade_actuel" required>
                            <option value="">Sélectionner un grade</option>
                            @foreach($grades as $grade)
                            <option value="{{ $grade->nom_grade }}"
                                    {{ old('grade_actuel', $militaire->grade_actuel) == $grade->nom_grade ? 'selected' : '' }}>
                                {{ $grade->nom_grade }} ({{ $grade->code_grade }})
                            </option>
                            @endforeach
                        </select>
                        @error('grade_actuel')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom *</label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror"
                               id="nom" name="nom" value="{{ old('nom', $militaire->nom) }}" required>
                        @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom *</label>
                        <input type="text" class="form-control @error('prenom') is-invalid @enderror"
                               id="prenom" name="prenom" value="{{ old('prenom', $militaire->prenom) }}" required>
                        @error('prenom')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_naissance" class="form-label">Date de naissance *</label>
                        <input type="date" class="form-control @error('date_naissance') is-invalid @enderror"
                               id="date_naissance" name="date_naissance"
                               value="{{ old('date_naissance', $militaire->date_naissance ? $militaire->date_naissance->format('Y-m-d') : '') }}" required>
                        @error('date_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_entree_service" class="form-label">Date d'entrée en service *</label>
                        <input type="date" class="form-control @error('date_entree_service') is-invalid @enderror"
                               id="date_entree_service" name="date_entree_service"
                               value="{{ old('date_entree_service', $militaire->date_entree_service ? $militaire->date_entree_service->format('Y-m-d') : '') }}" required>
                        @error('date_entree_service')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_derniere_promotion" class="form-label">Date dernière promotion</label>
                        <input type="date" class="form-control @error('date_derniere_promotion') is-invalid @enderror"
                               id="date_derniere_promotion" name="date_derniere_promotion"
                               value="{{ old('date_derniere_promotion', $militaire->date_derniere_promotion ? $militaire->date_derniere_promotion->format('Y-m-d') : '') }}">
                        @error('date_derniere_promotion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="specialite" class="form-label">Spécialité</label>
                        <input type="text" class="form-control @error('specialite') is-invalid @enderror"
                               id="specialite" name="specialite" value="{{ old('specialite', $militaire->specialite) }}">
                        @error('specialite')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Statut -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut">
                            <option value="actif" {{ old('statut', $militaire->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="retraité" {{ old('statut', $militaire->statut) == 'retraité' ? 'selected' : '' }}>Retraité</option>
                            <option value="déserteur" {{ old('statut', $militaire->statut) == 'déserteur' ? 'selected' : '' }}>Déserteur</option>
                            <option value="décédé" {{ old('statut', $militaire->statut) == 'décédé' ? 'selected' : '' }}>Décédé</option>
                            <option value="formation" {{ old('statut', $militaire->statut) == 'formation' ? 'selected' : '' }}>formation</option>
                            <option value="stage" {{ old('statut', $militaire->statut) == 'stage' ? 'selected' : '' }}>stage</option>
                        </select>
                        @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="a_permis_conduire"
                               name="a_permis_conduire" value="1" {{ old('a_permis_conduire', $militaire->a_permis_conduire) ? 'checked' : '' }}>
                        <label class="form-check-label" for="a_permis_conduire">
                            Permis de conduire obtenu
                        </label>
                    </div>
                </div>
            </div>

            <!-- Certificats et formations -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Certificats et formations obtenus</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($certificats as $certificat)
                            @php
                                $certifObtenu = $certificatsDuMilitaire->get($certificat->id);
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div class="border p-3 rounded">
                                    <div class="form-check">
                                        <input class="form-check-input certificat-check"
                                               type="checkbox"
                                               name="certificats[{{ $certificat->id }}][obtenu]"
                                               value="1"
                                               id="certif_{{ $certificat->id }}"
                                               {{ old('certificats.' . $certificat->id . '.obtenu', $certifObtenu ? true : false) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="certif_{{ $certificat->id }}">
                                            {{ $certificat->nom_certificat }} ({{ $certificat->niveau_certificat }})
                                        </label>
                                    </div>
                                    <div class="mt-2">
                                        <label for="date_certif_{{ $certificat->id }}" class="form-label small">Date d'obtention</label>
                                        <input type="date"
                                               class="form-control form-control-sm"
                                               name="certificats[{{ $certificat->id }}][date_obtention]"
                                               id="date_certif_{{ $certificat->id }}"
                                               value="{{ old('certificats.' . $certificat->id . '.date_obtention', $certifObtenu ? $certifObtenu->pivot->date_obtention : '') }}">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted">Aucun certificat défini dans la base. Veuillez d'abord ajouter des certificats.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Boutons -->
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Mettre à jour
                </button>
                <a href="{{ route('militaires.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de l'activation/désactivation des champs de date en fonction de la case cochée
        document.querySelectorAll('.certificat-check').forEach(checkbox => {
            const dateInput = document.getElementById('date_certif_' + checkbox.id.split('_')[1]);
            if (dateInput) {
                // Initialiser l'état
                dateInput.disabled = !checkbox.checked;

                // Écouter les changements
                checkbox.addEventListener('change', function() {
                    dateInput.disabled = !this.checked;
                    if (!this.checked) dateInput.value = '';
                });
            }
        });
    });
</script>
@endsection