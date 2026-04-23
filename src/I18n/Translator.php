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

namespace RssFusionKiss\I18n;

final class Translator
{
    /**
     * @var array<string, string>
     */
    private array $messages = [];

    private string $lang;

    public function __construct(string $projectRoot, string $lang)
    {
        $lang = strtolower(trim($lang));
        $this->lang = $lang !== '' ? $lang : 'fr';

        $basePath = $projectRoot . '/config/lang';
        $fallback = $this->loadMessages($basePath . '/fr.json');
        $current = $this->loadMessages($basePath . '/' . $this->lang . '.json');

        if ($current === []) {
            $this->lang = 'fr';
        }

        $this->messages = array_merge($fallback, $current);
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param array<string, string|int> $replacements
     */
    public function t(string $key, array $replacements = []): string
    {
        $message = $this->messages[$key] ?? $key;

        foreach ($replacements as $name => $value) {
            $message = str_replace('{' . $name . '}', (string) $value, $message);
        }

        return $message;
    }

    /**
     * @param array<int, string> $keys
     * @return array<string, string>
     */
    public function forClient(array $keys): array
    {
        $rows = [];
        foreach ($keys as $key) {
            $rows[$key] = $this->t($key);
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function loadMessages(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $json = file_get_contents($path);
        if ($json === false) {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $messages = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $messages[$key] = $value;
            }
        }

        return $messages;
    }
}
