<?php

namespace App\Http\Controllers;

use App\Imports\MilitairesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;

class MilitairesImportController extends Controller
{
    public function showForm()
    {
        return Inertia::render('militaires/import');
    }

    public function process(Request $request)
    {
        // Augmenter le temps d'exécution et la mémoire pour les gros fichiers
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'duplicate_action' => 'sometimes|in:ignore,update',
        ]);

        $file = $request->file('fichier');
        $duplicateAction = $request->input('duplicate_action', 'ignore');

        try {
            $import = new MilitairesImport($duplicateAction);
            Excel::import($import, $file);

            $summary = $import->getSummary();
            $duplicates = $import->getDuplicates();
            $errorDetails = $import->getErrorDetails();

            if (!empty($duplicates) && $duplicateAction === 'ignore') {
                return back()->with([
                    'duplicates' => $duplicates,
                    'flash' => [
                        'warning' => 'Des doublons ont été détectés et ignorés'
                    ]
                ]);
            }

            $message = "Importation terminée : ";
            if ($summary['created'] > 0) $message .= "{$summary['created']} créé(s) ";
            if ($summary['updated'] > 0) $message .= "{$summary['updated']} mis à jour ";
            if ($summary['skipped'] > 0) $message .= "{$summary['skipped']} ignoré(s) ";
            if ($summary['errors'] > 0) $message .= "⚠️ {$summary['errors']} erreur(s) ";

            return redirect()->route('militaires.index')->with([
                'success' => trim($message),
                'import_summary' => $summary
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'fichier' => 'Erreur lors de l\'importation : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Télécharger le modèle d'import au format Excel (XLSX)
     */
    public function downloadTemplateXlsx()
    {
        $headers = MilitairesImport::getTemplateHeaders();
        $exampleRow = MilitairesImport::getTemplateExampleRow();

        return Excel::download(
            new \App\Exports\MilitairesTemplateExport($headers, $exampleRow),
            'modele_import_militaires.xlsx'
        );
    }

    /**
     * Télécharger le modèle d'import au format CSV
     */
    public function downloadTemplateCsv()
    {
        $headers = MilitairesImport::getTemplateHeaders();
        $exampleRow = MilitairesImport::getTemplateExampleRow();

        $csvContent = implode(';', $headers) . "\n";
        $csvContent .= implode(';', $exampleRow) . "\n";

        // Ajout du BOM UTF-8 pour les caractères français
        $csvContent = "\xEF\xBB\xBF" . $csvContent;

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="modele_import_militaires.csv"',
        ]);
    }
}
