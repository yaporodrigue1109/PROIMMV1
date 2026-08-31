<?php

namespace App\Http\Controllers\Admin\Agence;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AgencyImpersonationController extends Controller
{
    public function start(Request $request, Agence $agence): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        abort_unless($admin, 403);

        if ($agence->statut === 'desactive') {
            return back()->with('error', 'Cette agence est désactivée. Activez-la avant d’accéder à son espace.');
        }

        $user = User::query()
            ->where('agence_id', $agence->agence_id)
            ->where('statut', 'actif')
            ->orderByDesc('is_responsable')
            ->orderByRaw('CASE WHEN id_users = ? THEN 0 ELSE 1 END', [$agence->responsable_id])
            ->first();

        if (! $user) {
            return back()->with('error', 'Aucun responsable actif n’est disponible pour cette agence.');
        }

        Auth::guard('user')->logout();
        Auth::guard('user')->login($user);

        $request->session()->put('admin_agency_impersonation', [
            'admin_id' => $admin->getAuthIdentifier(),
            'admin_name' => $admin->name,
            'agence_id' => $agence->agence_id,
            'agence_name' => $agence->name,
            'user_id' => $user->getAuthIdentifier(),
            'started_at' => now()->toIso8601String(),
            'return_url' => route('admin.agences.index', ['selected_agence_id' => $agence->agence_id]),
        ]);
        $request->session()->regenerate();

        Log::notice('Entrée administrateur dans un espace agence', [
            'admin_id' => $admin->getAuthIdentifier(),
            'agence_id' => $agence->agence_id,
            'user_id' => $user->getAuthIdentifier(),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('agence.dashboard')
            ->with('success', "Vous consultez maintenant l’espace de l’agence {$agence->name}.");
    }

    public function stop(Request $request): RedirectResponse
    {
        $context = $request->session()->get('admin_agency_impersonation');
        $admin = Auth::guard('admin')->user();

        abort_unless($admin && is_array($context), 403);
        abort_unless((string) $context['admin_id'] === (string) $admin->getAuthIdentifier(), 403);

        Auth::guard('user')->logout();
        $request->session()->forget('admin_agency_impersonation');
        $request->session()->regenerate();

        Log::notice('Retour administrateur depuis un espace agence', [
            'admin_id' => $admin->getAuthIdentifier(),
            'agence_id' => $context['agence_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'ip' => $request->ip(),
        ]);

        return redirect()->to($context['return_url'] ?? route('admin.dashboard'))
            ->with('success', 'Vous êtes revenu dans l’espace administration.');
    }
}
