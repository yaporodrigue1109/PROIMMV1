<?php

namespace App\Http\Controllers\Admin\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
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
}
