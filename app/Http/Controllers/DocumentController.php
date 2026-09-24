<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /** Reached only through a short-lived signed URL (see `signed` middleware in routes/web.php). */
    public function download(Document $document): StreamedResponse
    {
        Gate::authorize('view', $document);

        activity('document')->performedOn($document)->causedBy(auth()->user())->log('downloaded document');

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }
}
