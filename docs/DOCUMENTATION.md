# Technical Documentation

Project: Sympli RSS Fusion

Live demo: [https://sympli.rss-fusion.com/](https://sympli.rss-fusion.com/)

---

## 🏗️ Architecture

- **Front controller**: `public/index.php`
- **HTTP router**: `RssFusionKiss\Http\App`
- **Idempotent installer**: `RssFusionKiss\Installer` (auto-triggered on first access)
- **Error logger**: `RssFusionKiss\Support\Logger`
- **SQLite persistence**: `RssFusionKiss\Persistence\*`
- **Aggregation**: `RssFusionKiss\Service\FeedAggregator`
- **Feed fetching/parsing + preview**: `RssFusionKiss\Service\FeedFetcher`
- **XML cache**: `RssFusionKiss\Service\CacheService`
- **JSON i18n**: `RssFusionKiss\I18n\Translator`

---

## 🔒 Web Root

The web root **must** target `public/` to prevent HTTP exposure of sensitive files:

- `.env`
- SQLite databases in `var/data`
- Logs in `var/log`
- Application code in `src`

---

## 🛣️ Routes
   Method | Route | Description |
 |--------|-------|-------------|
 | GET | `/` | Interface for creating/opening a feed. |
 | POST | `/create` | Create a new feed. |
 | POST | `/import-master` | Import a new master feed from JSON. |
 | GET | `/export-master?token=...` | Export a master feed as JSON from the entry page. |
 | GET | `/manage/{token}` | Edit a feed. |
 | POST | `/manage/{token}` | Save feed edits. |
 | POST | `/manage/{token}/delete` | Delete a feed. |
 | GET | `/manage/{token}/export` | Export feed configuration as JSON. |
 | POST | `/manage/{token}/import` | Import feed configuration from JSON. |
 | GET | `/preview-source?url=...` | Mini-parser to preview a source. |
 | GET | `/rss/{token}` | RSS XML output. |
 | GET | `/privacy` | Personal data transparency page. |

**Error pages:**
- 404: `public/views/errors/404.php`
- 500: `public/views/errors/500.php`

---

## Open Mode

No account required. The **48-char hex token** is the access key.

---

## Unique Link Security

- Token generated via `random_bytes(24)`.
- Token is unguessable.
- Anyone with the link can **view, modify, or delete** the feed (product design choice).

---

## ⚙️ Auto-Installation

On every startup via `public/index.php`:

1. Copies `.env.example` to `.env` if missing.
2. Runs SQLite migration via `config/schema.sql`.
3. Creates the cache directory.

The CLI script `bin/install.php` is also available and reuses the same logic.

---

## Dev Mode

Configure in `.env`:

- `APP_ENV=dev`
- `DB_PATH_DEV=...`
- `LOG_PATH=...`

**Behavior:**
- Detailed error display.
- PHP errors converted to exceptions.
- Logging of uncaught and fatal exceptions.
- Dedicated SQLite database for development.

---

## Auto-Pruning of Inactive Feeds

Configure in `.env`:

- `AUTO_PRUNE_ENABLED=1` to enable.
- `AUTO_PRUNE_DAYS=<number>` to set the threshold in days.

**Behavior:**
- Runs on every request.
- Deletes feeds where `COALESCE(last_viewed_at, created_at)` is older than the threshold.
- Invalidates associated XML cache files.

---

## Cache

- XML files stored in `var/cache`.
- Filename: `{token}.xml`.
- **Invalidation triggers:**
  - After `CACHE_TTL` expiry.
  - After feed update/import/deletion.
  - After auto-prune.

---

## SQLite Database

**Tables:**
- `feeds`: Master feed metadata (+ `last_viewed_at`).
- `sources`: Associated sources + blacklist/star rules.

**Schema:** `config/schema.sql`

---

## Filtering Rules

**Per source:**
- `black_words`: If any word is present in the targeted fields, the item is hidden.
- `star_words`: Each matching word increases a priority score.

**Targeted fields:**
- Title
- Description
- Content

---

## Deduplication and Sorting

- **Deduplication:** By `guid/id`, then `link`, then fallback hash.
- **Final sort:**
  1. Star score (descending)
  2. Date (descending)

---

## I18n

- Translation files: `config/lang/<code>.json`
- Active language: `APP_LANG`
- Automatic fallback to `fr`

---

## Themes

- Theme files: `public/themes/<name>.css`
- Active theme: `APP_THEME`
- Provided themes: `default`, `basic`, `dashboard`, `tiles`
- Automatic fallback to `default`

---

## 🔄 Version Check

Configure in `.env`:

- `VERSION_CHECK_ENABLED=1` to enable.
- `VERSION_CHECK_ENABLED=0` to disable (default).

**Behavior:**
- Reads local version from `VERSION`.
- Fetches remote version from:
  - [https://raw.githubusercontent.com/GreenEffect/Sympli-RSS-Fusion/refs/heads/main/VERSION](https://raw.githubusercontent.com/GreenEffect/Sympli-RSS-Fusion/refs/heads/main/VERSION)
- Compares versions with `version_compare`.
- Displays "Update available" in the footer (bottom right, italic) if the remote version is newer.
- Links to the GitHub repository:
  - [https://github.com/GreenEffect/Sympli-RSS-Fusion](https://github.com/GreenEffect/Sympli-RSS-Fusion)

---

## 🧪 Deployment

### Local Startup

```bash
cp .env.example .env
php -S 127.0.0.1:8080 -t public