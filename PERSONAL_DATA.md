# Donnees personnelles

Sympli RSS Fusion est pense pour minimiser la collecte de donnees.

## Ce qui est stocke

- Configuration des flux master (titre, description, sources, regles de filtrage).
- Cache XML des flux agreges.
- Journaux techniques selon le mode d'execution (`prod` ou `dev`).

## Ce qui n'est pas impose

- Aucun compte utilisateur.
- Aucune base distante obligatoire.
- Aucun service tiers necessaire pour fonctionner.

## Controle et suppression

- Suppression manuelle des flux via l'interface.
- Purge automatique optionnelle des flux inactifs.
- Suppression locale des fichiers SQLite/cache/logs par l'hebergeur.

## Hebergement

Les donnees restent sur votre infrastructure (self-hosted), selon votre configuration serveur.
