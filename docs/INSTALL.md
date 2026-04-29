# Installation Guide / Guide d'installation

This document describes a full installation from zero.
Ce document décrit une installation complète depuis zéro.

## FR - Installation complète

### Pourquoi `public/` comme racine web

Sympli RSS Fusion sert le site depuis `public/` pour garder le reste du projet hors exposition HTTP.

Concrètement, ce choix évite d'exposer:

- `.env` (configuration),
- `var/data/*.sqlite` (base),
- `var/log/*` (journaux),
- `src/*` (code PHP interne).

### 1. Pré-requis

- PHP: version 8.1+ (recommended 8.2 or 8.3).
- PHP extensions required:
  - `pdo_sqlite`
  - `sqlite3`
  - `dom` (DOMDocument / DOMXPath)
  - `libxml`
  - `json`
- PHP setting / extensions required:
  - `curl` extension (required for secure HTTP fetching of feeds)
  - `allow_url_fopen` is no longer required for feed fetching (the application uses cURL internally)
  - PHP upload settings: `upload_max_filesize` and `post_max_size` should be configured to allow uploads of at least `1M` (we recommend `2M`) to support import operations via the web UI.
- HTTPS support for remote requests (OpenSSL enabled in PHP).
- Write permissions on:
  - `var/data`
  - `var/cache`
  - `var/log`

Note (FR): les écritures du cache et des journaux utilisent désormais une écriture atomique (fichier temporaire + renommage) et un verrouillage de fichier pour réduire les risques de corruption en cas d'accès concurrent.

### 2. Récupérer le projet

```bash
git clone https://github.com/GreenEffect/Sympli-RSS-Fusion.git
cd Sympli-RSS-Fusion
```

### 3. Créer votre configuration locale

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Paramétrer `.env`

Minimum recommandé:

- `APP_URL=http://127.0.0.1:8080`
- `APP_ENV=prod` (ou `dev` pour debug)
- `APP_LANG=fr` (ou `en`)
- `APP_THEME=default`

Configuration importante:

- `DB_PATH=var/data/sympli_rss_fusion.sqlite`
- `DB_PATH_DEV=var/data/sympli_rss_fusion_dev.sqlite`
- `LOG_PATH=var/log/app.log`
- `VERSION_CHECK_ENABLED=0` (désactivé par défaut)
  - passer à `1` pour activer la vérification de nouvelle version GitHub.

### ▶️ 5. Démarrer l'application (local)

```bash
php -S 127.0.0.1:8080 -t public
```

Puis ouvrir:

- `http://127.0.0.1:8080`

### 6. Ce qui se passe au premier accès

Le bootstrap lance une installation idempotente:

- création de `.env` si absent (depuis `.env.example`),
- création des dossiers de travail (`var/cache`, `var/data`, `var/log` selon config),
- application du schéma SQLite (`config/schema.sql`) si nécessaire.

Aucune étape Composer n'est requise.

### 7. Vérifications rapides

- La page d'accueil s'affiche sans erreur 500.
- La création d'un flux fonctionne.
- Un fichier SQLite est créé dans `var/data`.
- Le cache XML apparaît dans `var/cache` après consultation d'un RSS.
- En mode `dev`, les erreurs détaillées sont visibles et les logs sont alimentés.

### 🌐 8. Mise en production (résumé)

- Configurer votre serveur web avec `public/` comme document root.
- Configurer une vraie `APP_URL` (HTTPS recommandé).
- Vérifier les droits d'écriture sur `var/`.
- Garder `APP_ENV=prod` en production.

Exemples minimaux:

- Apache (VirtualHost):

```apache
<VirtualHost *:80>
  ServerName rssfusion.local
  DocumentRoot /var/www/Sympli-RSS-Fusion/public

  <Directory /var/www/Sympli-RSS-Fusion/public>
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

- Nginx:

```nginx
server {
  listen 80;
  server_name rssfusion.local;
  root /var/www/Sympli-RSS-Fusion/public;
  index index.php;

  location / {
    try_files $uri /index.php?$query_string;
  }

  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
  }
}
```

- Hébergement mutualisé:
  - ouvrir le panneau de gestion du domaine,
  - régler le document root vers `.../Sympli-RSS-Fusion/public`,
  - laisser les autres dossiers du projet hors web.

Si vous avez déjà un serveur web opérationnel, le chemin le plus simple est:

- déposer les fichiers du projet sur le serveur,
- régler le document root vers `.../Sympli-RSS-Fusion/public`,
- éditer `APP_URL` dans `.env` avec l'URL publique de votre instance,
- ouvrir cette URL dans votre navigateur :)

### 🛠️ 9. Dépannage

- `Class not found`:
  - vérifier que `src/autoload.php` est présent et chargé par `public/index.php`.
- Erreur SQLite (fichier inaccessible):
  - vérifier les droits sur `var/data` et la valeur de `DB_PATH`.
- Impossible de récupérer les flux externes:
  - vérifier internet sortant, DNS, SSL, et l'extension `curl` de PHP.
  - note: l'application effectue des contrôles supplémentaires (schéma http(s), résolution DNS et filtrage d'adresses privées) ; si une URL est refusée elle peut être bloquée pour des raisons de sécurité.
- Vérification de version inactive:
  - vérifier `VERSION_CHECK_ENABLED=1` dans `.env`.

- Mon hébergeur pointe la racine du dépôt:
  - privilégier le changement de document root vers `public/` (recommandé),
  - si l'hébergeur impose `public_html`, utiliser un lien symbolique `public_html -> .../Sympli-RSS-Fusion/public` quand disponible,
  - éviter d'exposer la racine du dépôt directement sur le web.

### ✅ 10. Checklist de validation post-install

1. La page `/` répond sans erreur 500.
2. La création d'un flux produit une URL `/rss/{token}` valide.
3. Le fichier SQLite est créé dans `var/data`.
4. Le cache XML est créé dans `var/cache` après lecture du RSS.
5. Les fichiers `.env` et `src/` ne sont pas accessibles via le navigateur.

Routes import/export disponibles en plus du JSON:

- `POST /import-master-opml`
- `GET /export-master-opml?token=...`
- `GET /manage/{token}/export-opml`
- `POST /manage/{token}/import-opml`

---

## EN - Full setup

### Why `public/` as web root

Sympli RSS Fusion serves HTTP traffic from `public/` so that internal files remain outside direct web access.

This prevents exposing:

- `.env` (configuration),
- `var/data/*.sqlite` (database),
- `var/log/*` (logs),
- `src/*` (internal PHP code).

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

Note (EN): cache and log writes now use atomic write (temp file + rename) and file locking to reduce the risk of corruption under concurrent access.

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

### ▶️ 5. Start application

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

### 🌐 8. Production quick notes

- Configure your web server with `public/` as document root.
- Set a real `APP_URL` (HTTPS recommended).
- Verify write permissions on `var/`.
- Keep `APP_ENV=prod` in production.

Minimal examples:

- Apache (VirtualHost):

```apache
<VirtualHost *:80>
  ServerName rssfusion.local
  DocumentRoot /var/www/Sympli-RSS-Fusion/public

  <Directory /var/www/Sympli-RSS-Fusion/public>
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>
```

- Nginx:

```nginx
server {
  listen 80;
  server_name rssfusion.local;
  root /var/www/Sympli-RSS-Fusion/public;
  index index.php;

  location / {
    try_files $uri /index.php?$query_string;
  }

  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
  }
}
```

- Shared hosting:
  - open your domain settings panel,
  - set the domain document root to `.../Sympli-RSS-Fusion/public`,
  - keep all other project folders out of direct web access.

If you already have a working web server, the quickest path is:

- upload the project files to that server,
- set the domain document root to `.../Sympli-RSS-Fusion/public`,
- update `APP_URL` in `.env` with your public instance URL,
- open that URL in your browser :)

### 🛠️ 9. Troubleshooting

- `Class not found`:
  - ensure `src/autoload.php` exists and is required by `public/index.php`.
- SQLite access error:
  - check permissions on `var/data` and value of `DB_PATH`.
- External feeds cannot be fetched:
  - check outbound network, DNS, SSL, and `allow_url_fopen=1`.
- Version check not visible:
  - ensure `VERSION_CHECK_ENABLED=1` in `.env`.

- Host points to repository root:
  - prefer changing document root to `public/` (recommended),
  - if host enforces `public_html`, use a symlink `public_html -> .../Sympli-RSS-Fusion/public` when available,
  - avoid exposing repository root directly on the web.

### ✅ 10. Post-install validation checklist

1. `/` loads without 500 errors.
2. Creating a feed returns a working `/rss/{token}` URL.
3. SQLite file exists in `var/data`.
4. XML cache appears in `var/cache` after RSS access.
5. `.env` and `src/` are not directly reachable from browser.

Additional import/export routes available (besides JSON):

- `POST /import-master-opml`
- `GET /export-master-opml?token=...`
- `GET /manage/{token}/export-opml`
- `POST /manage/{token}/import-opml`

Note: JSON import uploads via the web UI are restricted to 1 MiB and the uploaded file's MIME/type is validated by the application. If you encounter "file too large" errors during import, verify `upload_max_filesize` and `post_max_size` in your PHP configuration.
