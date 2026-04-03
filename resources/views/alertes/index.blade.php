@extends('layouts.app')

@section('title', 'Liste des alertes')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Toutes les alertes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Militaire</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Échéance</th>
                        <th>Créée le</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alertes as $alerte)
                    <tr class="{{ !$alerte->est_vue ? 'table-warning' : '' }}">
                        <td>
                            <a href="{{ route('militaires.show', $alerte->militaire) }}">
                                {{ $alerte->militaire->nom }} {{ $alerte->militaire->prenom }}
                            </a>
                        </td>
                        <td>
                            @switch($alerte->type_alerte)
                                @case('promotion') Promotion @break
                                @case('formation') Formation @break
                                @case('retraite') Retraite @break
                                @case('certificat') Certificat @break
                                @default {{ $alerte->type_alerte }}
                            @endswitch
                        </td>
                        <td>{{ $alerte->message }}</td>
                        <td>{{ $alerte->date_echeance->format('d/m/Y') }}</td>
                        <td>{{ $alerte->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($alerte->est_vue)
                                <span class="badge bg-success">Vue</span>
                            @else
                                <span class="badge bg-warning">Non vue</span>
                            @endif
                        </td>
                        <td>
                            @if(!$alerte->est_vue)
                            <form action="{{ route('alertes.marquer-vue', $alerte) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-check"></i> Marquer vue
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Aucune alerte</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $alertes->links() }}
        </div>
    </div>
</div>
@endsection