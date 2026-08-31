<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Admin/Profile/Show', [
            'admin' => Auth::guard('admin')->user()?->only([
                'id_admin',
                'name',
                'email',
                'phone',
                'statut',
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        abort_if(! $admin, 401);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id_admin, 'id_admin')],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $admin->update($data);

        return back()->with('success', 'Votre profil a été mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        abort_if(! $admin, 401);

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ]);
        }

        $admin->password = $data['password'];
        $admin->save();

        return back()->with('success', 'Votre mot de passe a été modifié.');
    }
}
