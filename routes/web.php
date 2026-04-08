<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CertificatController;
use App\Http\Controllers\MilitaireController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\EligibiliteController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rediriger la racine vers la page de login
Route::get('/', function () {
    return redirect()->route('login');
});

// Route du dashboard avec votre contrôleur
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::get('dashboard/export-proposables-annee-n', [DashboardController::class, 'exportProposablesAnneeN'])->name('dashboard.export-proposables-annee-n');
Route::get('dashboard/export-proposables-annee-n1', [DashboardController::class, 'exportProposablesAnneeN1'])->name('dashboard.export-proposables-annee-n1');
// Exports retraites
Route::get('dashboard/export-retraites-annee-n', [DashboardController::class, 'exportRetraitesAnneeN'])->name('dashboard.export-retraites-annee-n');
Route::get('dashboard/export-retraites-annee-n1', [DashboardController::class, 'exportRetraitesAnneeN1'])->name('dashboard.export-retraites-annee-n1');
// Route pour marquer une alerte comme vue
Route::post('/alertes/{alerte}/marquer-vue', [DashboardController::class, 'marquerAlerteVue'])
    ->middleware(['auth'])
    ->name('alertes.marquer-vue');

// Routes pour les alertes
Route::middleware(['auth'])->group(function () {
    Route::get('/alertes', [AlerteController::class, 'index'])->name('alertes.index');
    Route::post('/alertes/{alerte}/marquer-vue', [AlerteController::class, 'marquerVue'])->name('alertes.marquer-vue');
    Route::post('/alertes/marquer-tout-vue', [AlerteController::class, 'marquerToutVue'])->name('alertes.marquer-tout-vue');
});

// Routes pour les éligibilités
Route::middleware(['auth'])->group(function () {
    Route::get('/eligibilites', [EligibiliteController::class, 'index'])->name('eligibilites.index');
    Route::get('/eligibilites/export', [EligibiliteController::class, 'export'])->name('eligibilites.export');
});

// Routes pour les grades
Route::middleware(['auth'])->group(function () {
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
});

// Routes pour les certificats
Route::middleware(['auth'])->group(function () {
    Route::get('/certificats', [CertificatController::class, 'index'])->name('certificats.index');
    Route::get('/certificats/{certificat}', [CertificatController::class, 'show'])->name('certificats.show');
    Route::get('/certificats/create', [CertificatController::class, 'create'])->name('certificats.create');
    Route::post('/certificats', [CertificatController::class, 'store'])->name('certificats.store');
    Route::get('/certificats/{certificat}/edit', [CertificatController::class, 'edit'])->name('certificats.edit');
    Route::put('/certificats/{certificat}', [CertificatController::class, 'update'])->name('certificats.update');
    Route::delete('/certificats/{certificat}', [CertificatController::class, 'destroy'])->name('certificats.destroy');
});

// Routes pour les militaires
Route::middleware(['auth'])->group(function () {
    Route::get('/militaires', [MilitaireController::class, 'index'])->name('militaires.index');
    Route::get('/militaires/create', [MilitaireController::class, 'create'])->name('militaires.create');
    Route::post('/militaires', [MilitaireController::class, 'store'])->name('militaires.store');
    Route::get('/militaires/{militaire}', [MilitaireController::class, 'show'])->name('militaires.show');
    Route::get('/militaires/{militaire}/edit', [MilitaireController::class, 'edit'])->name('militaires.edit');
    Route::put('/militaires/{militaire}', [MilitaireController::class, 'update'])->name('militaires.update');
    Route::delete('/militaires/{militaire}', [MilitaireController::class, 'destroy'])->name('militaires.destroy');
    
    // Routes pour l'import/export
    Route::get('/militaires/import/form', [MilitaireController::class, 'importForm'])->name('militaires.import');
    Route::post('/militaires/import', [MilitaireController::class, 'import'])->name('militaires.import.process');
    Route::get('/militaires/export/template', [MilitaireController::class, 'exportTemplate'])->name('militaires.export.template');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';