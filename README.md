# Sympli RSS Fusion

Sympli RSS Fusion est une application PHP auto-hebergeable pour fusionner plusieurs flux RSS/Atom en un flux master unique, sans Composer.

## Fonctionnalites

- Fusion de plusieurs sources RSS/Atom dans un flux master.
- Filtres par source: black words et star words (titre/description/contenu).
- Previsualisation source avec prise en compte immediate des regles de filtrage.
- Import/export JSON depuis la page d'entree et la page de gestion.
- Suppression manuelle d'un flux depuis l'UI.
- Suppression automatique optionnelle des flux inactifs.
- Interface multilingue FR/EN extensible via JSON.
- Themes configurables: `default`, `basic`, `dashboard`, `tiles`.
- Mode `dev` (erreurs detaillees, logs, DB dediee).
- Pages d'erreur 404/500 + page Donnees personnelles.
- Verification optionnelle de version distante avec alerte de mise a jour dans le footer.

## Installation rapide

```bash
cp .env.example .env
php -S 127.0.0.1:8080 -t public
```

Puis ouvrir `http://127.0.0.1:8080`.

## Configuration .env

- `APP_NAME`: nom du projet (par defaut `Sympli RSS Fusion`).
- `APP_URL`: URL publique.
- `APP_LANG`: `fr` ou `en` (ou autre JSON dans `config/lang`).
- `APP_THEME`: `default`, `basic`, `dashboard`, `tiles` (ou theme custom).
- `APP_ENV`: `prod` ou `dev`.
- `DB_PATH`: base SQLite prod.
- `DB_PATH_DEV`: base SQLite dev.
- `LOG_PATH`: fichier de logs.
- `CACHE_DIR`, `CACHE_TTL`, `HTTP_TIMEOUT`, `MAX_ITEMS`.
- `AUTO_PRUNE_ENABLED`, `AUTO_PRUNE_DAYS`.
- `PREVIEW_ITEMS`.
- `VERSION_CHECK_ENABLED`: `1` pour activer la verification de version distante (desactive par defaut).

## Routes

- `GET /`
- `POST /create`
- `POST /import-master`
- `GET /export-master?token=...`
- `GET /manage/{token}`
- `POST /manage/{token}`
- `POST /manage/{token}/delete`
- `GET /manage/{token}/export`
- `POST /manage/{token}/import`
- `GET /preview-source?url=...`
- `GET /rss/{token}`
- `GET /privacy`

## Documentation projet

- Installation detaillee: `docs/INSTALL.md`
- Technique: `docs/DOCUMENTATION.md`
- Donnees personnelles: `PERSONAL_DATA.md`
- Contribuer: `CONTRIBUTING.md`
- Code de conduite: `CODE_OF_CONDUCT.md`
- Securite: `SECURITY.md`
- Historique: `CHANGELOG.md`
