<?php

namespace App\Http\Controllers\Admin\Statistique;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StatistiqueController extends Controller
{

    public function index(): Response
    {
        return Inertia::render('Admin/Statistiques/Index');
    }


}
