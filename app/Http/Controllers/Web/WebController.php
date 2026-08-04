<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Propriete;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebController extends Controller
{
    public function home(): Response { return Inertia::render('Web/Home', ['properties' => $this->listProperties(6)]); }
    public function properties(Request $request): Response { return Inertia::render('Web/Properties', ['properties' => $this->listProperties(24), 'mode' => $request->string('mode')->toString()]); }
    public function pricing(): Response { return Inertia::render('Web/Pricing'); }
    public function about(): Response { return Inertia::render('Web/About'); }
    public function contact(): Response { return Inertia::render('Web/Contact'); }
    public function sendContact(Request $request) { $request->validate(['name'=>'required|string|max:100','email'=>'required|email','message'=>'required|string|max:2000']); return back()->with('success', 'Votre message a bien été envoyé. Notre équipe vous répondra rapidement.'); }
    private function listProperties(int $limit): array { return Propriete::query()->where('is_actif', true)->with(['typePropriete','batiments.portes.tarifActif'])->latest()->limit($limit)->get()->map(function ($property) { $door=$property->batiments->flatMap->portes->firstWhere('is_occupe', false) ?? $property->batiments->flatMap->portes->first(); return ['id'=>$property->propriete_id,'reference'=>$property->reference,'title'=>$property->typePropriete?->name ?? 'Propriété','address'=>$property->adresse_complete,'mode'=>$property->is_allocation ? 'location' : 'vente','price'=>(float)($door?->tarifActif?->mt_loyer ?? $door?->mt_loyer ?? 0),'surface'=>$door?->superficie_m2,'image'=>null]; })->all(); }
}
