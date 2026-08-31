<?php

namespace App\Http\Controllers\Agence\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AgenceActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

            // Une authentification avec email/mot de passe est une connexion
            // normale, distincte d'un accès lancé depuis l'administration.
            Auth::guard('admin')->logout();
            $request->session()->forget('admin_agency_impersonation');

            $request->session()->regenerate(); // ✅ Une seule fois

            $user = Auth::guard('user')->user();

            if ($user->statut == 'actif' && $user->agence && $user->agence->statut !== 'desactive') {
                try {
                    if (Schema::hasTable('agence_activity_logs')
                        && (bool) ($user->agence->parametrage?->journal_activites ?? true)) {
                        AgenceActivityLog::query()->create([
                            'agence_id' => $user->agence_id,
                            'user_id' => $user->getAuthIdentifier(),
                            'user_name' => $user->name,
                            'action' => 'connexion',
                            'description' => 'Connexion à l’espace agence',
                            'route_name' => 'agence.login.post',
                            'method' => 'POST',
                            'path' => '/agence/login',
                            'ip_address' => $request->ip(),
                            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                            'created_at' => now(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }

                return redirect()->intended(route('agence.dashboard'));
            }

            Auth::guard('user')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors([
                'email' => ! $user->agence || $user->agence->statut === 'desactive'
                    ? "Votre agence est désactivée. La connexion est temporairement impossible."
                    : "Votre compte a été désactivé. Veuillez contacter votre administrateur."
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
