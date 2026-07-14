<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $admin = Admin::where('email', $request->email)->first();

            if (! $admin || ! Hash::check($request->password, $admin->password)) {
                return back()
                    ->withErrors(['email' => 'Email ou mot de passe incorrect.'])
                    ->onlyInput('email');
            }

            if (Auth::guard('admin')->attempt(
                ['email' => $request->email, 'password' => $request->password],
                $request->boolean('remember')
            )) {
                $request->session()->regenerate();
                $admin = Auth::guard('admin')->user();

                if ((int) $admin->statut !== 1) {
                    Auth::guard('admin')->logout();

                    return back()
                        ->withErrors(['email' => 'Ce compte est desactive. Veuillez contacter l\'administrateur.'])
                        ->onlyInput('email');
                }

                return redirect()->intended(route('admin.dashboard'));
            }

            return back()
                ->withErrors(['email' => 'Email ou mot de passe incorrect.'])
                ->onlyInput('email');
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['email' => 'Une erreur est survenue. Veuillez reessayer.'])
                ->onlyInput('email');
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Deconnexion reussie.');
    }
}
