# Documentation technique

Projet: Sympli RSS Fusion

## Architecture

- Front controller: `public/index.php`
- Routeur HTTP: `RssFusionKiss\Http\App`
- Installateur idempotent (auto au premier acces): `RssFusionKiss\Installer`
- Logger erreur: `RssFusionKiss\Support\Logger`
- Persistance SQLite: `RssFusionKiss\Persistence\*`
- Aggregation: `RssFusionKiss\Service\FeedAggregator`
- Recuperation/parsing des feeds + preview: `RssFusionKiss\Service\FeedFetcher`
- Cache XML: `RssFusionKiss\Service\CacheService`
- I18n JSON: `RssFusionKiss\I18n\Translator`

## Routes

- `GET /`: interface creation + ouverture d'un flux existant.
- `POST /create`: creation d'un flux.
- `POST /import-master`: import JSON d'un nouveau flux master.
- `GET /export-master?token=...`: export JSON d'un flux master depuis la page d'entree.
- `GET /manage/{token}`: edition d'un flux.
- `POST /manage/{token}`: sauvegarde de l'edition.
- `POST /manage/{token}/delete`: suppression d'un flux.
- `GET /manage/{token}/export`: export JSON de configuration.
- `POST /manage/{token}/import`: import JSON de configuration.
- `GET /preview-source?url=...`: mini parseur pour previsualiser une source.
- `GET /rss/{token}`: sortie RSS XML.
- `GET /privacy`: page de transparence sur les donnees personnelles.

Pages d'erreur:

- `404`: `public/views/errors/404.php`
- `500`: `public/views/errors/500.php`

## Mode ouvert

Aucun compte n'est requis. Le token (48 chars hex) est la cle d'acces.

## Securite du lien unique

- Token genere via `random_bytes(24)`.
- Token difficilement devinable.
- Toute personne ayant le lien peut consommer/modifier/supprimer le flux (choix produit assume).

## Auto-installation

A chaque demarrage via `public/index.php`:

1. copie `.env.example` vers `.env` si absent,
2. migration SQLite via `config/schema.sql`,
3. creation du dossier de cache.

Le script CLI `bin/install.php` reste disponible et reutilise la meme logique.

## Mode dev

Config `.env`:

- `APP_ENV=dev`
- `DB_PATH_DEV=...`
- `LOG_PATH=...`

Comportement:

- affichage detaille des erreurs,
- conversion des erreurs PHP en exceptions,
- journalisation des exceptions non capturees et fatales,
- base SQLite dediee au dev.

## Auto-suppression des flux inactifs

Config `.env`:

- `AUTO_PRUNE_ENABLED=1` pour activer,
- `AUTO_PRUNE_DAYS=<nombre>` pour le seuil.

Comportement:

- execute a chaque requete,
- supprime les flux dont `COALESCE(last_viewed_at, created_at)` est plus ancien que le seuil,
- invalide les fichiers cache XML associes.

## Cache

- Fichiers XML dans `var/cache`.
- Nom de fichier: `{token}.xml`.
- Invalidation:
  - apres expiration `CACHE_TTL`,
  - apres mise a jour/import/suppression,
  - apres auto-prune.

## Base SQLite

Tables:

- `feeds`: metadonnees du flux master (+ `last_viewed_at`).
- `sources`: sources associees + regles black/star.

Schema: `config/schema.sql`.

## Regles de filtrage

Par source:

- `black_words`: si un mot est present dans les zones ciblees, l'item est masque.
- `star_words`: chaque mot present augmente un score de priorite.

Zones ciblees:

- titre,
- description,
- contenu.

## Deduplication et tri

- Dedupe: `guid/id`, sinon `link`, sinon hash de secours.
- Tri final:
  1. score star descendant,
  2. date descendante.

## I18n

- Fichiers de traduction: `config/lang/<code>.json`.
- Langue active: `APP_LANG`.
- Fallback automatique sur `fr`.

## Themes

- Fichiers de theme: `public/themes/<nom>.css`.
- Theme actif: `APP_THEME`.
- Themes fournis: `default`, `basic`, `dashboard`, `tiles`.
- Fallback automatique sur `default`.

## Verification de version

Config `.env`:

- `VERSION_CHECK_ENABLED=1` pour activer la verification,
- `VERSION_CHECK_ENABLED=0` pour la desactiver (par defaut).

Comportement:

- lit la version locale depuis le fichier `VERSION`,
- interroge le marqueur distant:
  - `https://raw.githubusercontent.com/GreenEffect/Sympli-RSS-Fusion/refs/heads/master/VERSION`,
- compare les versions avec `version_compare`,
- affiche la mention "Mise a jour disponible" dans le footer (bas droite, petit texte italique) quand la version distante est superieure,
- lien de la mention vers le depot GitHub:
  - `https://github.com/GreenEffect/Sympli-RSS-Fusion`.

## Exploitation

### Demarrage local

```bash
cp .env.example .env
php -S 127.0.0.1:8080 -t public
```

### Production

- Exposer `public/` comme document root.
- Configurer `APP_URL` avec l'URL publique.
- Verifier les droits d'ecriture sur `var/cache` et `var/data`.

## Fichiers de gouvernance

- `PERSONAL_DATA.md`
- `CONTRIBUTING.md`
- `CODE_OF_CONDUCT.md`
- `SECURITY.md`
- `CHANGELOG.md`
