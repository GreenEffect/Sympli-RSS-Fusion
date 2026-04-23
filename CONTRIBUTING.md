# Contributing

Merci pour votre intérêt pour Sympli RSS Fusion.

## Prérequis

- PHP 8.2+
- Serveur web (optionnel)

## Démarrage local

1. Copier `.env.example` vers `.env`.
2. Lancer `php -S 127.0.0.1:8080 -t public`.
3. Ouvrir `http://127.0.0.1:8080`.

## Bonnes pratiques de contribution

- Ouvrir une issue pour décrire le besoin (bug ou feature).
- Proposer des modifications minimales et ciblées.
- Respecter le style existant (PHP strict types, code clair, commentaires utiles).
- Mettre à jour la documentation quand un comportement change.
- Vérifier la syntaxe PHP avant soumission:

```bash
find . -name "*.php" -type f -print0 | xargs -0 -n1 php -l
```

## Pull Request

- Expliquer le contexte et l'impact utilisateur.
- Lister les routes/fichiers modifiés.
- Ajouter une note de test manuel.
- Si pertinent, ajouter des captures écran