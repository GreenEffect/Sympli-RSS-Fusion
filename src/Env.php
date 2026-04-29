<?php

/*
 * --------------------------------------------------------------------------------
 *  Sympli RSS Fusion
 * --------------------------------------------------------------------------------
 *  RSS Fusion [https://www.rss-fusion.fr] en mode KISS : Fusionner, filtrer, manipuler et gérer ses flux RSS
 *  en toute simplicité / Merge, filter, manipulate and manage your RSS feeds
 *  with simplicity
 *
 *  @project     Sympli RSS Fusion
 *  @description Fusion, filtrage et gestion simplifiée de flux RSS /
 *               Simplified RSS feed merging, filtering, and management
 *  @author      Erase ● Green Effect <contact@green-effect.fr>
 *  @version     1.0
 *  @license     Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International
 *               https://creativecommons.org/licenses/by-nc-sa/4.0/
 * --------------------------------------------------------------------------------
 */


declare(strict_types=1);

namespace RssFusionKiss;

final class Env
{
    /**
     * @return array<string, string>
     */
    public static function load(string $projectRoot): array
    {
        $defaults = [
            'APP_ENV' => 'prod',
            'APP_URL' => 'http://127.0.0.1:8080',
            'APP_NAME' => 'Sympli RSS Fusion',
            'DB_PATH' => 'var/data/sympli_rss_fusion.sqlite',
            'DB_PATH_DEV' => 'var/data/sympli_rss_fusion_dev.sqlite',
            'CACHE_DIR' => 'var/cache',
            'CACHE_TTL' => '900',
            'HTTP_TIMEOUT' => '10',
            'MAX_ITEMS' => '80',
            'AUTO_PRUNE_ENABLED' => '0',
            'AUTO_PRUNE_DAYS' => '30',
            'APP_LANG' => 'fr',
            'APP_THEME' => 'default',
            'PREVIEW_ITEMS' => '4',
            'LOG_PATH' => 'var/log/app.log',
            'VERSION_CHECK_ENABLED' => '1',
            'RATE_FILE_TTL' => 3600,
            'RATE_PURGE_FREQUENCY' => 3600,
        ];

        $envPath = $projectRoot . DIRECTORY_SEPARATOR . '.env';
        $values = $defaults;

        if (is_file($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $values[trim($key)] = trim($value);
            }
        }

        return $values;
    }
}
