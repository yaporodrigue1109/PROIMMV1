<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agence\Auth\AuthController;
use App\Http\Controllers\Agence\Dashboard\DashboardController;
use App\Http\Controllers\Agence\Statistique\StatistiqueController;
use App\Http\Controllers\Agence\Propriete\ProprieteController;
use App\Http\Controllers\Agence\Maintenance\MaintenanceController;
use App\Http\Controllers\Agence\Maintenance\FonctionMaintenanceController;
use App\Http\Controllers\Agence\Maintenance\TypeMaintenanceController;
use App\Http\Controllers\Agence\Maintenance\MaintenancierController;
use App\Http\Controllers\Agence\Personnel\PersonnelController;
use App\Http\Controllers\Agence\Locataire\LocataireController;
use App\Http\Controllers\Agence\Proprietaire\ProprietaireController;
use App\Http\Controllers\Agence\Proprietaire\LotController;
use App\Http\Controllers\Agence\Caisse\CaisseController;
use App\Http\Controllers\Agence\Reversement\ReversementController;
use App\Http\Controllers\Agence\Loyer\LoyerController;
use App\Http\Controllers\Agence\Propriete\TypeProprieteController;
use App\Http\Controllers\Agence\Propriete\EquipementProprieteController;
use App\Http\Controllers\Agence\Propriete\ProssimiteProprieteController;
use App\Http\Controllers\Agence\Parametrage\ParametrageController;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Auth c'est la route qui traite la partie agences
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
});

Route::middleware(['user'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', function () {
        return Inertia::render('Agence/Profile/Index', [
            'user' => auth('user')->user(),
        ]);
    })->name('profile');

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::controller(StatistiqueController::class)->prefix('statistiques')->name('statistiques.')->group(function () {
        Route::get('/', 'index')->name('index');
    });

    Route::controller(ProprieteController::class)->prefix('proprietes')->name('proprietes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/show/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/update/{id}', 'update')->name('update');
        Route::delete('/destroy/{id}', 'destroy')->name('destroy');
        Route::patch('/liberer/{id}', 'liberer')->name('liberer');
        Route::patch('/occuper/{id}', 'occuper')->name('occuper');
        //  Route::resource('proprietes', ProprieteController::class);

    });

    Route::prefix('maintenance')->name('maintenance.')->group(function () {

        Route::controller(MaintenanceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');

            // âš ï¸ IMPORTANT : Les routes statiques DOIVENT Ãªtre AVANT la route dynamique /{id}

            Route::get('/intervention/{id}', 'showIntervention')->name('intervention.show');

            // ðŸ†• Routes JSON pour les modales de modification
            Route::get('/maintenancier/{id}/json', 'getMaintenancierJson')->name('maintenancier.json');
            Route::get('/intervention/{id}/json', 'getInterventionJson')->name('intervention.json');
            Route::get('/intervention/{id}/tasks', 'getInterventionTasks')->name('intervention.tasks');
            Route::patch('/{id}/statut', 'changerStatut')->name('statut');
            Route::patch('/detail/{id}/statut', 'changerDetailStatut')->name('detail.statut');

            // ðŸ†• Routes de mise Ã  jour
            Route::put('/maintenancier/{id}', 'updateMaintenancier')->name('maintenancier.update');
            Route::put('/fonction/{id}', 'updateFonction')->name('fonction.update');
            Route::put('/type-intervention/{id}', 'updateTypeIntervention')->name('type.update');

            // âš ï¸ Cette route doit Ãªtre la DERNIÃˆRE car elle capture tout ce qui n'est pas matchÃ© avant
            Route::get('/{id}', 'show')->name('show.json'); // pour AJAX
        });

        Route::controller(FonctionMaintenanceController::class)->prefix('fonction')->name('fonction.')->group(function () {
            Route::post('/store', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('delete');
        });

        Route::controller(TypeMaintenanceController::class)->prefix('type')->name('type.')->group(function () {
            Route::post('/store', 'store')->name('store');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('delete');
        });

        Route::controller(MaintenancierController::class)->prefix('maintenancier')->name('maintenancier.')->group(function () {
            Route::post('/store', 'store')->name('store');
            Route::get('/maintenancier/{id}', 'showMaintenancier')->name('show');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('delete');
        });

    });




    Route::controller(PersonnelController::class)->prefix('personnel')->name('personnel.')->group(function () {
        Route::get('/',                         'index')->name('index');
        Route::get('/create',                  'create')->name('create');
        Route::get('/{id}',                    'show')->name('show');
        Route::post('/',                       'store')->name('store');
        Route::get('/{id}/edit',               'edit')->name('edit');
        Route::put('/{id}',                    'update')->name('update');
        Route::delete('/{id}',                 'destroy')->name('destroy');
        Route::patch('/{id}/activate',         'activate')->name('activate');
        Route::patch('/{id}/deactivate',       'deactivate')->name('deactivate');
    });



    Route::resource('locataires', LocataireController::class);

    Route::patch('locataires/{locataire}/resilier',[LocataireController::class, 'resilier'])->name('locataires.resilier');

    Route::get('locataires/portes-libres/{propriete}',[LocataireController::class, 'portesLibres'])->name('locataires.portes-libres');




    Route::prefix('caisse')->name('caisse.')->group(function () {
        Route::get('/', [CaisseController::class, 'index'])->name('index');
        // Route::get('/loyer', [CaisseController::class, 'loyer'])->name('caisse.loyer');
        Route::get('/maintenance', [CaisseController::class, 'maintenance'])->name('maintenance');
        Route::get('/depense-agence', [CaisseController::class, 'depenseAgence'])->name('depense.agence');
        Route::get('/vente-bien', [CaisseController::class, 'venteBien'])->name('vente.bien');


        Route::controller(LoyerController::class)->prefix('loyer')->group(function () {
            Route::get('/', 'index')->name('loyer');
            Route::get('/search',  'search')->name('loyer.search');
            Route::post('/pay', 'pay')->name('loyer.payer');

        });

    });



    Route::prefix('reversement')->group(function () {
        Route::get('/', [ReversementController::class, 'index'])->name('reversement.index');


    });

    Route::controller(ProprietaireController::class)->prefix('proprietaire')->name('proprietaire.')->group(function () {
        Route::get('/','index')->name('index');
        Route::get('/create','create')->name('create');
        Route::post('/','store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit','edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        // Actions sur la liaison agence
        Route::patch('/{proprietaireAgenceId}/activate', 'activate')->name('activate');
        Route::patch('/{proprietaireAgenceId}/deactivate','deactivate')->name('deactivate');





        Route::controller(LotController::class)->prefix('lots')->name('lots.')->group(function () {
            Route::get('{proprietaireId}',  'getByProprietaire')->name('by-proprietaire');
            Route::post('{proprietaireId}', 'store')->name('store');
            Route::put('/{id}',     'update')->name('update');
            Route::delete('/{id}',   'destroy')->name('destroy');
        });

    });




    Route::controller(ParametrageController::class)->prefix('parametrage')->name('parametrage.')->group(function () {

        Route::get('/',  'index')->name('index');
        Route::put('agence',        'updateAgence')->name('agence.update');
        Route::put('general',       'updateGeneral')->name('general.update');
        Route::put('facturation',  'updateFacturation')->name('facturation.update');
        Route::put('logos',       'updateLogos')->name('logos.update');
        Route::put('signatures',   'updateSignatures')->name('signatures.update');
        Route::put('notifications', 'updateNotifications')->name('notifications.update');

    });



    Route::resource('types-propriete', TypeProprieteController::class)
        ->only(['store', 'update', 'destroy'])
        ->names('types-propriete');

    Route::resource('equipement-propriete', EquipementProprieteController::class)
        ->only(['store', 'update', 'destroy'])
        ->names('equipement-propriete');

    Route::resource('possimite-propriete', ProssimiteProprieteController::class)
        ->only(['store', 'update', 'destroy'])
        ->names('possimite-propriete');

});
