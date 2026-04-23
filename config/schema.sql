CREATE TABLE IF NOT EXISTS feeds (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    token TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    description TEXT DEFAULT '',
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    last_viewed_at TEXT
);

CREATE TABLE IF NOT EXISTS sources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    feed_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    url TEXT NOT NULL,
    black_words TEXT DEFAULT '',
    black_target_title INTEGER NOT NULL DEFAULT 1,
    black_target_description INTEGER NOT NULL DEFAULT 1,
    black_target_content INTEGER NOT NULL DEFAULT 0,
    star_words TEXT DEFAULT '',
    star_target_title INTEGER NOT NULL DEFAULT 1,
    star_target_description INTEGER NOT NULL DEFAULT 1,
    star_target_content INTEGER NOT NULL DEFAULT 0,
    sort_order INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_sources_feed_id ON sources(feed_id);
