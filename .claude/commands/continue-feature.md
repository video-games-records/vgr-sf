Reprise de la feature : $ARGUMENTS

Règles strictes pour cette session :

- Ne jamais exécuter de commande Git, quelle que soit la situation
- Ne PAS utiliser les migrations Doctrine
- Tout changement de schéma SQL doit être écrit directement dans `migration.sql` à la racine du projet

## Fichier de suivi

Au démarrage, lis le fichier `.claude/feature-status/[nom-feature].md` et fais un résumé de :
- L'objectif de la feature
- Le SQL déjà écrit dans migration.sql
- Les fichiers déjà modifiés
- Où on en était

Puis demande-moi ce qu'on attaque dans cette session.

Met à jour ce fichier au fil de la session à chaque fois qu'un fichier est modifié, et fais un point sur l'état en fin de session.

## Qualité du code

En fin de session, lance `make qa` (phpcs + phpstan) et corrige toutes les erreurs avant de terminer.
