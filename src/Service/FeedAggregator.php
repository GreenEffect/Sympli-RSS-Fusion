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

final class FeedAggregator
{
    public function __construct(
        private readonly FeedFetcher $fetcher,
        private readonly int $maxItems
    ) {
    }

    /**
     * @param array<string, mixed> $feed
     */
    public function toRssXml(array $feed, string $appUrl, string $appName): string
    {
        $seen = [];
        $items = [];

        foreach ($feed['sources'] as $source) {
            $sourceItems = $this->fetcher->fetchItems((string) $source['url']);
            foreach ($sourceItems as $item) {
                $dedupeKey = $item['id'] ?: ($item['link'] ?: md5(($item['title'] ?? '') . ($item['published_at'] ?? 0)));
                if (isset($seen[$dedupeKey])) {
                    continue;
                }

                if ($this->mustHideItem($item, $source)) {
                    continue;
                }

                $seen[$dedupeKey] = true;
                $item['source_name'] = (string) $source['name'];
                $item['source_url'] = (string) $source['url'];
                $item['score'] = $this->starScore($item, $source);
                $items[] = $item;
            }
        }

        usort($items, static function (array $a, array $b): int {
            if ($a['score'] === $b['score']) {
                return ($b['published_at'] ?? 0) <=> ($a['published_at'] ?? 0);
            }
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        $items = array_slice($items, 0, $this->maxItems);

        return $this->buildXml($feed, $items, $appUrl, $appName);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string, mixed> $source
     * @return array<int, array<string, mixed>>
     */
    public function filterPreviewItems(array $items, array $source, int $maxItems): array
    {
        $rows = [];
        foreach ($items as $item) {
            if ($this->mustHideItem($item, $source)) {
                continue;
            }
            $item['score'] = $this->starScore($item, $source);
            $rows[] = $item;
        }

        usort($rows, static function (array $a, array $b): int {
            if (($a['score'] ?? 0) === ($b['score'] ?? 0)) {
                return ($b['published_at'] ?? 0) <=> ($a['published_at'] ?? 0);
            }
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        return array_slice($rows, 0, max(1, $maxItems));
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $source
     */
    private function mustHideItem(array $item, array $source): bool
    {
        $words = $this->explodeWords((string) ($source['black_words'] ?? ''));
        if ($words === []) {
            return false;
        }

        $haystack = $this->buildHaystack(
            $item,
            !empty($source['black_target_title']),
            !empty($source['black_target_description']),
            !empty($source['black_target_content'])
        );

        foreach ($words as $word) {
            if ($word !== '' && str_contains($haystack, mb_strtolower($word))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $source
     */
    private function starScore(array $item, array $source): int
    {
        $words = $this->explodeWords((string) ($source['star_words'] ?? ''));
        if ($words === []) {
            return 0;
        }

        $haystack = $this->buildHaystack(
            $item,
            !empty($source['star_target_title']),
            !empty($source['star_target_description']),
            !empty($source['star_target_content'])
        );

        $score = 0;
        foreach ($words as $word) {
            if ($word !== '' && str_contains($haystack, mb_strtolower($word))) {
                $score++;
            }
        }

        return $score;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function buildHaystack(array $item, bool $inTitle, bool $inDescription, bool $inContent): string
    {
        $parts = [];
        if ($inTitle) {
            $parts[] = (string) ($item['title'] ?? '');
        }
        if ($inDescription) {
            $parts[] = (string) ($item['description'] ?? '');
        }
        if ($inContent) {
            $parts[] = (string) ($item['content'] ?? '');
        }

        if ($parts === []) {
            $parts[] = (string) ($item['title'] ?? '');
            $parts[] = (string) ($item['description'] ?? '');
        }

        return mb_strtolower(implode(' ', $parts));
    }

    /**
     * @return array<int, string>
     */
    private function explodeWords(string $words): array
    {
        $chunks = array_map('trim', explode(',', $words));
        return array_values(array_filter($chunks, static fn (string $word): bool => $word !== ''));
    }

    /**
     * @param array<string, mixed> $feed
     * @param array<int, array<string, mixed>> $items
     */
    private function buildXml(array $feed, array $items, string $appUrl, string $appName): string
    {
        $title = htmlspecialchars((string) $feed['title'], ENT_XML1);
        $description = htmlspecialchars((string) ($feed['description'] ?: ('Flux genere avec ' . $appName)), ENT_XML1);
        $link = htmlspecialchars($appUrl, ENT_XML1);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0"><channel>';
        $xml .= '<title>' . $title . '</title>';
        $xml .= '<link>' . $link . '</link>';
        $xml .= '<description>' . $description . '</description>';

        foreach ($items as $item) {
            $itemTitle = htmlspecialchars((string) ($item['title'] ?: 'Sans titre'), ENT_XML1);
            $itemLink = htmlspecialchars((string) ($item['link'] ?: $appUrl), ENT_XML1);
            $itemDesc = (string) ($item['description'] ?? '');
            $sourceName = htmlspecialchars((string) ($item['source_name'] ?? 'Source inconnue'), ENT_XML1);
            $sourceUrl = htmlspecialchars((string) ($item['source_url'] ?? $appUrl), ENT_XML1);
            $pubDate = !empty($item['published_at']) ? date(DATE_RSS, (int) $item['published_at']) : gmdate(DATE_RSS);

            $xml .= '<item>';
            $xml .= '<title>' . $itemTitle . '</title>';
            $xml .= '<link>' . $itemLink . '</link>';
            $xml .= '<description><![CDATA[' . $itemDesc . ']]></description>';
            $xml .= '<pubDate>' . htmlspecialchars($pubDate, ENT_XML1) . '</pubDate>';
            $xml .= '<source url="' . $sourceUrl . '">' . $sourceName . '</source>';
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return $xml;
    }
}
