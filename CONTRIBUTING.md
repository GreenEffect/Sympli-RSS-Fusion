# Contributing

Merci pour votre interet pour Sympli RSS Fusion.

## Prerequis

- PHP 8.2+
- Aucun Composer requis

## Demarrage local

1. Copier `.env.example` vers `.env`.
2. Lancer `php -S 127.0.0.1:8080 -t public`.
3. Ouvrir `http://127.0.0.1:8080`.

## Bonnes pratiques de contribution

- Ouvrir une issue pour decrire le besoin (bug ou feature).
- Proposer des modifications minimales et ciblees.
- Respecter le style existant (PHP strict types, code clair, commentaires utiles).
- Mettre a jour la documentation quand un comportement change.
- Verifier la syntaxe PHP avant soumission:

```bash
find . -name "*.php" -type f -print0 | xargs -0 -n1 php -l
```

## Pull Request

- Expliquer le contexte et l'impact utilisateur.
- Lister les routes/fichiers modifies.
- Ajouter une note de test manuel.
