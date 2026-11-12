<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class RecuperarClaveController extends Controller
{
    public function create(): View
    {
        return view('auth.recuperar');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $estado = Password::sendResetLink($request->only('email'));

        if ($estado === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => __($estado)])->onlyInput('email');
        }

        // Mismo mensaje exista o no la cuenta, para no revelar qué correos están registrados.
        return back()->with('estado', 'Si el correo está registrado, te enviamos un enlace para crear una contraseña nueva.');
    }
}
