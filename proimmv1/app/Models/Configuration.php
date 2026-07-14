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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtenir le chemin complet du logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    /**
     * Obtenir le chemin complet du favicon
     */
    public function getFaviconUrlAttribute(): ?string
    {
        return $this->flavicon ? asset('storage/' . $this->flavicon) : null;
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