<?php

namespace App\Http\Controllers;

use App\Models\CertificatDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificatDocumentController extends Controller
{
    public function download($id): StreamedResponse
    {
        $document = CertificatDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($document->chemin_fichier)) {
            abort(404, 'Document non trouvé');
        }

        return Storage::disk('public')->download(
            $document->chemin_fichier,
            $document->nom_fichier
        );
    }
}
