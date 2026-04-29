Revue de code de la feature : $ARGUMENTS

## Étape 1 : Lecture du résumé de la feature

Lis le fichier `.claude/feature-status/$ARGUMENTS.md` et extrais :
- L'objectif de la feature
- La liste des fichiers modifiés déclarés
- L'état actuel (ce qui est fait, ce qui reste)

## Étape 2 : Lecture du dernier commit

Exécute les commandes suivantes (lecture seule) pour récupérer le contenu du dernier commit sur la branche courante :

1. `git log -1 --pretty=format:"%H %s"` — pour obtenir le hash et le message du dernier commit
2. `git show --stat HEAD` — pour voir les fichiers modifiés dans ce commit
3. `git diff HEAD~1 HEAD` — pour lire le diff complet du dernier commit

Si le diff est trop long, lis directement les fichiers modifiés identifiés.

## Étape 3 : Revue de code

Effectue une revue rigoureuse sur les points suivants :

### Architecture DDD
- Les fichiers sont-ils dans les bonnes couches (Domain / Application / Infrastructure / Presentation) ?
- Pas de dépendances inversées entre couches ?
- Cohérence avec le Bounded Context concerné ?

### Qualité du code PHP
- Respect des conventions PSR (nommage, typage, visibilité)
- Pas de code mort, de TODO oubliés, de var_dump ou dd()
- Types de retour explicites, paramètres typés
- Pas de logique métier dans les Controllers (doit être dans Application/Service)
- Pas de requêtes SQL brutes dans des couches qui ne devraient pas en avoir

### Sécurité
- Pas d'injection SQL possible
- Données utilisateur correctement validées/échappées
- Routes protégées si nécessaire (IsGranted, firewall)

### Templates Twig
- Pas de style inline (`style="..."` ou balise `<style>`)
- Utilisation exclusive de classes Bootstrap 5
- Traductions présentes dans les 8 langues pour toute nouvelle clé i18n

### Performance
- Pas de requêtes N+1 (jointures manquantes dans les repositories)
- Pagination ou limit sur les listes

### Cohérence avec le fichier de suivi
- Les fichiers modifiés dans le commit correspondent-ils à ceux listés dans `.claude/feature-status/$ARGUMENTS.md` ?
- Rien d'inattendu ou hors scope ?

## Étape 4 : Rapport de revue

Présente le rapport structuré ainsi :

### Résumé
- Feature : `$ARGUMENTS`
- Dernier commit : [hash court] - [message]
- Fichiers reviewés : [liste]

### Points positifs
[Ce qui est bien fait]

### Problèmes critiques 🔴
[Bugs, failles sécu, violations DDD graves — à corriger immédiatement]

### Améliorations recommandées 🟡
[Code smell, manque de typage, conventions non respectées — à corriger avant merge]

### Suggestions mineures 🟢
[Optionnel, style, lisibilité — à discuter]

### Verdict
✅ Prêt à merger / ⚠️ Corrections requises / ❌ Bloquant

Demande-moi si je veux que tu corriges les problèmes identifiés.
