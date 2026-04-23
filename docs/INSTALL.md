# Installation Guide / Guide d'installation

This document describes a full installation from zero.
Ce document decrit une installation complete depuis zero.

## FR - Installation complete

### 1. Pre-requis

- PHP: version 8.1+ (recommended 8.2 or 8.3).
- PHP extensions required:
  - `pdo_sqlite`
  - `sqlite3`
  - `dom` (DOMDocument / DOMXPath)
  - `libxml`
  - `json`
- PHP setting required:
  - `allow_url_fopen=1` (used to fetch RSS/Atom feeds and remote VERSION marker).
- HTTPS support for remote requests (OpenSSL enabled in PHP).
- Write permissions on:
  - `var/data`
  - `var/cache`
  - `var/log`

### 2. Recuperer le projet

```bash
git clone https://github.com/GreenEffect/Sympli-RSS-Fusion.git
cd Sympli-RSS-Fusion
```

### 3. Creer votre configuration locale

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Parametrer `.env`

Minimum recommande:

- `APP_URL=http://127.0.0.1:8080`
- `APP_ENV=prod` (ou `dev` pour debug)
- `APP_LANG=fr` (ou `en`)
- `APP_THEME=default`

Configuration importante:

- `DB_PATH=var/data/sympli_rss_fusion.sqlite`
- `DB_PATH_DEV=var/data/sympli_rss_fusion_dev.sqlite`
- `LOG_PATH=var/log/app.log`
- `VERSION_CHECK_ENABLED=0` (desactive par defaut)
  - passer a `1` pour activer la verification de nouvelle version GitHub.

### 5. Demarrer l'application

```bash
php -S 127.0.0.1:8080 -t public
```

Puis ouvrir:

- `http://127.0.0.1:8080`

### 6. Ce qui se passe au premier acces

Le bootstrap lance une installation idempotente:

- creation de `.env` si absent (depuis `.env.example`),
- creation des dossiers de travail (`var/cache`, `var/data`, `var/log` selon config),
- application du schema SQLite (`config/schema.sql`) si necessaire.

Aucune etape Composer n'est requise.

### 7. Verifications rapides

- La page d'accueil s'affiche sans erreur 500.
- La creation d'un flux fonctionne.
- Un fichier SQLite est cree dans `var/data`.
- Le cache XML apparait dans `var/cache` apres consultation d'un RSS.
- En mode `dev`, les erreurs detaillees sont visibles et les logs sont alimentes.

### 8. Mise en production (resume)

- Configurer votre serveur web avec `public/` comme document root.
- Configurer une vraie `APP_URL` (HTTPS recommande).
- Verifier les droits d'ecriture sur `var/`.
- Garder `APP_ENV=prod` en production.

Si vous avez deja un serveur web operationnel, le chemin le plus simple est:

- deposer les fichiers du projet sur le serveur,
- editer `APP_URL` dans `.env` avec l'URL publique de votre instance,
- ouvrir cette URL dans votre navigateur :)

### 9. Depannage

- `Class not found`:
  - verifier que `src/autoload.php` est present et charge par `public/index.php`.
- Erreur SQLite (fichier inaccessible):
  - verifier les droits sur `var/data` et la valeur de `DB_PATH`.
- Impossible de recuperer les flux externes:
  - verifier internet sortant, DNS, SSL, et `allow_url_fopen=1`.
- Verification de version inactive:
  - verifier `VERSION_CHECK_ENABLED=1` dans `.env`.

---

## EN - Full setup

### 1. Prerequisites

- OS: Linux, macOS, or Windows.
- Git: 2.30+ (recommended).
- PHP: 8.1+ (8.2/8.3 recommended).
- Required PHP extensions:
  - `pdo_sqlite`
  - `sqlite3`
  - `dom` (DOMDocument / DOMXPath)
  - `libxml`
  - `json`
- Required PHP setting:
  - `allow_url_fopen=1` (used for RSS/Atom fetching and remote VERSION check).
- HTTPS support in PHP (OpenSSL enabled).
- Write permissions on:
  - `var/data`
  - `var/cache`
  - `var/log`

### 2. Clone repository

```bash
git clone https://github.com/GreenEffect/Sympli-RSS-Fusion.git
cd Sympli-RSS-Fusion
```

### 3. Create local config

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Configure `.env`

Suggested minimum:

- `APP_URL=http://127.0.0.1:8080`
- `APP_ENV=prod` (or `dev` for debugging)
- `APP_LANG=en` (or `fr`)
- `APP_THEME=default`

Important values:

- `DB_PATH=var/data/sympli_rss_fusion.sqlite`
- `DB_PATH_DEV=var/data/sympli_rss_fusion_dev.sqlite`
- `LOG_PATH=var/log/app.log`
- `VERSION_CHECK_ENABLED=0` (disabled by default)
  - set to `1` to enable remote version checks from GitHub.

### 5. Start application

```bash
php -S 127.0.0.1:8080 -t public
```

Then open:

- `http://127.0.0.1:8080`

### 6. What happens on first request

Startup runs an idempotent installer:

- creates `.env` from `.env.example` if missing,
- creates runtime directories (`var/cache`, `var/data`, `var/log` depending on config),
- applies SQLite schema from `config/schema.sql` if needed.

No Composer step is needed.

### 7. Quick checks

- Home page loads without 500 errors.
- Feed creation works.
- SQLite file is created in `var/data`.
- XML cache files appear in `var/cache` after RSS access.
- In `dev` mode, detailed errors are displayed and logs are written.

### 8. Production quick notes

- Configure your web server with `public/` as document root.
- Set a real `APP_URL` (HTTPS recommended).
- Verify write permissions on `var/`.
- Keep `APP_ENV=prod` in production.

If you already have a working web server, the quickest path is:

- upload the project files to that server,
- update `APP_URL` in `.env` with your public instance URL,
- open that URL in your browser :)

### 9. Troubleshooting

- `Class not found`:
  - ensure `src/autoload.php` exists and is required by `public/index.php`.
- SQLite access error:
  - check permissions on `var/data` and value of `DB_PATH`.
- External feeds cannot be fetched:
  - check outbound network, DNS, SSL, and `allow_url_fopen=1`.
- Version check not visible:
  - ensure `VERSION_CHECK_ENABLED=1` in `.env`.
