<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Militaires - FAMa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .alert-promotion { border-left: 5px solid #007bff; }
        .alert-formation { border-left: 5px solid #28a745; }
        .alert-retraite { border-left: 5px solid #dc3545; }
        .alert-certificat { border-left: 5px solid #ffc107; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar bg-dark">
                <div class="sidebar-sticky pt-3 text-center">
                    <!-- Logo DTTIA -->
                    @if(file_exists(public_path('images/logo-dttia.png')))
                        <div class="mb-2">
                            <img src="{{ asset('images/logo-dttia.png') }}" alt="DTTIA" style="max-height: 60px; width: auto;">
                        </div>
                    @endif
                    <!-- Titre DTTIA -->
                    <h4 class="text-white mb-4">DTTIA</h4>

                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link">
                                <i class="bi bi-speedometer2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('militaires.index') }}" class="nav-link">
                                <i class="bi bi-people"></i> Militaires
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('alertes.index') }}" class="nav-link">
                                <i class="bi bi-bell"></i> Alertes
                                @if(isset($alertesCount) && $alertesCount > 0)
                                    <span class="badge bg-danger ms-1">{{ $alertesCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('eligibilites.index') }}" class="nav-link">
                                <i class="bi bi-check-circle"></i> Éligibilités
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('grades.index') }}" class="nav-link">
                                <i class="bi bi-star"></i> Grades
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('certificats.index') }}" class="nav-link">
                                <i class="bi bi-award"></i> Certificats
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-white">
                                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title')</h1>
                    @yield('actions')
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>