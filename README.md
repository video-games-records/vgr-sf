# vgr-sf

[![CI](https://github.com/video-games-records/vgr-sf/actions/workflows/quality.yml/badge.svg?branch=dev)](https://github.com/video-games-records/vgr-sf/actions/workflows/quality.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](https://phpstan.org)
[![PHPCS](https://img.shields.io/badge/code%20style-PSR--12-blue)](https://www.php-fig.org/psr/psr-12)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Symfony](https://img.shields.io/badge/Symfony-8.0-000000?logo=symfony&logoColor=white)](https://symfony.com)

Application Symfony pour la gestion des **records de jeux vidéo**. Elle expose une API REST consommée par des clients front-end et propose une interface d'administration complète.

---

## Prérequis

- PHP 8.5+
- Composer 2
- Node.js LTS + npm
- MySQL 8.0+

---

## Stack technique

| Couche | Technologie |
|---|---|
| Framework | Symfony 8.0 |
| API | API Platform 4 |
| ORM | Doctrine ORM 3 |
| Administration | Sonata Admin 4 |
| Authentification | JWT (LexikJWT + Gesdinet Refresh Token) |
| Messagerie asynchrone | Symfony Messenger |
| Scheduler | Symfony Scheduler |
| Stockage fichiers | Flysystem + AWS S3 |
| Intégration IGDB | kris-kuiper/igdbv4 |
| Audit | DamienHarper Auditor |
| Front-end | Bootstrap 5 + Webpack Encore |
| Tests | PHPUnit 12 + Zenstruck Foundry |

---

## Installation

```bash
# Dépendances PHP
composer install

# Dépendances JS et build des assets
npm install && npm run build

# Variables d'environnement
cp .env .env.local
# Éditer .env.local avec les paramètres locaux

# Base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Clés JWT
php bin/console lexik:jwt:generate-keypair
```

---

## Architecture

Le projet suit une architecture **Domain-Driven Design (DDD)** organisée en **Bounded Contexts** :

```
src/
├── SharedKernel/           # Code transversal partagé
└── BoundedContext/
    ├── User/               # Gestion des utilisateurs, groupes, sécurité
    ├── VideoGamesRecords/
    │   ├── Core/           # Domaine métier principal (records, jeux, joueurs)
    │   ├── Badge/          # Gestion des badges (Master, Serie, Platform, Country)
    │   └── Igdb/           # Intégration API IGDB
    └── ...
```

Chaque contexte est découpé en 4 couches : **Domain** → **Application** → **Infrastructure** → **Presentation**.

---

## Commandes Make

```bash
make help          # Liste toutes les commandes disponibles
```

### Qualité de code

```bash
make phpcs         # Vérifie le style (PHPCS)
make phpcs-fix     # Corrige automatiquement le style (PHPCBF)
make phpstan       # Analyse statique (PHPStan)
make qa            # Lance phpcs + phpstan
```

### Tests

```bash
make test                   # Lance tous les tests
make test-coverage          # Tests avec couverture HTML (var/coverage/)
make test-vgr-core-api      # Tests API du contexte Core uniquement
```

### Base de données

```bash
make db-create     # Crée la base de données
make db-migrate    # Exécute les migrations
make db-rollback   # Annule la dernière migration
make db-fixtures   # Charge les fixtures
make db-reset      # Réinitialise complètement (drop + create + migrate + fixtures)
make db-local      # Base locale complète (schema:update + fixtures)
make db-test       # Base de test complète (schema:update + fixtures)
```

### Projet

```bash
make install       # Installe les dépendances Composer
make cache-clear   # Vide le cache
make cache-warmup  # Préchauffe le cache
```

### Assets

```bash
make assets-install   # Installe les assets dans public/
make assets-compile   # Compile les assets (asset-map)
```

### Serveur de développement

```bash
make serve         # Démarre le serveur Symfony + npm watch
make serve-stop    # Arrête le serveur
make serve-log     # Affiche les logs du serveur
```

### Messenger

```bash
make messenger-consume   # Consomme la queue async
```

---

## Qualité du code

Le pipeline CI vérifie automatiquement PHPCS, PHPStan et les tests sur chaque push/PR vers `dev`.

---

## Déploiement

Le déploiement est géré via GitHub Actions (`deploy.yml`) :

- **Staging** : automatique à chaque push sur `dev`
- **Production** : déclenchement manuel (`workflow_dispatch`)

Le déploiement utilise `rsync` via SSH suivi d'un `cache:clear` et, en production, d'un `asset-map:compile`.
