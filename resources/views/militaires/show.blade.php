@extends('layouts.app')

@section('title', 'Détails du militaire')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $militaire->nom }} {{ $militaire->prenom }}</h5>
                <div>
                    <a href="{{ route('militaires.edit', $militaire) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    <form action="{{ route('militaires.destroy', $militaire) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer la suppression ?')">
                            <i class="bi bi-trash"></i> Supprimer
                        </button>
                    </form>
                    <a href="{{ route('militaires.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Matricule :</dt>
                    <dd class="col-sm-8">{{ $militaire->matricule }}</dd>

                    <dt class="col-sm-4">Nom complet :</dt>
                    <dd class="col-sm-8">{{ $militaire->nom }} {{ $militaire->prenom }}</dd>

                    <dt class="col-sm-4">Date de naissance :</dt>
                    <dd class="col-sm-8">{{ $militaire->date_naissance->format('d/m/Y') }} ({{ $militaire->age }} ans)</dd>

                    <dt class="col-sm-4">Date d'entrée en service :</dt>
                    <dd class="col-sm-8">{{ $militaire->date_entree_service->format('d/m/Y') }} ({{ $militaire->anciennete }} ans de service)</dd>

                    <dt class="col-sm-4">Grade actuel :</dt>
                    <dd class="col-sm-8">{{ $militaire->grade_actuel }}</dd>

                    <dt class="col-sm-4">Date dernière promotion :</dt>
                    <dd class="col-sm-8">{{ $militaire->date_derniere_promotion ? $militaire->date_derniere_promotion->format('d/m/Y') : '-' }}</dd>

                    <dt class="col-sm-4">Spécialité :</dt>
                    <dd class="col-sm-8">{{ $militaire->specialite ?? '-' }}</dd>

                    <dt class="col-sm-4">Statut :</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-{{ $militaire->statut == 'actif' ? 'success' : ($militaire->statut == 'retraité' ? 'secondary' : 'danger') }}">
                            {{ ucfirst($militaire->statut) }}
                        </span>
                    </dd>

                    <dt class="col-sm-4">Date de retraite :</dt>
                    <dd class="col-sm-8">
                        @if($militaire->date_retraite)
                            {{ $militaire->date_retraite->format('d/m/Y') }}
                        @else
                            <span class="text-muted">Non calculée</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Permis de conduire :</dt>
                    <dd class="col-sm-8">{{ $militaire->a_permis_conduire ? 'Oui' : 'Non' }}</dd>

                    <dt class="col-sm-4">Problème judiciaire :</dt>
                    <dd class="col-sm-8">{{ $militaire->a_fait_justice ? 'Oui' : 'Non' }}</dd>

                    <dt class="col-sm-4">Problème disciplinaire :</dt>
                    <dd class="col-sm-8">{{ $militaire->a_fait_discipline ? 'Oui' : 'Non' }}</dd>
                </dl>

                <hr>
                <h6>Certificats obtenus</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Certificat</th>
                            <th>Obtenu</th>
                            <th>Date d'obtention</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>CAT1</td>
                            <td>{{ $militaire->a_fait_cat1 ? 'Oui' : 'Non' }}</td>
                            <td>{{ $militaire->date_obtention_cat1 ? $militaire->date_obtention_cat1->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td>CAT2</td>
                            <td>{{ $militaire->a_fait_cat2 ? 'Oui' : 'Non' }}</td>
                            <td>{{ $militaire->date_obtention_cat2 ? $militaire->date_obtention_cat2->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td>CIA</td>
                            <td>{{ $militaire->a_fait_cia ? 'Oui' : 'Non' }}</td>
                            <td>{{ $militaire->date_obtention_cia ? $militaire->date_obtention_cia->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td>BA1</td>
                            <td>{{ $militaire->a_fait_ba1 ? 'Oui' : 'Non' }}</td>
                            <td>{{ $militaire->date_obtention_ba1 ? $militaire->date_obtention_ba1->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td>BA2</td>
                            <td>{{ $militaire->a_fait_ba2 ? 'Oui' : 'Non' }}</td>
                            <td>{{ $militaire->date_obtention_ba2 ? $militaire->date_obtention_ba2->format('d/m/Y') : '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr>
                <h6>Alertes associées</h6>
                @if($alertes->isEmpty())
                    <p class="text-muted">Aucune alerte pour ce militaire.</p>
                @else
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Message</th>
                                <th>Échéance</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alertes as $alerte)
                            <tr class="{{ !$alerte->est_vue ? 'table-warning' : '' }}">
                                <td>{{ ucfirst($alerte->type_alerte) }}</td>
                                <td>{{ $alerte->message }}</td>
                                <td>{{ $alerte->date_echeance->format('d/m/Y') }}</td>
                                <td>
                                    @if($alerte->est_vue)
                                        <span class="badge bg-success">Vue</span>
                                    @else
                                        <span class="badge bg-warning">Non vue</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection