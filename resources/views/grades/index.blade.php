@extends('layouts.app')

@section('title', 'Liste des grades')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Tous les grades</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Grade</th>
                        <th>Type</th>
                        <th>Ordre</th>
                        <th>Effectif actif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grades as $grade)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $grade->code_grade }}</span></td>
                        <td>{{ $grade->nom_grade }}</td>
                        <td>{{ $grade->type_grade }}</td>
                        <td>{{ $grade->ordre }}</td>
                        <td>{{ $grade->militaires()->where('statut', 'actif')->count() }}</td>
                        <td>
                            <a href="{{ route('grades.show', $grade) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Aucun grade trouvé</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $grades->links() }}
        </div>
    </div>
</div>
@endsection