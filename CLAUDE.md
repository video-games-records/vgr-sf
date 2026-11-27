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

Chaque couche garde le namespace du contexte, par exemple pour `User` :
`Domain/Entity/User.php`, `Infrastructure/Doctrine/Repository/UserRepository.php`,
`Application/Service/UserRegistrationService.php`, `Presentation/Admin/UserAdmin.php`.
Le SharedKernel suit le même schéma sans préfixe de contexte
(ex. `SharedKernel/Domain/Security/SecurityEventTypeEnum.php`).

Voir la structure complète ci-dessus et les CLAUDE.md par contexte (section
"Documentation par Contexte") pour le détail de chaque bounded context.

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

### 5. Web Controllers — Préfixe Locale Obligatoire

Tout Web Controller (hors API) doit porter le préfixe `/{_locale}` au niveau de la **classe**, et la route spécifique au niveau de la **méthode** :

```php
#[AsController]
#[IsGranted('ROLE_USER')]
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
class MyController extends AbstractController
{
    #[Route('/my-path', name: 'app_my_route', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response { ... }
}
```

Sans ce préfixe, la route ne correspond pas aux URLs localisées (`/fr/...`) et Symfony redirige vers la home page.

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
| VideoGamesRecords.GamingPlatform | [src/BoundedContext/VideoGamesRecords/GamingPlatform/CLAUDE.md](src/BoundedContext/VideoGamesRecords/GamingPlatform/CLAUDE.md) |

---

# Internationalisation (i18n)

## Langues Supportées

Le projet supporte **8 langues** avec codes de locale spécifiques :

| Langue | Code Locale | Exemple Route |
|--------|-------------|---------------|
| 🇬🇧 Anglais | `en` | `/en/videos` |
| 🇫🇷 Français | `fr` | `/fr/videos` |
| 🇩🇪 Allemand | `de` | `/de/videos` |
| 🇪🇸 Espagnol | `es` | `/es/videos` |
| 🇮🇹 Italien | `it` | `/it/videos` |
| 🇯🇵 Japonais | `ja` | `/ja/videos` |
| 🇧🇷 Portugais (Brésil) | `pt_BR` | `/pt_BR/videos` |
| 🇨🇳 Chinois (Simplifié) | `zh_CN` | `/zh_CN/videos` |

## 🚨 RÈGLE IMPORTANTE : Traductions Obligatoires

**⚠️ QUAND TU AJOUTES UNE NOUVELLE TRADUCTION, TU DOIS LA METTRE DANS TOUS LES FICHIERS DE LANGUES !**

### Fichiers de Traduction par Contexte

**VideoGamesRecords.Core :**
```
src/BoundedContext/VideoGamesRecords/Core/Presentation/Resources/translations/
├── VgrCore.en.yml          # Anglais
├── VgrCore.fr.yml          # Français  
├── VgrCore.de.yml          # Allemand
├── VgrCore.es.yml          # Espagnol
├── VgrCore.it.yml          # Italien
├── VgrCore.ja.yml          # Japonais
├── VgrCore.pt_BR.yml       # Portugais (Brésil)
└── VgrCore.zh_CN.yml       # Chinois (Simplifié)
```

**Autres Contextes :**
- Chaque contexte a ses propres fichiers de traduction
- Suivre la même convention de nommage
- Toujours inclure les 8 langues

### ⚠️ Exception : Domaines Admin (Backoffice)

Les fichiers de traduction du **backoffice Sonata Admin** (domaine `*Admin`, ex. `VgrCoreAdmin.*.yml`)
ne suivent **pas** la règle des 8 langues : seuls `en` et `fr` existent pour ces domaines
(ex. `VgrCoreAdmin.en.yml`, `VgrCoreAdmin.fr.yml`), car le backoffice n'est utilisé qu'en interne
par l'équipe. La règle des 8 langues s'applique uniquement aux domaines **front public**
(ex. `VgrCore.*.yml`). Quand tu ajoutes une clé de traduction dans un domaine `*Admin`,
ajoute-la uniquement dans `en` et `fr`.

### Processus d'Ajout de Traduction

1. **Identifier la nouvelle clé** (ex: `player.profile.tabs.videos`)
2. **Ajouter dans EN** avec la valeur anglaise
3. **🚨 OBLIGATOIRE : Ajouter dans les 7 autres langues :**
   - `fr` : Traduction française
   - `de` : Traduction allemande  
   - `es` : Traduction espagnole
   - `it` : Traduction italienne
   - `ja` : Traduction japonaise
   - `pt_BR` : Traduction portugaise (Brésil)
   - `zh_CN` : Traduction chinoise (Simplifié)

### Exemple

```yaml
# VgrCore.en.yml
player:
  profile:
    tabs:
      videos: Videos
```

Répliquer la même clé avec sa traduction dans les 7 autres fichiers
(`.fr.yml`, `.de.yml`, `.es.yml`, `.it.yml`, `.ja.yml`, `.pt_BR.yml`, `.zh_CN.yml`)
— voir la checklist ci-dessous.

### Routes Localisées

Toutes les routes utilisent le préfixe locale obligatoire :

```php
#[Route('/{_locale}', requirements: ['_locale' => 'en|fr|de|it|ja|es|pt_BR|zh_CN'], defaults: ['_locale' => 'en'])]
```

### Utilisation dans les Templates

```twig
{{ 'player.profile.tabs.videos'|trans }}
```

### ✅ Checklist Traductions

Avant de terminer une feature qui ajoute des traductions :

- [ ] ✅ Traduction ajoutée dans VgrCore.en.yml
- [ ] ✅ Traduction ajoutée dans VgrCore.fr.yml  
- [ ] ✅ Traduction ajoutée dans VgrCore.de.yml
- [ ] ✅ Traduction ajoutée dans VgrCore.es.yml
- [ ] ✅ Traduction ajoutée dans VgrCore.it.yml
- [ ] ✅ Traduction ajoutée dans VgrCore.ja.yml
- [ ] ✅ Traduction ajoutée dans VgrCore.pt_BR.yml
- [ ] ✅ Traduction ajoutée dans VgrCore.zh_CN.yml

---

# Bonnes Pratiques de Développement Frontend

## CSS et Styling

### Règle Principale : Utiliser Bootstrap Uniquement

Le projet utilise **Bootstrap 5** comme framework CSS. Toutes les interfaces doivent être stylées en utilisant exclusivement les classes Bootstrap.

#### ❌ À NE PAS FAIRE

- CSS inline dans les templates Twig (ex. `style="color: red;"`)
- Balises `<style>` dans les templates (ex. dans `{% block stylesheets %}`)
- Fichiers SCSS personnalisés par page sans exception validée (ex. `_register.scss`, `_login.scss`)

#### ✅ À FAIRE

- Classes Bootstrap pour le styling (ex. `class="text-danger mt-4"`)
- Composants Bootstrap (cards, forms, alerts, etc. — ex. `class="card shadow-sm"`)
- Utilitaires Bootstrap pour spacing/couleurs/typographie (ex. `class="container py-5"`, `class="row justify-content-center"`)

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