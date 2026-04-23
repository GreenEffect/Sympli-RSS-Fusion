# Données personnelles

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

## Hébergement

Les données restent sur votre infrastructure (self-hosted), selon votre configuration serveur.
