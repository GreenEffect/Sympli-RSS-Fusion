# Roadmap — Sympli RSS Fusion

Ce document liste les évolutions envisagées pour le projet, classées par thème et par priorité approximative. Rien n'est gravé dans le marbre : les contributions, retours d'usage et bonnes idées font évoluer les priorités.

---

## 🔒 Autonomie & vie privée

### Supprimer la dépendance à Google Fonts
**Priorité : haute**

Les thèmes chargent actuellement plusieurs polices de caractère via `fonts.googleapis.com`. Ce sont des requêtes tierces qui envoient l'IP du visiteur à Google à chaque chargement de page.

Solutions envisagées :
- Intégrer les fichiers `.woff2` directement dans `public/assets/fonts/`
- Générer un sous-ensemble minimal (latin uniquement, glyphes utilisés) via [glyphhanger](https://github.com/zachleat/glyphhanger) ou [pyftsubset](https://fonttools.readthedocs.io/)
- Mettre à jour les `@font-face` dans chaque thème pour pointer vers les fichiers locaux
- Ajouter une option `.env` `FONT_SOURCE=local|google|system` pour laisser le choix à l'hébergeur

### Sécurité réseau / SSRF & XXE
**Priorité : haute — status : implémenté**

Un ensemble de protections contre les risques SSRF et XXE a été déployé dans la version 1.1.0 :
- restrictions de schéma (`http`/`https` seulement), résolution DNS et blocage d'adresses privées/localhost, requêtes via `cURL` avec limites (1 MiB) et timeouts, et désactivation des entités externes XML.

Ces protections peuvent évoluer (support optionnel de listes blanches, journalisation plus fine, ou mode opérateur pour environnements contrôlés).

---

## ⚡ Performance

### Cache conditionnel avec ETag / Last-Modified
**Priorité : haute**

Actuellement le cache est binaire : valide ou expiré. Ajouter des en-têtes HTTP `ETag` et `Last-Modified` sur les endpoints `/feed/{token}` permettrait aux agrégateurs RSS de faire des requêtes conditionnelles (`If-None-Match`, `If-Modified-Since`) et d'économiser de la bande passante sans toucher au cache fichier.

### Récupération des sources en parallèle
**Priorité : moyenne**

La récupération des sources d'un flux master est aujourd'hui séquentielle. Si une source est lente (timeout à 15s), elle bloque toutes les suivantes. Passer à un modèle de requêtes parallèles via `curl_multi_exec` réduirait significativement le temps de génération du flux sur des agrégateurs avec plusieurs dizaines de sources.

### Page de statut des sources
**Priorité : basse**

Ajouter dans la page de gestion un indicateur visuel par source : dernière récupération réussie, code HTTP retourné, nombre d'items trouvés. Utile pour diagnostiquer rapidement une source morte ou qui a changé d'URL.

---

## 🛠️ Fonctionnalités

### Pagination / limite d'items par source
**Priorité : haute**

Permettre de configurer un nombre maximum d'items à conserver par source (ex. : "garder les 10 derniers articles de ce flux"). Évite qu'une source très prolifique noie les autres dans le flux fusionné.

### Dédoublonnage cross-flux par titre
**Priorité : moyenne**

Le dédoublonnage actuel se fait sur l'URL exacte. Or un même article peut être repris sur plusieurs agrégateurs avec des URLs différentes mais des titres identiques. Ajouter une option de dédoublonnage par similarité de titre (hash normalisé : bas de casse, suppression de la ponctuation).

### Filtres de date
**Priorité : moyenne**

Ajouter la possibilité d'exclure les articles plus anciens qu'une durée configurable par source (ex. : "ignorer les articles de plus de 7 jours"). Utile pour des sources qui republient leur archive ou agrègent du très vieux contenu.

### Planification de la mise à jour du cache
**Priorité : moyenne**

Ajouter un endpoint `GET /cron/{token-admin}` appelable par un cron système pour pré-chauffer le cache de tous les flux actifs en dehors des requêtes des agrégateurs. Réduit la latence perçue par l'utilisateur final.

### Flux master imbriqués
**Priorité : basse**

Permettre à un flux master d'inclure l'URL d'un autre flux master Sympli comme source. Ouvre la possibilité de construire des arbres de fusion : un flux "veille techno" qui agrège lui-même plusieurs flux thématiques.

### Webhook à la publication d'un nouvel item
**Priorité : basse**

Option par flux : envoyer un POST HTTP vers une URL configurée dès qu'un nouvel item apparaît dans le flux fusionné. Permet d'intégrer Sympli avec des outils d'automatisation sans polling.

---

## 🎨 Interface & expérience

### Thème sombre
**Priorité : basse**

Ajouter un thème `dark.css` ou une bascule automatique `prefers-color-scheme: dark` dans les thèmes existants.

---

## 🔧 Administration & déploiement

### CLI d'administration
**Priorité : moyenne**

Un script `bin/console` avec quelques commandes utiles :
- `purge:cache` — vide le cache fichier
- `purge:inactive` — supprime les flux sans activité depuis N jours
- `stats` — affiche le nombre de flux, sources, taille du cache
- `export:all` — exporte tous les flux en JSON

### Dockerfile optionnel en surcouche
**Priorité : basse**

Un `Dockerfile` minimal (PHP-FPM + Nginx ou Caddy) avec un `docker-compose.yml` pour faciliter le déploiement sur des environnements qui le permettent, sans forcer cette approche sur les hébergements mutualisés.
