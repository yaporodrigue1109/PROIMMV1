<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $admin = Auth::guard('admin')->user();
        $user = Auth::guard('user')->user();
        $user?->loadMissing(['agence']);

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'auth' => [
                'admin' => $admin ? [
                    'id_admin' => $admin->id_admin,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'phone' => $admin->phone,
                    'statut' => $admin->statut,
                ] : null,
                'user' => $user ? [
                    'id_users' => $user->id_users,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'statut' => $user->statut,
                    'agence_id' => $user->agence_id,
                    'agence' => $user->agence ? [
                        'agence_id' => $user->agence->agence_id,
                        'name' => $user->agence->name,
                        'code_agence' => $user->agence->code_agence,
                        'abonnement_id' => $user->agence->abonnement_id,
                        'abonnement_start' => $user->agence->abonnement_start,
                        'abonnement_end' => $user->agence->abonnement_end,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
