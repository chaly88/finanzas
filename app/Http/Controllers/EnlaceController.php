<?php

namespace App\Http\Controllers;

use App\Models\Enlace;
use Illuminate\Http\RedirectResponse;

class EnlaceController extends Controller
{
    public function abrir(Enlace $enlace): RedirectResponse
    {
        abort_unless((int) $enlace->user_id === (int) auth()->id() && ! $enlace->is_folder, 404);

        $scheme = parse_url($enlace->url, PHP_URL_SCHEME);
        abort_unless(in_array(strtolower((string) $scheme), ['http', 'https'], true), 404);

        $enlace->increment('visits', 1, ['last_visited_at' => now()]);

        return redirect()->away($enlace->url);
    }
}
