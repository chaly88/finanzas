<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{

public function store(\Illuminate\Http\Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (\Illuminate\Support\Facades\Auth::attempt(
        $credentials,
        $request->boolean('remember')
    )) {
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    return back()
        ->withErrors([
            'email' => 'Las credenciales ingresadas no son correctas.',
        ])
        ->onlyInput('email');
}

public function destroy(\Illuminate\Http\Request $request)
{
    \Illuminate\Support\Facades\Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
}

}
