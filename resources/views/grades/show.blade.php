@extends('layouts.app')

@section('title', $grade->nom_grade)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Détails du grade</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Code :</dt>
                    <dd class="col-sm-9"><span class="badge bg-secondary">{{ $grade->code_grade }}</span></dd>

                    <dt class="col-sm-3">Nom complet :</dt>
                    <dd class="col-sm-9">{{ $grade->nom_grade }}</dd>

                    <dt class="col-sm-3">Type :</dt>
                    <dd class="col-sm-9">{{ $grade->type_grade }}</dd>

                    <dt class="col-sm-3">Ordre hiérarchique :</dt>
                    <dd class="col-sm-9">{{ $grade->ordre }}</dd>

                    <dt class="col-sm-3">Effectif actif :</dt>
                    <dd class="col-sm-9">{{ $grade->militaires()->where('statut', 'actif')->count() }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Militaires ayant ce grade (actifs)</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom & prénom</th>
                            <th>Âge</th>
                            <th>Ancienneté</th>
                            <th>Date retraite</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($militaires as $militaire)
                        <tr>
                            <td>{{ $militaire->matricule }}</td>
                            <td>
                                <a href="{{ route('militaires.show', $militaire) }}">
                                    {{ $militaire->nom }} {{ $militaire->prenom }}
                                </a>
                            </td>
                            <td>{{ $militaire->age }} ans</td>
                            <td>{{ $militaire->anciennete }} ans</td>
                            <td>
                                @if($militaire->date_retraite)
                                    {{ $militaire->date_retraite->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucun militaire actif à ce grade</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-center">
                    {{ $militaires->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('grades.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>
@endsection