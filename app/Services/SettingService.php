<?php

namespace App\Services;

use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Models\Configuration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingService
{
    protected SettingRepositoryInterface $settingRepository;

    const CACHE_KEY = 'app_settings';
    const CACHE_TTL = 3600; // 1 heure

    public function __construct(SettingRepositoryInterface $settingRepository)
    {
        $this->settingRepository = $settingRepository;
    }

    /**
     * Récupérer la configuration
     */
//    public function getSetting(): Configuration
//    {
//        return Cache::remember(
//            self::CACHE_KEY,
//            self::CACHE_TTL,
//            fn() => $this->settingRepository->get()
//        );
//    }

    public function getSetting(): Configuration  // ← Type hint strict
    {
        return $this->settingRepository->get();  // ← Doit retourner Configuration
    }

    /**
     * Mettre à jour la configuration
     */
    public function updateSetting(array $data): Configuration
    {
        try {
            $setting = $this->settingRepository->update($data);

            // Invalider le cache
            $this->clearCache();

            Log::info('Configuration mise à jour', [
                'user_id' => auth()->id(),
                'updated_fields' => array_keys($data),
            ]);

            return $setting;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la configuration', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Récupérer une valeur spécifique
     */
    public function getValue(string $key): mixed
    {
        $setting = $this->getSetting();

        return $setting->getAttribute($key);
    }

    /**
     * Définir une valeur spécifique
     */
    public function setValue(string $key, mixed $value): void
    {
        $this->settingRepository->setValue($key, $value);
        $this->clearCache();
    }

    /**
     * Vérifier si une configuration existe
     */
    public function exists(): bool
    {
        try {
            $this->getSetting();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir le nom commercial
     */
    public function getCompanyName(): string
    {
        return $this->getValue('name') ?? config('app.name');
    }

    /**
     * Obtenir l'email principal
     */
    public function getPrimaryEmail(): string
    {
        return $this->getValue('email1') ?? config('mail.from.address');
    }

    /**
     * Obtenir le téléphone principal
     */
    public function getPrimaryPhone(): string
    {
        return $this->getValue('contact1') ?? '';
    }

    /**
     * Vider le cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Rafraîchir le cache
     */
    public function refreshCache(): Configuration
    {
        $this->clearCache();
        return $this->getSetting();
    }

    /**
     * Récupérer toutes les données publiques (pour le frontend)
     */
    public function getPublicData(): array
    {
        $setting = $this->getSetting();

        return [
            'name' => $setting->name,
            'raison_social' => $setting->raison_social,
            'email' => $setting->email1,
            'phone' => $setting->contact1,
            'address' => $setting->adresse,
            'site_web' => $setting->site_web,
            'logo' => $setting->logo ? asset('storage/' . $setting->logo) : null,
            'favicon' => $setting->flavicon ? asset('storage/' . $setting->flavicon) : null,
            'social' => [
                'facebook' => $setting->facebook,
                'instagram' => $setting->instagram,
                'linkedin' => $setting->linkedin,
                'twitter' => $setting->twitter,
                'google' => $setting->google,
            ],
        ];
    }
}