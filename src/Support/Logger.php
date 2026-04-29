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

namespace RssFusionKiss\Support;

final class Logger
{
    public function __construct(
        private readonly string $projectRoot,
        private readonly string $relativePath
    ) {
    }

    public function error(string $message, array $context = []): void
    {
        $path = $this->absolutePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $line = '[' . gmdate(DATE_ATOM) . '] ERROR ' . $message;
        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $handle = @fopen($path, 'a');
        if ($handle === false) {
            @file_put_contents($path, $line . PHP_EOL, FILE_APPEND);
            return;
        }

        try {
            if (flock($handle, LOCK_EX)) {
                fwrite($handle, $line . PHP_EOL);
                fflush($handle);
                flock($handle, LOCK_UN);
            } else {
                fwrite($handle, $line . PHP_EOL);
                fflush($handle);
            }
        } finally {
            fclose($handle);
        }
    }

    private function absolutePath(): string
    {
        return $this->projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->relativePath);
    }
}
