<p align="center">
  <img src="docs/logo.svg" alt="Sympli RSS Fusion logo" width="220">
</p>

<p align="center">
  <a href="https://github.com/GreenEffect/Sympli-RSS-Fusion/releases"><img src="https://img.shields.io/github/v/tag/GreenEffect/Sympli-RSS-Fusion?sort=semver&label=version&1" alt="Latest version"></a>
  <img src="https://img.shields.io/badge/php-%3E%3D%208.1-777bb4" alt="PHP >= 8.1">
  <img src="https://img.shields.io/badge/license-CC%20BY--NC--SA%204.0-0a7ea4" alt="License CC BY-NC-SA 4.0">
  <img src="https://img.shields.io/badge/status-stable-1f9d55" alt="Status stable">
</p>

# Sympli RSS Fusion

## EN

Sympli RSS Fusion is a self-hosted PHP application that merges multiple RSS/Atom feeds into one master feed.

Demo: https://sympli.rss-fusion.com/

<p align="center">
  <img src="docs/screenshot/rssfusion.png" alt="Theme RSS Fusion" width="48%" />
  <img src="docs/screenshot/dashboard.png" alt="Theme Dashboard" width="48%" />
</p>
<p align="center">
  <img src="docs/screenshot/default.png" alt="Theme Default" width="48%" />
  <img src="docs/screenshot/basic.png" alt="Theme Basic" width="48%" />
</p>

### Highlights

- KISS architecture with no mandatory Composer dependency.
- Single front controller: `public/index.php`.
- JSON and OPML import/export.
- Per-source filtering rules (black words / star words).
- Source preview with immediate filtering.
- Conditional HTTP requests (`ETag` / `If-Modified-Since`).
- SSRF/XXE protections, import size cap, and server-side rate limiting.
- Multilingual UI (FR/EN) and themes: `default`, `basic`, `dashboard`, `rssfusion`.

### Quick Start

```bash
cp .env.example .env
php -S 127.0.0.1:8080 -t public
```

Open: http://127.0.0.1:8080

### Documentation

- Installation: `docs/INSTALL.md`
- Technical documentation: `docs/DOCUMENTATION.md`
- Roadmap: `docs/ROADMAP.md`
- Changelog (single source for released changes): `CHANGELOG.md`
- Contributing: `CONTRIBUTING.md`
- Security: `SECURITY.md`
- Personal data: `PERSONAL_DATA.md`

## FR

Sympli RSS Fusion est une application PHP auto-hébergeable qui fusionne plusieurs flux RSS/Atom en un flux master unique.

Demo: https://sympli.rss-fusion.com/

### Points clés

- Architecture KISS sans dépendance Composer obligatoire.
- Front controller unique : `public/index.php`.
- Import/export JSON et OPML.
- Règles de filtrage par source (black words / star words).
- Prévisualisation de source avec filtrage immédiat.
- Requêtes HTTP conditionnelles (`ETag` / `If-Modified-Since`).
- Protections SSRF/XXE, limite de taille d'import, rate limiting serveur.
- UI multilingue (FR/EN) et thèmes : `default`, `basic`, `dashboard`, `rssfusion`.

### Démarrage rapide

```bash
cp .env.example .env
php -S 127.0.0.1:8080 -t public
```

Ouvrir : http://127.0.0.1:8080

### Documentation

- Installation : `docs/INSTALL.md`
- Documentation technique : `docs/DOCUMENTATION.md`
- Roadmap : `docs/ROADMAP.md`
- Changelog (source unique des évolutions livrées) : `CHANGELOG.md`
- Contribution : `CONTRIBUTING.md`
- Sécurité : `SECURITY.md`
- Données personnelles : `PERSONAL_DATA.md`
