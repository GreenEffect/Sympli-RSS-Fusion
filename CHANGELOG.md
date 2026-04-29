# Changelog

## EN

All notable changes to Sympli RSS Fusion are documented here.

### [1.1.0] - 2026-04-29

#### Added

- JavaScript modernization (legacy DOM-0 to ES6).
- Ability to attach new secondary sources while editing an existing feed.
- Dedicated page to display configuration items.
- Application version displayed in footer.
- Application logger (`src/Support/Logger.php`) with `info`, `warning`, `error`, `debug` levels (with the assistance of Claude Code)
- Conditional HTTP requests support (`ETag` / `If-Modified-Since` - with the assistance of Claude Code) with idempotent migration script: `bin/migrate_add_source_metadata.php`.

#### Changed

- Footer links visual adjustments.
- Project version bumped to `1.1.0`.

#### Security

- JSON import validation (MIME + 1 MiB limit).
- Atomic writes and locking for cache/log files under concurrent access.
- Server-side rate limiter on sensitive endpoints (`/preview-source`, `/create`, imports/exports) with HTTP 429 responses.

### [1.0.0] - 2026-04-27

#### Added

- FR/EN multilingual support through JSON files.
- Configurable UI themes: `default`, `basic`, `dashboard`, `rssfusion`.
- JSON import/export from home and manage pages.
- OPML import/export for RSS source lists.
- Source preview with filtering rules.
- Manual feed deletion in UI.
- Optional auto-pruning of inactive feeds.
- `dev` mode (detailed errors, logs, dedicated DB).
- Dedicated 404/500 pages and personal data page.
- Optional remote version checks.
- Apache front-controller routing via `public/.htaccess`.
- First-run bootstrap (`.env` creation + SQLite schema setup).

#### Changed

- Rebranding to Sympli RSS Fusion.
- Removed Composer references.
- Updated installation and route documentation.

#### Security

- CSRF protection on sensitive POST operations.
- SSRF/XXE mitigations for remote fetch and XML parsing.
- External request timeout and payload size limits.

## FR

Toutes les évolutions notables de Sympli RSS Fusion sont documentées ici.

### [1.1.0] - 2026-04-29

#### Ajouts

- Modernisation JavaScript (legacy DOM-0 vers ES6).
- Possibilité d'ajouter de nouvelles sources secondaires pendant l'édition d'un flux.
- Page dédiée d'affichage des éléments de configuration.
- Affichage de la version applicative dans le footer.
- Logger applicatif (`src/Support/Logger.php`) avec niveaux `info`, `warning`, `error`, `debug` (avec l'assistance de Claude Code)
- Support des requêtes HTTP conditionnelles (`ETag` / `If-Modified-Since` - avec l'assistance de Claude Code) avec script de migration idempotent : `bin/migrate_add_source_metadata.php`.

#### Changements

- Ajustements visuels des liens du footer.
- Version du projet passée à `1.1.0`.

#### Sécurité

- Validation des imports JSON (MIME + limite à 1 MiB).
- Écritures atomiques et verrouillage pour cache/log en cas d'accès concurrents.
- Rate limiting serveur sur endpoints sensibles (`/preview-source`, `/create`, imports/exports) avec réponses HTTP 429.

### [1.0.0] - 2026-04-27

#### Ajouts

- Support multilingue FR/EN via JSON.
- Thèmes UI configurables : `default`, `basic`, `dashboard`, `rssfusion`.
- Imports/exports JSON depuis les pages d'entrée et de gestion.
- Imports/exports OPML pour les listes de sources RSS.
- Prévisualisation de source avec règles de filtrage.
- Suppression manuelle de flux via l'UI.
- Auto-pruning optionnel des flux inactifs.
- Mode `dev` (erreurs détaillées, logs, base dédiée).
- Pages dédiées 404/500 et page données personnelles.
- Vérification distante de version en option.
- Routage front-controller Apache via `public/.htaccess`.
- Bootstrap de premier lancement (création `.env` + schéma SQLite).

#### Changements

- Rebranding vers Sympli RSS Fusion.
- Suppression des références Composer.
- Mise à jour de la documentation d'installation et des routes.

#### Sécurité

- Protection CSRF sur opérations POST sensibles.
- Mitigations SSRF/XXE pour récupération distante et parsing XML.
- Limites de timeout et de taille sur les requêtes externes.
