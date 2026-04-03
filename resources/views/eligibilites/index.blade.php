@extends('layouts.app')

@section('title', 'Éligibilités')

@section('actions')
<a href="{{ route('eligibilites.export') }}" class="btn btn-success">
    <i class="bi bi-file-excel"></i> Exporter vers Excel
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Promotions (sous-officiers et militaires du rang) -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Promotions (sous-officiers et militaires du rang)</h5>
            </div>
            <div class="card-body">
                @if(count($eligibilites['promotions']) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom & Prénom</th>
                                    <th>Grade actuel</th>
                                    <th>Type</th>
                                    <th>Grade cible</th>
                                    <th>Message</th>
                                    <th>Date estimation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eligibilites['promotions'] as $item)
                                <tr>
                                    <td>{{ $item['militaire']->matricule }}</td>
                                    <td>
                                        <a href="{{ route('militaires.show', $item['militaire']) }}">
                                            {{ $item['militaire']->nom }} {{ $item['militaire']->prenom }}
                                        </a>
                                    </td>
                                    <td>{{ $item['militaire']->grade_actuel }}</td>
                                    <td>{{ $item['type'] }}</td>
                                    <td>{{ $item['grade_cible'] }}</td>
                                    <td>{{ $item['message'] }}</td>
                                    <td>{{ $item['date_estimation']->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Aucune promotion imminente.</p>
                @endif
            </div>
        </div>

        <!-- Formations pour officiers -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Formations pour officiers</h5>
            </div>
            <div class="card-body">
                @if(count($eligibilites['formations_officiers']) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom & Prénom</th>
                                    <th>Grade actuel</th>
                                    <th>Formation</th>
                                    <th>Message</th>
                                    <th>Date estimation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eligibilites['formations_officiers'] as $item)
                                <tr>
                                    <td>{{ $item['militaire']->matricule }}</td>
                                    <td>
                                        <a href="{{ route('militaires.show', $item['militaire']) }}">
                                            {{ $item['militaire']->nom }} {{ $item['militaire']->prenom }}
                                        </a>
                                    </td>
                                    <td>{{ $item['militaire']->grade_actuel }}</td>
                                    <td>{{ $item['formation'] }}</td>
                                    <td>{{ $item['message'] }}</td>
                                    <td>{{ $item['date_estimation']->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Aucune formation d'officier imminente.</p>
                @endif
            </div>
        </div>

        <!-- Retraites proches -->
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Retraites proches (dans les 6 mois)</h5>
            </div>
            <div class="card-body">
                @if(count($eligibilites['retraites']) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom & Prénom</th>
                                    <th>Grade actuel</th>
                                    <th>Date retraite</th>
                                    <th>Mois restants</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($eligibilites['retraites'] as $item)
                                <tr>
                                    <td>{{ $item['militaire']->matricule }}</td>
                                    <td>
                                        <a href="{{ route('militaires.show', $item['militaire']) }}">
                                            {{ $item['militaire']->nom }} {{ $item['militaire']->prenom }}
                                        </a>
                                    </td>
                                    <td>{{ $item['militaire']->grade_actuel }}</td>
                                    <td>{{ $item['date_retraite']->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-danger">{{ $item['mois_restants'] }} mois</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Aucune retraite proche.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection