# Technical Documentation

## EN

### Scope

This document describes architecture and internals.
For installation, see `docs/INSTALL.md`.
For released changes, see `CHANGELOG.md`.

### Architecture

- Front controller: `public/index.php`
- HTTP router: `src/Http/App.php`
- Idempotent installer: `src/Installer.php`
- ENV config: `src/Env.php`
- SQLite persistence: `src/Persistence/Database.php`, `src/Persistence/FeedRepository.php`
- Aggregation: `src/Service/FeedAggregator.php`
- Feed fetch/parsing: `src/Service/FeedFetcher.php`
- XML cache: `src/Service/CacheService.php`
- Logging: `src/Support/Logger.php`
- I18n: `src/I18n/Translator.php`

### Web root isolation

The web root must target `public/` to avoid exposing `.env`, `src/`, `var/data`, and `var/log`.

### Routes

| Method | Route | Description |
|---|---|---|
| GET | `/` | Entry page |
| POST | `/create` | Create master feed |
| POST | `/import-master` | JSON import from home |
| GET | `/export-master?token=...` | JSON export from home |
| POST | `/import-master-opml` | OPML import from home |
| GET | `/export-master-opml?token=...` | OPML export from home |
| GET | `/manage/{token}` | Management page |
| POST | `/manage/{token}` | Save changes |
| POST | `/manage/{token}/delete` | Delete feed |
| GET | `/manage/{token}/export` | JSON export |
| POST | `/manage/{token}/import` | JSON import |
| GET | `/manage/{token}/export-opml` | OPML export |
| POST | `/manage/{token}/import-opml` | OPML import |
| GET | `/preview-source?url=...` | Source preview |
| GET | `/rss/{token}` | Final RSS output |
| GET | `/privacy` | Personal data page |

### Runtime model

- No user account.
- A 48-char hex token is the access key.
- Anyone with the link can read/edit/delete that feed.

### Security

- CSRF protection on sensitive POST operations.
- Hardened external fetching:
  - only `http`/`https` schemes,
  - DNS resolution + private/localhost blocking,
  - `cURL` with timeout and payload size limit.
- XML external entities disabled (XXE mitigation).
- JSON imports capped at 1 MiB with MIME validation.
- Server-side rate limiting on sensitive endpoints (`var/rate` hashed counters).

### Cache and performance

- XML cache files in `var/cache/{token}.xml`.
- Invalidation on TTL expiry, edit, import, delete, auto-prune.
- Atomic cache/log writes with file locking.
- Conditional HTTP requests (`ETag` / `If-Modified-Since`).

### Data model

- `feeds`: master feed metadata.
- `sources`: feed sources, filtering rules, HTTP metadata (`etag`, `last_modified`).
- Schema file: `config/schema.sql`.

### Key `.env` settings

- General: `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_LANG`, `APP_THEME`
- DB/log: `DB_PATH`, `DB_PATH_DEV`, `LOG_PATH`
- Cache/fetch: `CACHE_DIR`, `CACHE_TTL`, `HTTP_TIMEOUT`, `MAX_ITEMS`, `PREVIEW_ITEMS`
- Cleanup: `AUTO_PRUNE_ENABLED`, `AUTO_PRUNE_DAYS`
- Version check: `VERSION_CHECK_ENABLED`
- Rate limiting: `RATE_FILE_TTL`, `RATE_PURGE_FREQUENCY`

### Themes and i18n

- Built-in themes: `default`, `basic`, `dashboard`, `rssfusion`
- Theme files: `public/themes/*.css`
- Languages: `config/lang/*.json`
- Fallback language: `fr`

## FR

### Portée

Ce document décrit l'architecture et le fonctionnement interne.
Pour l'installation, voir `docs/INSTALL.md`.
Pour l'historique des livraisons, voir `CHANGELOG.md`.

### Architecture

- Front controller: `public/index.php`
- Routeur HTTP: `src/Http/App.php`
- Installateur idempotent: `src/Installer.php`
- Configuration ENV: `src/Env.php`
- Persistance SQLite: `src/Persistence/Database.php`, `src/Persistence/FeedRepository.php`
- Agrégation: `src/Service/FeedAggregator.php`
- Récupération/parsing des flux: `src/Service/FeedFetcher.php`
- Cache XML: `src/Service/CacheService.php`
- Journalisation: `src/Support/Logger.php`
- I18n: `src/I18n/Translator.php`

### Isolation web root

Le web root doit pointer vers `public/` pour éviter l'exposition de `.env`, `src/`, `var/data`, `var/log`.

### Routes

| Method | Route | Description |
|---|---|---|
| GET | `/` | Page d'entrée |
| POST | `/create` | Création d'un flux master |
| POST | `/import-master` | Import JSON depuis la home |
| GET | `/export-master?token=...` | Export JSON depuis la home |
| POST | `/import-master-opml` | Import OPML depuis la home |
| GET | `/export-master-opml?token=...` | Export OPML depuis la home |
| GET | `/manage/{token}` | Page de gestion |
| POST | `/manage/{token}` | Sauvegarde des modifications |
| POST | `/manage/{token}/delete` | Suppression d'un flux |
| GET | `/manage/{token}/export` | Export JSON |
| POST | `/manage/{token}/import` | Import JSON |
| GET | `/manage/{token}/export-opml` | Export OPML |
| POST | `/manage/{token}/import-opml` | Import OPML |
| GET | `/preview-source?url=...` | Prévisualisation de source |
| GET | `/rss/{token}` | Flux RSS final |
| GET | `/privacy` | Page données personnelles |

### Mode de fonctionnement

- Aucun compte utilisateur.
- Un token hex de 48 caractères sert de clé d'accès.
- Toute personne ayant le lien peut lire/modifier/supprimer le flux.

### Sécurité

- Protection CSRF sur les opérations POST sensibles.
- Récupération externe durcie:
  - schémas `http`/`https` uniquement,
  - résolution DNS + blocage privé/localhost,
  - `cURL` avec timeout et limite de taille.
- Entités externes XML désactivées (mitigation XXE).
- Imports JSON limités à 1 MiB avec validation MIME.
- Rate limiting serveur sur endpoints sensibles (compteurs hachés dans `var/rate`).

### Cache et performances

- Fichiers cache XML dans `var/cache/{token}.xml`.
- Invalidations sur expiration TTL, édition, import, suppression, auto-prune.
- Écritures atomiques cache/log avec verrouillage de fichier.
- Requêtes HTTP conditionnelles (`ETag` / `If-Modified-Since`).

### Modèle de données

- `feeds`: métadonnées des flux master.
- `sources`: sources RSS/Atom, règles de filtrage, métadonnées HTTP (`etag`, `last_modified`).
- Schéma: `config/schema.sql`.

### Variables `.env` principales

- Général: `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_LANG`, `APP_THEME`
- DB/log: `DB_PATH`, `DB_PATH_DEV`, `LOG_PATH`
- Cache/fetch: `CACHE_DIR`, `CACHE_TTL`, `HTTP_TIMEOUT`, `MAX_ITEMS`, `PREVIEW_ITEMS`
- Entretien: `AUTO_PRUNE_ENABLED`, `AUTO_PRUNE_DAYS`
- Vérification version: `VERSION_CHECK_ENABLED`
- Rate limiting: `RATE_FILE_TTL`, `RATE_PURGE_FREQUENCY`

### Thèmes et i18n

- Thèmes fournis: `default`, `basic`, `dashboard`, `rssfusion`
- Fichiers de thème: `public/themes/*.css`
- Langues: `config/lang/*.json`
- Langue de repli: `fr`
