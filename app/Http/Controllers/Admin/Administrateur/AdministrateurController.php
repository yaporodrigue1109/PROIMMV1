<?php

namespace App\Http\Controllers\Admin\Administrateur;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdministrateurController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Administrateurs/Index', [
            'administrateurs' => Admin::query()
                ->latest('created_at')
                ->get(['id_admin', 'name', 'email', 'phone', 'statut', 'created_at']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'statut' => ['required', 'boolean'],
        ]);

        $data['created_by'] = Auth::guard('admin')->id();
        Admin::create($data);

        return back()->with('success', 'L\'administrateur a été ajouté avec succès.');
    }
}
