<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CertificatController;
use App\Http\Controllers\MilitaireController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\EligibiliteController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\CertificatDocumentController;
use App\Http\Controllers\MilitairesImportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rediriger la racine vers la page de login
Route::get('/', function () {
    return redirect()->route('login');
});

// Route du dashboard : Redirection intelligente selon le rôle
Route::get('/dashboard', function () {
    if (auth()->user()->isSuperAdmin()) {
        return redirect()->route('users.index');
    }
    return app(DashboardController::class)->index();
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes réservées à l'Admin (Gestionnaire)
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard actions
    Route::get('/dashboard/section', [DashboardController::class, 'section'])->name('dashboard.section');
    Route::get('/dashboard/export-proposables-annee-n', [DashboardController::class, 'exportProposablesAnneeN'])->name('dashboard.export-proposables-annee-n');
    Route::get('/dashboard/export-proposables-annee-n1', [DashboardController::class, 'exportProposablesAnneeN1'])->name('dashboard.export-proposables-annee-n1');
    Route::get('/dashboard/export-retraites-annee-n', [DashboardController::class, 'exportRetraitesAnneeN'])->name('dashboard.export-retraites-annee-n');
    Route::get('/dashboard/export-retraites-annee-n1', [DashboardController::class, 'exportRetraitesAnneeN1'])->name('dashboard.export-retraites-annee-n1');

    // Alertes
    Route::get('/alertes', [AlerteController::class, 'index'])->name('alertes.index');
    Route::post('/alertes/{alerte}/marquer-vue', [AlerteController::class, 'marquerVue'])->name('alertes.marquer-vue');
    Route::post('/alertes/marquer-tout-vue', [AlerteController::class, 'marquerToutVue'])->name('alertes.marquer-tout-vue');
    Route::post('/alertes/check-renouvellements', [AlerteController::class, 'checkRenouvellements'])->name('alertes.check-renouvellements');
    Route::post('/alertes/generer', [AlerteController::class, 'genererAlertes'])->name('alertes.generer');

    // Contrats
    Route::get('/contrats', [ContratController::class, 'index'])->name('contrats.index');
    Route::post('/contrats', [ContratController::class, 'store'])->name('contrats.store');
    Route::get('/contrats/{contrat}', [ContratController::class, 'show'])->name('contrats.show');
    Route::get('/contrats/historique/{militaire}', [ContratController::class, 'historique'])->name('contrats.historique');
    Route::post('/contrats/check-alerts', [ContratController::class, 'checkAndGenerateAlerts'])->name('contrats.check-alerts');
    Route::get('/contrats/stats', [ContratController::class, 'stats'])->name('contrats.stats');
    Route::post('/contrats/auto-renew-all', [ContratController::class, 'autoRenewAll'])->name('contrats.auto-renew-all');
    Route::post('/contrats/annuler-renouvellement', [ContratController::class, 'annulerRenouvellement'])->name('contrats.annuler-renouvellement');

    // Grades
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
    Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');

    // Certificats
    Route::get('/certificats', [CertificatController::class, 'index'])->name('certificats.index');
    Route::get('/certificats/create', [CertificatController::class, 'create'])->name('certificats.create');
    Route::post('/certificats', [CertificatController::class, 'store'])->name('certificats.store');
    Route::get('/certificats/{certificat}', [CertificatController::class, 'show'])->name('certificats.show');
    Route::get('/certificats/{certificat}/edit', [CertificatController::class, 'edit'])->name('certificats.edit');
    Route::put('/certificats/{certificat}', [CertificatController::class, 'update'])->name('certificats.update');
    Route::delete('/certificats/{certificat}', [CertificatController::class, 'destroy'])->name('certificats.destroy');
    Route::get('/certificats/document/{id}/download', [CertificatDocumentController::class, 'download'])
        ->name('certificats.document.download');

    // Militaires
    Route::get('/militaires/export', [MilitaireController::class, 'export'])->name('militaires.export');
    Route::get('/militaires/export/template', [MilitaireController::class, 'exportTemplate'])->name('militaires.export.template');
    Route::get('/militaires/import/form', [MilitairesImportController::class, 'showForm'])->name('militaires.import');
    Route::post('/militaires/import/process', [MilitairesImportController::class, 'process'])->name('militaires.import.process');
    Route::get('/militaires/import/template/xlsx', [MilitairesImportController::class, 'downloadTemplateXlsx'])->name('militaires.import.template.xlsx');
    Route::get('/militaires/import/template/csv', [MilitairesImportController::class, 'downloadTemplateCsv'])->name('militaires.import.template.csv');
    Route::get('/militaires', [MilitaireController::class, 'index'])->name('militaires.index');
    Route::get('/militaires/create', [MilitaireController::class, 'create'])->name('militaires.create');
    Route::post('/militaires', [MilitaireController::class, 'store'])->name('militaires.store');
    Route::get('/militaires/{militaire}', [MilitaireController::class, 'show'])->name('militaires.show');
    Route::get('/militaires/{militaire}/edit', [MilitaireController::class, 'edit'])->name('militaires.edit');
    Route::put('/militaires/{militaire}', [MilitaireController::class, 'update'])->name('militaires.update');
    Route::delete('/militaires/{militaire}', [MilitaireController::class, 'destroy'])->name('militaires.destroy');

    // Eligibilites
    Route::get('/eligibilites', [EligibiliteController::class, 'index'])->name('eligibilites.index');
    Route::get('/eligibilites/stats', [EligibiliteController::class, 'getStats'])->name('eligibilites.stats');
    Route::get('/eligibilites/filtered', [EligibiliteController::class, 'getFiltered'])->name('eligibilites.filtered');
    Route::get('/eligibilites/export', [EligibiliteController::class, 'export'])->name('eligibilites.export');
    Route::post('/eligibilites/invalidate-cache', [EligibiliteController::class, 'invalidateCache'])->name('eligibilites.invalidate-cache');
});

// Routes réservées au Super Administrateur
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
});

// Routes profil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
