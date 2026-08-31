<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Web\WebController;

Route::get('/guide/captures/{filename}', function (string $filename) {
    abort_unless(preg_match('/^[a-z0-9-]+\.png$/', $filename) === 1, 404);

    $path = base_path('documentation/captures/' . $filename);
    abort_unless(is_file($path), 404);

    return response(file_get_contents($path), 200, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('guide.capture');

//Route::get('/', function () {
//    return view('admin.auth.login');
//});


Route::get('test', [AuthController::class, 'showLoginForm'])->name('login');

Route::controller(WebController::class)->group(function () {
    Route::get('/', 'home')->name('web.home');
    Route::get('/biens', 'properties')->name('web.properties');
    Route::get('/biens/{listing}', 'propertyDetails')->name('web.properties.show');
    Route::get('/tarifs', 'pricing')->name('web.pricing');
    Route::get('/a-propos', 'about')->name('web.about');
    Route::get('/inscription-agence', 'registration')->name('web.registration');
    Route::post('/inscription-agence', 'registerAgency')->middleware('throttle:5,1')->name('web.registration.store');
    Route::get('/contact', 'contact')->name('web.contact');
    Route::get('/informations-legales/{document}', 'legal')
        ->whereIn('document', ['politique-confidentialite', 'conditions-generales', 'conditions-utilisation', 'mentions-legales'])
        ->name('web.legal');
    Route::post('/contact', 'sendContact')->middleware('throttle:5,1')->name('web.contact.send');
});
