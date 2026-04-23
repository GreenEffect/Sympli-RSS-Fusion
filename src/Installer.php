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

use RssFusionKiss\Persistence\Database;

final class Installer
{
    public static function ensureInstalled(string $projectRoot): void
    {
        if (!is_file($projectRoot . '/.env') && is_file($projectRoot . '/.env.example')) {
            copy($projectRoot . '/.env.example', $projectRoot . '/.env');
        }

        $config = Env::load($projectRoot);
        $dbPath = self::resolveDbPath($config);
        $pdo = Database::connect($projectRoot, $dbPath);
        Database::migrate($pdo, $projectRoot . '/config/schema.sql');

        $cacheDir = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $config['CACHE_DIR']);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $logPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $config['LOG_PATH'] ?? 'var/log/app.log');
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    /**
     * @param array<string, string> $config
     */
    private static function resolveDbPath(array $config): string
    {
        $isDev = strtolower(trim($config['APP_ENV'] ?? 'prod')) === 'dev';
        if ($isDev) {
            return $config['DB_PATH_DEV'] ?? 'var/data/sympli_rss_fusion_dev.sqlite';
        }

        return $config['DB_PATH'];
    }
}
