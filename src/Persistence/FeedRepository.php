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

namespace RssFusionKiss\Persistence;

use PDO;

final class FeedRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array<int, array<string, mixed>> $sources
     */
    public function createFeed(string $title, string $description, array $sources): string
    {
        $token = bin2hex(random_bytes(24));
        $now = gmdate(DATE_ATOM);

        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare(
            'INSERT INTO feeds (token, title, description, created_at, updated_at) VALUES (:token, :title, :description, :created_at, :updated_at)'
        );
        $stmt->execute([
            ':token' => $token,
            ':title' => $title,
            ':description' => $description,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        $feedId = (int) $this->pdo->lastInsertId();
        $this->replaceSources($feedId, $sources);
        $this->pdo->commit();

        return $token;
    }

    /**
     * @param array<int, array<string, mixed>> $sources
     */
    public function updateFeedByToken(string $token, string $title, string $description, array $sources): bool
    {
        $feed = $this->findFeedByToken($token);
        if ($feed === null) {
            return false;
        }

        $this->pdo->beginTransaction();
        $stmt = $this->pdo->prepare(
            'UPDATE feeds SET title = :title, description = :description, updated_at = :updated_at WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':updated_at' => gmdate(DATE_ATOM),
            ':id' => (int) $feed['id'],
        ]);

        $this->pdo->prepare('DELETE FROM sources WHERE feed_id = :feed_id')->execute([':feed_id' => (int) $feed['id']]);
        $this->replaceSources((int) $feed['id'], $sources);
        $this->pdo->commit();

        return true;
    }

    public function touchLastViewed(string $token): void
    {
        $stmt = $this->pdo->prepare('UPDATE feeds SET last_viewed_at = :last_viewed_at WHERE token = :token');
        $stmt->execute([':last_viewed_at' => gmdate(DATE_ATOM), ':token' => $token]);
    }

    public function findFeedByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM feeds WHERE token = :token LIMIT 1');
        $stmt->execute([':token' => $token]);
        $feed = $stmt->fetch();

        if (!$feed) {
            return null;
        }

        $sourcesStmt = $this->pdo->prepare('SELECT * FROM sources WHERE feed_id = :feed_id ORDER BY sort_order ASC, id ASC');
        $sourcesStmt->execute([':feed_id' => (int) $feed['id']]);
        $feed['sources'] = $sourcesStmt->fetchAll() ?: [];

        return $feed;
    }

    public function deleteFeedByToken(string $token): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM feeds WHERE token = :token');
        $stmt->execute([':token' => $token]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<int, string>
     */
    public function pruneInactiveFeedTokens(int $days): array
    {
        $days = max(1, $days);
        $threshold = gmdate(DATE_ATOM, time() - ($days * 86400));

        $select = $this->pdo->prepare(
            'SELECT token FROM feeds WHERE COALESCE(last_viewed_at, created_at) < :threshold'
        );
        $select->execute([':threshold' => $threshold]);
        $tokens = $select->fetchAll(PDO::FETCH_COLUMN);

        if (!is_array($tokens) || $tokens === []) {
            return [];
        }

        $delete = $this->pdo->prepare('DELETE FROM feeds WHERE token = :token');
        foreach ($tokens as $token) {
            if (is_string($token)) {
                $delete->execute([':token' => $token]);
            }
        }

        return array_values(array_filter($tokens, static fn (mixed $token): bool => is_string($token) && $token !== ''));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getExportableConfig(string $token): ?array
    {
        $feed = $this->findFeedByToken($token);
        if ($feed === null) {
            return null;
        }

        $sources = [];
        foreach ($feed['sources'] as $source) {
            $sources[] = [
                'name' => (string) ($source['name'] ?? ''),
                'url' => (string) ($source['url'] ?? ''),
                'black_words' => (string) ($source['black_words'] ?? ''),
                'black_target_title' => !empty($source['black_target_title']),
                'black_target_description' => !empty($source['black_target_description']),
                'black_target_content' => !empty($source['black_target_content']),
                'star_words' => (string) ($source['star_words'] ?? ''),
                'star_target_title' => !empty($source['star_target_title']),
                'star_target_description' => !empty($source['star_target_description']),
                'star_target_content' => !empty($source['star_target_content']),
            ];
        }

        return [
            'title' => (string) $feed['title'],
            'description' => (string) $feed['description'],
            'sources' => $sources,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $sources
     */
    private function replaceSources(int $feedId, array $sources): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sources (
                feed_id, name, url, black_words, black_target_title, black_target_description, black_target_content,
                star_words, star_target_title, star_target_description, star_target_content, sort_order
            ) VALUES (
                :feed_id, :name, :url, :black_words, :black_target_title, :black_target_description, :black_target_content,
                :star_words, :star_target_title, :star_target_description, :star_target_content, :sort_order
            )'
        );

        foreach ($sources as $index => $source) {
            $stmt->execute([
                ':feed_id' => $feedId,
                ':name' => (string) ($source['name'] ?? ''),
                ':url' => (string) ($source['url'] ?? ''),
                ':black_words' => (string) ($source['black_words'] ?? ''),
                ':black_target_title' => !empty($source['black_target_title']) ? 1 : 0,
                ':black_target_description' => !empty($source['black_target_description']) ? 1 : 0,
                ':black_target_content' => !empty($source['black_target_content']) ? 1 : 0,
                ':star_words' => (string) ($source['star_words'] ?? ''),
                ':star_target_title' => !empty($source['star_target_title']) ? 1 : 0,
                ':star_target_description' => !empty($source['star_target_description']) ? 1 : 0,
                ':star_target_content' => !empty($source['star_target_content']) ? 1 : 0,
                ':sort_order' => $index,
            ]);
        }
    }
}
