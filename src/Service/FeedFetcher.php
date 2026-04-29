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

use DOMDocument;
use DOMXPath;

final class FeedFetcher
{
    public function __construct(private readonly int $timeout)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    /**
     * Fetch feed items supporting conditional requests.
     *
     * Returns an array with keys:
     * - 'items' => array of items
     * - 'etag' => string|null
     * - 'last_modified' => string|null
     * - 'not_modified' => bool
     *
     * @param string $url
     * @param array<string,string> $conditional Optional associative array with 'etag' and/or 'last_modified'
     * @return array<string, mixed>
     */
    public function fetchItems(string $url, array $conditional = []): array
    {
        $headers = ['User-Agent: Sympli-RSS-Fusion/1.0'];
        if (!empty($conditional['etag'])) {
            $headers[] = 'If-None-Match: ' . $conditional['etag'];
        }
        if (!empty($conditional['last_modified'])) {
            $headers[] = 'If-Modified-Since: ' . $conditional['last_modified'];
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'header' => implode("\r\n", $headers),
            ],
        ]);

        $xmlRaw = @file_get_contents($url, false, $context);
        // If file_get_contents returned false and there are response headers, try to detect 304
        $responseHeaders = $http_response_header ?? [];
        $status = null;
        if (!empty($responseHeaders) && is_array($responseHeaders)) {
            $m = [];
            if (preg_match('#HTTP/\d\.?\d\s+(\d{3})#i', $responseHeaders[0], $m)) {
                $status = (int) $m[1];
            }
        }

        if ($xmlRaw === false || trim((string) $xmlRaw) === '') {
            if ($status === 304) {
                // Not modified
                $etag = null;
                $lastModified = null;
                foreach ($responseHeaders as $h) {
                    if (stripos($h, 'ETag:') === 0) {
                        $etag = trim(substr($h, 5));
                    }
                    if (stripos($h, 'Last-Modified:') === 0) {
                        $lastModified = trim(substr($h, 14));
                    }
                }

                return ['items' => [], 'etag' => $etag, 'last_modified' => $lastModified, 'not_modified' => true];
            }

            return ['items' => [], 'etag' => null, 'last_modified' => null, 'not_modified' => false];
        }

        $dom = new DOMDocument();
        if (@$dom->loadXML($xmlRaw) === false) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xpath->registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');

        $items = $xpath->query('//channel/item');
        $rows = [];
        if ($items !== false && $items->length > 0) {
            $rows = $this->parseRssItems($xpath);
        } else {
            $entries = $xpath->query('//atom:feed/atom:entry');
            if ($entries !== false && $entries->length > 0) {
                $rows = $this->parseAtomEntries($xpath);
            }
        }

        // extract response headers for etag/last-modified
        $responseHeaders = $http_response_header ?? [];
        $etag = null;
        $lastModified = null;
        if (!empty($responseHeaders) && is_array($responseHeaders)) {
            foreach ($responseHeaders as $h) {
                if (stripos($h, 'ETag:') === 0) {
                    $etag = trim(substr($h, 5));
                }
                if (stripos($h, 'Last-Modified:') === 0) {
                    $lastModified = trim(substr($h, 14));
                }
            }
        }

        return ['items' => $rows, 'etag' => $etag, 'last_modified' => $lastModified, 'not_modified' => false];
    }

    /**
     * @return array<string, mixed>
     */
    public function preview(string $url, int $maxItems = 4): array
    {
        $maxItems = max(1, $maxItems);

        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'user_agent' => 'Sympli-RSS-Fusion/1.0',
            ],
        ]);

        $xmlRaw = @file_get_contents($url, false, $context);
        if ($xmlRaw === false || trim($xmlRaw) === '') {
            return ['feed_title' => '', 'items' => []];
        }

        $dom = new DOMDocument();
        if (@$dom->loadXML($xmlRaw) === false) {
            return ['feed_title' => '', 'items' => []];
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xpath->registerNamespace('content', 'http://purl.org/rss/1.0/modules/content/');

        $feedTitle = trim((string) $xpath->evaluate('string(//channel/title)'));
        if ($feedTitle === '') {
            $feedTitle = trim((string) $xpath->evaluate('string(//atom:feed/atom:title)'));
        }

        $res = $this->fetchItems($url);
        $items = is_array($res) && isset($res['items']) ? $res['items'] : [];

        return [
            'feed_title' => $feedTitle,
            'items' => array_slice($items, 0, $maxItems),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseRssItems(DOMXPath $xpath): array
    {
        $rows = [];
        $nodes = $xpath->query('//channel/item');
        if ($nodes === false) {
            return [];
        }

        foreach ($nodes as $node) {
            $title = trim((string) $xpath->evaluate('string(title)', $node));
            $link = trim((string) $xpath->evaluate('string(link)', $node));
            $description = trim((string) $xpath->evaluate('string(description)', $node));
            $content = trim((string) $xpath->evaluate('string(content:encoded)', $node));
            $pubDate = trim((string) $xpath->evaluate('string(pubDate)', $node));
            $guid = trim((string) $xpath->evaluate('string(guid)', $node));

            $rows[] = [
                'id' => $guid !== '' ? $guid : ($link !== '' ? $link : md5($title . $pubDate)),
                'title' => $title,
                'link' => $link,
                'description' => $description,
                'content' => $content,
                'published_at' => $this->toTimestamp($pubDate),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function parseAtomEntries(DOMXPath $xpath): array
    {
        $rows = [];
        $nodes = $xpath->query('//atom:feed/atom:entry');
        if ($nodes === false) {
            return [];
        }

        foreach ($nodes as $node) {
            $title = trim((string) $xpath->evaluate('string(atom:title)', $node));
            $description = trim((string) $xpath->evaluate('string(atom:summary)', $node));
            $content = trim((string) $xpath->evaluate('string(atom:content)', $node));
            $updated = trim((string) $xpath->evaluate('string(atom:updated)', $node));
            $published = trim((string) $xpath->evaluate('string(atom:published)', $node));
            $id = trim((string) $xpath->evaluate('string(atom:id)', $node));

            $link = '';
            $linkNode = $xpath->query('atom:link[@rel="alternate"]/@href', $node);
            if ($linkNode !== false && $linkNode->length > 0) {
                $link = trim((string) $linkNode->item(0)?->nodeValue);
            } else {
                $fallback = $xpath->query('atom:link/@href', $node);
                if ($fallback !== false && $fallback->length > 0) {
                    $link = trim((string) $fallback->item(0)?->nodeValue);
                }
            }

            $date = $published !== '' ? $published : $updated;

            $rows[] = [
                'id' => $id !== '' ? $id : ($link !== '' ? $link : md5($title . $date)),
                'title' => $title,
                'link' => $link,
                'description' => $description,
                'content' => $content,
                'published_at' => $this->toTimestamp($date),
            ];
        }

        return $rows;
    }

    private function toTimestamp(string $date): int
    {
        if ($date === '') {
            return 0;
        }
        $time = strtotime($date);
        return $time === false ? 0 : $time;
    }
}
