<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configurations';

    protected $fillable = [
        'name',
        'boite_postal',
        'contact1',
        'contact2',
        'contact3',
        'langue',
        'adresse',
        'raison_social',
        'site_web',
        'politique_confidentialite',
        'condition_generale',
        'cgu',
        'mention_legale',
        'email1',
        'email2',
        'logo',
        'flavicon',
        'num_rccm',
        'capital',
        'num_cnps',
        'num_cc',
        'facebook',
        'instagram',
        'linkedin',
        'google',
        'twitter',
        'website_story',
        'website_mission_title',
        'website_mission_text',
        'website_commitments',
        'website_faqs',
        'owner_android_url',
        'owner_ios_url',
        'tenant_android_url',
        'tenant_ios_url',
        'subscription_manual_payment_enabled',
        'subscription_wave_number',
        'subscription_orange_money_number',
        'subscription_moov_money_number',
        'subscription_mtn_money_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'website_commitments' => 'array',
        'website_faqs' => 'array',
        'subscription_manual_payment_enabled' => 'boolean',
    ];

    protected $appends = [
        'logo_url',
        'favicon_url',
    ];

    /**
     * Obtenir le chemin complet du logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->publicMediaUrl($this->logo);
    }

    /**
     * Obtenir le chemin complet du favicon
     */
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->publicMediaUrl($this->flavicon);
    }

    private function publicMediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        // Compatibilité avec les anciens fichiers placés directement dans public/admin/...
        if (is_file(public_path($path))) {
            return asset($path);
        }

        $path = preg_replace('#^storage/#', '', $path);

        return asset('storage/' . $path);
    }

    /**
     * Obtenir les informations de contact principales
     */
    public function getPrimaryContactAttribute(): array
    {
        return [
            'email' => $this->email1,
            'phone' => $this->contact1,
        ];
    }

    /**
     * Obtenir tous les réseaux sociaux
     */
    public function getSocialMediaAttribute(): array
    {
        return [
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'twitter' => $this->twitter,
            'google' => $this->google,
        ];
    }
}
