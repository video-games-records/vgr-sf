# Migration vers Doctrine ORM 3.x - Conversion des types `array` vers `json`

## Contexte

Doctrine ORM 3 a supprimé le type de colonne `array` (qui utilisait la sérialisation PHP).
Il faut utiliser le type `json` à la place.

## Entités concernées

- **User** (`pnu_user.roles`)
- **Group** (`pnu_group.roles`)

## Scripts de migration disponibles

Les scripts suivants permettent de convertir les données existantes du format PHP sérialisé vers JSON :

### 1. Migration des rôles utilisateurs

```bash
php bin/migrate-user-roles-to-json.php
```

Ce script :
- Lit tous les utilisateurs de la table `pnu_user`
- Convertit le champ `roles` du format `a:0:{}` (PHP serialize) vers `[]` (JSON)
- Ignore les enregistrements déjà au format JSON (safe pour réexécution)
- Affiche un rapport détaillé de la migration

### 2. Migration des rôles des groupes

```bash
php bin/migrate-group-roles-to-json.php
```

Ce script :
- Lit tous les groupes de la table `pnu_group`
- Convertit le champ `roles` du format PHP serialize vers JSON
- Ignore les enregistrements déjà au format JSON (safe pour réexécution)
- Affiche un rapport détaillé de la migration

## Procédure de migration en production

### Étape 1 : Sauvegarde

**IMPORTANT** : Avant toute migration, faire une sauvegarde complète de la base de données :

```bash
mysqldump -u user -p database_name > backup_avant_migration_$(date +%Y%m%d_%H%M%S).sql
```

### Étape 2 : Vérification de l'environnement

Vérifier que la variable `DATABASE_URL` est correctement configurée dans `.env` ou `.env.local` :

```bash
php -r "require 'vendor/autoload.php'; (new Symfony\Component\Dotenv\Dotenv())->bootEnv('.env'); echo \$_ENV['DATABASE_URL'] . PHP_EOL;"
```

### Étape 3 : Exécution des migrations

Exécuter les deux scripts dans l'ordre :

```bash
# 1. Migration des groupes (car les utilisateurs dépendent des groupes)
php bin/migrate-group-roles-to-json.php

# 2. Migration des utilisateurs
php bin/migrate-user-roles-to-json.php
```

### Étape 4 : Vérification

Vérifier que les données ont été correctement converties :

```sql
-- Vérifier un échantillon de groupes
SELECT id, name, roles FROM pnu_group LIMIT 5;

-- Vérifier un échantillon d'utilisateurs
SELECT id, username, roles FROM pnu_user LIMIT 5;
```

Le format devrait être JSON :
- `[]` pour un tableau vide
- `["ROLE_ADMIN","ROLE_USER"]` pour un tableau avec des valeurs

### Étape 5 : Déploiement du code

Une fois la migration des données réussie, déployer le nouveau code avec les entités mises à jour.

### Étape 6 : Vider le cache

```bash
php bin/console cache:clear --env=prod
```

## Rollback en cas de problème

Si un problème survient après la migration :

1. Restaurer la sauvegarde de la base de données :
   ```bash
   mysql -u user -p database_name < backup_avant_migration_YYYYMMDD_HHMMSS.sql
   ```

2. Revenir au code précédent avec les types `array`

## Caractéristiques des scripts

- ✅ **Idempotents** : Peuvent être réexécutés sans danger
- ✅ **Safe** : Détectent automatiquement les données déjà converties
- ✅ **Informatifs** : Affichent un rapport détaillé avec statistiques
- ✅ **Configurables** : Utilisent `DATABASE_URL` depuis `.env`
- ✅ **Gestion d'erreurs** : Continuent en cas d'erreur sur un enregistrement
- ✅ **Exit codes** : Retournent 0 en cas de succès, 1 en cas d'erreur

## Exemples de conversions

### Groupes

```
Avant : a:1:{i:0;s:16:"ROLE_SUPER_ADMIN";}
Après : ["ROLE_SUPER_ADMIN"]

Avant : a:0:{}
Après : []
```

### Utilisateurs

```
Avant : a:2:{i:0;s:10:"ROLE_ADMIN";i:1;s:9:"ROLE_USER";}
Après : ["ROLE_ADMIN","ROLE_USER"]

Avant : a:0:{}
Après : []
```

## Tests recommandés après migration

1. **Login** : Tester la connexion avec différents utilisateurs sur `/login`
2. **Permissions** : Vérifier que les rôles sont bien appliqués
3. **Admin** : Accéder à l'interface Sonata Admin sur `/admin/dashboard` (redirection automatique vers `/login` si non connecté)
4. **Groupes** : Vérifier que les rôles des groupes sont bien hérités par les utilisateurs

## Notes sur l'authentification

- Un **seul point de login** : `/login` pour toute l'application (front et admin)
- Les firewalls `admin` et `main` partagent le même contexte (`context: user`)
- Une fois connecté sur `/login`, vous avez accès à l'admin si vous avez les rôles nécessaires
- Les routes admin protégées redirigent automatiquement vers `/login` si non authentifié

## Support

En cas de problème lors de la migration, consulter les logs des scripts qui affichent :
- Le nombre total d'enregistrements traités
- Le nombre d'enregistrements mis à jour
- Le nombre d'enregistrements déjà en JSON (ignorés)
- Le nombre d'erreurs avec détails
