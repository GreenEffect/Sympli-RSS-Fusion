# Données personnelles

## FR

Sympli RSS Fusion est pensé pour minimiser la collecte de données.

## Ce qui est stocké

- Configuration des flux master (titre, description, sources, règles de filtrage).
- Cache XML des flux agrégés.
- Journaux techniques selon le mode d'exécution (`prod` ou `dev`).

## Ce qui n'est pas imposé

- Aucun compte utilisateur.
- Aucune base distante obligatoire.
- Aucun service tiers nécessaire pour fonctionner.

## Controle et suppression

- Suppression manuelle des flux via l'interface.
- Purge automatique optionnelle des flux inactifs.
- Suppression locale des fichiers SQLite/cache/logs par l'hébergeur.

### Protection complémentaire

- L'application met en œuvre des contrôles lors de la récupération de flux externes pour éviter l'accès non intentionnel à des ressources internes (SSRF). Seuls les schémas `http`/`https` sont autorisés, et les adresses privées/localhost sont bloquées après résolution DNS.
- Les entités externes dans les documents XML sont désactivées pour prévenir les fuites via XXE.

 - Les fichiers JSON importés via l'interface sont également validés et limités à 1 MiB afin de réduire le risque d'attaques par déni de service via des téléversements volumineux.

Les écritures des fichiers de cache et des journaux sont réalisées de façon atomique et via des verrous de fichier pour réduire le risque de corruption en cas d'accès concurrents.

### Limitation de débit / Rate limiting

Une limitation de débit côté serveur a été ajoutée pour les endpoints sensibles (prévisualisation, création, import/export). Le système utilise l'adresse IP du client comme identifiant, mais les compteurs sont stockés sous forme de fichiers hachés dans `var/rate/` contenant uniquement un `count` et un `start` (horodatage). Aucune autre information personnelle n'est enregistrée, et ces compteurs sont de courte durée selon la fenêtre configurée.

## Hébergement

Les données restent sur votre infrastructure (self-hosted), selon votre configuration serveur.

---

## EN

Sympli RSS Fusion is designed to minimize data collection.

### What is stored

- Master feed configuration (title, description, sources, filtering rules).
- XML cache of aggregated feeds.
- Technical logs depending on runtime mode (`prod` or `dev`).

### What is not required

- No user account.
- No mandatory remote database.
- No mandatory third-party service.

### Control and deletion

- Manual feed deletion from the interface.
- Optional automatic pruning of inactive feeds.
- Local deletion of SQLite/cache/log files by the host.

### Hosting

Data remains on your own infrastructure (self-hosted), depending on your server configuration.

### Additional protections

- Uploaded JSON import files are validated and capped at 1 MiB to reduce the risk of denial-of-service attacks through oversized uploads.

Cache and log writes are performed atomically and use file locking to reduce the risk of corruption under concurrent access.

### Rate limiting

A server-side rate limiter protects sensitive endpoints (preview, create, import/export). It uses the client IP as an identifier; however counters are stored as hashed files under `var/rate/` containing only a `count` and a `start` timestamp. No other personal data is recorded and counters are short-lived according to the configured window.
