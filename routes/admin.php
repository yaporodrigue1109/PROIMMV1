<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\preferenceController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Dashboard\DashboardController;
use App\Http\Controllers\Admin\Agence\AgenceController;
use App\Http\Controllers\Admin\Abonnement\AbonnementController;
use App\Http\Controllers\Admin\Profile\ProfileController;
use App\Http\Controllers\Admin\Module\ModuleController;
use App\Http\Controllers\Admin\Ticket\TicketController;
use App\Http\Controllers\Admin\Settings\SettingsController;
use App\Http\Controllers\Admin\Statistique\StatistiqueController;


Route::prefix('list')->controller(preferenceController::class)->group(function () {
    Route::get('/city', 'getVille');
    Route::get('/lotByProprietaire', 'getlotByProprietaire');
    Route::get('/getBatimentBylot', 'getBatimentBylot');
    Route::get('/getPorteByBatiment', 'getPorteByBatiment');

});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::match(['get', 'post'], '/deploy/build-assets', function () {
        try {
            $exitCode = Artisan::call('assets:build-root');

            if ($exitCode !== 0) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('error', Artisan::output());
            }

            return redirect()
                ->route('admin.dashboard')
                ->with('success', Artisan::output());
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', $e->getMessage());
        }
    })->name('deploy.build-assets');

    /*
    |--------------------------------------------------------------------------
    | Dashboard / Profile
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])

            ->name('dashboard');
    });

    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])

            ->name('profile');
    });

    /*
    |--------------------------------------------------------------------------
    | Agences
    |--------------------------------------------------------------------------
    */
    Route::prefix('agences')->name('agences.')->group(function () {
        Route::get('/', [AgenceController::class, 'index'])

            ->name('index');
        Route::get('/create', [AgenceController::class, 'create'])

            ->name('create');

        Route::post('/store', [AgenceController::class, 'store'])

            ->name('store');

        Route::put('/update/{agence}', [AgenceController::class, 'update'])

            ->name('update');

        Route::get('/abonnement', [AgenceController::class, 'abonnementAgence'])

            ->name('abonnementAgence');
        
        Route::get('/{code}', [AgenceController::class, 'show'])
          //  ->middleware('permission:view-agences')
            ->name('show');
        Route::get('/{code}/edit', [AgenceController::class, 'edit'])
           // ->middleware('permission:edit-agences')
            ->name('edit');


        Route::get('/{code}/life', [AgenceController::class, 'life'])
            //->middleware('permission:view-agences')
            ->name('life');


    });

    /*
    |--------------------------------------------------------------------------
    | Abonnements
    |--------------------------------------------------------------------------
    */
    Route::prefix('abonnements')->name('abonnements.')->group(function () {
        Route::get('/', [AbonnementController::class, 'index'])
            //->middleware('permission:view-abonnements')
            ->name('index');
        Route::get('/create', [AbonnementController::class, 'create'])
         //   ->middleware('permission:create-abonnements')
            ->name('create');
        Route::post('/', [AbonnementController::class, 'store'])
         //   ->middleware('permission:create-abonnements')
            ->name('store');
        Route::get('/{codeAgence}', [AbonnementController::class, 'show'])
         //   ->middleware('permission:view-abonnements')
            ->name('show');
        Route::get('/{codeAgence}/edit', [AbonnementController::class, 'edit'])
         //   ->middleware('permission:edit-abonnements')
            ->name('edit');
        Route::put('/{codeAgence}', [AbonnementController::class, 'update'])
         //   ->middleware('permission:edit-abonnements')
            ->name('update');
    });

    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    */
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])
        //    ->middleware('permission:view-modules')
            ->name('index');
        Route::get('/create', [ModuleController::class, 'create'])
           // ->middleware('permission:create-modules')
            ->name('create');
        Route::get('/{code}', [ModuleController::class, 'show'])
          //  ->middleware('permission:view-modules')
            ->name('show');
        Route::get('/{code}/edit', [ModuleController::class, 'edit'])
          //  ->middleware('permission:edit-modules')
            ->name('edit');
    });

    /*
    |--------------------------------------------------------------------------
    | Tickets
    |--------------------------------------------------------------------------
    */
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])
            ->middleware('permission:view-tickets')
            ->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])
           // ->middleware('permission:view-settings')
             ->name('index');
        Route::put('/', [SettingsController::class, 'update'])
          //  ->middleware('permission:edit-settings')
             ->name('update');
        Route::put('/tarification', [SettingsController::class, 'updateTarification'])
          //  ->middleware('permission:edit-settings')
             ->name('updateTarification');

        Route::post('/calculer-prix', [SettingsController::class, 'calculerPrix'])
            ->name('calculer-prix');

        // Récupérer les tarifs publics
        Route::get('/tarifs-publics', [SettingsController::class, 'getTarifsPublics'])
            ->name('tarifs-publics');
        Route::put('/tarifs', [SettingsController::class, 'updateTarification'])
         //   ->middleware('permission:edit-settings')
            ->name('update_tarification');
    });



    Route::prefix('statistiques')->name('statistiques.')->group(function () {
        Route::get('/', [StatistiqueController::class, 'index'])
            ->name('index');
    });

});









//
//use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Admin\Auth\AuthController;
//use App\Http\Controllers\Admin\Dashboard\DashboardController;
//use App\Http\Controllers\Admin\Agence\AgenceController;
//use App\Http\Controllers\Admin\Abonnement\AbonnementController;
//use App\Http\Controllers\Admin\Profile\ProfileController;
//use App\Http\Controllers\Admin\Module\ModuleController;
//use App\Http\Controllers\Admin\Ticket\TicketController;
//use App\Http\Controllers\Admin\Settings\SettingsController;
//
//
//
//    /*
//    |--------------------------------------------------------------------------
//    | Auth
//    |--------------------------------------------------------------------------
//    */
//    Route::controller(AuthController::class)->group(function () {
//        Route::get('/login', 'showLoginForm')->name('login');
//        Route::post('/login', 'login')->name('login.post');
//    });
//
//    /*
//    |--------------------------------------------------------------------------
//    | Protected Routes
//    |--------------------------------------------------------------------------
//    */
//    Route::middleware(['admin'])->group(function () {
//
//        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
//
//        /*
//        |--------------------------------------------------------------------------
//        | Dashboard / Profile
//        |--------------------------------------------------------------------------
//        */
//        Route::prefix('dashboard')->group(function () {
//            Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
//        });
//
//        Route::prefix('profile')->group(function () {
//            Route::get('/', [ProfileController::class, 'show'])->name('profile');
//        });
//
//        /*
//        |--------------------------------------------------------------------------
//        | Agences
//        |--------------------------------------------------------------------------
//        */
//        Route::prefix('agences')->name('agences.')->group(function () {
//            Route::get('/', [AgenceController::class, 'index'])->name('index');
//            Route::get('/create', [AgenceController::class, 'create'])->name('create');
//            Route::get('/{code}', [AgenceController::class, 'show'])->name('show');
//            Route::get('/{code}/edit', [AgenceController::class, 'edit'])->name('edit');
//        });
//
//        /*
//        |--------------------------------------------------------------------------
//        | Abonnements
//        |--------------------------------------------------------------------------
//        */
//        Route::prefix('abonnements')->name('abonnements.')->group(function () {
//            Route::get('/', [AbonnementController::class, 'index'])->name('index');
//            Route::get('/plans', [AbonnementController::class, 'plans'])->name('plans');
//            Route::get('/create', [AbonnementController::class, 'create'])->name('create');
//            Route::get('/{codeAgence}', [AbonnementController::class, 'show'])->name('show');
//            Route::get('/{codeAgence}/edit', [AbonnementController::class, 'edit'])->name('edit');
//        });
//
//        /*
//        |--------------------------------------------------------------------------
//        | Modules
//        |--------------------------------------------------------------------------
//        */
//        Route::prefix('modules')->name('modules.')->group(function () {
//            Route::get('/', [ModuleController::class, 'index'])->name('index');
//            Route::get('/create', [ModuleController::class, 'create'])->name('create');
//            Route::get('/{code}', [ModuleController::class, 'show'])->name('show');
//            Route::get('/{code}/edit', [ModuleController::class, 'edit'])->name('edit');
//        });
//
//        /*
//        |--------------------------------------------------------------------------
//        | Tickets
//        |--------------------------------------------------------------------------
//        */
//        Route::prefix('tickets')->name('tickets.')->group(function () {
//            Route::get('/', [TicketController::class, 'index'])->name('index');
//        });
//
//        /*
//        |--------------------------------------------------------------------------
//        | Settings
//        |--------------------------------------------------------------------------
//        */
//        Route::prefix('configuration')->name('settings.')->group(function () {
//            Route::get('/', [SettingsController::class, 'index'])->name('index');
//            Route::put('/', [SettingsController::class, 'update'])->name('update');
//        });
//
//    });
//
