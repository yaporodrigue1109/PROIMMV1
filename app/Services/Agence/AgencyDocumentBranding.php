<?php

namespace App\Services\Agence;

use App\Models\Agence;
use App\Services\SettingService;

class AgencyDocumentBranding
{
    /** @var array<int, string> */
    private array $temporaryFiles = [];

    public function __construct(private readonly SettingService $settings)
    {
    }

    public function localLogoPath(?Agence $agence): ?string
    {
        foreach ($this->logoValues($agence) as $value) {
            if ($path = $this->resolveLocalPath($value)) {
                return $path;
            }
        }

        $fallback = public_path('admin/logo/playstore-icon-revised.png');

        return is_file($fallback) && is_readable($fallback) ? $fallback : null;
    }

    public function logoUrl(?Agence $agence): ?string
    {
        foreach ($this->logoValues($agence) as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            if (preg_match('#^https?://#i', $value)) {
                $host = parse_url($value, PHP_URL_HOST);
                $path = parse_url($value, PHP_URL_PATH);

                return in_array($host, ['localhost', '127.0.0.1'], true) && $path
                    ? asset(ltrim($path, '/'))
                    : $value;
            }

            $path = ltrim($value, '/');
            if (str_starts_with($path, 'admin/') || str_starts_with($path, 'assets/') || str_starts_with($path, 'storage/')) {
                return asset($path);
            }

            return asset('storage/'.preg_replace('#^public/#', '', $path));
        }

        return asset('admin/logo/playstore-icon-revised.png');
    }

    public function logoDataUri(?Agence $agence): ?string
    {
        $path = $this->localLogoPath($agence);
        if (! $path || ! ($contents = @file_get_contents($path))) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    public function watermarkPath(?Agence $agence): ?string
    {
        $sourcePath = $this->localLogoPath($agence);
        if (! $sourcePath || ! extension_loaded('gd')) {
            return null;
        }

        $source = match (@exif_imagetype($sourcePath)) {
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            default => false,
        };
        if (! $source) {
            return null;
        }

        $canvas = imagecreatetruecolor(imagesx($source), imagesy($source));
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
        imagecopy($canvas, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
        imagefilter($canvas, IMG_FILTER_COLORIZE, 0, 0, 0, 112);

        $temporary = tempnam(sys_get_temp_dir(), 'agency-document-watermark-');
        if ($temporary === false) {
            return null;
        }

        $path = $temporary.'.png';
        @unlink($temporary);
        if (! imagepng($canvas, $path)) {
            return null;
        }

        $this->temporaryFiles[] = $path;

        return $path;
    }

    public function __destruct()
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /** @return array<int, mixed> */
    private function logoValues(?Agence $agence): array
    {
        $agence?->loadMissing('parametrage');
        $configuration = $this->settings->getSetting();

        return [
            $agence?->parametrage?->logo,
            $configuration->logo,
        ];
    }

    private function resolveLocalPath(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            $value = (string) parse_url($value, PHP_URL_PATH);
        }

        $path = ltrim(urldecode($value), '/');
        $storageRelative = preg_replace('#^(?:public/|storage/)#', '', $path);
        $candidates = [
            public_path($path),
            storage_path('app/public/'.$storageRelative),
            public_path('storage/'.$storageRelative),
        ];

        foreach (array_unique($candidates) as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
