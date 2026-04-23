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

use RssFusionKiss\Env;
use RssFusionKiss\Http\App;
use RssFusionKiss\I18n\Translator;
use RssFusionKiss\Installer;
use RssFusionKiss\Persistence\Database;
use RssFusionKiss\Persistence\FeedRepository;
use RssFusionKiss\Service\CacheService;
use RssFusionKiss\Service\FeedAggregator;
use RssFusionKiss\Service\FeedFetcher;
use RssFusionKiss\Support\Logger;

$projectRoot = dirname(__DIR__);

require $projectRoot . '/src/autoload.php';
Installer::ensureInstalled($projectRoot);

$config = Env::load($projectRoot);
$isDev = strtolower(trim($config['APP_ENV'] ?? 'prod')) === 'dev';

if ($isDev) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);
    ini_set('display_errors', '0');
}

$logger = new Logger($projectRoot, $config['LOG_PATH'] ?? 'var/log/app.log');

$render500 = static function (Throwable $exception) use ($projectRoot, $config, $isDev): void {
    http_response_code(500);
    $message = $isDev ? $exception->getMessage() : 'Internal server error';
    $trace = $isDev ? $exception->getTraceAsString() : '';
    $lang = strtolower(trim($config['APP_LANG'] ?? 'fr'));
    $theme = preg_replace('/[^a-z0-9_-]/i', '', strtolower(trim($config['APP_THEME'] ?? 'default')));
    if ($theme === '' || !is_file($projectRoot . '/public/themes/' . $theme . '.css')) {
        $theme = 'default';
    }
    $themeStylesheet = '/themes/' . $theme . '.css';

    require $projectRoot . '/public/views/errors/500.php';
};

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(static function (Throwable $exception) use ($logger, $render500): void {
    $logger->error('Unhandled exception', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString(),
    ]);
    $render500($exception);
});

register_shutdown_function(static function () use ($logger, $isDev, $projectRoot, $config): void {
    $error = error_get_last();
    if (!is_array($error)) {
        return;
    }

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if (!in_array($error['type'] ?? 0, $fatalTypes, true)) {
        return;
    }

    $logger->error('Fatal shutdown error', $error);

    if (!headers_sent()) {
        http_response_code(500);
    }

    $message = $isDev ? (string) ($error['message'] ?? 'Fatal error') : 'Internal server error';
    $trace = '';
    $lang = strtolower(trim($config['APP_LANG'] ?? 'fr'));
    $theme = preg_replace('/[^a-z0-9_-]/i', '', strtolower(trim($config['APP_THEME'] ?? 'default')));
    if ($theme === '' || !is_file($projectRoot . '/public/themes/' . $theme . '.css')) {
        $theme = 'default';
    }
    $themeStylesheet = '/themes/' . $theme . '.css';

    require $projectRoot . '/public/views/errors/500.php';
});

$dbPath = $isDev
    ? ($config['DB_PATH_DEV'] ?? 'var/data/sympli_rss_fusion_dev.sqlite')
    : ($config['DB_PATH'] ?? 'var/data/sympli_rss_fusion.sqlite');

$pdo = Database::connect($projectRoot, $dbPath);
$repo = new FeedRepository($pdo);
$fetcher = new FeedFetcher((int) $config['HTTP_TIMEOUT']);
$aggregator = new FeedAggregator($fetcher, (int) $config['MAX_ITEMS']);
$cache = new CacheService($projectRoot . DIRECTORY_SEPARATOR . $config['CACHE_DIR'], (int) $config['CACHE_TTL']);
$translator = new Translator($projectRoot, $config['APP_LANG'] ?? 'fr');

$app = new App($projectRoot, $config, $repo, $aggregator, $cache, $fetcher, $translator);
$app->run();
