@extends('layouts.app')

@section('title', 'Gestion des Militaires')

@section('actions')
<div class="btn-group">
    <a href="{{ route('militaires.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nouveau militaire
    </a>
    <a href="{{ route('militaires.import') }}" class="btn btn-success">
        <i class="bi bi-upload"></i> Importer Excel
    </a>
</div>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('militaires.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="grade" class="form-select">
                            <option value="">Tous les grades</option>
                            @foreach(App\Models\Grade::all() as $grade)
                            <option value="{{ $grade->nom_grade }}" 
                                    {{ request('grade') == $grade->nom_grade ? 'selected' : '' }}>
                                {{ $grade->nom_grade }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="retraité" {{ request('statut') == 'retraité' ? 'selected' : '' }}>Retraité</option>
                            <option value="déserteur" {{ request('statut') == 'déserteur' ? 'selected' : '' }}>Déserteur</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Filtrer</button>
                        <a href="{{ route('militaires.index') }}" class="btn btn-secondary">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom & Prénom</th>
                        <th>Grade</th>
                        <th>Âge / Ancienneté</th>
                        <th>Date retraite</th>
                        <th>Certificats</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($militaires as $militaire)
                    <tr>
                        <td>{{ $militaire->matricule }}</td>
                        <td>
                            <strong>{{ $militaire->nom }} {{ $militaire->prenom }}</strong>
                            @if($militaire->alertes()->where('est_vue', false)->exists())
                            <span class="badge bg-warning ms-2">
                                <i class="bi bi-exclamation-triangle"></i>
                            </span>
                            @endif
                        </td>
                        <td>{{ $militaire->grade_actuel }}</td>
                        <td>
                            {{ $militaire->age }} ans<br>
                            <small class="text-muted">{{ $militaire->anciennete_detaillee }} de service</small>
                        </td>
                        <td>
                            @if($militaire->date_retraite)
                            {{ $militaire->date_retraite->format('d/m/Y') }}
                            @if($militaire->estEligibleRetraite())
                            <br><span class="badge bg-danger">Bientôt</span>
                            @endif
                            @else
                            <span class="text-muted">Non calculée</span>
                            @endif
                        </td>
                        <td>
                            @if($militaire->a_fait_cat1)<span class="badge bg-info me-1">CAT1</span>@endif
                            @if($militaire->a_fait_cat2)<span class="badge bg-info me-1">CAT2</span>@endif
                            @if($militaire->a_fait_cia)<span class="badge bg-success me-1">CIA</span>@endif
                            @if($militaire->a_fait_ba1)<span class="badge bg-warning me-1">BA1</span>@endif
                            @if($militaire->a_fait_ba2)<span class="badge bg-warning me-1">BA2</span>@endif
                        </td>
                        <td>
                            @php
                            $couleurs = [
                                'actif' => 'success',
                                'retraité' => 'secondary',
                                'déserteur' => 'danger',
                                'formation' => 'info',
                                'stage' => 'warning',
                            ];
                            $couleur = $couleurs[$militaire->statut] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $couleur }}">{{ $militaire->statut }}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('militaires.show', $militaire) }}" 
                                   class="btn btn-outline-primary" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('militaires.edit', $militaire) }}" 
                                   class="btn btn-outline-warning" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('militaires.destroy', $militaire) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('Confirmer la suppression ?')" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $militaires->links() }}
        </div>
    </div>
</div>
@endsection