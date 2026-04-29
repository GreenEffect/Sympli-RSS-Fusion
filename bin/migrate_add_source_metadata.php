#!/usr/bin/env php
<?php
declare(strict_types=1);

// Migration safe script: add etag and last_modified columns to sources table if missing.
// Usage: php bin/migrate_add_source_metadata.php /path/to/db.sqlite

$dbPath = $argv[1] ?? __DIR__ . '/../var/data/sympli_rss_fusion.sqlite';

if (!is_file($dbPath)) {
    fwrite(STDERR, "Database file not found: $dbPath\n");
    exit(2);
}

$backupPath = $dbPath . '.backup.' . date('Ymd_His');
if (!copy($dbPath, $backupPath)) {
    fwrite(STDERR, "Failed to create backup at: $backupPath\n");
    exit(3);
}

echo "Backup created: $backupPath\n";

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("PRAGMA table_info('sources')");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!is_array($cols)) {
        throw new RuntimeException('Unable to read table info for sources');
    }

    $needed = [];
    if (!in_array('etag', $cols, true)) {
        $needed[] = "ALTER TABLE sources ADD COLUMN etag TEXT DEFAULT ''";
    }
    if (!in_array('last_modified', $cols, true)) {
        $needed[] = "ALTER TABLE sources ADD COLUMN last_modified TEXT DEFAULT ''";
    }

    if ($needed === []) {
        echo "No changes needed; columns already present.\n";
        exit(0);
    }

    foreach ($needed as $sql) {
        echo "Applying: $sql\n";
        $pdo->exec($sql);
    }

    echo "Migration applied successfully.\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Migration failed: ' . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "You can restore the backup from: $backupPath\n");
    exit(1);
}
