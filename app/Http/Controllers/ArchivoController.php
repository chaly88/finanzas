<?php

namespace App\Http\Controllers;

use App\Models\Archivo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ArchivoController extends Controller
{
    public function preview(Archivo $archivo): Response
    {
        abort_unless((int) $archivo->user_id === (int) auth()->id() && ! $archivo->is_folder, 404);
        abort_unless(in_array($archivo->mime_type, [
            'application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        ], true), 415);

        $disk = Storage::disk('local');
        abort_unless($archivo->path && $disk->exists($archivo->path), 404);

        return response()->file($disk->path($archivo->path), [
            'Content-Type' => $archivo->mime_type,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
