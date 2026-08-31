<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Cache les donnees rattachees a un proprietaire desactive dans une agence.
 *
 * Le filtre est volontairement global : une donnee historique ne doit pas
 * reapparaitre dans un autre module simplement parce que celui-ci a oublie
 * d'ajouter son propre filtre.
 */
trait VisibleWithActiveProprietaire
{
    public static function bootVisibleWithActiveProprietaire(): void
    {
        static::addGlobalScope('active_proprietaire', function (Builder $query): void {
            $model = $query->getModel();
            $table = $model->getTable();
            $isMysql = $model->getConnection()->getDriverName() === 'mysql';

            $query->where(function (Builder $visibility) use ($table, $isMysql): void {
                $visibility->whereNull("{$table}.proprietaire_id")
                    ->orWhereExists(function ($activeLink) use ($table, $isMysql): void {
                        $activeLink->selectRaw('1')->from('proprietaire_agences');

                        if ($isMysql) {
                            // Les anciennes tables n'ont pas toutes la meme collation.
                            $activeLink->whereRaw("BINARY proprietaire_agences.proprietaire_id = BINARY {$table}.proprietaire_id")
                                ->whereRaw("BINARY proprietaire_agences.agence_id = BINARY {$table}.agence_id");
                        } else {
                            $activeLink->whereColumn('proprietaire_agences.proprietaire_id', "{$table}.proprietaire_id")
                                ->whereColumn('proprietaire_agences.agence_id', "{$table}.agence_id");
                        }

                        $activeLink
                            ->where('proprietaire_agences.is_active', true)
                            ->whereNull('proprietaire_agences.deleted_at');
                    });
            });
        });
    }
}
