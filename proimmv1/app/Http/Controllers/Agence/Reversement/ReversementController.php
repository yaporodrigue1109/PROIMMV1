<?php

namespace App\Http\Controllers\Agence\Reversement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReversementController extends Controller
{
    public function index()
    {
        return view('agence.reversement.index');
    }




}