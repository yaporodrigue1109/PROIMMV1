<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/** Empêche les données d'un locataire sans bail actif de réapparaître. */
trait VisibleWithActiveLocataire
{
    public static function bootVisibleWithActiveLocataire(): void
    {
        static::addGlobalScope('active_locataire', function (Builder $query): void {
            $model = $query->getModel();
            $table = $model->getTable();
            $isMysql = $model->getConnection()->getDriverName() === 'mysql';

            $query->where(function (Builder $visibility) use ($table, $isMysql): void {
                $visibility->whereNull("{$table}.locataire_id")
                    ->orWhereExists(function ($activeLease) use ($table, $isMysql): void {
                        $activeLease->selectRaw('1')->from('locataire_agence');

                        if ($isMysql) {
                            $activeLease->whereRaw("BINARY locataire_agence.locataire_id = BINARY {$table}.locataire_id")
                                ->whereRaw("BINARY locataire_agence.agence_id = BINARY {$table}.agence_id");
                        } else {
                            $activeLease->whereColumn('locataire_agence.locataire_id', "{$table}.locataire_id")
                                ->whereColumn('locataire_agence.agence_id', "{$table}.agence_id");
                        }

                        $activeLease->where('locataire_agence.is_active', true);
                    });
            });
        });
    }
}
