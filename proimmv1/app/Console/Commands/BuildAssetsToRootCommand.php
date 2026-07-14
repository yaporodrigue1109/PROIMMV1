<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ProcessStartFailedException;

class BuildAssetsToRootCommand extends Command
{
    protected $signature = 'assets:build-root';

    protected $description = 'Exécute Vite build pour générer les assets Vite';

    public function handle(): int
    {
        try {
            $nodeBinary = $this->resolveNodeBinary();
            $viteBinary = base_path('node_modules/vite/bin/vite.js');

            if (! File::exists($viteBinary)) {
                $this->error('Impossible de trouver Vite dans node_modules.');
                $this->line('Vérifie que les dépendances JavaScript sont bien installées sur le serveur.');

                return self::FAILURE;
            }

            if (! $nodeBinary) {
                $this->error('Impossible de trouver Node.js sur ce serveur.');
                $this->line('Définis NODE_BINARY dans .env avec le chemin complet de node, ou installe Node.js sur le serveur.');

                return self::FAILURE;
            }

            $this->info("Lancement de {$nodeBinary} {$viteBinary} build...");

            $result = Process::path(base_path())
                ->timeout($this->buildTimeout())
                ->run([$nodeBinary, $viteBinary, 'build']);
        } catch (ProcessStartFailedException $e) {
            $this->error('Le build a échoué au démarrage.');
            $this->line($e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Le build a échoué de façon inattendue.');
            $this->line($e->getMessage());
            $this->line($this->buildCrashHint($e->getMessage()));

            return self::FAILURE;
        }

        if ($result->failed()) {
            $this->error("Le build a échoué.");
            $this->line($result->output());
            $this->line($result->errorOutput());

            return self::FAILURE;
        }

        $this->info('Build terminé avec succès.');

        return self::SUCCESS;
    }

    private function resolveNodeBinary(): ?string
    {
        $configured = env('NODE_BINARY');

        $candidates = array_values(array_filter([
            $configured,
            'node',
            'node.exe',
            '/opt/cpanel/ea-nodejs22/bin/node',
            '/opt/cpanel/ea-nodejs20/bin/node',
            '/opt/cpanel/ea-nodejs18/bin/node',
            '/opt/cpanel/ea-nodejs16/bin/node',
            '/opt/cpanel/ea-nodejs14/bin/node',
        ]));

        foreach ($candidates as $candidate) {
            if (str_contains($candidate, DIRECTORY_SEPARATOR)) {
                if (File::exists($candidate)) {
                    return $candidate;
                }

                continue;
            }

            try {
                $probe = Process::run([$candidate, '--version']);

                if ($probe->successful()) {
                    $version = trim($probe->output());

                    if ($this->isSupportedNodeVersion($version)) {
                        return $candidate;
                    }
                }
            } catch (ProcessStartFailedException) {
                // Essayez le candidat suivant.
            }
        }

        return null;
    }

    private function buildTimeout(): int
    {
        $configured = (int) env('VITE_BUILD_TIMEOUT', 1200);

        return max(1, $configured);
    }

    private function isSupportedNodeVersion(string $version): bool
    {
        $normalized = ltrim(trim($version), "vV");

        if ($normalized === '') {
            return false;
        }

        [$major] = array_map('intval', explode('.', $normalized . '.0.0'));

        if ($major === 20) {
            return version_compare($normalized, '20.19.0', '>=');
        }

        if ($major >= 22) {
            return version_compare($normalized, '22.12.0', '>=');
        }

        return false;
    }

    private function buildCrashHint(string $message): string
    {
        if (str_contains($message, 'signal "6"') || str_contains($message, 'Cannot find native binding') || str_contains($message, 'ELF load command past end of file')) {
            return 'Piste probable: le binaire natif Vite/Rolldown est corrompu ou mal installé sur le serveur. Fais un `rm -rf node_modules package-lock.json` puis réinstalle les dépendances avec le Node compatible, ou rebuild uniquement les dépendances JS sur la machine de prod.';
        }

        return 'Vérifie la version de Node.js et l’intégrité de node_modules sur le serveur.';
    }
}
