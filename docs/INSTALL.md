# Installation Guide

## EN

This document covers installation and production setup.

### 1. Prerequisites

- PHP 8.1+
- PHP extensions: `pdo_sqlite`, `dom`, `libxml`, `json`, `mbstring`
- PHP setting: `allow_url_fopen=1` (required for remote feed and version fetches)
- OpenSSL enabled in PHP
- Optional but recommended: `fileinfo` (improves MIME validation for JSON uploads)
- Write permissions on `var/data`, `var/cache`, `var/log`
- Web server configured with `public/` as document root

Quick check:

```bash
php -r 'echo "allow_url_fopen=" . ini_get("allow_url_fopen") . PHP_EOL;'
```

### 2. Get the project

```bash
git clone https://github.com/GreenEffect/Sympli-RSS-Fusion.git
cd Sympli-RSS-Fusion
cp .env.example .env
```

### 3. Minimal `.env` configuration

Suggested values:

- `APP_URL=http://127.0.0.1:8080`
- `APP_ENV=prod` (or `dev`)
- `APP_LANG=en` (or `fr`)
- `APP_THEME=default`
- `DB_PATH=var/data/sympli_rss_fusion.sqlite`
- `LOG_PATH=var/log/app.log`

Useful options:

- `VERSION_CHECK_ENABLED=0` to disable remote update checks.
- `RATE_FILE_TTL` and `RATE_PURGE_FREQUENCY` to tune rate-limiter file cleanup.

### 4. Local run

```bash
php -S 127.0.0.1:8080 -t public
```

Open: http://127.0.0.1:8080

### 5. First startup

Startup is idempotent:

- creates `.env` if missing,
- creates runtime directories,
- creates/updates SQLite schema from `config/schema.sql`.

For existing instances migrating source metadata (`etag`, `last_modified`):

```bash
php bin/migrate_add_source_metadata.php var/data/sympli_rss_fusion.sqlite
```

### 6. Production

Main rule: document root MUST point to `public/`.

Apache example:

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

Nginx example:

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

### 7. Quick checks

- Home page loads without 500 error.
- Feed creation returns a valid `/rss/{token}` URL.
- SQLite file exists in `var/data`.
- XML cache files appear in `var/cache`.
- `.env` and `src/` are not reachable via HTTP.

### 8. Troubleshooting

- SQLite read/write errors: verify permissions and `DB_PATH`.
- External feed fetch errors: verify DNS, SSL, outbound network, and `allow_url_fopen=1`.
- Theme not applied: verify `APP_THEME` and matching CSS in `public/themes`.
- Missing update notice: verify `VERSION_CHECK_ENABLED`.

## FR

Ce document couvre l'installation et la mise en production.

### 1. Prérequis

- PHP 8.1+
- Extensions PHP: `pdo_sqlite`, `dom`, `libxml`, `json`, `mbstring`
- Paramètre PHP: `allow_url_fopen=1` (nécessaire pour la récupération distante des flux et de version)
- OpenSSL actif dans PHP
- Optionnel mais recommandé: `fileinfo` (améliore la validation MIME des imports JSON)
- Droits d'écriture sur `var/data`, `var/cache`, `var/log`
- Serveur web configuré avec `public/` comme document root

Vérification rapide:

```bash
php -r 'echo "allow_url_fopen=" . ini_get("allow_url_fopen") . PHP_EOL;'
```

### 2. Récupérer le projet

```bash
git clone https://github.com/GreenEffect/Sympli-RSS-Fusion.git
cd Sympli-RSS-Fusion
cp .env.example .env
```

### 3. Configuration minimale `.env`

Valeurs conseillées:

- `APP_URL=http://127.0.0.1:8080`
- `APP_ENV=prod` (ou `dev`)
- `APP_LANG=fr` (ou `en`)
- `APP_THEME=default`
- `DB_PATH=var/data/sympli_rss_fusion.sqlite`
- `LOG_PATH=var/log/app.log`

Options utiles:

- `VERSION_CHECK_ENABLED=0` pour désactiver la vérification distante.
- `RATE_FILE_TTL` et `RATE_PURGE_FREQUENCY` pour ajuster la purge des fichiers de rate-limiting.

### 4. Démarrage local

```bash
php -S 127.0.0.1:8080 -t public
```

Ouvrir: http://127.0.0.1:8080

### 5. Premier démarrage

Le démarrage est idempotent:

- crée `.env` si absent,
- crée les dossiers runtime,
- crée/met à jour le schéma SQLite depuis `config/schema.sql`.

Pour une instance existante avec migration des métadonnées source (`etag`, `last_modified`):

```bash
php bin/migrate_add_source_metadata.php var/data/sympli_rss_fusion.sqlite
```

### 6. Production

Règle principale: le document root DOIT pointer vers `public/`.

Exemple Apache:

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

Exemple Nginx:

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

### 7. Vérifications rapides

- La page d'accueil s'affiche sans erreur 500.
- La création d'un flux renvoie une URL `/rss/{token}` valide.
- Le fichier SQLite existe dans `var/data`.
- Le cache XML apparaît dans `var/cache`.
- `.env` et `src/` ne sont pas accessibles en HTTP.

### 8. Dépannage

- Erreurs SQLite lecture/écriture: vérifier les droits et `DB_PATH`.
- Erreurs de récupération des flux externes: vérifier DNS, SSL, sortie réseau et `allow_url_fopen=1`.
- Thème non appliqué: vérifier `APP_THEME` et le CSS associé dans `public/themes`.
- Alerte de mise à jour absente: vérifier `VERSION_CHECK_ENABLED`.
