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

namespace RssFusionKiss\Http;

use RssFusionKiss\I18n\Translator;
use RssFusionKiss\Persistence\FeedRepository;
use RssFusionKiss\Service\CacheService;
use RssFusionKiss\Service\FeedAggregator;
use RssFusionKiss\Service\FeedFetcher;

final class App
{
    private const VERSION_REMOTE_URL = 'https://raw.githubusercontent.com/GreenEffect/Sympli-RSS-Fusion/refs/heads/main/VERSION';
    private const VERSION_REPO_URL = 'https://github.com/GreenEffect/Sympli-RSS-Fusion';
    private const OPML_MAX_BYTES = 1048576;
    private const JSON_MAX_BYTES = 1048576;

    private ?bool $versionUpdateAvailable = null;
    private ?string $localVersionMarker = null;

    /**
     * @param array<string, string> $config
     */
    public function __construct(
        private readonly string $projectRoot,
        private readonly array $config,
        private readonly FeedRepository $repository,
        private readonly FeedAggregator $aggregator,
        private readonly CacheService $cache,
        private readonly FeedFetcher $fetcher,
        private readonly Translator $translator
    ) {
    }

    public function run(): void
    {
        $this->autoPrune();

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($path === '/' && $method === 'GET') {
            $this->renderHome();
            return;
        }

        if ($path === '/create' && $method === 'POST') {
            $this->handleCreate();
            return;
        }

        if ($path === '/import-master' && $method === 'POST') {
            $this->handleImportMaster();
            return;
        }

        if ($path === '/import-master-opml' && $method === 'POST') {
            $this->handleImportMasterOpml();
            return;
        }

        if ($path === '/export-master' && $method === 'GET') {
            $this->handleExportMaster();
            return;
        }

        if ($path === '/export-master-opml' && $method === 'GET') {
            $this->handleExportMasterOpml();
            return;
        }

        if ($path === '/preview-source' && $method === 'GET') {
            $this->handlePreviewSource();
            return;
        }

        if ($path === '/privacy' && $method === 'GET') {
            $this->renderPrivacy();
            return;
        }

        if ($path === '/configuration' && $method === 'GET') {
            $this->renderConfiguration();
            return;
        }

        if (preg_match('#^/manage/([a-f0-9]{48})/delete$#', $path, $m) === 1 && $method === 'POST') {
            $this->handleDelete($m[1]);
            return;
        }

        if (preg_match('#^/manage/([a-f0-9]{48})/export$#', $path, $m) === 1 && $method === 'GET') {
            $this->handleExport($m[1]);
            return;
        }

        if (preg_match('#^/manage/([a-f0-9]{48})/export-opml$#', $path, $m) === 1 && $method === 'GET') {
            $this->handleExportOpml($m[1]);
            return;
        }

        if (preg_match('#^/manage/([a-f0-9]{48})/import$#', $path, $m) === 1 && $method === 'POST') {
            $this->handleImport($m[1]);
            return;
        }

        if (preg_match('#^/manage/([a-f0-9]{48})/import-opml$#', $path, $m) === 1 && $method === 'POST') {
            $this->handleImportOpml($m[1]);
            return;
        }

        if (preg_match('#^/manage/([a-f0-9]{48})$#', $path, $m) === 1) {
            if ($method === 'POST') {
                $this->handleUpdate($m[1]);
            } else {
                $this->renderManage($m[1]);
            }
            return;
        }

        if (preg_match('#^/rss/([a-f0-9]{48})$#', $path, $m) === 1) {
            $this->renderRss($m[1]);
            return;
        }

        $this->renderErrorPage(404, $this->translator->t('error.404'));
    }

    private function renderHome(?string $error = null, ?string $flash = null, array $old = []): void
    {
        $token = $this->normalizeToken((string) ($_GET['token'] ?? ''));
        $feed = null;
        if ($token !== '') {
            $feed = $this->repository->findFeedByToken($token);
        }

        $appName = $this->config['APP_NAME'];
        $appUrl = rtrim($this->config['APP_URL'], '/');
        $lang = $this->translator->getLang();
        $themeStylesheet = '/themes/' . $this->resolveTheme() . '.css';
        $t = fn (string $key, array $replacements = []): string => $this->translator->t($key, $replacements);
        $clientI18n = json_encode($this->translator->forClient([
            'ui.preview_loading',
            'ui.preview_error',
            'ui.preview_empty',
            'ui.preview_feed',
            'ui.preview_untitled_item',
            'ui.source',
            'error.invalid_url',
            'manage.copy_done',
            'manage.copy_failed',
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privacyUrl = '/privacy';
        $csrfToken = $this->getCsrfToken();
        $versionUpdateAvailable = $this->isVersionUpdateAvailable();
        $versionRepoUrl = self::VERSION_REPO_URL;
        $localVersion = $this->getLocalVersionMarker();

        require __DIR__ . '/../../public/views/home.php';
    }

    private function renderManage(string $token, ?string $error = null, ?string $flash = null): void
    {
        $feed = $this->repository->findFeedByToken($token);
        if ($feed === null) {
            $this->renderErrorPage(404, $this->translator->t('error.feed_not_found'));
            return;
        }

        $appName = $this->config['APP_NAME'];
        $appUrl = rtrim($this->config['APP_URL'], '/');
        $lang = $this->translator->getLang();
        $themeStylesheet = '/themes/' . $this->resolveTheme() . '.css';
        $t = fn (string $key, array $replacements = []): string => $this->translator->t($key, $replacements);
        $clientI18n = json_encode($this->translator->forClient([
            'ui.preview_loading',
            'ui.preview_error',
            'ui.preview_empty',
            'ui.preview_feed',
            'ui.preview_untitled_item',
            'ui.source',
            'error.invalid_url',
            'manage.copy_done',
            'manage.copy_failed',
        ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privacyUrl = '/privacy';
        $csrfToken = $this->getCsrfToken();
        $versionUpdateAvailable = $this->isVersionUpdateAvailable();
        $versionRepoUrl = self::VERSION_REPO_URL;
        $localVersion = $this->getLocalVersionMarker();

        require __DIR__ . '/../../public/views/manage.php';
    }

    private function renderPrivacy(): void
    {
        $appName = $this->config['APP_NAME'];
        $lang = $this->translator->getLang();
        $themeStylesheet = '/themes/' . $this->resolveTheme() . '.css';
        $t = fn (string $key, array $replacements = []): string => $this->translator->t($key, $replacements);
        $privacyUrl = '/privacy';
        $configurationUrl = '/configuration';
        $versionUpdateAvailable = $this->isVersionUpdateAvailable();
        $versionRepoUrl = self::VERSION_REPO_URL;
        $localVersion = $this->getLocalVersionMarker();

        require __DIR__ . '/../../public/views/privacy.php';
    }

    private function renderConfiguration(): void
    {
        $appName = $this->config['APP_NAME'];
        $lang = $this->translator->getLang();
        $themeStylesheet = '/themes/' . $this->resolveTheme() . '.css';
        $t = fn (string $key, array $replacements = []): string => $this->translator->t($key, $replacements);
        $privacyUrl = '/privacy';
        $configurationUrl = '/configuration';
        $versionUpdateAvailable = $this->isVersionUpdateAvailable();
        $versionRepoUrl = self::VERSION_REPO_URL;
        $localVersion = $this->getLocalVersionMarker();

        $remoteVersion = '';
        if ($this->isEnabled($this->config['VERSION_CHECK_ENABLED'] ?? '0')) {
            $remoteVersion = $this->fetchRemoteVersionMarker(self::VERSION_REMOTE_URL);
        }

        $langVal = $this->translator->getLang();
        $themeName = $this->resolveTheme();
        $cacheTtl = (int) ($this->config['CACHE_TTL'] ?? '0');
        $autoPruneEnabled = $this->isEnabled($this->config['AUTO_PRUNE_ENABLED'] ?? '0');
        $autoPruneDays = (int) ($this->config['AUTO_PRUNE_DAYS'] ?? '0');

        require __DIR__ . '/../../public/views/configuration.php';
    }

    private function handleCreate(): void
    {
        if (!$this->validateCsrfToken()) {
            $this->renderHome($this->translator->t('error.invalid_csrf'));
            return;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $sources = $this->extractSources($_POST);

        if ($title === '' || $sources === []) {
            $this->renderHome($this->translator->t('error.required_title_source'), null, $_POST);
            return;
        }

        $token = $this->repository->createFeed($title, $description, $sources);
        header('Location: /manage/' . $token, true, 302);
    }

    private function handleUpdate(string $token): void
    {
        if (!$this->validateCsrfToken()) {
            $this->renderManage($token, $this->translator->t('error.invalid_csrf'));
            return;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $sources = $this->extractSources($_POST);

        if ($title === '' || $sources === []) {
            $this->renderManage($token, $this->translator->t('error.required_title_source'));
            return;
        }

        $ok = $this->repository->updateFeedByToken($token, $title, $description, $sources);
        if (!$ok) {
            $this->renderHome($this->translator->t('error.feed_deleted'));
            return;
        }

        $this->cache->invalidate($token);
        $this->renderManage($token, null, $this->translator->t('flash.feed_updated_cache_cleared'));
    }

    private function handleDelete(string $token): void
    {
        if (!$this->validateCsrfToken()) {
            $this->renderManage($token, $this->translator->t('error.invalid_csrf'));
            return;
        }

        $deleted = $this->repository->deleteFeedByToken($token);
        $this->cache->invalidate($token);

        if ($deleted) {
            $this->renderHome(null, $this->translator->t('flash.feed_deleted'));
            return;
        }

        $this->renderHome($this->translator->t('error.feed_not_found'));
    }

    private function handleExportMaster(): void
    {
        $token = $this->normalizeToken((string) ($_GET['token'] ?? ''));
        if ($token === '') {
            $this->renderHome($this->translator->t('error.master_token_required'));
            return;
        }

        $this->streamExport($token);
    }

    private function handleExportMasterOpml(): void
    {
        $token = $this->normalizeToken((string) ($_GET['token'] ?? ''));
        if ($token === '') {
            $this->renderHome($this->translator->t('error.master_token_required'));
            return;
        }

        $this->streamExportOpml($token);
    }

    private function handleExport(string $token): void
    {
        $this->streamExport($token);
    }

    private function handleExportOpml(string $token): void
    {
        $this->streamExportOpml($token);
    }

    private function streamExport(string $token): void
    {
        $config = $this->repository->getExportableConfig($token);
        if ($config === null) {
            $this->renderErrorPage(404, $this->translator->t('error.feed_not_found'));
            return;
        }

        $payload = json_encode([
            'format' => 'rss-fusion-kiss-config-v1',
            'exported_at' => gmdate(DATE_ATOM),
            'config' => $config,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            throw new \RuntimeException($this->translator->t('error.export_failed'));
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="feed-config-' . $token . '.json"');
        echo $payload;
    }

    private function streamExportOpml(string $token): void
    {
        $config = $this->repository->getExportableConfig($token);
        if ($config === null) {
            $this->renderErrorPage(404, $this->translator->t('error.feed_not_found'));
            return;
        }

        $xml = $this->buildOpmlFromConfig($config);
        header('Content-Type: text/x-opml; charset=utf-8');
        header('Content-Disposition: attachment; filename="feed-config-' . $token . '.opml"');
        echo $xml;
    }

    private function handleImportMaster(): void
    {
        if (!$this->validateCsrfToken()) {
            $this->renderHome($this->translator->t('error.invalid_csrf'));
            return;
        }

        $config = $this->parseImportUpload($_FILES['import_master_file'] ?? null, $error);
        if ($config === null) {
            $this->renderHome($error ?? $this->translator->t('error.import_invalid_structure'));
            return;
        }

        $token = $this->repository->createFeed($config['title'], $config['description'], $config['sources']);
        header('Location: /manage/' . $token, true, 302);
    }

    private function handleImportMasterOpml(): void
    {
        if (!$this->validateCsrfToken()) {
            $this->renderHome($this->translator->t('error.invalid_csrf'));
            return;
        }

        $config = $this->parseOpmlUpload($_FILES['import_master_opml_file'] ?? null, $error);
        if ($config === null) {
            $this->renderHome($error ?? $this->translator->t('error.import_invalid_structure'));
            return;
        }

        $token = $this->repository->createFeed($config['title'], $config['description'], $config['sources']);
        header('Location: /manage/' . $token, true, 302);
    }

    private function handleImport(string $token): void
    {
        if (!$this->validateCsrfToken()) {
            $this->renderManage($token, $this->translator->t('error.invalid_csrf'));
            return;
        }

        $config = $this->parseImportUpload($_FILES['import_file'] ?? null, $error);
        if ($config === null) {
            $this->renderManage($token, $error ?? $this->translator->t('error.import_invalid_structure'));
            return;
        }

        $ok = $this->repository->updateFeedByToken($token, $config['title'], $config['description'], $config['sources']);
        if (!$ok) {
            $this->renderHome($this->translator->t('error.feed_not_found'));
            return;
        }

        $this->cache->invalidate($token);
        $this->renderManage($token, null, $this->translator->t('flash.import_success'));
    }

    private function handleImportOpml(string $token): void
    {
        if (!$this->validateCsrfToken()) {
            $this->renderManage($token, $this->translator->t('error.invalid_csrf'));
            return;
        }

        $feed = $this->repository->findFeedByToken($token);
        if ($feed === null) {
            $this->renderHome($this->translator->t('error.feed_not_found'));
            return;
        }

        $config = $this->parseOpmlUpload($_FILES['import_opml_file'] ?? null, $error);
        if ($config === null) {
            $this->renderManage($token, $error ?? $this->translator->t('error.import_invalid_structure'));
            return;
        }

        $existingSources = is_array($feed['sources'] ?? null) ? $feed['sources'] : [];
        $sources = $this->mergeSourcesByUrl($existingSources, $config['sources']);

        $ok = $this->repository->updateFeedByToken(
            $token,
            (string) $feed['title'],
            (string) $feed['description'],
            $sources
        );
        if (!$ok) {
            $this->renderHome($this->translator->t('error.feed_not_found'));
            return;
        }

        $this->cache->invalidate($token);
        $this->renderManage($token, null, $this->translator->t('flash.opml_sources_added'));
    }

    private function renderRss(string $token): void
    {
        $feed = $this->repository->findFeedByToken($token);
        if ($feed === null) {
            $this->renderErrorPage(404, $this->translator->t('error.feed_not_found'));
            return;
        }

        $cached = $this->cache->get($token);
        if ($cached !== null) {
            header('Content-Type: application/rss+xml; charset=utf-8');
            echo $cached;
            return;
        }

        $xml = $this->aggregator->toRssXml($feed, rtrim($this->config['APP_URL'], '/'), $this->config['APP_NAME']);
        $this->cache->put($token, $xml);
        $this->repository->touchLastViewed($token);

        header('Content-Type: application/rss+xml; charset=utf-8');
        echo $xml;
    }

    private function handlePreviewSource(): void
    {
        $url = trim((string) ($_GET['url'] ?? ''));
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->json(['error' => $this->translator->t('error.invalid_url')], 400);
            return;
        }

        $preview = $this->fetcher->preview($url, (int) ($this->config['PREVIEW_ITEMS'] ?? '4'));

        $source = [
            'black_words' => trim((string) ($_GET['black_words'] ?? '')),
            'star_words' => trim((string) ($_GET['star_words'] ?? '')),
            'black_target_title' => $this->toIntBool($_GET['black_target_title'] ?? 0),
            'black_target_description' => $this->toIntBool($_GET['black_target_description'] ?? 0),
            'black_target_content' => $this->toIntBool($_GET['black_target_content'] ?? 0),
            'star_target_title' => $this->toIntBool($_GET['star_target_title'] ?? 0),
            'star_target_description' => $this->toIntBool($_GET['star_target_description'] ?? 0),
            'star_target_content' => $this->toIntBool($_GET['star_target_content'] ?? 0),
        ];

        $preview['items'] = $this->aggregator->filterPreviewItems(
            is_array($preview['items'] ?? null) ? $preview['items'] : [],
            $source,
            (int) ($this->config['PREVIEW_ITEMS'] ?? '4')
        );

        $this->json($preview);
    }

    /**
     * @param array<string, mixed> $post
     * @return array<int, array<string, mixed>>
     */
    private function extractSources(array $post): array
    {
        $names = $post['source_name'] ?? [];
        $urls = $post['source_url'] ?? [];
        $blackWords = $post['black_words'] ?? [];
        $starWords = $post['star_words'] ?? [];
        $blackTitle = $post['black_target_title'] ?? [];
        $blackDescription = $post['black_target_description'] ?? [];
        $blackContent = $post['black_target_content'] ?? [];
        $starTitle = $post['star_target_title'] ?? [];
        $starDescription = $post['star_target_description'] ?? [];
        $starContent = $post['star_target_content'] ?? [];

        if (!is_array($names) || !is_array($urls)) {
            return [];
        }

        $sources = [];
        foreach ($names as $idx => $name) {
            $name = trim((string) $name);
            $url = trim((string) ($urls[$idx] ?? ''));
            if ($name === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $sources[] = [
                'name' => $name,
                'url' => $url,
                'black_words' => trim((string) ($blackWords[$idx] ?? '')),
                'star_words' => trim((string) ($starWords[$idx] ?? '')),
                'black_target_title' => isset($blackTitle[$idx]) ? 1 : 0,
                'black_target_description' => isset($blackDescription[$idx]) ? 1 : 0,
                'black_target_content' => isset($blackContent[$idx]) ? 1 : 0,
                'star_target_title' => isset($starTitle[$idx]) ? 1 : 0,
                'star_target_description' => isset($starDescription[$idx]) ? 1 : 0,
                'star_target_content' => isset($starContent[$idx]) ? 1 : 0,
            ];
        }

        return $sources;
    }

    /**
     * @param mixed $sourcesRaw
     * @return array<int, array<string, mixed>>
     */
    private function extractImportedSources(mixed $sourcesRaw): array
    {
        if (!is_array($sourcesRaw)) {
            return [];
        }

        $sources = [];
        foreach ($sourcesRaw as $sourceRaw) {
            if (!is_array($sourceRaw)) {
                continue;
            }

            $name = trim((string) ($sourceRaw['name'] ?? ''));
            $url = trim((string) ($sourceRaw['url'] ?? ''));
            if ($name === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $sources[] = [
                'name' => $name,
                'url' => $url,
                'black_words' => trim((string) ($sourceRaw['black_words'] ?? '')),
                'star_words' => trim((string) ($sourceRaw['star_words'] ?? '')),
                'black_target_title' => !empty($sourceRaw['black_target_title']) ? 1 : 0,
                'black_target_description' => !empty($sourceRaw['black_target_description']) ? 1 : 0,
                'black_target_content' => !empty($sourceRaw['black_target_content']) ? 1 : 0,
                'star_target_title' => !empty($sourceRaw['star_target_title']) ? 1 : 0,
                'star_target_description' => !empty($sourceRaw['star_target_description']) ? 1 : 0,
                'star_target_content' => !empty($sourceRaw['star_target_content']) ? 1 : 0,
            ];
        }

        return $sources;
    }

    /**
     * @param mixed $file
     * @param string|null $error
     * @return array{title:string,description:string,sources:array<int, array<string,mixed>>}|null
     */
    private function parseImportUpload(mixed $file, ?string &$error = null): ?array
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = $this->translator->t('error.import_upload');
            return null;
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_file($tmpPath)) {
            $error = $this->translator->t('error.import_upload');
            return null;
        }

        if ((int) ($file['size'] ?? 0) > self::JSON_MAX_BYTES) {
            $error = $this->translator->t('error.import_too_large');
            return null;
        }

        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = (string) finfo_file($finfo, $tmpPath);
                finfo_close($finfo);
            }
        }

        if ($mime !== '') {
            $allowed = ['application/json', 'text/json', 'application/octet-stream', 'text/plain'];
            if (!in_array(strtolower($mime), $allowed, true)) {
                $error = $this->translator->t('error.import_invalid_json');
                return null;
            }
        }
        $raw = file_get_contents($tmpPath);
        if ($raw === false || trim($raw) === '') {
            $error = $this->translator->t('error.import_invalid_json');
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $error = $this->translator->t('error.import_invalid_json');
            return null;
        }

        $config = $decoded['config'] ?? $decoded;
        if (!is_array($config)) {
            $error = $this->translator->t('error.import_invalid_structure');
            return null;
        }

        $title = trim((string) ($config['title'] ?? ''));
        $description = trim((string) ($config['description'] ?? ''));
        $sources = $this->extractImportedSources($config['sources'] ?? []);

        if ($title === '' || $sources === []) {
            $error = $this->translator->t('error.import_invalid_structure');
            return null;
        }

        return [
            'title' => $title,
            'description' => $description,
            'sources' => $sources,
        ];
    }

    /**
     * @param mixed $file
     * @param string|null $error
     * @return array{title:string,description:string,sources:array<int, array<string,mixed>>}|null
     */
    private function parseOpmlUpload(mixed $file, ?string &$error = null): ?array
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $error = $this->translator->t('error.import_upload');
            return null;
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_file($tmpPath)) {
            $error = $this->translator->t('error.import_upload');
            return null;
        }

        if ((int) ($file['size'] ?? 0) > self::OPML_MAX_BYTES) {
            $error = $this->translator->t('error.opml_too_large');
            return null;
        }

        $raw = file_get_contents($tmpPath);
        if (!is_string($raw) || trim($raw) === '') {
            $error = $this->translator->t('error.opml_invalid');
            return null;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadXML($raw, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOBLANKS);
        libxml_clear_errors();

        if (!$loaded) {
            $error = $this->translator->t('error.opml_invalid');
            return null;
        }

        $outlines = $dom->getElementsByTagName('outline');
        $sources = [];
        $seen = [];

        foreach ($outlines as $outline) {
            if (!$outline instanceof \DOMElement) {
                continue;
            }

            $url = trim((string) $outline->getAttribute('xmlUrl'));
            if ($url === '') {
                $url = trim((string) $outline->getAttribute('url'));
            }

            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $urlKey = $this->normalizeUrlKey($url);
            if (isset($seen[$urlKey])) {
                continue;
            }

            $name = trim((string) $outline->getAttribute('title'));
            if ($name === '') {
                $name = trim((string) $outline->getAttribute('text'));
            }
            if ($name === '') {
                $name = $this->sourceNameFromUrl($url);
            }

            $sources[] = [
                'name' => $name,
                'url' => $url,
                'black_words' => '',
                'star_words' => '',
                'black_target_title' => 0,
                'black_target_description' => 0,
                'black_target_content' => 0,
                'star_target_title' => 0,
                'star_target_description' => 0,
                'star_target_content' => 0,
            ];
            $seen[$urlKey] = true;
        }

        if ($sources === []) {
            $error = $this->translator->t('error.opml_no_sources');
            return null;
        }

        $title = $this->translator->t('opml.default_title');
        $heads = $dom->getElementsByTagName('head');
        if ($heads->length > 0) {
            $head = $heads->item(0);
            if ($head instanceof \DOMElement) {
                foreach ($head->childNodes as $node) {
                    if ($node instanceof \DOMElement && strtolower($node->tagName) === 'title') {
                        $candidate = trim((string) $node->textContent);
                        if ($candidate !== '') {
                            $title = $candidate;
                        }
                        break;
                    }
                }
            }
        }

        return [
            'title' => $title,
            'description' => '',
            'sources' => $sources,
        ];
    }

    /**
     * @param array<string, mixed> $config
     */
    private function buildOpmlFromConfig(array $config): string
    {
        $title = trim((string) ($config['title'] ?? ''));
        if ($title === '') {
            $title = $this->translator->t('opml.default_title');
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $opml = $dom->createElement('opml');
        $opml->setAttribute('version', '2.0');
        $dom->appendChild($opml);

        $head = $dom->createElement('head');
        $head->appendChild($dom->createElement('title', $title));
        $head->appendChild($dom->createElement('dateCreated', gmdate(DATE_RSS)));
        $opml->appendChild($head);

        $body = $dom->createElement('body');
        $opml->appendChild($body);

        $sources = $config['sources'] ?? [];
        if (is_array($sources)) {
            foreach ($sources as $source) {
                if (!is_array($source)) {
                    continue;
                }

                $url = trim((string) ($source['url'] ?? ''));
                if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $name = trim((string) ($source['name'] ?? ''));
                if ($name === '') {
                    $name = $this->sourceNameFromUrl($url);
                }

                $outline = $dom->createElement('outline');
                $outline->setAttribute('text', $name);
                $outline->setAttribute('title', $name);
                $outline->setAttribute('type', 'rss');
                $outline->setAttribute('xmlUrl', $url);
                $body->appendChild($outline);
            }
        }

        $xml = $dom->saveXML();
        return is_string($xml) ? $xml : '';
    }

    /**
     * @param array<int, array<string, mixed>> $existing
     * @param array<int, array<string, mixed>> $added
     * @return array<int, array<string, mixed>>
     */
    private function mergeSourcesByUrl(array $existing, array $added): array
    {
        $merged = [];
        $seen = [];

        foreach ([$existing, $added] as $list) {
            foreach ($list as $source) {
                if (!is_array($source)) {
                    continue;
                }

                $url = trim((string) ($source['url'] ?? ''));
                if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $key = $this->normalizeUrlKey($url);
                if (isset($seen[$key])) {
                    continue;
                }

                $merged[] = [
                    'name' => trim((string) ($source['name'] ?? '')) ?: $this->sourceNameFromUrl($url),
                    'url' => $url,
                    'black_words' => trim((string) ($source['black_words'] ?? '')),
                    'star_words' => trim((string) ($source['star_words'] ?? '')),
                    'black_target_title' => !empty($source['black_target_title']) ? 1 : 0,
                    'black_target_description' => !empty($source['black_target_description']) ? 1 : 0,
                    'black_target_content' => !empty($source['black_target_content']) ? 1 : 0,
                    'star_target_title' => !empty($source['star_target_title']) ? 1 : 0,
                    'star_target_description' => !empty($source['star_target_description']) ? 1 : 0,
                    'star_target_content' => !empty($source['star_target_content']) ? 1 : 0,
                ];
                $seen[$key] = true;
            }
        }

        return $merged;
    }

    private function sourceNameFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (is_string($host) && $host !== '') {
            return $host;
        }

        return 'RSS source';
    }

    private function normalizeUrlKey(string $url): string
    {
        $url = trim($url);
        return strtolower(rtrim($url, '/'));
    }

    private function normalizeToken(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^[a-f0-9]{48}$/', $raw) === 1) {
            return $raw;
        }

        $path = parse_url($raw, PHP_URL_PATH);
        if (!is_string($path)) {
            return '';
        }

        if (preg_match('#/rss/([a-f0-9]{48})$#', $path, $m) === 1) {
            return $m[1];
        }

        if (preg_match('#/manage/([a-f0-9]{48})$#', $path, $m) === 1) {
            return $m[1];
        }

        return '';
    }

    private function ensureSessionStarted(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
        if (!$isHttps && isset($_SERVER['SERVER_PORT'])) {
            $isHttps = ((int) $_SERVER['SERVER_PORT']) === 443;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    private function getCsrfToken(): string
    {
        $this->ensureSessionStarted();

        $token = $_SESSION['csrf_token'] ?? null;
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = $token;
        }

        return $token;
    }

    private function validateCsrfToken(): bool
    {
        $this->ensureSessionStarted();

        $token = $_SESSION['csrf_token'] ?? null;
        $submitted = $_POST['_csrf'] ?? null;

        if (!is_string($token) || $token === '' || !is_string($submitted) || $submitted === '') {
            return false;
        }

        return hash_equals($token, $submitted);
    }

    private function autoPrune(): void
    {
        if (!$this->isEnabled($this->config['AUTO_PRUNE_ENABLED'] ?? '0')) {
            return;
        }

        $days = max(1, (int) ($this->config['AUTO_PRUNE_DAYS'] ?? '30'));
        $deletedTokens = $this->repository->pruneInactiveFeedTokens($days);
        foreach ($deletedTokens as $token) {
            $this->cache->invalidate($token);
        }
    }

    private function isEnabled(string $value): bool
    {
        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function resolveTheme(): string
    {
        $theme = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string) ($this->config['APP_THEME'] ?? 'default')));
        if ($theme === '') {
            $theme = 'default';
        }

        $path = $this->projectRoot . '/public/themes/' . $theme . '.css';
        if (!is_file($path)) {
            return 'default';
        }

        return $theme;
    }

    private function toIntBool(mixed $value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) > 0 ? 1 : 0;
        }

        $text = strtolower(trim((string) $value));
        return in_array($text, ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
    }

    private function renderErrorPage(int $status, string $message): void
    {
        http_response_code($status);

        $lang = $this->translator->getLang();
        $themeStylesheet = '/themes/' . $this->resolveTheme() . '.css';
        $title = $status . ' - ' . $this->config['APP_NAME'];
        $homeUrl = '/';
        $t = fn (string $key, array $replacements = []): string => $this->translator->t($key, $replacements);
        $privacyUrl = '/privacy';
        $versionUpdateAvailable = $this->isVersionUpdateAvailable();
        $versionRepoUrl = self::VERSION_REPO_URL;
        $localVersion = $this->getLocalVersionMarker();

        require __DIR__ . '/../../public/views/errors/404.php';
    }

    private function isVersionUpdateAvailable(): bool
    {
        if ($this->versionUpdateAvailable !== null) {
            return $this->versionUpdateAvailable;
        }

        if (!$this->isEnabled($this->config['VERSION_CHECK_ENABLED'] ?? '0')) {
            $this->versionUpdateAvailable = false;
            return false;
        }

        $localVersion = $this->readVersionMarker($this->projectRoot . '/VERSION');
        if ($localVersion === '') {
            $this->versionUpdateAvailable = false;
            return false;
        }

        $remoteVersion = $this->fetchRemoteVersionMarker(self::VERSION_REMOTE_URL);
        if ($remoteVersion === '') {
            $this->versionUpdateAvailable = false;
            return false;
        }

        $this->versionUpdateAvailable = version_compare($localVersion, $remoteVersion, '<');
        return $this->versionUpdateAvailable;
    }

    private function readVersionMarker(string $path): string
    {
        if (!is_file($path)) {
            return '';
        }

        $content = file_get_contents($path);
        if (!is_string($content) || trim($content) === '') {
            return '';
        }

        return $this->extractVersionLine($content);
    }

    private function getLocalVersionMarker(): string
    {
        if ($this->localVersionMarker !== null) {
            return $this->localVersionMarker;
        }

        $this->localVersionMarker = $this->readVersionMarker($this->projectRoot . '/VERSION');
        return $this->localVersionMarker;
    }

    private function fetchRemoteVersionMarker(string $url): string
    {
        $timeout = max(1, (int) ($this->config['HTTP_TIMEOUT'] ?? '10'));
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $content = @file_get_contents($url, false, $context);
        if (!is_string($content) || trim($content) === '') {
            return '';
        }

        return $this->extractVersionLine($content);
    }

    private function extractVersionLine(string $content): string
    {
        $lines = preg_split('/\R/', $content);
        if (!is_array($lines) || $lines === []) {
            return '';
        }

        $version = trim((string) $lines[0]);
        if ($version === '') {
            return '';
        }

        return ltrim($version, "vV");
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
