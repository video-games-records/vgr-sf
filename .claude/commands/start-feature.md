Nouvelle feature démarrée : $ARGUMENTS

On part de zéro sur cette feature. Règles pour toute la session :

- Les commandes Git sont autorisées, mais demande toujours confirmation avant d'en exécuter une (commit, push, etc.)
- Ne PAS utiliser les migrations Doctrine
- Tout changement de schéma SQL doit être écrit directement dans `migration.sql` à la racine du projet

Si le schéma doit évoluer pendant le dev :
1. Modifier l'entité
2. Écrire le SQL correspondant dans `migration.sql` (CREATE TABLE, ALTER TABLE, etc.)

## Fichier de suivi

Au démarrage, demande-moi :
- L'objectif de la feature (ce qu'on cherche à faire)
- S'il y a du SQL à jouer sur le schéma

Puis crée le fichier `.claude/feature-status/[nom-feature].md` avec la structure suivante :

```markdown
# Feature : [nom]

## Objectif
[description de ce qu'on cherche à faire]

## SQL (migration.sql)
[les instructions SQL ajoutées, ou "aucune pour l'instant"]

## Fichiers modifiés
- [liste au fur et à mesure]

## État
[ce qui est fait, ce qui reste à faire]
```

Met à jour ce fichier au fil de la session à chaque fois qu'un fichier est modifié, et fais un point sur l'état en fin de session.

## Qualité du code

En fin de session, lance `make qa` (phpcs + phpstan) et corrige toutes les erreurs avant de terminer.
