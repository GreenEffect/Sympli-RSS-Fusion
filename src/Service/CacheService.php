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

namespace RssFusionKiss\Service;

final class CacheService
{
    public function __construct(
        private readonly string $cacheDir,
        private readonly int $ttl
    ) {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get(string $token): ?string
    {
        $path = $this->cachePath($token);
        if (!is_file($path)) {
            return null;
        }

        if ((time() - filemtime($path)) > $this->ttl) {
            return null;
        }

        $content = file_get_contents($path);
        return $content === false ? null : $content;
    }

    public function put(string $token, string $xml): void
    {
        file_put_contents($this->cachePath($token), $xml);
    }

    public function invalidate(string $token): void
    {
        $path = $this->cachePath($token);
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function cachePath(string $token): string
    {
        return rtrim($this->cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $token . '.xml';
    }
}
