# Personal Data / Données personnelles

## EN

Sympli RSS Fusion is designed to minimize data collection.

### Stored data

- Master feed configuration (title, description, sources, rules).
- XML cache of aggregated feeds.
- Technical logs (`LOG_PATH`, default `var/log/app.log`).
- Hashed rate-limiting counters in `var/rate/` (`count`, `start`).
- Per-source technical metadata (`etag`, `last_modified`).

### Not required

- No user account.
- No mandatory remote database.
- No mandatory third-party service.

### Control and deletion

- Manual feed deletion from the interface.
- Optional automatic pruning of inactive feeds.
- Local deletion of SQLite/cache/log files by the host.

### Technical protections

- External schemes restricted to `http`/`https`.
- Private/localhost DNS target blocking (SSRF mitigation).
- XML parser hardening (XXE mitigation).
- JSON import size cap (1 MiB) and MIME validation.
- Atomic writes and file locking for cache/log files.

### Hosting

Data remains on your own infrastructure (self-hosted), depending on your server configuration.

## FR

Sympli RSS Fusion est conçu pour minimiser la collecte de données.

### Données stockées

- Configuration des flux master (titre, description, sources, règles).
- Cache XML des flux agrégés.
- Journaux techniques (`LOG_PATH`, par défaut `var/log/app.log`).
- Compteurs de rate limiting hachés dans `var/rate/` (`count`, `start`).
- Métadonnées techniques par source (`etag`, `last_modified`).

### Non requis

- Aucun compte utilisateur.
- Aucune base distante obligatoire.
- Aucun service tiers obligatoire.

### Contrôle et suppression

- Suppression manuelle des flux via l'interface.
- Purge automatique optionnelle des flux inactifs.
- Suppression locale des fichiers SQLite/cache/log par l'hébergeur.

### Protections techniques

- Schémas externes limités à `http`/`https`.
- Blocage DNS des cibles privées/localhost (mitigation SSRF).
- Durcissement du parser XML (mitigation XXE).
- Limite de taille JSON import (1 MiB) et validation MIME.
- Écritures atomiques et verrouillage de fichier pour cache/log.

### Hébergement

Les données restent sur votre infrastructure (self-hosted), selon votre configuration serveur.
