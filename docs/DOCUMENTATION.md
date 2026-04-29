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

## 🔐 Sécurité des récupérations externes / External fetch security

- FR: Depuis la version 1.1.0 l'application renforce la sécurité lors de la récupération et du parsing des flux externes pour prévenir les attaques SSRF et XXE. Concrètement :
  - seules les URLs en `http` ou `https` sont autorisées ;
  - les hôtes sont résolus (A/AAAA) et les adresses privées / `localhost` / link-local / ULA IPv6 sont bloquées ;
  - les requêtes HTTP sont effectuées via `cURL` avec timeouts et limite de taille (1 MiB) ;
  - le parser XML désactive explicitement la résolution des entités externes (`LIBXML_NONET`, `libxml_disable_entity_loader`) pour prévenir les XXE.

- EN: Since 1.1.0 the application hardens external feed fetching and XML parsing to mitigate SSRF and XXE risks:
  - only `http` and `https` URLs are accepted;
  - hosts are resolved (A/AAAA) and private addresses / `localhost` / link-local / IPv6 ULA ranges are blocked;
  - HTTP fetches use `cURL` with timeouts and a size cap (1 MiB);
  - XML parsing disables external entity resolution (`LIBXML_NONET`, `libxml_disable_entity_loader`).

## 🛡️ Rate limiting / Limitation de débit

- FR: Une protection par rate-limiting est maintenant active pour les endpoints sensibles (prévisualisation, création, import/export). Elle utilise un compteur simple côté serveur, indexé par client (IP) et stocké en petites entrées sous `var/rate/` (fichiers hachés contenant un `count` et un `start`). Les paramètres par défaut limitent par exemple `/preview-source` à 30 requêtes / 60s et `/create` à 10 requêtes / 60s. Ceci réduit les abus et la charge serveur.

- EN: A simple server-side rate limiter is now active for sensitive endpoints (preview, create, import/export). It stores small per-client counters under `var/rate/` (hashed files containing `count` and `start`). Defaults include `/preview-source` = 30 reqs/60s and `/create` = 10 reqs/60s. This reduces abuse and server load.

Configuration options (tunable via `.env`):

- `RATE_FILE_TTL` — how long (in seconds) a rate file is considered valid before eligible for purge (default `3600`).
- `RATE_PURGE_FREQUENCY` — minimum interval (in seconds) between automatic purge runs (default `3600`).

Additional note:

- FR: Les imports JSON téléversés via l'interface sont maintenant limités à 1 MiB et subissent une validation du type MIME pour réduire le risque d'attaques par déni de service via des fichiers volumineux.

- EN: Uploaded JSON import files via the UI are now capped at 1 MiB and undergo MIME/type validation to reduce the risk of denial-of-service attacks using oversized uploads.


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

  Note: Cache and log writes use atomic write (temp file + rename) and file locking to avoid corruption under concurrent access.

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