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

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return null;
        }

        try {
            if (!flock($handle, LOCK_SH)) {
                fclose($handle);
                return null;
            }

            $content = stream_get_contents($handle);
            flock($handle, LOCK_UN);
            fclose($handle);
            return $content === false ? null : $content;
        } finally {
            if (is_resource($handle)) {
                @fclose($handle);
            }
        }
    }

    public function put(string $token, string $xml): void
    {
        $path = $this->cachePath($token);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $tmp = tempnam($dir, 'cache_');
        if ($tmp === false) {
            // Fallback to direct write if tempnam fails
            file_put_contents($path, $xml, LOCK_EX);
            return;
        }

        $written = file_put_contents($tmp, $xml, LOCK_EX);
        if ($written === false) {
            @unlink($tmp);
            return;
        }

        // Ensure permissions are reasonable
        @chmod($tmp, 0666 & ~umask());

        // Atomic replace
        rename($tmp, $path);
    }

    public function invalidate(string $token): void
    {
        $path = $this->cachePath($token);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function cachePath(string $token): string
    {
        return rtrim($this->cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $token . '.xml';
    }
}
