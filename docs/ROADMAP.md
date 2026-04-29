# Roadmap — Sympli RSS Fusion

## EN

This document tracks future work only.
Released items are documented exclusively in `CHANGELOG.md`.

### High priority

- Self-hosted fonts (remove third-party Google Fonts calls).
- Per-source item limit.
- Parallel source fetching with `curl_multi_exec`.

### Medium priority

- Per-source time filters (ignore items older than N days).
- Optional title-similarity deduplication.
- Cache warmup/cron endpoint.
- Admin CLI (`purge:cache`, `purge:inactive`, `stats`, `export:all`).
- Log retention/rotation tooling (or `logrotate` config generator).

### Low priority

- Per-source status indicators (last success, HTTP code, items count).
- Nested master feeds.
- Webhook on new item publication.
- Dark theming option.
- Optional Dockerfile and compose setup.

## FR

Ce document suit uniquement les évolutions futures.
Les éléments déjà livrés sont documentés uniquement dans `CHANGELOG.md`.

### Priorité haute

- Auto-hébergement des polices (suppression des appels tiers à Google Fonts).
- Limite d'items par source configurable.
- Récupération parallèle des sources via `curl_multi_exec`.

### Priorité moyenne

- Filtres temporels par source (ignorer les items plus vieux que N jours).
- Dédoublonnage optionnel par similarité de titre.
- CLI d'administration (`purge:cache`, `purge:inactive`, `stats`, `export:all`).

### Priorité basse

- Indicateurs de statut par source (dernier succès, code HTTP, nombre d'items).
- Flux master imbriqués.
- Option de thème sombre.
