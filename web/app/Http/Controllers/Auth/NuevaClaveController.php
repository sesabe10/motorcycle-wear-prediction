<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NuevaClaveController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.nueva-clave', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $usuario, string $clave) {
                $usuario->forceFill(['password' => $clave, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($usuario));
            },
        );

        if ($estado !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($estado)])->onlyInput('email');
        }

        return redirect()->route('login')->with('estado', 'Listo. Ya puedes ingresar con tu contraseña nueva.');
    }
}
