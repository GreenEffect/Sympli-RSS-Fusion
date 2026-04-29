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
