<?php

namespace App\Http\Controllers\Agence\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Agence/Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['email'] = trim($credentials['email']);

        if (Auth::guard('user')->attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate(); // ✅ Une seule fois

            $user = Auth::guard('user')->user();

            if ($user->statut == 'actif') {
                return redirect()->intended(route('agence.dashboard'));
            }

            Auth::guard('user')->logout();
            return back()->withErrors([
                'email' => "Votre compte a été désactivé. Veuillez contacter votre administrateur."
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Veuillez vérifier votre email ou votre mot de passe.',
        ])->onlyInput('email');
    }




    public function logout(Request $request)
    {
        Auth::guard('user')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('agence.login')->with('success', 'Déconnexion réussie.');
    }
}
