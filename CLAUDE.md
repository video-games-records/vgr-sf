# Architecture DDD - Domain-Driven Design

Ce projet utilise une architecture **Domain-Driven Design (DDD)** avec des **Bounded Contexts** pour organiser le code selon les domaines métier.

## Structure Générale

```
src/
├── SharedKernel/                           # Code partagé entre tous les contextes
│   ├── Domain/                            # Concepts partagés du domaine
│   │   └── Security/                      # Ex: SecurityEventTypeEnum
│   ├── Infrastructure/                    # Couches techniques partagées
│   └── Presentation/
│       └── Web/
│           ├── Controller/                # Controllers transversaux (ex: LogReaderController)
│           └── Resources/views/           # Templates transversaux (layout, base.html.twig)
│
└── BoundedContext/                        # Contextes métier délimités
    └── User/                              # Contexte de gestion des utilisateurs
        ├── Domain/
        │   ├── Entity/                    # Entités métier (User, SecurityEvent, Group)
        │   └── Repository/                # Interfaces des repositories
        ├── Infrastructure/
        │   ├── Doctrine/                  # Implémentations Doctrine
        │   │   └── Repository/            # Repositories concrets
        │   └── Admin/
        │       └── Extension/             # Extensions Sonata Admin
        ├── Application/
        │   └── Service/                   # Services applicatifs
        └── Presentation/
            ├── Admin/                     # Classes Admin Sonata
            ├── Api/
            │   └── Controller/            # API Controllers
            ├── Web/
            │   └── Controller/            # Web Controllers
            └── Resources/
                └── views/                 # Templates spécifiques au contexte User
```

## Principes DDD Appliqués

### 1. Bounded Contexts (Contextes Délimités)
- **User Context** : Gestion des utilisateurs, groupes, événements de sécurité
- **VideoGamesRecords.Igdb Context** : Intégration avec l'API IGDB pour les données de jeux vidéo
- **Autres contextes** peuvent être ajoutés (Product, Order, etc.)

### 2. SharedKernel (Noyau Partagé)
- Contient les concepts partagés entre contextes
- Fonctionnalités transversales (logs, layouts admin)
- Éviter les duplications entre contextes

### 3. Couches DDD
- **Domain** : Logique métier pure, entités, value objects
- **Application** : Orchestration, services applicatifs  
- **Infrastructure** : Persistence, APIs externes
- **Presentation** : Controllers, vues, APIs

## Exemples de Composants

### Contexte User
```php
// Domain Layer
src/BoundedContext/User/Domain/Entity/User.php
src/BoundedContext/User/Domain/Entity/SecurityEvent.php

// Infrastructure Layer  
src/BoundedContext/User/Infrastructure/Doctrine/Repository/UserRepository.php
src/BoundedContext/User/Infrastructure/Admin/Extension/SecurityEventStatisticsExtension.php

// Application Layer
src/BoundedContext/User/Application/Service/UserRegistrationService.php

// Presentation Layer
src/BoundedContext/User/Presentation/Admin/UserAdmin.php
src/BoundedContext/User/Presentation/Web/Controller/Admin/SecurityEventStatisticsController.php
src/BoundedContext/User/Resources/views/admin/security_statistics.html.twig
```

### SharedKernel
```php
// Domaine partagé
src/SharedKernel/Domain/Security/SecurityEventTypeEnum.php

// Infrastructure partagée
src/SharedKernel/Presentation/Web/Controller/LogReaderController.php
src/SharedKernel/Resources/views/base.html.twig
src/SharedKernel/Resources/views/admin/layout.html.twig
```

### Contexte VideoGamesRecords.Igdb
```php
// Domain Layer - Entités métier IGDB
src/BoundedContext/VideoGamesRecords/Igdb/Domain/Entity/Game.php
src/BoundedContext/VideoGamesRecords/Igdb/Domain/Entity/Genre.php
src/BoundedContext/VideoGamesRecords/Igdb/Domain/Entity/Platform.php
src/BoundedContext/VideoGamesRecords/Igdb/Domain/Entity/PlatformType.php
src/BoundedContext/VideoGamesRecords/Igdb/Domain/Entity/PlatformLogo.php

// Application Layer - Services et commandes d'import
src/BoundedContext/VideoGamesRecords/Igdb/Application/Service/IgdbImportService.php
src/BoundedContext/VideoGamesRecords/Igdb/Application/Command/ImportGamesCommand.php
src/BoundedContext/VideoGamesRecords/Igdb/Application/Command/ImportGenresCommand.php

// Infrastructure Layer - Client IGDB et repositories
src/BoundedContext/VideoGamesRecords/Igdb/Infrastructure/Client/IgdbClient.php
src/BoundedContext/VideoGamesRecords/Igdb/Infrastructure/Doctrine/Repository/GameRepository.php
src/BoundedContext/VideoGamesRecords/Igdb/Infrastructure/Client/Endpoint/PlatformTypeEndpoint.php

// Presentation Layer - Interfaces admin (lecture seule)
src/BoundedContext/VideoGamesRecords/Igdb/Presentation/Admin/GameAdmin.php
src/BoundedContext/VideoGamesRecords/Igdb/Presentation/Admin/GenreAdmin.php
src/BoundedContext/VideoGamesRecords/Igdb/Presentation/Admin/PlatformAdmin.php
```

## Configuration Twig

Les templates sont organisés par namespace :

```yaml
# config/packages/twig.yaml
twig:
    paths:
        '%kernel.project_dir%/src/SharedKernel/Resources/views': 'SharedKernel'
        '%kernel.project_dir%/src/BoundedContext/User/Resources/views': 'User'
```

Utilisation :
```twig
{# Template SharedKernel #}
{% extends '@SharedKernel/admin/layout.html.twig' %}

{# Template User Context #}
{% include '@User/admin/security_statistics.html.twig' %}
```

## Bonnes Pratiques

### 1. Isolation des Contextes
- Chaque contexte est autonome
- Pas de dépendances directes entre contextes
- Communication via events ou services partagés

### 2. Couches Respectées  
- Domain ne dépend de rien
- Application dépend du Domain
- Infrastructure dépend de Application et Domain
- Presentation dépend de Application

### 3. Naming Conventions
- Contextes : PascalCase (User, Product, Order)
- Couches : PascalCase (Domain, Infrastructure, etc.)
- Entités : Suffixe explicite (UserAdmin, SecurityEvent)

### 4. Templates
- Templates métier dans leur contexte
- Templates transversaux dans SharedKernel
- Utiliser les namespaces Twig appropriés

## Ajout d'un Nouveau Contexte

Pour ajouter un nouveau contexte (ex: Product) :

```
src/BoundedContext/Product/
├── Domain/
│   ├── Entity/
│   │   └── Product.php
│   └── Repository/
│       └── ProductRepositoryInterface.php
├── Infrastructure/
│   └── Doctrine/
│       └── Repository/
│           └── ProductRepository.php
├── Application/
│   └── Service/
│       └── ProductCatalogService.php
└── Presentation/
    ├── Admin/
    │   └── ProductAdmin.php
    └── Resources/
        └── views/
            └── admin/
                └── product_list.html.twig
```

Puis ajouter le namespace Twig :
```yaml
# config/packages/twig.yaml
'%kernel.project_dir%/src/BoundedContext/Product/Resources/views': 'Product'
```

## Documentation par Contexte

Chaque bounded context peut avoir sa propre documentation CLAUDE.md :

| Contexte | Documentation |
|----------|---------------|
| VideoGamesRecords.Core | [src/BoundedContext/VideoGamesRecords/Core/CLAUDE.md](src/BoundedContext/VideoGamesRecords/Core/CLAUDE.md) |
| VideoGamesRecords.Igdb | [src/BoundedContext/VideoGamesRecords/Igdb/CLAUDE.md](src/BoundedContext/VideoGamesRecords/Igdb/CLAUDE.md) |

---

# Bonnes Pratiques de Développement Frontend

## CSS et Styling

### Règle Principale : Utiliser Bootstrap Uniquement

Le projet utilise **Bootstrap 5** comme framework CSS. Toutes les interfaces doivent être stylées en utilisant exclusivement les classes Bootstrap.

#### ❌ À NE PAS FAIRE

1. **NE PAS** ajouter de CSS inline dans les templates Twig
   ```twig
   {# MAUVAIS #}
   <div style="color: red; margin: 20px;">Contenu</div>
   ```

2. **NE PAS** ajouter de balises `<style>` dans les templates
   ```twig
   {# MAUVAIS #}
   {% block stylesheets %}
       <style>
           .my-class { color: blue; }
       </style>
   {% endblock %}
   ```

3. **NE PAS** créer de fichiers SCSS personnalisés pour des pages spécifiques (sauf exception validée)
   ```scss
   // MAUVAIS - Éviter de créer des fichiers comme _register.scss, _login.scss, etc.
   ```

#### ✅ À FAIRE

1. **Utiliser les classes Bootstrap** pour tous les besoins de styling
   ```twig
   {# BON #}
   <div class="text-danger mt-4">Contenu</div>
   ```

2. **Utiliser les composants Bootstrap** (cards, forms, alerts, etc.)
   ```twig
   {# BON #}
   <div class="card shadow-sm">
       <div class="card-body p-4">
           <h1 class="card-title text-center mb-4">Title</h1>
       </div>
   </div>
   ```

3. **Utiliser les utilitaires Bootstrap** pour le spacing, les couleurs, la typographie, etc.
   ```twig
   {# BON #}
   <div class="container py-5">
       <div class="row justify-content-center">
           <div class="col-md-6 col-lg-5">
               <!-- Contenu -->
           </div>
       </div>
   </div>
   ```

### Ressources Bootstrap

- [Bootstrap Documentation](https://getbootstrap.com/docs/5.3/)
- [Bootstrap Utilities](https://getbootstrap.com/docs/5.3/utilities/)
- [Bootstrap Components](https://getbootstrap.com/docs/5.3/components/)

### Exceptions

Si un besoin de styling personnalisé est vraiment nécessaire et ne peut pas être résolu avec Bootstrap :

1. **Consulter** d'abord s'il existe une classe utilitaire Bootstrap
2. **Documenter** la raison de l'exception
3. **Ajouter** le CSS dans `assets/styles/app.scss` (pas dans les templates)
4. **Utiliser** une classe sémantique réutilisable

### Structure des Assets

```
assets/
├── styles/
│   ├── config/
│   │   └── _variables.scss    # Variables personnalisées
│   ├── utilities/
│   │   └── _base.scss          # Styles de base globaux
│   ├── views/
│   │   └── _home.scss          # Styles spécifiques (si vraiment nécessaire)
│   └── app.scss                # Point d'entrée principal
```

### Formulaires Symfony

Les formulaires Symfony utilisent automatiquement les thèmes Bootstrap :

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - 'bootstrap_5_layout.html.twig'
```

Cela signifie que tous les formulaires générés avec `form_widget()`, `form_label()`, etc. utilisent déjà les classes Bootstrap.