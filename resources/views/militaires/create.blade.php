@extends('layouts.app')

@section('title', 'Ajouter un militaire')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Nouveau militaire</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('militaires.store') }}" method="POST">
            @csrf

            <!-- Informations générales -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="matricule" class="form-label">Matricule *</label>
                        <input type="text" class="form-control @error('matricule') is-invalid @enderror"
                               id="matricule" name="matricule" value="{{ old('matricule') }}" required>
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
                                    {{ old('grade_actuel') == $grade->nom_grade ? 'selected' : '' }}>
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
                               id="nom" name="nom" value="{{ old('nom') }}" required>
                        @error('nom')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom *</label>
                        <input type="text" class="form-control @error('prenom') is-invalid @enderror"
                               id="prenom" name="prenom" value="{{ old('prenom') }}" required>
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
                               value="{{ old('date_naissance') }}" required>
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
                               value="{{ old('date_entree_service') }}" required>
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
                               value="{{ old('date_derniere_promotion') }}">
                        @error('date_derniere_promotion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="specialite" class="form-label">Spécialité</label>
                        <input type="text" class="form-control @error('specialite') is-invalid @enderror"
                               id="specialite" name="specialite" value="{{ old('specialite') }}">
                        @error('specialite')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Permis de conduire -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="a_permis_conduire"
                               name="a_permis_conduire" value="1" {{ old('a_permis_conduire') ? 'checked' : '' }}>
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
                        <div class="col-md-6 mb-3">
                            <div class="border p-3 rounded">
                                <div class="form-check">
                                    <input class="form-check-input certificat-check"
                                           type="checkbox"
                                           name="certificats[{{ $certificat->id }}][obtenu]"
                                           value="1"
                                           id="certif_{{ $certificat->id }}"
                                           {{ old('certificats.' . $certificat->id . '.obtenu') ? 'checked' : '' }}>
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
                                           value="{{ old('certificats.' . $certificat->id . '.date_obtention') }}">
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
                    <i class="bi bi-save"></i> Enregistrer
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
    // Optionnel : activer/désactiver le champ date en fonction de la case à cocher
    document.querySelectorAll('.certificat-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const dateInput = document.getElementById('date_certif_' + this.id.split('_')[1]);
            if (dateInput) {
                dateInput.disabled = !this.checked;
                if (!this.checked) dateInput.value = '';
            }
        });
        // Initialisation au chargement
        const dateInput = document.getElementById('date_certif_' + checkbox.id.split('_')[1]);
        if (dateInput) {
            dateInput.disabled = !checkbox.checked;
        }
    });
</script>
@endsection