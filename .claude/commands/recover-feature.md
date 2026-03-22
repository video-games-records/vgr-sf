Récupération du contexte d'une feature en cours : $ARGUMENTS

Je n'ai pas de fichier de suivi pour cette feature. Règles strictes pour toute la session :

- Ne jamais exécuter de commande Git, quelle que soit la situation
- Ne PAS utiliser les migrations Doctrine
- Tout changement de schéma SQL doit être écrit directement dans `migration.sql` à la racine du projet

## Reconstruction du contexte

Analyse le projet pour reconstituer l'état de la feature :

1. Lis `migration.sql` à la racine si il existe
2. Identifie les fichiers récemment modifiés (entités, repositories, controllers, templates, services...)
3. Déduis ce qui a déjà été fait

Puis demande-moi :
- L'objectif de la feature (ce qu'on cherche à faire)
- Confirmation des fichiers que tu as identifiés comme modifiés
- Si le SQL de migration.sql a déjà été joué en base ou non

Ensuite crée le fichier `.claude/feature-status/[nom-feature].md` avec la structure suivante :

```markdown
# Feature : [nom]

## Objectif
[description de ce qu'on cherche à faire]

## SQL (migration.sql)
[les instructions SQL, ou "aucune pour l'instant"] — [joué / pas encore joué]

## Fichiers modifiés
- [liste reconstituée]

## État
[ce qui est fait, ce qui reste à faire]
```

Met à jour ce fichier au fil de la session à chaque fois qu'un fichier est modifié, et fais un point sur l'état en fin de session.

## Qualité du code

En fin de session, lance `make qa` (phpcs + phpstan) et corrige toutes les erreurs avant de terminer.
