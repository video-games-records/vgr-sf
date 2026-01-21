# Contexte VideoGamesRecords.Igdb

## Vue d'ensemble

Le contexte **VideoGamesRecords.Igdb** gère l'intégration avec l'API IGDB (Internet Game Database) pour importer et synchroniser les données de jeux vidéo. Ce contexte suit l'architecture DDD et utilise le bundle **video-games-records/igdb-bundle** comme source de données externe.

## Fonctionnalités

### Entités IGDB Supportées
- **Game** : Informations complètes sur les jeux vidéo
- **Genre** : Classifications des genres de jeux
- **Platform** : Plateformes de jeu (consoles, PC, mobile)
- **PlatformType** : Types de plateformes (console, arcade, etc.)
- **PlatformLogo** : Logos des plateformes avec métadonnées d'images

### Import de Données
- Import automatisé via commandes CLI
- Synchronisation avec l'API IGDB
- Services d'import configurables
- Gestion des erreurs et logs d'import

### Sécurité
- Interfaces admin **lecture seule** uniquement
- Pas de modification manuelle des données IGDB
- Protection de l'intégrité des données

## Structure du Contexte

```
src/BoundedContext/VideoGamesRecords/Igdb/
├── Domain/
│   ├── Entity/                     # Entités métier IGDB
│   │   ├── Game.php               # Jeu vidéo
│   │   ├── Genre.php              # Genre de jeu
│   │   ├── Platform.php           # Plateforme
│   │   ├── PlatformType.php       # Type de plateforme
│   │   └── PlatformLogo.php       # Logo de plateforme
│   ├── Repository/                 # Interfaces des repositories (vides)
│   └── ValueObject/               # Value Objects (à développer)
├── Application/
│   ├── Command/                   # Commandes d'import
│   │   ├── ImportPlatformTypesCommand.php
│   │   ├── ImportPlatformLogosCommand.php
│   │   ├── ImportPlatformsCommand.php
│   │   ├── ImportGenresCommand.php
│   │   ├── ImportGamesCommand.php
│   │   └── SearchAndImportGamesCommand.php
│   └── Service/
│       └── IgdbImportService.php  # Service principal d'import
├── Infrastructure/
│   ├── Client/                    # Client IGDB et endpoints
│   │   ├── IgdbClient.php
│   │   └── Endpoint/
│   │       └── PlatformTypeEndpoint.php
│   ├── Doctrine/
│   │   └── Repository/            # Repositories Doctrine
│   │       ├── GameRepository.php
│   │       ├── GenreRepository.php
│   │       ├── PlatformRepository.php
│   │       ├── PlatformTypeRepository.php
│   │       └── PlatformLogoRepository.php
│   └── EventListener/             # Event listeners (à développer)
├── Presentation/
│   ├── Admin/                     # Sonata Admin (lecture seule)
│   │   ├── GameAdmin.php
│   │   ├── GenreAdmin.php
│   │   ├── PlatformAdmin.php
│   │   ├── PlatformTypeAdmin.php
│   │   └── PlatformLogoAdmin.php
│   └── Api/
│       └── Controller/            # API Controllers (à développer)
└── Tests/
    ├── Factory/                   # Factories pour les tests
    │   ├── GameFactory.php
    │   ├── GenreFactory.php
    │   ├── PlatformFactory.php
    │   ├── PlatformTypeFactory.php
    │   └── PlatformLogoFactory.php
    └── Story/
        └── IgdbStory.php         # Stories pour les fixtures
```

## Entités Principales

### Game (Jeu)
```php
// Relations principales
- genres (ManyToMany avec Genre)
- platforms (ManyToMany avec Platform)
- parentGame (ManyToOne, pour les versions/extensions)
- versionChildren (OneToMany, versions du jeu)

// Propriétés importantes
- name (string) : Nom du jeu
- slug (string) : Identifiant URL-friendly
- summary (text) : Résumé du jeu
- storyline (text) : Histoire du jeu
- releaseDate (DateTime) : Date de sortie
- url (string) : URL IGDB du jeu
```

### Genre
```php
// Propriétés
- name (string) : Nom du genre
- slug (string) : Identifiant URL-friendly
- url (string) : URL IGDB du genre
```

### Platform (Plateforme)
```php
// Relations
- platformType (ManyToOne avec PlatformType)
- platformLogos (OneToMany avec PlatformLogo)

// Propriétés importantes
- name (string) : Nom de la plateforme
- slug (string) : Identifiant URL-friendly
- abbreviation (string) : Abréviation (ex: "PS5", "Xbox")
- generation (int) : Génération de la console
```

### PlatformType
```php
// Propriétés
- name (string) : Type (Console, PC, Arcade, etc.)
- slug (string) : Identifiant URL-friendly
```

### PlatformLogo
```php
// Relations
- platform (ManyToOne avec Platform)

// Propriétés d'image
- url (string) : URL de l'image
- width (int) : Largeur en pixels
- height (int) : Hauteur en pixels
- imageId (string) : ID unique de l'image IGDB
```

## Commandes d'Import

### Ordre d'exécution recommandé

```bash
# 1. Types de plateformes (dépendance de base)
php bin/console igdb:import:platform-types

# 2. Logos de plateformes
php bin/console igdb:import:platform-logos

# 3. Plateformes (dépend des types et logos)
php bin/console igdb:import:platforms

# 4. Genres
php bin/console igdb:import:genres

# 5. Jeux (dépend des genres et plateformes)
php bin/console igdb:import:games

# 6. Recherche et import spécifique de jeux
php bin/console igdb:search-import:games
```

### Commandes disponibles

#### ImportPlatformTypesCommand
```bash
# Usage
php bin/console igdb:import:platform-types [--limit=50]

# Fonctionnalités
- Import de tous les types de plateformes IGDB
- Base de données pour les relations Platform
- Gestion des doublons par ID IGDB
- Création automatique des slugs
```

#### ImportPlatformLogosCommand
```bash
# Usage
php bin/console igdb:import:platform-logos [--limit=100]

# Fonctionnalités
- Import des logos de toutes les plateformes
- Métadonnées d'images (dimensions, URL)
- Support de différentes tailles d'images
- Gestion des relations avec Platform
```

#### ImportPlatformsCommand
```bash
# Usage
php bin/console igdb:import:platforms [--limit=100] [--offset=0]

# Fonctionnalités
- Import de toutes les plateformes de jeu
- Relations avec PlatformType et PlatformLogo
- Informations de génération et abréviation
- Gestion des plateformes obsolètes et actuelles
```

#### ImportGenresCommand
```bash
# Usage
php bin/console igdb:import:genres [--limit=50]

# Fonctionnalités
- Import de tous les genres IGDB
- Création automatique des slugs
- Gestion des doublons par ID IGDB
- Support des sous-genres et catégories
```

#### ImportGamesCommand
```bash
# Usage
php bin/console igdb:import:games [--limit=100] [--offset=0]

# Fonctionnalités
- Import par batch configurable
- Gestion des relations avec genres et plateformes
- Support des jeux parents/enfants (versions, extensions)
- Import des dates de sortie et métadonnées
- Logging détaillé des erreurs d'import
- Gestion des images de couverture
```

#### SearchAndImportGamesCommand
```bash
# Usage
php bin/console igdb:search-import:games [options]

# Fonctionnalités
- Recherche interactive de jeux spécifiques
- Import sélectif basé sur des critères
- Utile pour ajouter des jeux manquants
- Support de la recherche par nom, genre, plateforme
- Mode batch pour import en masse de résultats de recherche
```

## Services

### IgdbImportService
```php
// Localisation
src/BoundedContext/VideoGamesRecords/Igdb/Application/Service/IgdbImportService.php

// Responsabilités
- Orchestration des imports depuis l'API IGDB
- Transformation des données IGDB en entités Doctrine
- Gestion des relations entre entités
- Logging et gestion d'erreurs centralisée
```

## Client et Endpoints

### IgdbClient
```php
// Localisation
src/BoundedContext/VideoGamesRecords/Igdb/Infrastructure/Client/IgdbClient.php

// Fonctionnalités
- Wrapper autour du bundle video-games-records/igdb-bundle
- Configuration centralisée des credentials IGDB
- Gestion des timeouts et retry logic
- Abstraction des appels API
```

### Endpoints spécialisés
```php
// PlatformTypeEndpoint
src/BoundedContext/VideoGamesRecords/Igdb/Infrastructure/Client/Endpoint/PlatformTypeEndpoint.php

// Fonctionnalités
- Requêtes optimisées pour les types de plateformes
- Filtrage et pagination configurables
- Mapping vers les entités domain
```

## Interfaces Admin (Sonata)

### Caractéristiques communes
- **Lecture seule** : Pas d'actions create/edit/delete
- **Filtrage avancé** : Recherche par nom, slug, type, etc.
- **Relations lisibles** : Affichage des entités liées
- **Export possible** : CSV, Excel (si configuré)

### GameAdmin
```php
// Localisation
src/BoundedContext/VideoGamesRecords/Igdb/Presentation/Admin/GameAdmin.php

// Fonctionnalités
- Liste paginée des jeux
- Filtres par genre, plateforme, date de sortie
- Détail complet avec relations
- Recherche par nom et slug
```

### Autres Admin Classes
- **GenreAdmin** : Gestion des genres
- **PlatformAdmin** : Gestion des plateformes avec types et logos
- **PlatformTypeAdmin** : Types de plateformes
- **PlatformLogoAdmin** : Logos avec prévisualisation d'images

## Configuration

### Variables d'environnement requises
```bash
# .env
IGDB_CLIENT_ID=votre_client_id
IGDB_CLIENT_SECRET=votre_client_secret
```

### Configuration des services
```yaml
# config/services/igdb.yaml
services:
    # Import automatique des services du contexte
    App\BoundedContext\VideoGamesRecords\Igdb\:
        resource: '../../src/BoundedContext/VideoGamesRecords/Igdb/'
        exclude:
            - '../../src/BoundedContext/VideoGamesRecords/Igdb/Domain/Entity/'
            - '../../src/BoundedContext/VideoGamesRecords/Igdb/Tests/'

    # Configuration spécifique du client IGDB si nécessaire
    igdb.client:
        class: App\BoundedContext\VideoGamesRecords\Igdb\Infrastructure\Client\IgdbClient
        arguments:
            - '%env(IGDB_CLIENT_ID)%'
            - '%env(IGDB_CLIENT_SECRET)%'
```

## Tests

### Factories
Les factories permettent de créer des entités pour les tests :

```php
// Exemple d'utilisation
$game = GameFactory::createOne([
    'name' => 'Test Game',
    'slug' => 'test-game',
    'genres' => GenreFactory::createMany(2),
    'platforms' => PlatformFactory::createMany(3)
]);
```

### Stories
```php
// IgdbStory.php - Fixtures pour les tests d'intégration
IgdbStory::load([
    'game_rpg' => GameFactory::createOne(['name' => 'RPG Game']),
    'platform_pc' => PlatformFactory::createOne(['name' => 'PC']),
    'genre_rpg' => GenreFactory::createOne(['name' => 'Role-playing'])
]);
```

## Bonnes Pratiques

### 1. Import de données
- **Toujours** importer les dépendances avant (PlatformType -> Platform -> Game)
- **Utiliser** les options limit/offset pour les gros imports
- **Surveiller** les logs d'import pour détecter les erreurs
- **Planifier** les imports via cron pour maintenir la synchronisation

### 2. Gestion des erreurs
- **Logger** toutes les erreurs d'API dans les services
- **Gérer** les timeouts et les limitations de taux IGDB
- **Implémenter** un retry logic pour les échecs temporaires

### 3. Performance
- **Utiliser** la pagination pour les gros datasets
- **Optimiser** les requêtes avec les joins appropriés
- **Mettre en cache** les données fréquemment accédées

### 4. Sécurité
- **Jamais** exposer les credentials IGDB
- **Maintenir** les interfaces admin en lecture seule
- **Valider** toutes les données avant persistence

## Évolutions Future

### Fonctionnalités à développer
1. **API REST** pour exposer les données IGDB
2. **Synchronisation incrémentale** (delta updates)
3. **Cache intelligent** avec invalidation
4. **Webhooks IGDB** pour updates temps réel
5. **Interface de mapping** avec d'autres entités du système
6. **Metrics et monitoring** des imports

### Extensions possibles
1. **Screenshots et artworks** des jeux
2. **Informations développeur/éditeur**
3. **Notes et reviews agrégées**
4. **Données de vente et popularité**
5. **Support multilingue** des descriptions
