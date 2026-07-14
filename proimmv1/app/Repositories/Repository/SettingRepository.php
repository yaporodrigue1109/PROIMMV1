<?php

namespace App\Repositories\Repository;

use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Models\Configuration;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SettingRepository implements SettingRepositoryInterface
{
    protected Configuration $model;

    public function __construct(Configuration $model)
    {
        $this->model = $model;
    }

    /**
     * Récupérer la configuration (première entrée ou créer une nouvelle)
     */
    public function get(): Configuration
    {
        $setting = $this->model->first();

        if (!$setting) {
            $setting = $this->model->create([
                'name' => config('app.name'),
            ]);
        }

        return $setting;
    }

    /**
     * Mettre à jour la configuration
     */
    public function update(array $data): Configuration
    {
        $setting = $this->get();

        // Gérer les uploads de fichiers
        if (isset($data['logo'])) {
            if ($setting->logo) {
                \Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $data['logo']->store('settings/logos', 'public');
        }

        if (isset($data['flavicon'])) {
            if ($setting->flavicon) {
                \Storage::disk('public')->delete($setting->flavicon);
            }
            $data['flavicon'] = $data['flavicon']->store('settings/favicons', 'public');
        }

        $setting->update($data);

        return $setting->fresh();
    }

    /**
     * Récupérer une valeur spécifique
     */
    public function getValue(string $key): mixed
    {
        $setting = $this->get();

        if (!$setting->hasAttribute($key)) {
            throw new ModelNotFoundException("L'attribut '{$key}' n'existe pas");
        }

        return $setting->getAttribute($key);
    }

    /**
     * Définir une valeur spécifique
     */
    public function setValue(string $key, mixed $value): void
    {
        $setting = $this->get();

        if (!$setting->isFillable($key)) {
            throw new \InvalidArgumentException("L'attribut '{$key}' n'est pas assignable en masse");
        }

        $setting->update([$key => $value]);
    }
}